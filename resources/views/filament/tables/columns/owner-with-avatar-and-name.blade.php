@php
    $user = $getState();
@endphp

@if ($user)
    <div class="flex min-w-[10rem] items-center gap-2">
        <x-filament.table-avatar-hover
            :user="$user"
            thumb-class="h-8 w-8"
            :preview-w="160"
            :preview-h="168"
            img-max-class="max-h-[9rem] max-w-[8.5rem]"
            wire-key-prefix="owner"
        />
        <span class="truncate text-sm font-medium text-gray-950 dark:text-white" title="{{ $user->getFilamentName() }}">
            {{ $user->getFilamentName() }}
        </span>
    </div>
@else
    <span class="text-sm text-gray-400">—</span>
@endif
