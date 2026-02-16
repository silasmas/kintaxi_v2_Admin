@php
    $owner = $getState();
    $avatarUrl = $owner?->getFilamentAvatarUrl();
    $initials = $owner?->getFilamentInitials() ?? '?';
    $ownerName = $owner?->getFilamentName() ?? $owner?->email ?? $owner?->phone ?? '—';
@endphp

@if ($owner)
    <div class="flex items-center gap-2">
        <div
            x-data="{ imageError: false }"
            class="shrink-0 h-8 w-8"
        >
            @if ($avatarUrl)
                <img
                    x-show="! imageError"
                    x-on:error="imageError = true"
                    src="{{ $avatarUrl }}"
                    alt="{{ $ownerName }}"
                    class="h-8 w-8 rounded-full object-cover object-center"
                />
            @endif
            <div
                x-show="{{ $avatarUrl ? 'imageError' : 'true' }}"
                x-cloak
                x-transition
                class="fi-avatar-initials flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300"
            >
                {{ $initials }}
            </div>
        </div>
        <span class="text-sm text-gray-950 dark:text-white">
            {{ $ownerName }}
        </span>
    </div>
@else
    <span class="text-gray-400">—</span>
@endif
