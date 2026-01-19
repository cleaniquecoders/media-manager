<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closePreview">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $media['name'] }}</h3>
            <button
                wire:click="closePreview"
                class="text-gray-400 hover:text-gray-600"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-auto p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Preview --}}
                <div class="bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center min-h-[300px]">
                    @if($media['type'] === 'image')
                        <img src="{{ $media['url'] }}" alt="{{ $media['name'] }}" class="max-w-full max-h-[400px] object-contain">
                    @elseif($media['type'] === 'video')
                        <video controls class="max-w-full max-h-[400px]">
                            <source src="{{ $media['url'] }}" type="{{ $media['mime_type'] }}">
                            Your browser does not support the video tag.
                        </video>
                    @elseif($media['type'] === 'audio')
                        <div class="p-8 text-center">
                            <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                            <audio controls class="w-full">
                                <source src="{{ $media['url'] }}" type="{{ $media['mime_type'] }}">
                                Your browser does not support the audio tag.
                            </audio>
                        </div>
                    @elseif($media['type'] === 'pdf')
                        <iframe src="{{ $media['url'] }}" class="w-full h-[400px]"></iframe>
                    @else
                        <div class="text-center p-8">
                            <svg class="w-20 h-20 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-4 text-gray-500">Preview not available</p>
                        </div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">File Details</h4>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Name</dt>
                            <dd class="text-sm text-gray-900 break-all">{{ $media['file_name'] }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Collection</dt>
                            <dd>
                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                    {{ $media['collection'] }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="text-sm text-gray-900">{{ $media['mime_type'] }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Size</dt>
                            <dd class="text-sm text-gray-900">{{ $media['size_formatted'] }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $media['created_at'] }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Updated</dt>
                            <dd class="text-sm text-gray-900">{{ $media['updated_at'] }}</dd>
                        </div>

                        @if(!empty($media['custom_properties']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Custom Properties</dt>
                                <dd class="text-sm text-gray-900 mt-1">
                                    @foreach($media['custom_properties'] as $key => $value)
                                        <div class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                                            <span class="text-gray-600">{{ $key }}</span>
                                            <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-4 border-t bg-gray-50">
            <button
                wire:click="deleteSingle({{ $media['id'] }})"
                wire:confirm="Are you sure you want to delete this file?"
                class="px-4 py-2 text-sm text-red-600 hover:text-red-800"
            >
                Delete
            </button>
            <div class="flex items-center space-x-3">
                <a
                    href="{{ $media['url'] }}"
                    target="_blank"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                >
                    Download
                </a>
            </div>
        </div>
    </div>
</div>
