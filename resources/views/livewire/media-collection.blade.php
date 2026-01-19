<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-700">
            {{ ucfirst($collection) }}
            @if(count($media) > 0)
                <span class="text-gray-400">({{ count($media) }})</span>
            @endif
        </h3>
        @if($canUploadMore)
            <button
                wire:click="toggleUploadZone"
                class="text-sm text-blue-600 hover:text-blue-800"
            >
                {{ $showUploadZone ? 'Cancel' : 'Add Files' }}
            </button>
        @endif
    </div>

    {{-- Upload Zone --}}
    @if($showUploadZone)
        <div
            x-data="{ isDragging: false }"
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-on:drop.prevent="isDragging = false"
            class="space-y-3"
        >
            <label
                for="collection-upload-{{ $this->getId() }}"
                class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100'"
            >
                <div class="flex flex-col items-center justify-center py-4">
                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-xs text-gray-500">Drop files or click to browse</p>
                </div>
                <input
                    id="collection-upload-{{ $this->getId() }}"
                    type="file"
                    wire:model="uploads"
                    accept="{{ $acceptedTypesString }}"
                    class="hidden"
                    multiple
                >
            </label>

            {{-- Pending Uploads --}}
            @if(count($uploads) > 0)
                <div class="space-y-2">
                    @foreach($uploads as $index => $upload)
                        <div
                            wire:key="pending-{{ $index }}"
                            class="flex items-center space-x-2 p-2 bg-white border rounded {{ isset($uploadErrors[$index]) ? 'border-red-300' : 'border-gray-200' }}"
                        >
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center overflow-hidden flex-shrink-0">
                                @if($upload->isPreviewable())
                                    <img src="{{ $upload->temporaryUrl() }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 truncate">{{ $upload->getClientOriginalName() }}</p>
                                @if(isset($uploadErrors[$index]))
                                    @foreach($uploadErrors[$index] as $error)
                                        <p class="text-xs text-red-500">{{ $error }}</p>
                                    @endforeach
                                @endif
                            </div>
                            <button wire:click="removeUpload({{ $index }})" class="text-gray-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button
                    wire:click="uploadFiles"
                    wire:loading.attr="disabled"
                    class="w-full py-2 px-4 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="uploadFiles">Upload Files</span>
                    <span wire:loading wire:target="uploadFiles">Uploading...</span>
                </button>
            @endif
        </div>
    @endif

    {{-- Media Grid --}}
    @if(count($media) > 0)
        <div
            @if($sortable)
                x-data="{
                    items: @js(collect($media)->pluck('id')->toArray()),
                    init() {
                        new Sortable(this.$el, {
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: (evt) => {
                                const ids = Array.from(this.$el.children).map(el => parseInt(el.dataset.id));
                                $wire.updateOrder(ids);
                            }
                        });
                    }
                }"
            @endif
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
        >
            @foreach($media as $item)
                <div
                    wire:key="media-{{ $item['id'] }}"
                    data-id="{{ $item['id'] }}"
                    class="group relative bg-white rounded-lg border border-gray-200 overflow-hidden {{ $sortable ? 'cursor-move' : '' }}"
                >
                    {{-- Thumbnail --}}
                    <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($item['type'] === 'image' && $item['thumbnail_url'])
                            <img src="{{ $item['thumbnail_url'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @elseif($item['type'] === 'video')
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        @else
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Actions Overlay --}}
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <div class="flex items-center space-x-2">
                            <button
                                wire:click="editMedia({{ $item['id'] }})"
                                class="p-2 bg-white rounded-full text-gray-700 hover:bg-gray-100"
                                title="Edit"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button
                                wire:click="removeMedia({{ $item['id'] }})"
                                wire:confirm="Are you sure you want to remove this file?"
                                class="p-2 bg-white rounded-full text-red-600 hover:bg-red-50"
                                title="Remove"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="p-2">
                        <p class="text-xs font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500 text-sm">
            No files in this collection yet.
        </div>
    @endif

    {{-- Edit Modal --}}
    @if($editingMediaId)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="cancelEdit">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Properties</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            type="text"
                            wire:model="editingProperties.name"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    @foreach($withProperties as $prop)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ ucfirst($prop) }}</label>
                            <input
                                type="text"
                                wire:model="editingProperties.{{ $prop }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        wire:click="cancelEdit"
                        class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="saveProperties"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@if($sortable)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    @endpush
@endif
