@php
    $record = $getRecord();
    $avatarUrl = $record && method_exists($record, 'getFilamentAvatarUrl') ? $record->getFilamentAvatarUrl() : null;
    $initials = $record && method_exists($record, 'getFilamentInitials') ? $record->getFilamentInitials() : '?';
@endphp

<div class="fi-infolist-entry-wrp">
    <div class="flex items-center gap-4">
        <div class="fi-user-avatar shrink-0 h-24 w-24 relative rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
            @if ($avatarUrl)
                <img
                    src="{{ $avatarUrl }}"
                    alt=""
                    class="h-full w-full object-cover"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                />
            @endif
            <div
                class="fi-avatar-initials flex items-center justify-center h-full w-full text-2xl font-semibold text-gray-600 dark:text-gray-300 {{ $avatarUrl ? 'absolute inset-0' : '' }}"
                style="{{ $avatarUrl ? 'display: none;' : '' }}"
            >
                {{ $initials ?: '?' }}
            </div>
        </div>
    </div>
</div>
