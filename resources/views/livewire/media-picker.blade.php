<div>
    {{-- Selected Preview --}}
    @if(count($this->selectedMedia) > 0)
        <div class="space-y-2 mb-4">
            @foreach($this->selectedMedia as $media)
                <div
                    wire:key="selected-{{ $media['id'] }}"
                    class="flex items-center space-x-3 p-2 bg-blue-50 border border-blue-200 rounded-lg"
                >
                    <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($media['type'] === 'image' && $media['thumbnail_url'])
                            <img src="{{ $media['thumbnail_url'] }}" alt="{{ $media['name'] }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $media['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $media['size_formatted'] }}</p>
                    </div>
                    <button
                        wire:click="removeSelected({{ $media['id'] }})"
                        class="text-gray-400 hover:text-red-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Picker Button --}}
    <button
        wire:click="openPicker"
        type="button"
        class="w-full py-3 px-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-gray-400 hover:bg-gray-50 transition-colors"
    >
        <svg class="w-6 h-6 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-sm text-gray-500 mt-2 block">
            {{ $multiple ? 'Select media files' : 'Select a media file' }}
        </span>
    </button>

    {{-- Picker Modal --}}
    @if($isOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closePicker">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Select Media</h3>
                    <button wire:click="closePicker" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Search --}}
                <div class="px-6 py-3 border-b">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search media..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    >
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-auto p-6">
                    @if($this->media->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-4 text-gray-500">No media found</p>
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                            @foreach($this->media as $item)
                                <button
                                    wire:key="picker-{{ $item->id }}"
                                    wire:click="toggleSelect({{ $item->id }})"
                                    type="button"
                                    class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden focus:outline-none focus:ring-2 focus:ring-blue-500 {{ in_array($item->id, $selectedIds) ? 'ring-2 ring-blue-500' : '' }}"
                                >
                                    @if(str_starts_with($item->mime_type, 'image/'))
                                        <img
                                            src="{{ $item->getUrl() }}"
                                            alt="{{ $item->name }}"
                                            class="w-full h-full object-cover"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Selection Indicator --}}
                                    @if(in_array($item->id, $selectedIds))
                                        <div class="absolute top-1 right-1 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Name Overlay --}}
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                                        <p class="text-xs text-white truncate">{{ $item->name }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $this->media->links() }}
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t bg-gray-50">
                    <div class="text-sm text-gray-500">
                        {{ count($selectedIds) }} selected
                    </div>
                    <div class="flex items-center space-x-3">
                        @if(count($selectedIds) > 0)
                            <button
                                wire:click="clearSelection"
                                class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900"
                            >
                                Clear
                            </button>
                        @endif
                        <button
                            wire:click="confirm"
                            @disabled(count($selectedIds) === 0)
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Select
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
