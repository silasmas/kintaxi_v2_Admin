<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6 sm:p-8 text-center">
            <div class="text-8xl font-bold text-amber-500 dark:text-amber-400 mb-4">403</div>
            <h2 class="text-xl font-semibold text-gray-950 dark:text-white mb-2">Accès refusé</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                Vous n'avez pas les autorisations nécessaires pour accéder à cette page. Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/admin') }}"
                   class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-gray-950/10 dark:ring-white/20 bg-primary-600 text-white hover:bg-primary-500 focus-visible:ring-2 focus-visible:ring-primary-500/50 dark:focus-visible:ring-primary-400/50">
                    <x-filament::icon icon="heroicon-o-home" class="w-5 h-5" />
                    Retour à l'accueil
                </a>
                <form action="{{ filament()->getLogoutUrl() }}" method="post" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-gray-950/10 dark:ring-white/20 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus-visible:ring-2 focus-visible:ring-primary-500/50">
                        <x-filament::icon icon="heroicon-o-arrow-left-on-rectangle" class="w-5 h-5" />
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>
