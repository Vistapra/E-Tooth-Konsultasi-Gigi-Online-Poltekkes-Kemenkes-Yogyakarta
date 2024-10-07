<div class="bg-white overflow-hidden shadow sm:rounded-lg dark:bg-gray-900"
    @if (isset($route) && $route) onclick="window.location='{{ $route }}';" @endif>
    <div class="p-6">
        <dl>
            <dt class="text-sm leading-5 font-medium text-gray-500 truncate dark:text-gray-400">
                @if (isset($route) && $route)
                    <a href="{{ $route }}" class="hover:underline">
                        {{ $title }}
                    </a>
                @else
                    {{ $title }}
                @endif
            </dt>
            <dd class="mt-1 text-3xl leading-9 font-semibold text-indigo-600 dark:text-indigo-400">
                {{ $count }}
            </dd>
        </dl>
    </div>
</div>
