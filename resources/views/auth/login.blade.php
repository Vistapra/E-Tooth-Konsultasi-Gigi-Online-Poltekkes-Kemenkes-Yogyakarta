<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Tooth</title>
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

<body class="bg-gray-100 h-screen flex items-center justify-center" x-data="{ showNotification: false, message: '', isError: false }">
    <div class="flex flex-col items-center px-6 py-10 relative">
        <!-- Notification -->
        <div x-cloak x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" :class="isError ? 'bg-red-500' : 'bg-green-500'"
            class="fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg">
            <p x-text="message"></p>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login') }}"
            class="w-full max-w-2xl p-10 bg-white rounded-3xl shadow-lg" @submit.prevent="submitForm">
            @csrf
            <div class="flex flex-col gap-6">
                <a href="{{ route('front.index') }}">
                    <img src="{{ asset('assets/svgs/Logo_depan.svg') }}" alt="E-Tooth Logo" class="w-auto h-auto">
                </a>

                <!-- Email Address -->
                <div class="flex flex-col gap-4">
                    <label for="email" class="text-lg font-semibold">Email</label>
                    <input type="email" name="email" id="email" placeholder="Your email address"
                        class="pl-12 py-4 w-full border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                        style="background-image: url('{{ asset('assets/svgs/ic-email.svg') }}'); background-size: 24px; background-position: 10px center; background-repeat: no-repeat;"
                        value="{{ old('email') }}" required autofocus autocomplete="username" />
                </div>

                <!-- Password -->
                <div class="flex flex-col gap-4 mt-4">
                    <label for="password" class="text-lg font-semibold">Password</label>
                    <input type="password" name="password" id="password" placeholder="Protect your password"
                        class="pl-12 py-4 w-full border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                        style="background-image: url('{{ asset('assets/svgs/ic-lock.svg') }}'); background-size: 24px; background-position: 10px center; background-repeat: no-repeat;"
                        required autocomplete="current-password" />
                </div>

                <!-- Forgot Password -->
                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            href="{{ route('password.request') }}">
                            Lupa Password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3 mt-6 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition duration-300">
                    Masuk
                </button>

                <div class="w-full text-center mt-4">
                    <a class="text-gray-600 hover:text-gray-900 underline" href="{{ route('register') }}">
                        Belum Punya Akun?
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        function submitForm() {
            const form = document.getElementById('loginForm');
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
                    if (data.success) {
                        this.message = data.message;
                        this.isError = false;
                        this.showNotification = true;
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        this.message = data.message;
                        this.isError = true;
                        this.showNotification = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.message = 'An error occurred. Please try again.';
                    this.isError = true;
                    this.showNotification = true;
                });
        }
    </script>
</body>

</html>
