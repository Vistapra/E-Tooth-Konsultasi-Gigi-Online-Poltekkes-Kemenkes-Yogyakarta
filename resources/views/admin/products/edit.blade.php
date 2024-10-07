<x-app-layout>
    <?php
    // Add this to your PHP file (e.g., app/Helpers/YouTubeHelper.php)
    
    function getFlexibleYoutubeEmbed($url)
    {
        $videoId = extractYoutubeId($url);
        if (!$videoId) {
            return ['type' => 'error', 'message' => 'Invalid YouTube URL'];
        }
    
        $embedUrls = ['https://www.youtube-nocookie.com/embed/' . $videoId, 'https://www.youtube.com/embed/' . $videoId, 'https://www.youtube.com/watch?v=' . $videoId];
    
        $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/0.jpg';
    
        return [
            'type' => 'video',
            'videoId' => $videoId,
            'embedUrls' => $embedUrls,
            'watchUrl' => 'https://www.youtube.com/watch?v=' . $videoId,
            'thumbnailUrl' => $thumbnailUrl,
        ];
    }
    
    function extractYoutubeId($url)
    {
        $pattern = '~(?:https?://)?(?:www\.)?(?:youtube\.com|youtu\.be)/(?:watch\?v=)?(?:embed/)?(?:v/)?(?:shorts/)?(\w+)~i';
    
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    
        return null;
    }
    
    // Make sure to include or autoload this file where needed
    
    ?>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Edukasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="p-4 mb-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold">Whoops! Terjadi beberapa kesalahan:</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name', $product->name)" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category_id" :value="__('Kategori')" />
                            <select name="category_id" id="category_id"
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!-- Media Type Selection -->
                        <div class="mt-4">
                            <x-input-label :value="__('Pilih Jenis Media')" />
                            <div class="mt-2 space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" name="media_type" value="photo"
                                        {{ $product->photo ? 'checked' : '' }}>
                                    <span class="ml-2">Foto</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" class="form-radio" name="media_type" value="video"
                                        {{ $product->video ? 'checked' : '' }}>
                                    <span class="ml-2">Video</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" class="form-radio" name="media_type" value="video_link"
                                        {{ $product->video_link ? 'checked' : '' }}>
                                    <span class="ml-2">Link Video YouTube</span>
                                </label>
                            </div>
                        </div>

                        <!-- Photo -->
                        <div id="photo-input" class="mt-4 {{ $product->photo ? '' : 'hidden' }}">
                            <x-input-label for="photo" :value="__('Foto')" />
                            @if ($product->photo)
                                <img src="{{ Storage::url($product->photo) }}" alt="Current Photo"
                                    class="w-32 h-32 object-cover mb-2 rounded">
                            @endif
                            <x-text-input id="photo" class="block mt-1 w-full" type="file" name="photo" />
                            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        </div>

                        <!-- Video -->
                        <div id="video-input" class="mt-4 {{ $product->video ? '' : 'hidden' }}">
                            <x-input-label for="video" :value="__('Video')" />
                            @if ($product->video)
                                <video src="{{ Storage::url($product->video) }}" controls
                                    class="w-full max-w-md mb-2 rounded">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                            <x-text-input id="video" class="block mt-1 w-full" type="file" name="video"
                                accept="video/*" />
                            <x-input-error :messages="$errors->get('video')" class="mt-2" />
                        </div>

                        <!-- Video Link -->
                        <div id="video-link-input" class="mt-4 {{ $product->video_link ? '' : 'hidden' }}">
                            <x-input-label for="video_link" :value="__('Link Video YouTube')" />
                            <x-text-input id="video_link" class="block mt-1 w-full" type="url" name="video_link"
                                :value="old('video_link', $product->video_link)" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ" />
                            <x-input-error :messages="$errors->get('video_link')" class="mt-2" />
                            <p class="text-sm text-gray-600 mt-1">Masukkan URL video YouTube (contoh:
                                https://www.youtube.com/watch?v=dQw4w9WgXcQ)</p>
                            @if ($product->video_link)
                                <div class="mt-4">
                                    <h3 class="text-lg font-semibold mb-2">Preview Video:</h3>
                                    @php
                                        $videoData = getFlexibleYoutubeEmbed($product->video_link);
                                    @endphp
                                    @if ($videoData['type'] === 'video')
                                        <div id="youtube-preview" class="aspect-w-16 aspect-h-9">
                                            <iframe id="youtube-iframe" src="{{ $videoData['embedUrls'][0] }}"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen class="w-full h-full">
                                            </iframe>
                                        </div>
                                        <p class="mt-2">
                                            <a href="{{ $videoData['watchUrl'] }}" target="_blank"
                                                class="text-blue-500 hover:underline">
                                                Watch on YouTube
                                            </a>
                                        </p>
                                    @else
                                        <p class="text-red-500">{{ $videoData['message'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- About -->
                        <div class="mt-4">
                            <x-input-label for="about" :value="__('Deskripsi Edukasi')" />
                            <textarea id="about" name="about"
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                rows="5" required>{{ old('about', $product->about) }}</textarea>
                            <x-input-error :messages="$errors->get('about')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Edukasi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mediaTypeRadios = document.querySelectorAll('input[name="media_type"]');
                const photoInput = document.getElementById('photo-input');
                const videoInput = document.getElementById('video-input');
                const videoLinkInput = document.getElementById('video-link-input');
                const videoLinkField = document.getElementById('video_link');

                function showSelectedInput() {
                    const selectedValue = document.querySelector('input[name="media_type"]:checked').value;
                    photoInput.classList.add('hidden');
                    videoInput.classList.add('hidden');
                    videoLinkInput.classList.add('hidden');

                    if (selectedValue === 'photo') {
                        photoInput.classList.remove('hidden');
                    } else if (selectedValue === 'video') {
                        videoInput.classList.remove('hidden');
                    } else if (selectedValue === 'video_link') {
                        videoLinkInput.classList.remove('hidden');
                    }
                }

                function getYoutubeEmbedUrl(url) {
                    const pattern =
                        /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(?:embed\/)?(?:v\/)?(?:shorts\/)?(\w+)/i;
                    const match = url.match(pattern);
                    return match ? 'https://www.youtube-nocookie.com/embed/' + match[1] + '?rel=0&showinfo=0' : null;
                }

                function updateYoutubePreview() {
                    const videoLinkValue = videoLinkField.value.trim();
                    const previewContainer = document.getElementById('youtube-preview');

                    if (videoLinkValue) {
                        const embedUrl = getYoutubeEmbedUrl(videoLinkValue);
                        if (embedUrl) {
                            previewContainer.innerHTML = `
                        <iframe src="${embedUrl}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            class="w-full h-full">
                        </iframe>
                    `;
                        } else {
                            previewContainer.innerHTML = '<p class="text-red-500">Invalid YouTube URL</p>';
                        }
                    } else {
                        previewContainer.innerHTML =
                            '<p class="text-gray-500">Enter a YouTube URL to see a preview</p>';
                    }
                }

                mediaTypeRadios.forEach(radio => {
                    radio.addEventListener('change', showSelectedInput);
                });

                videoLinkField.addEventListener('input', updateYoutubePreview);

                // Show the initial selected input
                showSelectedInput();

                // Update video preview if there's an initial value
                if (videoLinkField.value) {
                    updateYoutubePreview();
                }

                // Handle YouTube embed fallback
                const iframe = document.getElementById('youtube-iframe');
                if (iframe) {
                    const embedUrls = @json($videoData['embedUrls'] ?? []);
                    let currentUrlIndex = 0;

                    function tryNextEmbedUrl() {
                        currentUrlIndex++;
                        if (currentUrlIndex < embedUrls.length) {
                            iframe.src = embedUrls[currentUrlIndex];
                        } else {
                            iframe.style.display = 'none';
                            iframe.insertAdjacentHTML('afterend',
                                '<p class="text-yellow-500">Video tidak dapat diputar. Silakan coba tonton di YouTube.</p>'
                            );
                        }
                    }

                    iframe.addEventListener('error', tryNextEmbedUrl);
                }
            });
        </script>
    @endpush
</x-app-layout>
