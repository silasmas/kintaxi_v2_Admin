<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Test d'envoi SMS Keccel
        </x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-300">
            Utilisez le bouton « Envoyer un SMS test » pour vérifier la passerelle configurée dans les opérateurs SMS ou le fichier <code>.env</code>.
            Chaque tentative est enregistrée dans l'historique SMS avec le statut HTTP, la livraison (lu chez l'opérateur) et le solde consultable depuis les opérateurs.
        </p>
    </x-filament::section>
</x-filament-panels::page>
