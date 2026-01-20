<x-layouts.app title="All Components Demo">
    <div class="space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Media Uploader Component</h2>
                <p class="text-gray-600 mb-4">Single file upload with preview. Bound to user: {{ $user->name }}</p>
                <livewire:media-manager::uploader
                    :model="$user"
                    collection="documents"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Media Collection Component</h2>
                <p class="text-gray-600 mb-4">Manage multiple files with ordering support. Bound to user: {{ $user->name }}</p>
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
                <h2 class="text-xl font-bold mb-4">Media Picker Component</h2>
                <p class="text-gray-600 mb-4">Modal-based media selection component.</p>
                <livewire:media-manager::picker
                    :multiple="true"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Media Browser</h2>
                <p class="text-gray-600 mb-4">Full-featured media browser with filtering and bulk operations.</p>
                <livewire:media-manager::browser />
            </div>
        </div>
    </div>
</x-layouts.app>
