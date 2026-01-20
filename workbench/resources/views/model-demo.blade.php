<x-layouts.app title="Model Demo">
    <div class="space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">User Avatar (Single File)</h2>
                <p class="text-gray-600 mb-4">Upload avatar for user: {{ $user->name }}</p>
                <livewire:media-manager::uploader
                    :model="$user"
                    collection="avatar"
                    :maxFiles="1"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">User Documents</h2>
                <p class="text-gray-600 mb-4">Manage documents for user: {{ $user->name }}</p>
                <livewire:media-manager::collection
                    :model="$user"
                    collection="documents"
                    :sortable="true"
                    :maxFiles="10"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">User Gallery</h2>
                <p class="text-gray-600 mb-4">Photo gallery for user: {{ $user->name }}</p>
                <livewire:media-manager::collection
                    :model="$user"
                    collection="gallery"
                    :sortable="true"
                    :showUploadZone="true"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Post Featured Image</h2>
                <p class="text-gray-600 mb-4">Featured image for post: {{ $post->title }}</p>
                <livewire:media-manager::uploader
                    :model="$post"
                    collection="featured"
                    :maxFiles="1"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Post Images</h2>
                <p class="text-gray-600 mb-4">Images for post: {{ $post->title }}</p>
                <livewire:media-manager::collection
                    :model="$post"
                    collection="images"
                    :sortable="true"
                />
            </div>
        </div>
    </div>
</x-layouts.app>
