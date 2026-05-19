@php
    $previousAvatars = $previousAvatars ?? [];
    $nextAvatars = $nextAvatars ?? [];
@endphp

@if ($previousUrl || $nextUrl)
    <div class="mb-6 grid gap-3 sm:grid-cols-2">
        @if ($previousUrl)
            <a
                href="{{ $previousUrl }}"
                class="group flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 transition hover:border-primary-500 hover:shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-500"
            >
                <x-filament::icon icon="heroicon-m-chevron-left" class="mt-1 h-5 w-5 shrink-0 text-gray-400 group-hover:text-primary-600" />
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Précédent</p>
                    <div class="mt-2 flex items-center gap-2">
                        @if (count($previousAvatars) > 0)
                            <div class="flex shrink-0 items-center -space-x-2">
                                @foreach ($previousAvatars as $item)
                                    <x-filament.table-avatar-hover
                                        :user="$item['user']"
                                        thumb-class="h-9 w-9 ring-2 ring-white dark:ring-gray-900"
                                        :preview-w="120"
                                        :preview-h="128"
                                        img-max-class="max-h-[7rem] max-w-[7rem]"
                                        wire-key-prefix="nav-prev-{{ $item['user']->id }}"
                                    />
                                @endforeach
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $previousLabel }}</p>
                            @if ($previousPreview)
                                <p class="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $previousPreview }}</p>
                            @endif
                        </div>
                    </div>
                    @if (count($previousAvatars) > 0)
                        <p class="mt-1.5 flex flex-wrap gap-1 text-[0.65rem] text-gray-500 dark:text-gray-400">
                            @foreach ($previousAvatars as $item)
                                <span>{{ $item['role'] }}</span>
                                @if (! $loop->last)
                                    <span aria-hidden="true">·</span>
                                @endif
                            @endforeach
                        </p>
                    @endif
                </div>
            </a>
        @else
            <div class="hidden sm:block"></div>
        @endif

        @if ($nextUrl)
            <a
                href="{{ $nextUrl }}"
                class="group flex items-start justify-end gap-3 rounded-xl border border-gray-200 bg-white p-3 text-right transition hover:border-primary-500 hover:shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-500"
            >
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Suivant</p>
                    <div class="mt-2 flex flex-row-reverse items-center gap-2">
                        @if (count($nextAvatars) > 0)
                            <div class="flex shrink-0 flex-row-reverse items-center -space-x-2 space-x-reverse">
                                @foreach ($nextAvatars as $item)
                                    <x-filament.table-avatar-hover
                                        :user="$item['user']"
                                        thumb-class="h-9 w-9 ring-2 ring-white dark:ring-gray-900"
                                        :preview-w="120"
                                        :preview-h="128"
                                        img-max-class="max-h-[7rem] max-w-[7rem]"
                                        wire-key-prefix="nav-next-{{ $item['user']->id }}"
                                    />
                                @endforeach
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $nextLabel }}</p>
                            @if ($nextPreview)
                                <p class="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $nextPreview }}</p>
                            @endif
                        </div>
                    </div>
                    @if (count($nextAvatars) > 0)
                        <p class="mt-1.5 flex flex-wrap justify-end gap-1 text-[0.65rem] text-gray-500 dark:text-gray-400">
                            @foreach ($nextAvatars as $item)
                                <span>{{ $item['role'] }}</span>
                                @if (! $loop->last)
                                    <span aria-hidden="true">·</span>
                                @endif
                            @endforeach
                        </p>
                    @endif
                </div>
                <x-filament::icon icon="heroicon-m-chevron-right" class="mt-1 h-5 w-5 shrink-0 text-gray-400 group-hover:text-primary-600" />
            </a>
        @endif
    </div>
@endif

