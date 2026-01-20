<x-layouts.app title="Media Picker Demo">
    <div class="space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Multiple Selection Picker</h2>
                <p class="text-gray-600 mb-4">Select multiple media files from the library.</p>
                <livewire:media-manager::picker
                    :multiple="true"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Single Selection Picker</h2>
                <p class="text-gray-600 mb-4">Select a single media file.</p>
                <livewire:media-manager::picker
                    :multiple="false"
                />
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Picker with Collection Filter</h2>
                <p class="text-gray-600 mb-4">Only shows media from the 'gallery' collection.</p>
                <livewire:media-manager::picker
                    :multiple="true"
                    collection="gallery"
                />
            </div>
        </div>
    </div>
</x-layouts.app>
