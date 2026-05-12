@php
    /** @var \App\Models\Media|null $record */
    $record = $getRecord();
    $url = ($record && $record->isImage()) ? $record->url : null;
@endphp

@if ($url)
    <div wire:ignore class="fi-table-avatar-hover-root flex justify-start"
        x-data="{
            show: false,
            x: 0,
            y: 0,
            previewW: 200,
            previewH: 200,
            pad: 12,
            updatePos(e) {
                const vw = window.innerWidth;
                const vh = window.innerHeight;
                let nx = e.clientX + this.pad;
                let ny = e.clientY + this.pad;
                if (nx + this.previewW > vw - this.pad) nx = e.clientX - this.previewW - this.pad;
                if (nx < this.pad) nx = this.pad;
                if (ny + this.previewH > vh - this.pad) ny = e.clientY - this.previewH - this.pad;
                if (ny < this.pad) ny = this.pad;
                this.x = nx;
                this.y = ny;
            },
        }"
    >
        <button
            type="button"
            wire:key="media-hover-{{ $record->getKey() }}"
            class="relative h-10 w-10 shrink-0 overflow-hidden rounded-lg ring-1 ring-gray-950/10 transition hover:ring-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600 dark:ring-white/10"
            @mouseenter="show = true; updatePos($event)"
            @mouseleave="show = false"
            @mousemove="if (show) updatePos($event)"
        >
            <img src="{{ $url }}" alt="" class="h-full w-full object-cover" loading="lazy" />
        </button>
        <template x-teleport="body">
            <div
                x-show="show"
                x-cloak
                x-transition.opacity.duration.100ms
                class="fixed pointer-events-none overflow-hidden rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg dark:border-white/10 dark:bg-gray-900"
                data-filament-hover-preview
                style="display: none; max-width: min(90vw, 22rem);"
                x-bind:style="{ left: x + 'px', top: y + 'px' }"
            >
                <img
                    src="{{ $url }}"
                    alt=""
                    class="block h-auto max-h-48 max-w-[20rem] w-auto rounded-md object-contain"
                    loading="lazy"
                />
            </div>
        </template>
    </div>
@elseif ($record && $record->isVideo())
    <div
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[0.625rem] font-medium text-gray-600 ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-gray-300 dark:ring-white/10"
        title="Vidéo"
    >
        VID
    </div>
@else
    <span class="text-gray-400">—</span>
@endif
