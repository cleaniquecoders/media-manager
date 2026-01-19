<div class="space-y-4">
    {{-- Existing Media --}}
    @if(count($existingMedia) > 0)
        <div class="space-y-2">
            @foreach($existingMedia as $media)
                <div
                    wire:key="existing-{{ $media['id'] }}"
                    class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg"
                >
                    {{-- Thumbnail --}}
                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($media['type'] === 'image' && $media['thumbnail_url'])
                            <img src="{{ $media['thumbnail_url'] }}" alt="{{ $media['name'] }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $media['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $media['size_formatted'] }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center space-x-2">
                        <a
                            href="{{ $media['url'] }}"
                            target="_blank"
                            class="text-gray-400 hover:text-gray-600"
                            title="View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        <button
                            wire:click="removeExisting({{ $media['id'] }})"
                            wire:confirm="Are you sure you want to remove this file?"
                            class="text-red-400 hover:text-red-600"
                            title="Remove"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Upload Zone --}}
    @if($canUploadMore)
        <div
            x-data="{ isDragging: false }"
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-on:drop.prevent="isDragging = false"
            class="relative"
        >
            <label
                for="upload-{{ $this->getId() }}"
                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100'"
            >
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-sm text-gray-500">
                        <span class="font-medium">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Max {{ $maxFileSizeMb }}MB
                    </p>
                </div>
                <input
                    id="upload-{{ $this->getId() }}"
                    type="file"
                    wire:model="uploads"
                    accept="{{ $acceptedTypesString }}"
                    class="hidden"
                    @if($isMultiple) multiple @endif
                >
            </label>
        </div>
    @endif

    {{-- Pending Uploads --}}
    @if(count($uploads) > 0)
        <div class="space-y-2">
            @foreach($uploads as $index => $upload)
                <div
                    wire:key="upload-{{ $index }}"
                    class="flex items-center space-x-3 p-3 bg-white border rounded-lg {{ isset($uploadErrors[$index]) ? 'border-red-300' : 'border-gray-200' }}"
                >
                    {{-- Preview --}}
                    <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($upload->isPreviewable())
                            <img src="{{ $upload->temporaryUrl() }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $upload->getClientOriginalName() }}</p>
                        <p class="text-xs text-gray-500">{{ $this->formatFileSize($upload->getSize()) }}</p>

                        {{-- Progress --}}
                        @if(isset($uploadProgress[$index]) && $uploadProgress[$index] < 100)
                            <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                                <div
                                    class="bg-blue-600 h-1.5 rounded-full transition-all"
                                    style="width: {{ $uploadProgress[$index] }}%"
                                ></div>
                            </div>
                        @endif

                        {{-- Errors --}}
                        @if(isset($uploadErrors[$index]))
                            @foreach($uploadErrors[$index] as $error)
                                <p class="text-xs text-red-500 mt-1">{{ $error }}</p>
                            @endforeach
                        @endif
                    </div>

                    {{-- Custom Properties --}}
                    @if(count($withProperties) > 0 && !isset($uploadErrors[$index]))
                        <div class="flex-shrink-0 space-y-1">
                            @foreach($withProperties as $prop)
                                <input
                                    type="text"
                                    wire:change="updatePropertyValue({{ $index }}, '{{ $prop }}', $event.target.value)"
                                    placeholder="{{ ucfirst($prop) }}"
                                    class="w-32 text-xs rounded border-gray-300"
                                >
                            @endforeach
                        </div>
                    @endif

                    {{-- Remove --}}
                    <button
                        wire:click="removeUpload({{ $index }})"
                        class="text-gray-400 hover:text-red-600 flex-shrink-0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Save Button --}}
        @if($modelClass && $modelId)
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="w-full py-2 px-4 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="save">
                    Upload {{ count($uploads) }} file(s)
                </span>
                <span wire:loading wire:target="save">
                    Uploading...
                </span>
            </button>
        @endif
    @endif
</div>
