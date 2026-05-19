<x-filament-panels::page
    @class([
        'fi-resource-view-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    @if (! empty($navigator))
        @include('filament.components.record-navigator', $navigator)
    @endif

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        @if ($this->hasInfolist())
            {{ $this->infolist }}
        @else
            <div wire:key="{{ $this->getId() }}.forms.{{ $this->getFormStatePath() }}">
                {{ $this->form }}
            </div>
        @endif
    @endif

    @if (count($relationManagers))
        <div class="relative min-h-[12rem]">
            <div
                wire:loading.delay.longer
                wire:target="activeRelationManager"
                class="absolute inset-0 z-20 flex items-center justify-center rounded-xl bg-white/70 dark:bg-gray-900/70"
            >
                <x-filament::loading-indicator class="h-10 w-10 text-primary-600" />
            </div>

            <div wire:loading.delay.longer.class="pointer-events-none opacity-50" wire:target="activeRelationManager">
                <x-filament-panels::resources.relation-managers
                    :active-locale="isset($activeLocale) ? $activeLocale : null"
                    :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
                    :content-tab-label="$this->getContentTabLabel()"
                    :content-tab-icon="$this->getContentTabIcon()"
                    :content-tab-position="$this->getContentTabPosition()"
                    :managers="$relationManagers"
                    :owner-record="$record"
                    :page-class="static::class"
                >
                    @if ($hasCombinedRelationManagerTabsWithContent)
                        <x-slot name="content">
                            @if ($this->hasInfolist())
                                {{ $this->infolist }}
                            @else
                                {{ $this->form }}
                            @endif
                        </x-slot>
                    @endif
                </x-filament-panels::resources.relation-managers>
            </div>
        </div>
    @endif
</x-filament-panels::page>
