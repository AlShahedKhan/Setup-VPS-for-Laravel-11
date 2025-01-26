<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');

        // Interact with OpenAI API
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        // Get the chatbot's reply
        $botReply = $response['choices'][0]['message']['content'];

        // Return the reply as JSON
        return response()->json(['reply' => $botReply], 200);
    }
}
