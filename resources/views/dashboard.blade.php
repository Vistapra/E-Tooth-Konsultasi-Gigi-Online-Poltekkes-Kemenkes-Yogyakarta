<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @role('owner')
            @php
                $dashboardData = app(App\Services\DashboardService::class)->getOwnerDashboardData();
            @endphp

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 sm:px-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <div class="mt-8 text-2xl font-medium text-gray-900 dark:text-gray-100">
                                Selamat Datang Di E-Tooth!
                            </div>
                        </div>

                        <div
                            class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 p-6 lg:p-8">
                            <!-- Kategori Edukasi -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Kategori Edukasi
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['categories'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-600 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="{{ route('admin.categories.index') }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View all
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Edukasi -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Edukasi
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['products'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-600 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="{{ route('admin.products.index') }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View all
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Dokter -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Dokter
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['doctors'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-600 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="{{ route('admin.doctor.index') }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View all
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Konsultasi -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Total Konsultasi
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['totalConsultations'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-600 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="{{ route('chatify') }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View all
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Pesan -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Total Pesan
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['totalMessages'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Responses -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    AI Responses
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['aiResponses'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unread Messages -->
                            <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-pink-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                    Unread Messages
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $dashboardData['unreadMessages'] }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 sm:px-20 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                            <div class="mt-8 text-2xl font-medium text-gray-900 dark:text-gray-100">
                                Recent Conversations
                            </div>
                            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($dashboardData['recentMessages'] as $message)
                                    @php
                                        $fromUser = $message->from;
                                        $toUser = $message->to;
                                        $otherUser = $fromUser->id === auth()->id() ? $toUser : $fromUser;
                                    @endphp
                                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow-sm sm:rounded-lg">
                                        <div
                                            class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <img class="h-10 w-10 rounded-full object-cover"
                                                        src="{{ $otherUser ? $otherUser->avatar : asset('path/to/default/avatar.png') }}"
                                                        alt="{{ $otherUser ? $otherUser->name . '\'s avatar' : 'Default avatar' }}">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $otherUser ? $otherUser->name : 'Unknown' }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $message->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('chatify') }}/{{ $otherUser->id }}"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    View Chat
                                                </a>
                                            </div>
                                            <div class="mt-4">
                                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                                    {{ Str::limit($message->body, 100) }}
                                                </p>
                                            </div>
                                            <div class="mt-4">
                                                <form class="reply-form"
                                                    data-to-id="{{ $otherUser ? $otherUser->id : '' }}">
                                                    <textarea name="message" rows="2"
                                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-800 dark:text-gray-300"
                                                        placeholder="Type your reply..."></textarea>
                                                    <button type="submit"
                                                        class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Reply
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-6">
                                {{ $dashboardData['recentMessages']->links() }}
                            </div>
                            <div class="mt-8 text-center">
                                <a href="{{ route('chatify') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View All Conversations
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const forms = document.querySelectorAll('.reply-form');
                            forms.forEach(form => {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const toId = this.dataset.toId;
                                    const message = this.querySelector('textarea[name="message"]').value;
                                    const submitButton = this.querySelector('button[type="submit"]');
                                    const originalButtonText = submitButton.innerText;

                                    submitButton.disabled = true;
                                    submitButton.innerText = 'Sending...';

                                    fetch('{{ route('chatify.send') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({
                                                id: toId,
                                                message: message
                                            })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === '200') {
                                                this.querySelector('textarea[name="message"]').value = '';
                                                alert('Message sent successfully');
                                            } else {
                                                alert('Failed to send message');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            alert('An error occurred while sending the message');
                                        })
                                        .finally(() => {
                                            submitButton.disabled = false;
                                            submitButton.innerText = originalButtonText;
                                        });
                                });
                            });
                        });
                    </script>
                @endpush
            @endrole

            @role('doctor|buyer')
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div
                                class="p-6 sm:px-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <div class="mt-8 text-2xl font-medium text-gray-900 dark:text-gray-100">
                                    Selamat Datang Dokter E-Tooth!
                                </div>

                                <div class="mt-6 text-gray-500 dark:text-gray-400">
                                    Silahkan Kunjungi Chat Untuk Melihat Konsultasi
                                </div>

                                <div class="mt-8">
                                    <a href="{{ route('chatify') }}"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                        Go to Chat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endrole
        </div>
</x-app-layout>
