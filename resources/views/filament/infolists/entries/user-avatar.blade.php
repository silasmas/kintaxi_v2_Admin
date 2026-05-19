@php
    $record = $getRecord();
    $avatarUrl = $record->getFilamentAvatarUrl();
    $initials = $record->getFilamentInitials();
    $name = $record->getFilamentName();
@endphp

<div class="fi-infolist-entry-wrp">
    @if ($avatarUrl)
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $name }}"
            class="h-16 w-16 shrink-0 rounded-full object-cover ring-1 ring-gray-950/10 dark:ring-white/10"
            loading="lazy"
        />
    @else
        <div
            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-200 text-lg font-semibold uppercase text-gray-700 ring-1 ring-gray-950/10 dark:bg-gray-700 dark:text-gray-100 dark:ring-white/10"
            title="{{ $name }}"
        >
            {{ $initials }}
        </div>
    @endif
</div>
