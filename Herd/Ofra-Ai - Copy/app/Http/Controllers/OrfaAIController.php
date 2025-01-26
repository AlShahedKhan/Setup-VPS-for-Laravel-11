<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\HandlesApiResponse;

class OrfaAIController extends Controller
{
    use HandlesApiResponse;

    // public function sendMessage(Request $request)
    // {
    //     return $this->safeCall(function () use ($request) {
    //         // Validate the request input
    //         $request->validate([
    //             'message' => 'required|string|max:1000',
    //             'conversation_history' => 'nullable|string',
    //         ]);

    //         // Prepare the payload
    //         $payload = [
    //             'message' => $this->sanitizeText($request->input('message')),
    //             'conversation_history' => $this->sanitizeText($request->input('conversation_history', '')),
    //         ];

    //         // Make the API request to the secondary OrfaAI endpoint
    //         $response = Http::post(env('ORFA_AI_API_URL_SECONDARY'), $payload);

    //         // Handle errors or a failed request
    //         if (!$response->successful()) {
    //             return $this->errorResponse(
    //                 'Failed to connect to OrfaAI.',
    //                 $response->status(),
    //                 $response->body()
    //             );
    //         }

    //         // Extract and sanitize the AI response
    //         $data = $response->json();

    //         // Sanitize and clean the fields
    //         $cleanedData = [
    //             'response' => isset($data['response']) ? $this->sanitizeText($data['response']) : 'No response received.',
    //             'conversation_history' => isset($data['conversation_history']) ? $this->sanitizeText($data['conversation_history']) : '',
    //         ];

    //         // Return the sanitized response in the desired format
    //         return $this->successResponse('Response received successfully.', $cleanedData, 200);
    //     });
    // }


    // working 10.41 1/25/25
    // public function sendMessage(Request $request)
    // {
    //     return $this->safeCall(function () use ($request) {
    //         // Validate request input
    //         $request->validate([
    //             'message' => 'required|string|max:1000',
    //             'conversation_history' => 'nullable|string',
    //         ]);

    //         // Prepare the payload
    //         $payload = [
    //             'message' => $this->sanitizeText($request->input('message')),
    //             'conversation_history' => $this->sanitizeText($request->input('conversation_history', '')),
    //         ];

    //         // Set the API URL
    //         $url = env('ORFA_AI_API_URL_SECONDARY', 'https://orca-app-lz9i6.ondigitalocean.app/api/chat');

    //         // Log the payload
    //         \Log::info('Payload sent to ORFA AI:', $payload);

    //         // Make the API request
    //         $response = Http::timeout(30)
    //             ->withHeaders([
    //                 'Accept' => 'application/json',
    //             ])
    //             ->post($url, $payload);

    //         // Log the response details
    //         \Log::info('Response Status:', ['status' => $response->status()]);
    //         \Log::info('Response Body:', ['body' => $response->body()]);

    //         // Extract and combine content fields from the response body
    //         $responseBody = $response->body();
    //         $contentPieces = [];
    //         if (preg_match_all('/data: ({.*?})/', $responseBody, $matches)) {
    //             foreach ($matches[1] as $jsonString) {
    //                 $decoded = json_decode($jsonString, true);
    //                 if (isset($decoded['content'])) {
    //                     $contentPieces[] = $decoded['content'];
    //                 }
    //             }
    //         }

    //         // Combine all content pieces into a single string
    //         $combinedContent = implode('', $contentPieces);

    //         // Check if combined content is empty
    //         if (empty($combinedContent)) {
    //             \Log::warning('API returned no meaningful content.');
    //             return $this->successResponse('Response received successfully, but no content returned.', null, 200);
    //         }

    //         // Return the combined content
    //         return $this->successResponse('Response received successfully.', [
    //             'response' => $combinedContent,
    //         ], 200);
    //     });
    // }

    public function sendMessage(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            // Validate request input
            $request->validate([
                'message' => 'required|string|max:1000',
                'conversation_history' => 'nullable|string',
            ]);

            // Prepare the payload
            $payload = [
                'message' => $this->sanitizeText($request->input('message')),
                'conversation_history' => $this->sanitizeText($request->input('conversation_history', '')),
            ];

            // Set the API URL
            $url = env('ORFA_AI_API_URL_SECONDARY', 'https://orca-app-lz9i6.ondigitalocean.app/api/chat');

            // Log the payload
            \Log::info('Payload sent to ORFA AI:', $payload);

            // Make the API request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            // Log the response details
            \Log::info('Response Status:', ['status' => $response->status()]);
            \Log::info('Response Body:', ['body' => $response->body()]);

            // Extract and combine content fields from the response body
            $responseBody = $response->body();
            $contentPieces = [];
            if (preg_match_all('/data: ({.*?})/', $responseBody, $matches)) {
                foreach ($matches[1] as $jsonString) {
                    $decoded = json_decode($jsonString, true);
                    if (isset($decoded['content'])) {
                        $contentPieces[] = $decoded['content'];
                    }
                }
            }

            // Combine all content pieces into a single string
            $combinedContent = implode('', $contentPieces);

            // Check if combined content is empty
            if (empty($combinedContent)) {
                \Log::warning('API returned no meaningful content.');
                return $this->successResponse('Response received successfully, but no content returned.', null, 200);
            }

            // Split the combined content into an array of words
            $wordsArray = preg_split('/\s+/', $combinedContent);

            // Return the response with words in an array
            return $this->successResponse('Response received successfully.', [
                'response' => $wordsArray,
            ], 200);
        });
    }








    //  working 1.22.2025
    /**
     * Sanitize and clean the given text.
     *
     * @param string $text
     * @return string
     */
    // private function sanitizeText(string $text): string
    // {
    //     $text = strip_tags($text); // Remove HTML tags
    //     $text = preg_replace('/\\\n|\\\t/', '', $text); // Remove literal \n and \t
    //     $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces/newlines with a single space
    //     return trim($text); // Trim leading and trailing spaces
    // }

    private function sanitizeText(string $text): string
    {
        $text = strip_tags($text); // Remove HTML tags
        $text = preg_replace('/\\\n|\\\t|\\\r/', '', $text); // Remove literal \n, \t, and \r
        $text = preg_replace('/\n+/', ' ', $text); // Remove multiple newlines
        $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces/newlines with a single space
        return trim($text); // Trim leading and trailing spaces
    }
}
