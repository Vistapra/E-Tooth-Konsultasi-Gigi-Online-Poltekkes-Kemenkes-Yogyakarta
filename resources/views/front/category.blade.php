<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Edukasi | E-Tooth</title>
    <link rel="shortcut icon" href="{{ asset('assets/svgs/Logo_depan.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <!-- Topbar -->
    <section class="relative flex items-center justify-between gap-5 wrapper">
        <a href="{{ route('front.index') }}" class="p-2 bg-white rounded-full">
            <img src="{{ asset('assets/svgs/ic-arrow-left.svg') }}" class="size-5" alt="Back">
        </a>
        <p class="absolute text-base font-semibold translate-x-1/2 -translate-y-1/2 top-1/2 right-1/2">
            {{ $category->name }}
        </p>
    </section>

    <!-- Category Results -->
    <section class="wrapper flex flex-col gap-2.5">
        <div class="flex flex-col gap-4">
            @forelse($products as $product)
                <div class="py-3.5 pl-4 pr-[22px] bg-white rounded-2xl flex gap-1 items-center container-relative">
                    <div class="w-[70px] h-[70px] relative overflow-hidden">
                        @if ($product->photo)
                            <img src="{{ Storage::url($product->photo) }}" class="w-full h-full object-cover"
                                alt="{{ $product->name }}">
                        @elseif ($product->video)
                            <video class="w-full h-full object-cover">
                                <source src="{{ Storage::url($product->video) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @elseif ($product->video_link)
                            @php
                                $videoId = getYoutubeVideoId($product->video_link);
                            @endphp
                            @if ($videoId)
                                <img src="https://img.youtube.com/vi/{{ $videoId }}/0.jpg"
                                    class="w-full h-full object-cover" alt="{{ $product->name }}">
                            @else
                                <iframe class="w-full h-full" src="{{ $product->video_link }}" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            @endif
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-500">No Media</span>
                            </div>
                        @endif
                        @if ($product->video || $product->video_link)
                            <div class="absolute top-1 right-1 bg-blue-500 rounded-full p-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center justify-between w-full gap-1">
                        <div class="flex flex-col gap-1">
                            <a href="{{ route('front.product.details', $product->slug) }}"
                                class="text-base font-semibold whitespace-nowrap w-[150px] truncate">
                                {{ $product->name }}
                            </a>
                            <p class="text-sm text-grey whitespace-nowrap w-[150px] truncate">
                                {{ $product->about }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No products found in this category.</p>
            @endforelse
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @php
        function getYoutubeVideoId($url)
        {
            $videoId = null;
            if (strpos($url, 'youtube.com') !== false) {
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                $videoId = $params['v'] ?? null;
            } elseif (strpos($url, 'youtu.be') !== false) {
                $videoId = substr(parse_url($url, PHP_URL_PATH), 1);
            }
            return $videoId;
        }
    @endphp
</body>

</html>
