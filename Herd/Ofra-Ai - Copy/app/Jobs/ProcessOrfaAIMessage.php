<?php

namespace App\Jobs;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessOrfaAIMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Make the API request to the secondary OrfaAI endpoint
        $response = Http::post(env('ORFA_AI_API_URL_SECONDARY'), $this->payload);

        // Check for a successful response
        if (!$response->successful()) {
            // Optionally log the failure or retry based on your use case
            throw new \Exception('Failed to connect to OrfaAI.');
        }

        // Process the response
        $data = $response->json();

        // Sanitize the response data
        $conversationHistory = isset($data['conversation_history']) ? $this->sanitizeText($data['conversation_history']) : '';
        $aiResponse = isset($data['response']) ? $this->sanitizeText($data['response']) : 'No response received.';

        // Save the chat in the database
        Chat::create([
            'user_id' => null, // Replace with actual user ID if available
            'chat_title_id' => null, // Replace with actual chat title ID if required
            'message' => $this->payload['message'],
            'response' => $aiResponse,
            'thumbs_up' => 0,
            'thumbs_down' => 0,
        ]);
    }

    /**
     * Sanitize and clean the given text.
     *
     * @param string $text
     * @return string
     */
    // private function sanitizeText(string $text): string
    // {
    //     $text = strip_tags($text);
    //     $text = preg_replace('/\s+/', ' ', $text);
    //     $text = preg_replace('/\\\n|\\\t/', '', $text);
    //     $text = preg_replace('/^\d+\./m', '', $text);
    //     return trim($text);
    // }

    private function sanitizeText(string $text): string
    {
        $text = strip_tags($text); // Remove HTML tags
        $text = preg_replace('/\\\n|\\\t|\\\r/', '', $text); // Remove literal \n, \t, \r
        $text = preg_replace('/\n+/', ' ', $text); // Remove multiple newlines (\n)
        $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces with a single space
        $text = preg_replace('/^\d+\./m', '', $text); // Remove numbering like 1., 2., 3., etc.
        $text = preg_replace('/\*\*/', '', $text); // Remove double asterisks (**)
        return trim($text); // Trim leading and trailing spaces
    }
}
