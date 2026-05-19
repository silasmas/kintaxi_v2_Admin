@php
    $record = $getRecord();
    $photoUrl = filled($record->photos) ? trim((string) $record->photos) : null;
    if ($photoUrl && ! str_starts_with($photoUrl, 'http')) {
        $photoUrl = asset(ltrim($photoUrl, '/'));
    }

    $driverReview = null;
    if ($record->driver_id && $record->passenger_id) {
        $driverReview = $record->reviews
            ->first(fn ($review) => (int) $review->reviewee_id === (int) $record->driver_id
                && (int) $review->reviewer_id === (int) $record->passenger_id);
    }
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <p class="mb-2 text-sm font-medium text-gray-950 dark:text-white">Photo siège arrière (chauffeur)</p>
        @if ($photoUrl)
            <a href="{{ $photoUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block">
                <img
                    src="{{ $photoUrl }}"
                    alt="Photo siège arrière"
                    class="max-h-56 rounded-lg border border-gray-200 object-cover shadow-sm dark:border-gray-700"
                    loading="lazy"
                />
            </a>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Aucune photo enregistrée pour cette course.</p>
        @endif
    </div>

    <div>
        <p class="mb-2 text-sm font-medium text-gray-950 dark:text-white">Note au chauffeur</p>
        @if ($driverReview)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-amber-500">{{ number_format((float) $driverReview->rating, 1) }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">/ 5</span>
                </div>
                @if (filled($driverReview->comment))
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $driverReview->comment }}</p>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Aucune note laissée par le client pour cette course.</p>
        @endif
    </div>
</div>
