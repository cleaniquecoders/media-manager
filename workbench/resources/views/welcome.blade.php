<x-layouts.app title="Welcome">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <h1 class="text-2xl font-bold mb-4">Welcome to Media Manager Demo</h1>
            <p class="mb-4">This workbench environment allows you to test all Media Manager components.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                <a href="/demo" class="block p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <h3 class="text-lg font-semibold text-blue-700">All Components</h3>
                    <p class="text-sm text-gray-600 mt-2">View all Livewire components in one page</p>
                </a>

                <a href="/uploader" class="block p-6 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <h3 class="text-lg font-semibold text-green-700">Media Uploader</h3>
                    <p class="text-sm text-gray-600 mt-2">Single file upload with preview</p>
                </a>

                <a href="/collection" class="block p-6 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <h3 class="text-lg font-semibold text-purple-700">Media Collection</h3>
                    <p class="text-sm text-gray-600 mt-2">Manage multiple files with drag-and-drop</p>
                </a>

                <a href="/picker" class="block p-6 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                    <h3 class="text-lg font-semibold text-orange-700">Media Picker</h3>
                    <p class="text-sm text-gray-600 mt-2">Modal-based media selection</p>
                </a>

                <a href="/model-demo" class="block p-6 bg-pink-50 rounded-lg hover:bg-pink-100 transition">
                    <h3 class="text-lg font-semibold text-pink-700">Model Demo</h3>
                    <p class="text-sm text-gray-600 mt-2">Upload to User and Post models</p>
                </a>

                <a href="/media-manager" class="block p-6 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                    <h3 class="text-lg font-semibold text-indigo-700">Media Browser</h3>
                    <p class="text-sm text-gray-600 mt-2">Full media library browser</p>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
