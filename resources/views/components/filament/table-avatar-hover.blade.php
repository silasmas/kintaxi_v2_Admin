@props([
    'user' => null,
    'thumbClass' => 'h-9 w-9',
    'previewW' => 168,
    'previewH' => 176,
    'imgMaxClass' => 'max-h-[9.5rem] max-w-[9rem]',
    'wireKeyPrefix' => 'avatar-hover',
    'initialsClass' => 'text-[0.625rem]',
])

@php
    $wireKey = $wireKeyPrefix.($user?->getKey() !== null ? '-'.$user->getKey() : '-'.uniqid('', true));
@endphp

@if (! $user instanceof \App\Models\User)
    <span class="fi-ta-placeholder text-sm text-gray-400 dark:text-gray-500">—</span>
@else
    @php
        $avatarUrl = $user->getFilamentAvatarUrl();
        $initials = $user->getFilamentInitials();
        $ownerName = $user->getFilamentName();
    @endphp

        <div wire:ignore class="fi-table-avatar-hover-root flex justify-start"
            x-data="{
            show: false,
            x: 0,
            y: 0,
            previewW: {{ (int) $previewW }},
            previewH: {{ (int) $previewH }},
            pad: 12,
            updatePos(e) {
                const vw = window.innerWidth;
                const vh = window.innerHeight;
                let nx = e.clientX + this.pad;
                let ny = e.clientY + this.pad;
                if (nx + this.previewW > vw - this.pad) {
                    nx = e.clientX - this.previewW - this.pad;
                }
                if (nx < this.pad) nx = this.pad;
                if (ny + this.previewH > vh - this.pad) {
                    ny = e.clientY - this.previewH - this.pad;
                }
                if (ny < this.pad) ny = this.pad;
                this.x = nx;
                this.y = ny;
            },
        }"
        >
        @if ($avatarUrl)
            <button
                type="button"
                wire:key="{{ $wireKey }}"
                title="{{ $ownerName }}"
                class="{{ $thumbClass }} relative shrink-0 overflow-hidden rounded-full ring-1 ring-gray-950/10 transition hover:ring-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600 dark:ring-white/10"
                @mouseenter="show = true; updatePos($event)"
                @mouseleave="show = false"
                @mousemove="if (show) updatePos($event)"
            >
                <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover" loading="lazy" />
            </button>

            <template x-teleport="body">
                <div
                    x-show="show"
                    x-cloak
                    x-transition.opacity.duration.100ms
                    class="fixed pointer-events-none overflow-hidden rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg dark:border-white/10 dark:bg-gray-900"
                    data-filament-hover-preview
                    style="display: none; max-width: min(90vw, 20rem);"
                    x-bind:style="{ left: x + 'px', top: y + 'px' }"
                >
                    <img
                        src="{{ $avatarUrl }}"
                        alt=""
                        class="block h-auto {{ $imgMaxClass }} w-auto rounded-md object-contain"
                        loading="lazy"
                    />
                </div>
            </template>
        @else
            <div
                class="{{ $thumbClass }} {{ $initialsClass }} flex shrink-0 items-center justify-center rounded-full bg-gray-200 font-semibold uppercase text-gray-700 ring-1 ring-gray-950/10 dark:bg-gray-700 dark:text-gray-100 dark:ring-white/10"
                title="{{ $ownerName }}"
            >
                {{ $initials }}
            </div>
        @endif
    </div>
@endif
