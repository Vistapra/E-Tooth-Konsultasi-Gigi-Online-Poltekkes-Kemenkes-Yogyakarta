<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi | E-Tooth</title>
    <link rel="shortcut icon" href="{{ asset('assets/svgs/Logo_depan.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .media-container {
            position: relative;
            width: 100%;
            height: 220px;
            /* Fixed height */
            overflow: hidden;
        }

        .media-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* This will crop the image/video to fill the container */
        }

        @media (min-width: 768px) {
            .media-container {
                max-width: 425px;
                margin: 0 auto;
            }
        }
    </style>
</head>


<body>
    <section class="relative flex items-center justify-between gap-5 wrapper">
        <a href="{{ route('front.index') }}" class="p-2 bg-white rounded-full">
            <img src="{{ asset('assets/svgs/ic-arrow-left.svg') }}" class="size-5" alt="">
        </a>
        <p class="absolute text-base font-semibold translate-x-1/2 -translate-y-1/2 top-1/2 right-1/2">
            Edukasi
        </p>
    </section>

    <div class="media-container">
        @if ($product->photo)
            <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}" class="media-content">
        @elseif ($product->video)
            <video controls class="media-content">
                <source src="{{ Storage::url($product->video) }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        @elseif ($product->video_link)
            @php
                $videoId = null;
                if (strpos($product->video_link, 'youtube.com') !== false) {
                    parse_str(parse_url($product->video_link, PHP_URL_QUERY), $params);
                    $videoId = $params['v'] ?? null;
                } elseif (strpos($product->video_link, 'youtu.be') !== false) {
                    $videoId = substr(parse_url($product->video_link, PHP_URL_PATH), 1);
                }
            @endphp
            @if ($videoId)
                <iframe src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen class="media-content"></iframe>
            @else
                <iframe src="{{ $product->video_link }}" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen class="media-content"></iframe>
            @endif
        @else
            <div class="media-content flex items-center justify-center bg-gray-200">
                <span class="text-gray-500">No Media Available</span>
            </div>
        @endif
    </div>

    <section
        class="bg-white rounded-t-[60px] pt-[60px] px-6 pb-5 -mt-9 flex flex-col gap-5 max-w-[425px] mx-auto items-center text-center">
        <div class="w-full flex flex-col items-center">
            <p class="font-bold text-[22px]">
                {{ $product->name }}
            </p>
            <div class="flex items-center gap-1.5 justify-center">
                <img src="{{ Storage::url($product->category->icon) }}" class="size-[30px]" alt="">
                <p class="font-semibold text-balance">
                    {{ $product->category->name }}
                </p>
            </div>
            <p class="mt-3.5 text-base leading-7 text-center">
                {{ $product->about }}
            </p>
        </div>
        <div class="flex justify-center mt-5">
            <a href="{{ route('front.konsultasi') }}"
                class="inline-flex w-max text-white font-bold text-base bg-primary rounded-full px-[30px] py-3 justify-center items-center whitespace-nowrap">
                Konsultasi Sekarang
            </a>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>

    <script src="{{ asset('scripts/sliderConfig.js') }}" type="module"></script>
</body>

</html>
