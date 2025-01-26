<?php
namespace App\Jobs;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class RegenerateChatResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chat;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Chat $chat
     */
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Make the API request to regenerate the response
        $response = Http::post(env('ORFA_AI_API_URL'), [
            // 'message' => $this->sanitizeText($this->chat->message), // Use sanitizeText for the message
            'message' => $this->sanitizeText($this->chat->query), // Use sanitizeText for the message
            // 'conversation_history' => '',
        ]);

        if (!$response->successful()) {
            // Log the error or handle it as needed
            return;
        }

        // Extract and sanitize the regenerated AI response
        $aiResponse = $this->sanitizeText($response->json()['response'] ?? 'Error fetching AI response');

        // Remove all thumbs from the chat_thumbs table
        \DB::table('chat_thumbs')
            ->where('chat_id', $this->chat->id)
            ->delete();

        // Update the chat response and reset thumbs
        $this->chat->update([
            'response' => $aiResponse,
            'thumbs_up' => 0, // Reset thumbs_up
            'thumbs_down' => 0, // Reset thumbs_down
        ]);
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

