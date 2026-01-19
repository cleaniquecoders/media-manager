<div
    class="relative group bg-white rounded-lg shadow overflow-hidden cursor-pointer hover:shadow-lg transition-shadow"
    wire:key="media-{{ $item['id'] }}"
>
    {{-- Selection Checkbox --}}
    <div class="absolute top-2 left-2 z-10">
        <input
            type="checkbox"
            wire:click="toggleSelect({{ $item['id'] }})"
            @checked(in_array($item['id'], $selected))
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 opacity-0 group-hover:opacity-100 transition-opacity {{ in_array($item['id'], $selected) ? '!opacity-100' : '' }}"
        >
    </div>

    {{-- Thumbnail --}}
    <div
        wire:click="openPreview({{ $item['id'] }})"
        class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden"
    >
        @if($item['type'] === 'image' && $item['thumbnail_url'])
            <img
                src="{{ $item['thumbnail_url'] }}"
                alt="{{ $item['name'] }}"
                class="w-full h-full object-cover"
            >
        @elseif($item['type'] === 'video')
            <div class="text-gray-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
        @elseif($item['type'] === 'audio')
            <div class="text-gray-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
            </div>
        @elseif($item['type'] === 'pdf')
            <div class="text-red-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
        @elseif($item['type'] === 'document')
            <div class="text-blue-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        @elseif($item['type'] === 'spreadsheet')
            <div class="text-green-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
        @else
            <div class="text-gray-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="p-3">
        <p class="text-sm font-medium text-gray-900 truncate" title="{{ $item['file_name'] }}">
            {{ $item['name'] }}
        </p>
        <p class="text-xs text-gray-500 mt-1">
            {{ $item['size_formatted'] }}
        </p>
    </div>
</div>
