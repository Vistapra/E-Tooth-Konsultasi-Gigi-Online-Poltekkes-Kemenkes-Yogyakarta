<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Chatify\MessagesController;
use Illuminate\Support\Facades\Log;
use App\Models\ChMessage;

class GenerateAIResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   protected $originalMessage;

    public $timeout = 300; // 5 minutes

    public function __construct(ChMessage $originalMessage)
{
    $this->originalMessage = $originalMessage;
}

    public function handle(MessagesController $controller)
{
    Log::info('GenerateAIResponse: Job started', ['messageId' => $this->originalMessage->id]);

    try {
        $controller->generateAIResponse($this->originalMessage);
        Log::info('GenerateAIResponse: Job completed', ['messageId' => $this->originalMessage->id]);
    } catch (\Exception $e) {
        Log::error('GenerateAIResponse: Unexpected error', [
            'messageId' => $this->originalMessage->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
}