<x-filament.table-avatar-hover
    :user="$getState()"
    thumb-class="h-8 w-8"
    :preview-w="160"
    :preview-h="168"
    img-max-class="max-h-[9rem] max-w-[8.5rem]"
    wire-key-prefix="owner"
/>
