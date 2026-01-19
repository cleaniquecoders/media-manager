<tr
    wire:key="media-{{ $item['id'] }}"
    class="hover:bg-gray-50 cursor-pointer"
>
    {{-- Checkbox --}}
    <td class="px-4 py-3">
        <input
            type="checkbox"
            wire:click="toggleSelect({{ $item['id'] }})"
            @checked(in_array($item['id'], $selected))
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        >
    </td>

    {{-- File Info --}}
    <td class="px-4 py-3" wire:click="openPreview({{ $item['id'] }})">
        <div class="flex items-center space-x-3">
            {{-- Thumbnail --}}
            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center overflow-hidden flex-shrink-0">
                @if($item['type'] === 'image' && $item['thumbnail_url'])
                    <img src="{{ $item['thumbnail_url'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                @elseif($item['type'] === 'video')
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                @elseif($item['type'] === 'pdf')
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                @else
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                @endif
            </div>

            {{-- Name --}}
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $item['file_name'] }}</p>
            </div>
        </div>
    </td>

    {{-- Collection --}}
    <td class="px-4 py-3" wire:click="openPreview({{ $item['id'] }})">
        <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
            {{ $item['collection'] }}
        </span>
    </td>

    {{-- Type --}}
    <td class="px-4 py-3" wire:click="openPreview({{ $item['id'] }})">
        <span class="text-sm text-gray-500 capitalize">{{ $item['type'] }}</span>
    </td>

    {{-- Size --}}
    <td class="px-4 py-3" wire:click="openPreview({{ $item['id'] }})">
        <span class="text-sm text-gray-500">{{ $item['size_formatted'] }}</span>
    </td>

    {{-- Date --}}
    <td class="px-4 py-3" wire:click="openPreview({{ $item['id'] }})">
        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}</span>
    </td>

    {{-- Actions --}}
    <td class="px-4 py-3 text-right">
        <a
            href="{{ $item['url'] }}"
            target="_blank"
            class="text-gray-400 hover:text-gray-600"
            title="Download"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
        </a>
    </td>
</tr>
