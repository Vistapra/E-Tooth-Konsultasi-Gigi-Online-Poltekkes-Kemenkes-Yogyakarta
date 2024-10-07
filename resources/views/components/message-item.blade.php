<li class="px-6 py-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                From: {{ $message->from->name }} ({{ $message->from->email }})
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                To: {{ $message->to->name }} ({{ $message->to->email }})
            </p>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ $message->created_at->format('M d, Y H:i') }}
        </div>
    </div>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
        {{ Str::limit($message->body, 100) }}
    </p>
    @if ($message->is_ai_response)
        <div class="mt-2">
            <x-badge label="AI Response" color="blue" />
            <x-badge label="Confidence: {{ number_format($message->ai_confidence * 100, 2) }}%" color="gray" />
            <x-badge :label="ucfirst($message->sentiment)" :color="$message->sentiment === 'positive' ? 'green' : ($message->sentiment === 'negative' ? 'red' : 'yellow')" />
        </div>
        <div class="mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Keywords:
                {{ implode(', ', json_decode($message->keywords, true) ?? []) }}</span>
        </div>
    @endif
</li>
