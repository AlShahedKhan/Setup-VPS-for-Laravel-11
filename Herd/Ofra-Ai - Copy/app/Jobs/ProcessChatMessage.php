<?php

namespace App\Jobs;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatTitleId;
    protected $userId;
    protected $userMessage;
    protected $chatId;

    /**
     * Create a new job instance.
     *
     * @param int $chatTitleId
     * @param int $userId
     * @param string $userMessage
     * @param int|null $chatId
     */
    public function __construct($chatTitleId, $userId, $userMessage, $chatId = null)
    {
        $this->chatTitleId = $chatTitleId;
        $this->userId = $userId;
        $this->userMessage = $userMessage;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    // public function handle()
    // {
    //     // Make the API request
    //     $response = Http::post(env('ORFA_AI_API_URL'), [
    //         // 'message' => $this->sanitizeText($this->userMessage),
    //         'query' => $this->sanitizeText($this->userMessage),
    //         'conversation_history' => '',
    //     ]);

    //     // Extract and sanitize the AI response
    //     $aiResponse = $response->json()['response'] ?? 'Error fetching AI response';
    //     $aiResponse = $this->sanitizeText($aiResponse);

    //     // Update the existing chat if $chatId is provided, otherwise create a new one
    //     if ($this->chatId) {
    //         $chat = Chat::find($this->chatId);
    //         if ($chat) {
    //             $chat->update(['response' => $aiResponse]);
    //         }
    //     } else {
    //         Chat::create([
    //             'user_id' => $this->userId,
    //             'chat_title_id' => $this->chatTitleId,
    //             // 'message' => $this->sanitizeText($this->userMessage),
    //             'query' => $this->sanitizeText($this->userMessage),
    //             'response' => $aiResponse,
    //             'thumbs_up' => 0,
    //             'thumbs_down' => 0,
    //         ]);
    //     }

    //     return $aiResponse;
    // }

    /**
     * Execute the job.
     */
    /**
     * Execute the job.
     */
    public function handle()
    {
        // Fetch the API URL from configuration
        $apiUrl = config('services.orfa_ai.url');

        // Log the API URL for debugging purposes
        if (!$apiUrl) {
            \Log::error('ORFA_AI_API_URL is not set in the configuration.');
            return 'Error: ORFA_AI_API_URL is not configured.';
        }

        // Make the API request
        $response = Http::post($apiUrl, [
            'query' => $this->sanitizeText($this->userMessage),
            'conversation_history' => '',
        ]);

        // Extract and sanitize the AI response
        $aiResponse = $response->json()['response'] ?? 'Error fetching AI response';
        $aiResponse = $this->sanitizeText($aiResponse);

        // Update the existing chat if $chatId is provided, otherwise create a new one
        if ($this->chatId) {
            $chat = Chat::find($this->chatId);
            if ($chat) {
                $chat->update(['response' => $aiResponse]);
            }
        } else {
            Chat::create([
                'user_id' => $this->userId,
                'chat_title_id' => $this->chatTitleId,
                'query' => $this->sanitizeText($this->userMessage),
                'response' => $aiResponse,
                'thumbs_up' => 0,
                'thumbs_down' => 0,
            ]);
        }

        return $aiResponse;
    }


    /**
     * Sanitize text to remove unwanted characters and format it properly.
     *
     * @param string $text
     * @return string
     */
    private function sanitizeText(string $text): string
    {
        $text = strip_tags($text); // Remove HTML tags
        $text = preg_replace('/\\\n|\\\t/', '', $text); // Remove literal \n and \t
        $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces/newlines with a single space
        return trim($text); // Trim leading and trailing spaces
    }
}
