<x-filament-panels::page.simple>
    <div class="flex justify-end mb-4">
        <button
            type="button"
            id="theme-toggle-login"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
        >
            <span id="theme-toggle-login-label">Mode clair / sombre</span>
        </button>
    </div>

    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

    <script>
        (function () {
            const key = 'theme';
            const html = document.documentElement;
            const button = document.getElementById('theme-toggle-login');
            const label = document.getElementById('theme-toggle-login-label');

            const applyTheme = (theme) => {
                const isDark = theme === 'dark';
                html.classList.toggle('dark', isDark);
                if (label) {
                    label.textContent = isDark ? 'Basculer en mode clair' : 'Basculer en mode sombre';
                }
            };

            const saved = localStorage.getItem(key) || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            applyTheme(saved);

            button?.addEventListener('click', () => {
                const next = html.classList.contains('dark') ? 'light' : 'dark';
                localStorage.setItem(key, next);
                applyTheme(next);
            });
        })();
    </script>
</x-filament-panels::page.simple>
