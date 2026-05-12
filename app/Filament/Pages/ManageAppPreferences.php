<?php

namespace App\Filament\Pages;

use App\Enums\AppPreferenceType;
use App\Filament\Resources\AppPreferenceResource;
use App\Models\AppPreference;
use App\Support\PreferenceDisplayFormatter;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class ManageAppPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.pages.manage-app-preferences';

    protected static ?string $navigationLabel = 'Préférences application';

    protected static ?string $title = 'Préférences application';

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 1;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('view_any_app::preference') ?? false;
    }

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'pref_values' => $this->buildPrefValuesState(),
        ]);
    }

    /**
     * @return array<int, string|array<int, string>|null>
     */
    protected function buildPrefValuesState(): array
    {
        $out = [];
        foreach (AppPreference::query()->orderBy('pref_name')->get() as $pref) {
            $raw = $pref->pref_value;
            $out[$pref->id] = match ($pref->pref_type) {
                AppPreferenceType::MultipleChoice => $pref->expectedOptionsList() === []
                    ? (string) ($raw ?? '')
                    : (
                        $raw === null || $raw === ''
                            ? []
                            : array_values(array_filter(array_map('trim', explode(',', (string) $raw))))
                    ),
                default => $raw,
            };
        }

        return $out;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Réglages')
                    ->description('Pour ajouter ou supprimer une option, passez par la gestion avancée (clés techniques).')
                    ->schema($this->buildPreferenceFields())
                    ->columns(1),
            ])
            ->statePath('data');
    }

    /**
     * Texte sous le titre (sans clé technique).
     */
    protected function describePreferenceForBoard(AppPreference $pref): HtmlString
    {
        $lines = [];

        $desc = $pref->pref_description;
        if (filled($desc)) {
            $lines[] = '<p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">'
                .e(PreferenceDisplayFormatter::formatHumanText($desc)).'</p>';
        }

        $typeLine = '<span class="text-xs font-medium text-gray-500 dark:text-gray-400">'.$pref->pref_type->label().'</span>';
        if ($pref->pref_expected_value) {
            $typeLine .= ' · <span class="italic text-xs text-gray-500 dark:text-gray-400">'
                .e(PreferenceDisplayFormatter::formatHumanText((string) $pref->pref_expected_value)).'</span>';
        }
        $lines[] = '<div class="text-xs">'.$typeLine.'</div>';

        return new HtmlString(implode('', $lines));
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected function buildPreferenceFields(): array
    {
        $preferences = AppPreference::query()->orderBy('pref_name')->get();
        if ($preferences->isEmpty()) {
            return [
                Forms\Components\Placeholder::make('empty')
                    ->content(
                        new HtmlString(
                            '<p class="text-sm text-gray-500 dark:text-gray-400">'
                            .e('Aucune préférence en base. Créez des entrées via « Gestion avancée (clés & types) ».')
                            .'</p>'
                        )
                    ),
            ];
        }

        return $preferences->map(function (AppPreference $pref): Forms\Components\Grid {
            $fieldName = 'pref_values.'.$pref->id;

            $title = PreferenceDisplayFormatter::formatHumanText($pref->pref_name) ?? $pref->pref_name;

            $labelBlock = Forms\Components\Placeholder::make('label_'.$pref->id)
                ->label('')
                ->content(new HtmlString(
                    '<div class="space-y-2 py-1">'
                    .'<div class="text-base font-semibold text-gray-950 dark:text-white">'.e($title).'</div>'
                    .$this->describePreferenceForBoard($pref)->toHtml()
                    .'</div>'
                ));

            $valueField = match ($pref->pref_type) {
                AppPreferenceType::Text => Forms\Components\Textarea::make($fieldName)
                    ->label('Valeur')
                    ->rows(3)
                    ->maxLength(1000),

                AppPreferenceType::Number => Forms\Components\TextInput::make($fieldName)
                    ->label('Valeur')
                    ->numeric()
                    ->maxLength(1000),

                AppPreferenceType::Radio => $this->radioField($pref, $fieldName),

                AppPreferenceType::MultipleChoice => $this->checkboxListField($pref, $fieldName),
            };

            return Forms\Components\Grid::make(12)
                ->schema([
                    $labelBlock->columnSpan(['default' => 12, 'md' => 4]),
                    $valueField->columnSpan(['default' => 12, 'md' => 8]),
                ])
                ->extraAttributes([
                    'class' => 'border-b border-gray-200 pb-6 pt-2 dark:border-white/10 last:border-0 last:pb-0',
                ]);
        })->all();
    }

    protected function radioField(AppPreference $pref, string $fieldName): Forms\Components\Component
    {
        $options = $pref->expectedOptionsList();
        if ($options === []) {
            return Forms\Components\TextInput::make($fieldName)
                ->label('Valeur')
                ->helperText('Ajoutez des options dans la fiche (gestion avancée) pour afficher des boutons radio.')
                ->maxLength(1000);
        }

        $map = [];
        foreach ($options as $opt) {
            $map[$opt] = PreferenceDisplayFormatter::formatHumanText($opt);
        }

        return Forms\Components\Radio::make($fieldName)
            ->label('Valeur')
            ->options($map)
            ->inline()
            ->inlineLabel(false);
    }

    protected function checkboxListField(AppPreference $pref, string $fieldName): Forms\Components\Component
    {
        $options = $pref->expectedOptionsList();
        if ($options === []) {
            return Forms\Components\Textarea::make($fieldName)
                ->label('Valeurs (virgules)')
                ->helperText('Définissez les options dans la gestion avancée pour utiliser des cases à cocher.')
                ->rows(2)
                ->maxLength(1000);
        }

        $map = [];
        foreach ($options as $opt) {
            $map[$opt] = PreferenceDisplayFormatter::formatHumanText($opt);
        }

        return Forms\Components\CheckboxList::make($fieldName)
            ->label('Valeurs actives')
            ->options($map)
            ->columns(2)
            ->gridDirection('row')
            ->bulkToggleable(false);
    }

    public function save(): void
    {
        if (! auth()->user()?->can('update_app::preference')) {
            Notification::make()
                ->title('Permission refusée')
                ->danger()
                ->send();

            return;
        }

        $state = $this->form->getState();
        /** @var array<int|string, mixed> $values */
        $values = $state['pref_values'] ?? [];

        foreach (AppPreference::query()->get() as $pref) {
            if (! array_key_exists($pref->id, $values)) {
                continue;
            }

            $raw = $values[$pref->id];
            $normalized = $this->normalizeSavedValue($pref, $raw);

            if ($this->validateValue($pref, $normalized) === false) {
                return;
            }

            $pref->update([
                'pref_value' => $normalized,
                'updated_by' => auth()->id(),
            ]);
        }

        $this->form->fill([
            'pref_values' => $this->buildPrefValuesState(),
        ]);

        Notification::make()
            ->title('Préférences enregistrées')
            ->success()
            ->send();
    }

    protected function normalizeSavedValue(AppPreference $pref, mixed $raw): ?string
    {
        if ($pref->pref_type === AppPreferenceType::MultipleChoice) {
            if ($pref->expectedOptionsList() === []) {
                $s = is_string($raw) ? trim($raw) : '';

                return $s === '' ? null : $s;
            }

            if (! is_array($raw)) {
                return null;
            }

            $selected = array_values(array_filter($raw, fn ($v) => $v !== null && $v !== ''));

            return $selected === [] ? null : implode(',', $selected);
        }

        if (is_array($raw)) {
            return null;
        }

        return $raw === null || $raw === '' ? null : (string) $raw;
    }

    protected function validateValue(AppPreference $pref, mixed $normalized): bool
    {
        if ($pref->pref_type === AppPreferenceType::Number && $normalized !== null && ! is_numeric($normalized)) {
            Notification::make()->title('Valeur non numérique pour « '.$pref->pref_name.' »')->danger()->send();

            return false;
        }

        if ($pref->pref_type === AppPreferenceType::Radio && $normalized !== null) {
            $allowed = $pref->expectedOptionsList();
            if ($allowed !== [] && ! in_array((string) $normalized, $allowed, true)) {
                Notification::make()->title('Valeur non valide pour « '.$pref->pref_name.' »')->danger()->send();

                return false;
            }
        }

        if ($pref->pref_type === AppPreferenceType::MultipleChoice && $normalized !== null) {
            $allowed = $pref->expectedOptionsList();
            if ($allowed === []) {
                return true;
            }
            $parts = array_filter(array_map('trim', explode(',', (string) $normalized)));
            foreach ($parts as $p) {
                if (! in_array($p, $allowed, true)) {
                    Notification::make()->title('Option non autorisée pour « '.$pref->pref_name.' » : '.$p)->danger()->send();

                    return false;
                }
            }
        }

        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('liste')
                ->label('Gestion avancée (clés & types)')
                ->url(AppPreferenceResource::getUrl())
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray'),
        ];
    }
}
