<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Edukasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="w-full rounded-3xl bg-red-500 text-white p-4 mb-4">
                            {{ $error }}
                        </div>
                    @endforeach
                @endif

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category_id" :value="__('Kategori')" />
                            <select name="category_id" id="category_id" class="block mt-1 w-full text-black" required>
                                <option value="" class="text-black">Pilih Kategori Edukasi</option>
                                @forelse($categories as $kategori)
                                    <option value="{{ $kategori->id }}" class="text-black">{{ $kategori->name }}
                                    </option>
                                @empty
                                    <option value="" class="text-black">Tidak ada kategori</option>
                                @endforelse
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!-- Media Type Selection -->
                        <div class="mt-4">
                            <x-input-label :value="__('Pilih Jenis Media')" />
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" name="media_type" value="photo" checked>
                                    <span class="ml-2">Foto</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" class="form-radio" name="media_type" value="video">
                                    <span class="ml-2">Video</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" class="form-radio" name="media_type" value="video_link">
                                    <span class="ml-2">Link Video</span>
                                </label>
                            </div>
                        </div>

                        <!-- Photo -->
                        <div id="photo-input" class="mt-4">
                            <x-input-label for="photo" :value="__('Foto')" />
                            <x-text-input id="photo" class="block mt-1 w-full" type="file" name="photo"
                                autofocus autocomplete="photo" />
                            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        </div>

                        <!-- Video -->
                        <div id="video-input" class="mt-4 hidden">
                            <x-input-label for="video" :value="__('Video')" />
                            <x-text-input id="video" class="block mt-1 w-full" type="file" name="video"
                                autofocus autocomplete="video" />
                            <x-input-error :messages="$errors->get('video')" class="mt-2" />
                        </div>

                        <!-- Video Link -->
                        <div id="video-link-input" class="mt-4 hidden">
                            <x-input-label for="video_link" :value="__('Link Video')" />
                            <x-text-input id="video_link" class="block mt-1 w-full" type="url" name="video_link"
                                :value="old('video_link')" autofocus autocomplete="video_link" />
                            <x-input-error :messages="$errors->get('video_link')" class="mt-2" />
                        </div>

                        <!-- About -->
                        <div class="mt-4">
                            <x-input-label for="about" :value="__('Deskripsi Edukasi')" />
                            <textarea name="about" id="about" cols="30" rows="5"
                                class="border border-slate-300 rounded-xl w-full text-black" required>{{ old('about') }}</textarea>
                            <x-input-error :messages="$errors->get('about')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Tambah Edukasi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mediaTypeRadios = document.querySelectorAll('input[name="media_type"]');
            const photoInput = document.getElementById('photo-input');
            const videoInput = document.getElementById('video-input');
            const videoLinkInput = document.getElementById('video-link-input');

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

            mediaTypeRadios.forEach(radio => {
                radio.addEventListener('change', showSelectedInput);
            });

            // Show the initial selected input
            showSelectedInput();
        });
    </script>
</x-app-layout>
