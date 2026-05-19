@props(['participant' => null])

@if (is_array($participant) && filled($participant['name'] ?? null))
    <div class="flex min-w-[7rem] max-w-[10rem] items-center gap-2">
        @if (! empty($participant['avatar_url']))
            <img
                src="{{ $participant['avatar_url'] }}"
                alt=""
                class="h-7 w-7 shrink-0 rounded-full object-cover ring-1 ring-gray-950/10 dark:ring-white/10"
                loading="lazy"
            />
        @else
            <span
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-200 text-[0.625rem] font-semibold uppercase text-gray-700 ring-1 ring-gray-950/10 dark:bg-gray-700 dark:text-gray-100 dark:ring-white/10"
            >
                {{ $participant['initials'] ?? '?' }}
            </span>
        @endif
        <span class="truncate text-sm text-gray-950 dark:text-white" title="{{ $participant['name'] }}">
            {{ $participant['name'] }}
        </span>
    </div>
@else
    <span class="text-sm text-gray-400">—</span>
@endif
