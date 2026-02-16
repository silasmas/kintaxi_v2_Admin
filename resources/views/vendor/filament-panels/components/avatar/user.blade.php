@props([
    'user' => filament()->auth()->user(),
    'size' => 'md',
])

@php
    $avatarUrl = filament()->getUserAvatarUrl($user);
    $initials = $user && method_exists($user, 'getFilamentInitials')
        ? $user->getFilamentInitials()
        : str(filament()->getUserName($user))->trim()->explode(' ')->map(fn ($s) => filled($s) ? mb_substr($s, 0, 1) : '')->take(2)->join('');
    $imgSizeClasses = match ($size) {
        'sm' => 'h-6 w-6',
        'lg' => 'h-10 w-10',
        default => 'h-8 w-8',
    };
    $initialsSizeClasses = match ($size) {
        'sm' => 'h-6 w-6 text-xs',
        'lg' => 'h-10 w-10 text-base',
        default => 'h-8 w-8 text-sm',
    };
    $showInitialsByDefault = ! $avatarUrl;
@endphp

<div class="fi-user-avatar shrink-0 {{ $imgSizeClasses }} relative">
    @if ($avatarUrl)
        <img
            src="{{ $avatarUrl }}"
            alt=""
            role="presentation"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            {{ $attributes->merge(['class' => 'fi-avatar object-cover object-center rounded-full ' . $imgSizeClasses]) }}
        />
    @endif
    <div
        class="fi-avatar-initials flex items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300 {{ $initialsSizeClasses }} {{ $avatarUrl ? 'absolute inset-0' : '' }}"
        style="{{ $showInitialsByDefault ? '' : 'display: none;' }}"
    >
        {{ $initials ?: '?' }}
    </div>
</div>
