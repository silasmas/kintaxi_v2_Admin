<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Véhicules & chauffeurs</x-slot>

        <div
            x-data="{ tab: @entangle('activeTab') }"
            class="space-y-4"
        >
            <x-filament::tabs>
                <x-filament::tabs.item
                    alpine-active="tab === 'vehicles'"
                    x-on:click="tab = 'vehicles'"
                    :active="$activeTab === 'vehicles'"
                    wire:click="$set('activeTab', 'vehicles')"
                >
                    Véhicules récents
                </x-filament::tabs.item>
                <x-filament::tabs.item
                    alpine-active="tab === 'drivers'"
                    x-on:click="tab = 'drivers'"
                    :active="$activeTab === 'drivers'"
                    wire:click="$set('activeTab', 'drivers')"
                >
                    Chauffeurs mieux notés
                </x-filament::tabs.item>
            </x-filament::tabs>

            <div wire:loading.flex wire:target="activeTab" class="items-center justify-center py-8">
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>

            <div wire:loading.remove wire:target="activeTab">
                @if ($activeTab === 'vehicles')
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full min-w-max text-left text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Plaque</th>
                                    <th class="px-4 py-3">Marque / Modèle</th>
                                    <th class="px-4 py-3 min-w-[12rem]">Propriétaire</th>
                                    <th class="px-4 py-3">Catégorie</th>
                                    <th class="px-4 py-3">Créé le</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->getVehiclesData() as $row)
                                    <tr wire:key="vehicle-{{ $row['id'] }}" class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="px-4 py-2 font-medium">{{ $row['registration_number'] }}</td>
                                        <td class="px-4 py-2">{{ trim(($row['mark'] ?? '') . ' ' . ($row['model'] ?? '')) ?: '—' }}</td>
                                        <td class="px-4 py-2">
                                            @if ($row['owner'])
                                                <div class="flex items-center gap-2">
                                                    <x-filament.table-avatar-hover
                                                        :user="$row['owner']"
                                                        thumb-class="h-8 w-8"
                                                        :preview-w="140"
                                                        :preview-h="148"
                                                        img-max-class="max-h-[8rem] max-w-[8rem]"
                                                        wire-key-prefix="dash-owner-{{ $row['id'] }}"
                                                    />
                                                    <span class="font-medium">{{ $row['owner']->getFilamentName() }}</span>
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $row['category'] ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $row['created_at'] }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ $row['view_url'] }}" class="text-primary-600 hover:underline dark:text-primary-400">Voir</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Aucun véhicule.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full min-w-max text-left text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 min-w-[12rem]">Chauffeur</th>
                                    <th class="px-4 py-3">Note</th>
                                    <th class="px-4 py-3">Téléphone</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->getTopDriversData() as $row)
                                    <tr wire:key="driver-{{ $row['id'] }}" class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-2">
                                                <x-filament.table-avatar-hover
                                                    :user="$row['user']"
                                                    thumb-class="h-8 w-8"
                                                    :preview-w="140"
                                                    :preview-h="148"
                                                    img-max-class="max-h-[8rem] max-w-[8rem]"
                                                    wire-key-prefix="dash-driver-{{ $row['id'] }}"
                                                />
                                                <span class="font-medium">{{ $row['user']->getFilamentName() }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="font-semibold text-amber-600">{{ number_format((float) $row['rate'], 1) }}</span> / 5
                                        </td>
                                        <td class="px-4 py-2">{{ $row['phone'] ?? '—' }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ $row['view_url'] }}" class="text-primary-600 hover:underline dark:text-primary-400">Voir</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucun chauffeur noté.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
