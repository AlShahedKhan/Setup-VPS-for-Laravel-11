<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatTitle;
use Illuminate\Http\Request;
use App\Jobs\ProcessChatMessage;
use App\Traits\HandlesApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;


class ChatController extends Controller
{
    use HandlesApiResponse;

    public function getChats($chat_title_id)
    {
        return $this->safeCall(function () use ($chat_title_id) {
            // Validate the chat_title_id
            $validator = Validator::make(['chat_title_id' => $chat_title_id], [
                'chat_title_id' => 'required|exists:chat_titles,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', 400, $validator->errors());
            }

            // Retrieve chats
            $chats = Chat::where('user_id', Auth::id())
                ->where('chat_title_id', $chat_title_id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($chats->isEmpty()) {
                return $this->errorResponse('No chat history found for this chat title.', 404);
            }

            // Sanitize chat fields
            $sanitizedChats = $chats->map(function ($chat) {
                $chat->content = !is_null($chat->content) ? $this->sanitizeText($chat->content) : null;
                $chat->response = !is_null($chat->response) ? $this->sanitizeText($chat->response) : null;
                return $chat;
            });

            return $this->successResponse('Chat history retrieved successfully.', $sanitizedChats->toArray());
        });
    }


    public function sendMessage(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            $request->validate([
                'chat_title_id' => 'required|exists:chat_titles,id',
                'query' => 'required|string|max:10000',
            ]);

            $chatTitleId = $request->input('chat_title_id'); // Correctly fetch chat_title_id
            $userMessage = $request->input('query'); // Correctly fetch query (the message)

            // Validate if the chat title belongs to the authenticated user
            $chatTitle = ChatTitle::where('id', $chatTitleId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$chatTitle) {
                return $this->errorResponse('Invalid chat title or you do not have access to it.', 403);
            }

            // Save the chat with a temporary response
            $chat = Chat::create([
                'user_id' => Auth::id(),
                'chat_title_id' => $chatTitleId,
                'query' => $this->sanitizeText($userMessage), // Ensure the correct input value is passed
                'response' => 'Processing...', // Placeholder response
                'thumbs_up' => 0,
                'thumbs_down' => 0,
            ]);

            // Process the message synchronously
            $aiResponse = (new ProcessChatMessage($chatTitleId, Auth::id(), $userMessage, $chat->id))->handle();

            // Update the chat with the AI response
            $chat->update(['response' => $aiResponse]);

            // Return the processed chat data
            return $this->successResponse('Message sent successfully.', [
                'id' => $chat->id,
                'user_id' => $chat->user_id,
                'chat_title_id' => $chat->chat_title_id,
                'message' => $chat->query,
                'response' => $chat->response,
                'created_at' => $chat->created_at,
                'updated_at' => $chat->updated_at,
            ]);
        });
    }


    // Helper function to sanitize text
    private function sanitizeText(string $text): string
    {
        // Preserve essential tags but remove closing </b>
        $text = strip_tags($text, '<b><ul><li>');

        // Remove specific closing tags like </b>
        $text = preg_replace('/<\/b>/', '', $text);

        // Remove escaped newline (\n) and tab (\t) characters
        $text = preg_replace('/\\\n|\\\t/', '', $text);

        // Replace multiple consecutive newline characters (\n\n) with a single space
        $text = preg_replace('/\n+/', ' ', $text);

        // Remove excessive spaces or newlines between tags
        $text = preg_replace('/>\s+</', '><', $text);

        // Replace multiple spaces with a single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim leading and trailing spaces
        return trim($text);
    }




    public function giveThumbs(Request $request, $chatId)
    {
        return $this->safeCall(function () use ($request, $chatId) {
            // Validate the input
            $request->validate([
                'thumb_type' => 'required|in:up,down', // Ensure thumb_type is either 'up' or 'down'
            ]);

            // Find the chat
            $chat = Chat::findOrFail($chatId);

            // Check if the user has already given a thumb for this chat
            $existingThumb = \DB::table('chat_thumbs')
                ->where('chat_id', $chatId)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingThumb) {
                // If the user is trying to give the same type of thumb again, throw an error
                if ($existingThumb->thumb_type === $request->thumb_type) {
                    return $this->errorResponse('You have already given a ' . $request->thumb_type . ' for this chat.', 403);
                }

                // If the user is changing the thumb type, update the thumbs count
                if ($request->thumb_type === 'up') {
                    // Decrement thumbs_down and increment thumbs_up
                    $chat->decrement('thumbs_down');
                    $chat->increment('thumbs_up');
                } else {
                    // Decrement thumbs_up and increment thumbs_down
                    $chat->decrement('thumbs_up');
                    $chat->increment('thumbs_down');
                }

                // Update the existing record in the chat_thumbs table
                \DB::table('chat_thumbs')
                    ->where('id', $existingThumb->id)
                    ->update(['thumb_type' => $request->thumb_type]);
            } else {
                // If no existing thumb, create a new record and update the thumbs count
                \DB::table('chat_thumbs')->insert([
                    'chat_id' => $chatId,
                    'user_id' => Auth::id(),
                    'thumb_type' => $request->thumb_type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($request->thumb_type === 'up') {
                    $chat->increment('thumbs_up');
                } else {
                    $chat->increment('thumbs_down');
                }
            }

            return $this->successResponse('Thumb updated successfully.', [
                'id' => $chat->id,
                'thumbs_up' => $chat->thumbs_up,
                'thumbs_down' => $chat->thumbs_down,
            ]);
        });
    }

    public function getTotalThumbs()
    {
        return $this->safeCall(function () {
            // Check if the authenticated user is an admin
            if (!Auth::user()->is_admin) {
                return $this->errorResponse(
                    'You are not authorized to perform this action.',
                    403
                );
            }
            $totalThumbsUp = Chat::sum('thumbs_up');
            $totalThumbsDown = Chat::sum('thumbs_down');

            return $this->successResponse('Total thumbs retrieved successfully.', [
                'total_thumbs_up' => $totalThumbsUp,
                'total_thumbs_down' => $totalThumbsDown,
            ]);
        });
    }

    public function regenerateResponse(Request $request, $chatId)
    {
        return $this->safeCall(function () use ($request, $chatId) {
            // Find the chat for the authenticated user
            $chat = Chat::where('id', $chatId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$chat) {
                return $this->errorResponse('Chat not found or you do not have access to it.', 404);
            }

            // Dispatch a job to regenerate the AI response and wait for completion
            $job = new \App\Jobs\RegenerateChatResponse($chat);
            $job->handle(); // Run the job synchronously

            // Fetch the updated chat details
            $updatedChat = Chat::find($chatId);

            if (!$updatedChat) {
                return $this->errorResponse('Failed to retrieve the updated chat.', 500);
            }

            // Return the updated chat data
            return $this->successResponse('Response regenerated successfully.', [
                'id' => $updatedChat->id,
                'user_id' => $updatedChat->user_id,
                'chat_title_id' => $updatedChat->chat_title_id,
                // 'message' => $updatedChat->message,
                'message' => $updatedChat->query,
                'response' => $updatedChat->response,
                'created_at' => $updatedChat->created_at,
                'updated_at' => $updatedChat->updated_at,
            ]);
        });
    }

    public function exportChats(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'chat_title_id' => 'required|exists:chat_titles,id', // Validate chat_title_id
                'min_id' => 'required|integer|min:0', // Required minimum ID range
                'max_id' => 'required|integer|min:1', // Required maximum ID range
            ]);

            $chatTitleId = $request->chat_title_id;
            $minId = $request->input('min_id'); // Start of the ID range
            $maxId = $request->input('max_id'); // End of the ID range

            // Check if the chat title belongs to the authenticated user
            $chatTitle = ChatTitle::where('id', $chatTitleId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$chatTitle) {
                return response()->json(['status' => false, 'message' => 'Invalid chat title or you do not have access to it.'], 403);
            }

            // Fetch chats for the given chat_title_id and within the ID range
            $chats = Chat::where('chat_title_id', $chatTitleId)
                ->where('user_id', Auth::id())
                ->whereBetween('id', [$minId, $maxId])
                ->get();

            if ($chats->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No chats found within the specified ID range.'], 404);
            }

            // Prepare data for CSV export
            $exportData = $chats->map(function ($chat) {
                return [
                    'Chat ID' => $chat->id,
                    // 'Message' => $chat->message,
                    'Message' => $chat->query,
                    'Response' => $chat->response
                ];
            });

            // Create a CSV file
            $csvData = $exportData->toArray();
            $headers = array_keys($csvData[0]);
            $csvContent = implode(',', $headers) . "\n";

            foreach ($csvData as $row) {
                $csvContent .= '"' . implode('","', $row) . '"' . "\n";
            }

            // Return the CSV file as a response
            $fileName = 'chats_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ]);
        } catch (\Exception $e) {
            // Return JSON response for exceptions
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.',
                'status_code' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function getTotalMessages()
    {
        return $this->safeCall(function () {
            // Check if the authenticated user is an admin
            if (!Auth::user()->is_admin) {
                return $this->errorResponse(
                    'You are not authorized to perform this action.',
                    403
                );
            }

            // Calculate the total number of messages
            $totalMessages = Chat::count();

            return $this->successResponse('Total messages retrieved successfully.', [
                'total_ask_questions' => $totalMessages,
            ]);
        });
    }
}
