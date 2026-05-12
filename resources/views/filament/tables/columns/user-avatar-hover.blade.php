@php
    /** @var \App\Models\User|null $user */
    $user = $getState();
@endphp

@if (! $user)
    <span class="fi-ta-placeholder text-sm text-gray-400 dark:text-gray-500">—</span>
@else
    @php
        $avatarUrl = $user->getFilamentAvatarUrl();
        $initials = $user->getFilamentInitials();
    @endphp

    <div
        class="flex justify-start"
        x-data="{
            show: false,
            x: 0,
            y: 0,
            move(e) {
                this.x = e.clientX + 16;
                this.y = e.clientY + 16;
                const maxLeft = typeof window !== 'undefined' ? window.innerWidth - 340 : this.x;
                const maxTop = typeof window !== 'undefined' ? window.innerHeight - 260 : this.y;
                if (this.x > maxLeft) this.x = maxLeft - 16;
                if (this.y > maxTop) this.y = Math.max(16, e.clientY - 220);
            }
        }"
    >
        @if ($avatarUrl)
            <button
                type="button"
                wire:key="loyalty-hover-avatar-{{ $user->getKey() }}"
                class="relative h-9 w-9 shrink-0 overflow-hidden rounded-full ring-1 ring-gray-950/10 transition hover:ring-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600 dark:ring-white/10"
                @mouseenter="show = true"
                @mouseleave="show = false"
                @mousemove="move($event)"
            >
                <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover" loading="lazy" />
            </button>

            <template x-teleport="body">
                <div
                    x-show="show"
                    x-cloak
                    x-transition.opacity.duration.100ms
                    class="fixed z-[100] pointer-events-none rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-white/10 dark:bg-gray-900"
                    style="display: none;"
                    x-bind:style="{ left: x + 'px', top: y + 'px' }"
                >
                    <img src="{{ $avatarUrl }}" alt="" class="max-h-56 max-w-[20rem] object-contain" loading="lazy" />
                </div>
            </template>
        @else
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-200 text-[0.625rem] font-semibold uppercase text-gray-700 ring-1 ring-gray-950/10 dark:bg-gray-700 dark:text-gray-100 dark:ring-white/10"
                title="{{ $user->getFilamentName() }}"
            >
                {{ $initials }}
            </div>
        @endif
    </div>
@endif
