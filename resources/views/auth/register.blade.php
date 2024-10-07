<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | E-Tooth</title>
    <link rel="shortcut icon" href="{{ asset('assets/svgs/Logo_depan.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center" x-data="{ showNotification: false, message: '' }">
    <div class="flex flex-col items-center px-6 py-10 relative">
        <!-- Notification -->
        <div x-cloak x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
            @notify.window="message = $event.detail; showNotification = true; setTimeout(() => showNotification = false, 3000)">
            <p x-text="message"></p>
        </div>

        <form method="POST" action="{{ route('register') }}"
            class="w-full max-w-2xl p-10 bg-white rounded-3xl shadow-lg" @submit.prevent="submitForm($event)">
            @csrf
            <div class="flex flex-col gap-6">
                <a href="{{ route('front.index') }}">
                    <img src="{{ asset('assets/svgs/Logo_depan.svg') }}" alt="E-Tooth Logo" class="w-auto h-auto">
                </a>

                <!-- Name -->
                <div class="w-full">
                    <label for="name" class="block text-sm font-semibold mb-2">Nama</label>
                    <input type="text" name="name" id="name" placeholder="Your name"
                        class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                        value="{{ old('name') }}" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="w-full mt-4">
                    <label for="email" class="block text-sm font-semibold mb-2">Email</label>
                    <input type="email" name="email" id="email" placeholder="Your email address"
                        class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                        value="{{ old('email') }}" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="w-full mt-4" x-data="{ showPassword: false }">
                    <label for="password" class="block text-sm font-semibold mb-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password"
                            placeholder="Protect your password"
                            class="w-full p-3 pr-10 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                            required autocomplete="new-password" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                viewbox="0 0 576 512">
                                <path fill="currentColor"
                                    d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z">
                                </path>
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                viewbox="0 0 640 512">
                                <path fill="currentColor"
                                    d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <p class="text-sm text-gray-600 mt-1">Password harus minimal 8 karakter, mengandung huruf besar,
                    huruf kecil, angka, dan karakter khusus.</p>
            </div>

            <!-- Confirm Password -->
            <div class="w-full mt-4" x-data="{ showConfirmPassword: false }">
                <label for="password_confirmation" class="block text-sm font-semibold mb-2">Konfirmasi Password</label>
                <div class="relative">
                    <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation"
                        id="password_confirmation" placeholder="Confirm your password"
                        class="w-full p-3 pr-10 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                        required autocomplete="new-password" />
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg x-show="!showConfirmPassword" class="h-5 w-5 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg" viewbox="0 0 576 512">
                            <path fill="currentColor"
                                d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z">
                            </path>
                        </svg>
                        <svg x-show="showConfirmPassword" class="h-5 w-5 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg" viewbox="0 0 640 512">
                            <path fill="currentColor"
                                d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Hidden Role Input -->
            <input type="hidden" name="role" value="buyer">

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-3 mt-6 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition duration-300">
                Daftar
            </button>

            <div class="w-full text-center mt-4">
                <a class="text-gray-600 hover:text-gray-900 underline" href="{{ route('login') }}">
                    Sudah Punya Akun?
                </a>
            </div>
    </div>
    </form>
    </div>

    <script>
        function submitForm(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const message = data.errors[key][0];
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: message
                            }));
                        });
                    } else if (data.message) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: data.message
                        }));
                        setTimeout(() => {
                            window.location.href = data.redirect || "{{ route('front.konsultasi') }}";
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: 'An error occurred. Please try again.'
                    }));
                });
        }
    </script>
</body>

</html>
