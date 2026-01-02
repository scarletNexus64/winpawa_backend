<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="flex items-center gap-4">
        @if($getRecord() && $getRecord()->image)
            <img
                src="{{ asset('images/' . $getRecord()->image) }}"
                alt="{{ $getRecord()->name }}"
                class="h-32 w-32 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-700"
            />
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p class="font-medium">{{ $getRecord()->image }}</p>
                <p class="text-xs mt-1">Téléchargez une nouvelle image ci-dessous pour la remplacer</p>
            </div>
        @endif
    </div>
</x-dynamic-component>
