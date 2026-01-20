<x-layouts.app title="Media Uploader Demo">
    <div class="space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Basic Uploader</h2>
                <p class="text-gray-600 mb-4">Single file upload bound to user: {{ $user->name }}</p>
                <livewire:media-manager::uploader
                    :model="$user"
                    collection="documents"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Uploader with Max Files</h2>
                <p class="text-gray-600 mb-4">Limited to 3 files maximum.</p>
                <livewire:media-manager::uploader
                    :model="$user"
                    collection="attachments"
                    :maxFiles="3"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Avatar Uploader (Single File)</h2>
                <p class="text-gray-600 mb-4">Single file collection for avatar.</p>
                <livewire:media-manager::uploader
                    :model="$user"
                    collection="avatar"
                    :maxFiles="1"
                />
            </div>
        </div>
    </div>
</x-layouts.app>
