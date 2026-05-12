<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if(\App\Models\AppPreference::query()->exists())
            <div class="mt-6 flex justify-end border-t border-gray-200 pt-4 dark:border-white/10">
                @can('update_app::preference')
                    <x-filament::button type="submit" color="primary" size="lg">
                        Enregistrer les modifications
                    </x-filament::button>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Vous n’avez pas la permission de modifier les préférences.') }}</p>
                @endcan
            </div>
        @endif
    </form>
</x-filament-panels::page>
