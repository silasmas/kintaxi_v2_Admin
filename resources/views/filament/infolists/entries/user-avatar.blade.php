@php
    $record = $getRecord();
@endphp

<div class="fi-infolist-entry-wrp">
    <x-filament.table-avatar-hover
        :user="$record"
        thumb-class="h-24 w-24"
        :preview-w="300"
        :preview-h="320"
        img-max-class="max-h-[18rem] max-w-[16rem]"
        initials-class="text-2xl"
        wire-key-prefix="profile"
    />
</div>
