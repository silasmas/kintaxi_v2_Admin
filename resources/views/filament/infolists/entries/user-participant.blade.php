@php
    $user = $getState();
@endphp

@if ($user)
    <div class="flex items-center gap-3">
        <x-filament.table-avatar-hover
            :user="$user"
            thumb-class="h-10 w-10"
            :preview-w="160"
            :preview-h="168"
            img-max-class="max-h-[9rem] max-w-[8.5rem]"
            wire-key-prefix="participant-{{ $user->id }}"
        />
        <div>
            <div class="font-medium text-gray-950 dark:text-white">
                {{ $user->getFilamentName() }}
            </div>
            @if (filled($user->phone))
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->phone }}</div>
            @endif
        </div>
    </div>
@else
    <span class="text-gray-400">—</span>
@endif
