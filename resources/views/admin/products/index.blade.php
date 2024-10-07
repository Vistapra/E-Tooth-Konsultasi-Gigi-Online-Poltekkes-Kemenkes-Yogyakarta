<x-app-layout>
    @php
        function getYoutubeVideoId($url)
        {
            $regExp = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/';
            $match = [];
            if (preg_match($regExp, $url, $match)) {
                return $match[2];
            }
            return null;
        }
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Edukasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('dashboard') }}" class="p-2 bg-white dark:bg-gray-700 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <a href="{{ route('admin.products.create') }}"
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">
                            Tambah Edukasi
                        </a>
                    </div>

                    <form action="{{ route('admin.products.index') }}" method="GET" class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <input type="text" name="query" placeholder="Cari Edukasi..."
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-500"
                                value="{{ request('query') }}">
                            <select name="category"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-500">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="sort"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-500">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru
                                </option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama
                                </option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama
                                    (A-Z)</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama
                                    (Z-A)</option>
                            </select>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                                Cari
                            </button>
                        </div>
                    </form>

                    @if (session('success'))
                        <div id="success-alert"
                            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <strong class="font-bold">Sukses!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-6 w-6 text-green-500" role="button"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <title>Close</title>
                                    <path
                                        d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                                </svg>
                            </span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Media</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Deskripsi</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Kategori</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-600">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($product->photo)
                                                <a href="{{ Storage::url($product->photo) }}" data-fancybox="gallery"
                                                    data-caption="{{ $product->name }}" class="block relative group">
                                                    <img src="{{ Storage::url($product->photo) }}"
                                                        alt="{{ $product->name }}"
                                                        class="w-16 h-16 object-cover rounded">
                                                    <div
                                                        class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        <svg class="w-8 h-8 text-white" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                        </svg>
                                                    </div>
                                                </a>
                                            @elseif($product->video)
                                                <a href="{{ Storage::url($product->video) }}" data-fancybox="gallery"
                                                    data-caption="{{ $product->name }}" class="block relative group">
                                                    <video src="{{ Storage::url($product->video) }}"
                                                        class="w-16 h-16 object-cover rounded">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <div
                                                        class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        <svg class="w-8 h-8 text-white" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                                            </path>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                </a>
                                            @elseif($product->video_link)
                                                @php
                                                    $videoId = getYoutubeVideoId($product->video_link);
                                                @endphp
                                                @if ($videoId)
                                                    <a href="https://www.youtube.com/watch?v={{ $videoId }}"
                                                        data-fancybox="gallery" data-caption="{{ $product->name }}"
                                                        class="block relative group">
                                                        <img src="https://img.youtube.com/vi/{{ $videoId }}/0.jpg"
                                                            alt="{{ $product->name }}"
                                                            class="w-16 h-16 object-cover rounded">
                                                        <div
                                                            class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                            <svg class="w-8 h-8 text-white" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                                                </path>
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">Invalid YouTube
                                                        Link</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">No media</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-200">
                                            <div class="name-container">
                                                {{ $product->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <div class="about-container">
                                                <span
                                                    class="description-preview">{{ Str::limit($product->about, 100) }}</span>
                                                @if (strlen($product->about) > 100)
                                                    <span class="description-full hidden">{{ $product->about }}</span>
                                                    <button class="text-blue-500 hover:text-blue-600 read-more">Read
                                                        more</button>
                                                @endif
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $product->category->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <a href="{{ route('admin.products.edit', $product) }}"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-3 rounded">
                                                    Edit
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.products.destroy', $product) }}"
                                                    class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-3 rounded delete-button">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                            Tidak Ada Edukasi Yang Ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for full description -->
    <div id="descriptionModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Deskripsi Lengkap
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="full-description"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        id="close-modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />
        <style>
            .about-container,
            .name-container {
                max-height: 100px;
                overflow-y: auto;
                white-space: pre-wrap;
            }

            .fancybox__content>img {
                max-width: 100%;
                height: auto;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteButtons = document.querySelectorAll('.delete-button');
                const successAlert = document.getElementById('success-alert');
                const descriptionModal = document.getElementById('descriptionModal');
                const fullDescriptionElement = document.getElementById('full-description');
                const closeModalButton = document.getElementById('close-modal');
                const readMoreButtons = document.querySelectorAll('.read-more');

                // Delete confirmation
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();
                        const form = button.closest('.delete-form');

                        if (confirm('Apakah Anda yakin ingin menghapus Edukasi ini?')) {
                            form.submit();
                        }
                    });
                });

                // Auto-hide success alert
                if (successAlert) {
                    setTimeout(() => {
                        successAlert.style.display = 'none';
                    }, 5000);
                }

                // Fancybox initialization
                Fancybox.bind("[data-fancybox]", {
                    // Custom options
                });

                // Read more functionality
                readMoreButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const container = this.closest('.about-container');
                        const fullText = container.querySelector('.description-full').textContent;
                        fullDescriptionElement.textContent = fullText;
                        descriptionModal.classList.remove('hidden');
                    });
                });

                // Close modal
                closeModalButton.addEventListener('click', function() {
                    descriptionModal.classList.add('hidden');
                });

                // Close modal when clicking outside
                descriptionModal.addEventListener('click', function(e) {
                    if (e.target === descriptionModal) {
                        descriptionModal.classList.add('hidden');
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>
