<!-- resources/views/history.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Konsultasi - E-Tooth</title>
    <link rel="shortcut icon" href="{{ asset('assets/svgs/Logo_depan.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <nav class="fixed z-50 bottom-[30px] bg-black rounded-[50px] pt-[18px] px-10 left-1/2 -translate-x-1/2 w-80">
        <div class="flex items-center justify-center gap-5 flex-nowrap">
            <a href="{{ route('front.index') }}" class="flex flex-col items-center justify-center gap-1 px-1 group">
                <img src="{{ asset('assets/svgs/ic-grid.svg') }}" class="filter-to-grey group-hover:filter-to-primary"
                    alt="">
                <p
                    class="border-b-4 border-transparent group-hover:border-primary pb-3 text-xs text-center font-semibold text-grey group-hover:text-primary">
                    Edukasi</p>
            </a>
            <a href="{{ route('front.konsultasi') }}"
                class="flex flex-col items-center justify-center gap-1 px-1 group">
                <img src="{{ asset('assets/svgs/ic-consultation.svg') }}"
                    class="filter-to-grey group-hover:filter-to-primary" alt="">
                <p
                    class="border-b-4 border-transparent group-hover:border-primary pb-3 text-xs text-center font-semibold text-grey group-hover:text-primary">
                    Konsultasi</p>
            </a>
            <a href="{{ route('front.riwayat') }}"
                class="flex flex-col items-center justify-center gap-1 px-1 group is-active">
                <img src="{{ asset('assets/svgs/ic-note.svg') }}"
                    class="filter-to-grey group-[.is-active]:filter-to-primary" alt="">
                <p
                    class="border-b-4 border-transparent group-[.is-active]:border-primary pb-3 text-xs text-center font-semibold text-grey group-[.is-active]:text-primary">
                    Riwayat</p>
            </a>
        </div>
    </nav>

    <section class="wrapper flex flex-col gap-2.5 items-center justify-center p-4 md:p-8">
        <p
            class="text-5xl md:text-6xl font-bold text-center bg-gradient-to-r from-[#8AD1C1] to-[#68B4E5] bg-clip-text text-transparent">
            Riwayat
        </p>
        <form action="{{ route('front.search') }}" method="GET" id="searchForm" class="w-full max-w-lg">
            <input type="text" name="keyword" id="searchProduct"
                class="block w-full py-3.5 pl-4 pr-10 rounded-full font-semibold placeholder:text-grey placeholder:font-normal text-black text-base bg-no-repeat bg-[calc(100%-16px)] focus:ring-2 focus:ring-primary focus:outline-none focus:border-none transition-all"
                placeholder="Cari Riwayat Konsultasi...">
        </form>
    </section>



    <section class="wrapper flex flex-col gap-2.5 pb-40 mt-10">
        <div class="flex flex-col gap-4">
            @forelse ($users as $user)
                <div class="py-3.5 pl-4 pr-[22px] bg-white rounded-2xl flex gap-1 items-center relative">
                    <img src="{{ Storage::url($user->doctor->photo ?? 'avatar.png') }}"
                        class="w-full max-w-[70px] max-h-[70px] object-contain" alt="{{ $user->name }}">
                    <div class="flex flex-wrap items-center justify-between w-full gap-1">
                        <div class="flex flex-col gap-1">
                            <a href="{{ url('chat', $user->id) }}"
                                class="text-base font-semibold stretched-link whitespace-nowrap w-[150px] truncate">{{ $user->name }}</a>
                            <p class="text-sm text-grey">
                                {{ $user->latestMessage ? Str::limit($user->latestMessage->body, 30) : 'Tidak ada pesan' }}
                            </p>
                        </div>
                        <div class="flex">
                            <img src="{{ asset('assets/svgs/ic-note.svg') }}" class="w-[35px] h-[35px]"
                                alt="Chat Icon">
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-grey">Belum ada riwayat</p>
            @endforelse

        </div>
    </section>

    <script>
        function redirectToLogin() {
            window.location.href = "{{ route('dashboard') }}";
        }
    </script>
</body>

</html>
