<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="relative flex flex-col gap-y-6">
        <div
            wire:loading.flex
            wire:target="activeTab"
            class="pointer-events-none absolute inset-0 z-30 hidden items-center justify-center rounded-xl bg-white/80 dark:bg-gray-950/80"
        >
            <div class="flex flex-col items-center gap-3">
                <x-filament::loading-indicator class="h-10 w-10 text-primary-600" />
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Chargement…</span>
            </div>
        </div>

        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>
