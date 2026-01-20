<x-layouts.app title="Media Collection Demo">
    <div class="space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Sortable Collection</h2>
                <p class="text-gray-600 mb-4">Drag and drop to reorder files. Bound to user: {{ $user->name }}</p>
                <livewire:media-manager::collection
                    :model="$user"
                    collection="gallery"
                    :sortable="true"
                    :maxFiles="10"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Non-Sortable Collection</h2>
                <p class="text-gray-600 mb-4">Standard collection without drag-and-drop.</p>
                <livewire:media-manager::collection
                    :model="$user"
                    collection="documents"
                    :sortable="false"
                    :maxFiles="5"
                />
            </div>
        </div>
    </div>
</x-layouts.app>
