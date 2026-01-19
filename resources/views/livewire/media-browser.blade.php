<div class="min-h-screen bg-gray-100">
    {{-- Header --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Media Manager</h1>
                <div class="flex items-center space-x-4">
                    {{-- View Toggle --}}
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button
                            wire:click="setView('grid')"
                            class="p-2 rounded {{ $view === 'grid' ? 'bg-white shadow' : 'text-gray-500 hover:text-gray-700' }}"
                            title="Grid View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            wire:click="setView('list')"
                            class="p-2 rounded {{ $view === 'list' ? 'bg-white shadow' : 'text-gray-500 hover:text-gray-700' }}"
                            title="List View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex gap-6">
            {{-- Sidebar Filters --}}
            <aside class="w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow p-4 space-y-4">
                    <h2 class="font-semibold text-gray-900">Filters</h2>

                    {{-- Search --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search files..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                    </div>

                    {{-- Collection Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Collection</label>
                        <select
                            wire:model.live="collection"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                            <option value="">All Collections</option>
                            @foreach($this->collections as $col)
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            wire:model.live="type"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                            <option value="">All Types</option>
                            @foreach($this->typeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input
                            type="date"
                            wire:model.live="dateFrom"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input
                            type="date"
                            wire:model.live="dateTo"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                    </div>

                    {{-- Clear Filters --}}
                    @if($search || $collection || $type || $dateFrom || $dateTo)
                        <button
                            wire:click="clearFilters"
                            class="w-full text-sm text-blue-600 hover:text-blue-800"
                        >
                            Clear all filters
                        </button>
                    @endif
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="flex-1">
                {{-- Bulk Actions --}}
                @if(count($selected) > 0)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 flex items-center justify-between">
                        <span class="text-sm text-blue-800">
                            {{ count($selected) }} item(s) selected
                        </span>
                        <div class="flex items-center space-x-2">
                            <button
                                wire:click="deselectAll"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                Deselect All
                            </button>
                            <button
                                wire:click="confirmDelete"
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                            >
                                Delete Selected
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Media Grid/List --}}
                @if($this->media->isEmpty())
                    <div class="bg-white rounded-lg shadow p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No media found</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            @if($search || $collection || $type || $dateFrom || $dateTo)
                                Try adjusting your filters.
                            @else
                                Upload some files to get started.
                            @endif
                        </p>
                    </div>
                @else
                    @if($view === 'grid')
                        {{-- Grid View --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-{{ $gridColumns }} gap-4">
                            @foreach($this->media as $item)
                                @include('media-manager::partials.grid-item', ['item' => $item])
                            @endforeach
                        </div>
                    @else
                        {{-- List View --}}
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-12 px-4 py-3"></th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collection</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($this->media as $item)
                                        @include('media-manager::partials.list-item', ['item' => $item])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $this->media->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>

    {{-- Preview Panel --}}
    @if($previewMediaId && $this->previewMedia)
        @include('media-manager::partials.preview-panel', ['media' => $this->previewMedia])
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm Delete</h3>
                <p class="text-gray-600 mb-4">
                    Are you sure you want to delete {{ count($selected) }} item(s)? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        wire:click="cancelDelete"
                        class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteSelected"
                        class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
