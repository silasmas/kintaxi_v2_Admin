<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

/**
 * @property Form $form
 */
class CustomLogin extends \Filament\Pages\Auth\Login
{
    public function getSubheading(): string | Htmlable | null
    {
        if (Filament::auth()->check()) {
            $user = Filament::auth()->user();
            $name = $user ? ($user->getFilamentName() ?? $user->email ?? 'Utilisateur') : 'Utilisateur';

            return "Vous êtes connecté en tant que {$name}. Pour vous connecter avec un autre compte, déconnectez-vous d'abord.";
        }

        return parent::getSubheading();
    }
    public function mount(): void
    {
        // Ne pas rediriger si déjà connecté : on affiche la page avec l'option de se déconnecter
        if (! Filament::auth()->check()) {
            $this->form->fill();
        }
    }

    public function logout(): void
    {
        Filament::auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(Filament::getLoginUrl());
    }
    /**
     * Détermine le type d'identifiant (email, phone, username) à partir de la valeur saisie.
     */
    protected function getCredentialType(string $value): string
    {
        $value = trim($value);

        if (str_contains($value, '@')) {
            return 'email';
        }

        $digitsOnly = preg_replace('/[^\d+]/', '', $value);
        if (strlen($digitsOnly) >= 8 && preg_match('/^\+?\d+$/', $digitsOnly)) {
            return 'phone';
        }

        return 'username';
    }

    /**
     * Normalise le numéro de téléphone pour la comparaison (chiffres uniquement).
     */
    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/[^\d]/', '', $phone);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim($data['login'] ?? '');
        $type = $this->getCredentialType($login);

        $credentials = [
            'password' => $data['password'],
        ];

        if ($type === 'phone') {
            $credentials['phone'] = $this->normalizePhone($login);
        } elseif ($type === 'email') {
            $credentials['email'] = $login;
        } else {
            $credentials['username'] = $login;
        }

        return $credentials;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        $actions = [$this->getAuthenticateFormAction()];

        if (Filament::auth()->check()) {
            $actions[] = Action::make('logout')
                ->label('Se déconnecter')
                ->color('gray')
                ->action('logout')
                ->icon('heroicon-o-arrow-left-on-rectangle');
        }

        return $actions;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLoginFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label(__('filament-panels::pages/auth/login.form.login.label'))
            ->placeholder(__('filament-panels::pages/auth/login.form.login.placeholder'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);

        $user = User::query()
            ->when(isset($credentials['email']), fn ($q) => $q->where('email', $credentials['email']))
            ->when(isset($credentials['phone']), function ($q) use ($credentials) {
                $driver = DB::connection()->getDriverName();
                $normalizedPhone = $credentials['phone'];
                if ($driver === 'mysql' || $driver === 'mariadb') {
                    $q->whereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '.', ''), '(', ''), ')', ''), '+', '') = ?",
                        [$normalizedPhone]
                    );
                } else {
                    $q->whereRaw(
                        "replace(replace(replace(replace(replace(replace(phone, ' ', ''), '-', ''), '.', ''), '(', ''), ')', ''), '+', '') = ?",
                        [$normalizedPhone]
                    );
                }
            })
            ->when(isset($credentials['username']), fn ($q) => $q->where('username', $credentials['username']))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->throwFailureValidationException();
        }

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            $this->throwFailureValidationException(__('filament-panels::pages/auth/login.messages.access_denied'));
        }

        Filament::auth()->login($user, $data['remember'] ?? false);
        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(?string $message = null): never
    {
        throw ValidationException::withMessages([
            'data.login' => $message ?? __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
