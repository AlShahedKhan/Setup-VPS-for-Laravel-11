<?php

use App\Models\ChatTitle;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrfaAIController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ChatTitleController;
use App\Http\Controllers\CaseExampleController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\TestimonialController;
// use App\Jobs\TestJob;



Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);
Route::post('forgot-password', [ApiController::class, 'forgotPassword']);
Route::post('verify-otp', [ApiController::class, 'verifyOtp']);
Route::post('reset-password', [ApiController::class, 'resetPassword']);

Route::post('/contact-form', [ContactFormController::class, 'submit']);

Route::post('/contact-form-send', [ContactFormController::class, 'submitSend']);

Route::post('/chat', [ChatbotController::class, 'handleChat']);

Route::post('/orfa-ai/chat', [OrfaAIController::class, 'sendMessage']);

Route::group([
    "middleware" => ["auth:api"]
], function () {

    Route::patch('/users/{user}/approve', [ApiController::class, 'approveUser']);

    Route::get("users", [ApiController::class, "users"]);
    Route::post("users/create", [ApiController::class, "store"]);
    Route::get("users/show", [ApiController::class, "getAllUsers"]);
    Route::post("users/active-inactive/{id}", [ApiController::class, "activeInactive"]);
    Route::delete("users/delete/{id}", [ApiController::class, "destroy"]);

    Route::post('/users/search', [ApiController::class, 'search']);

    Route::get("profile", [ApiController::class, "profile"]);
    Route::put("update-profile", [UserController::class, "updateProfile"]);
    Route::get("refresh-token", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    Route::get('users/graph/{year?}/{month?}', [ApiController::class, 'getGraphData']);

    Route::get('/contact', [ContactFormController::class, 'index']);
    Route::get('/contact-show/{id}', [ContactFormController::class, 'show']);
    Route::put('/contact-update/{id}', [ContactFormController::class, 'update']);
    Route::delete('/contact-delete/{id}', [ContactFormController::class, 'destroy']);

    Route::post('/blogs/{blog}', [BlogController::class, 'update']);

    Route::apiResource('blogs', BlogController::class);

    Route::apiResource('testimonials', TestimonialController::class);

    Route::get('case-examples', [CaseExampleController::class, 'index']);
    Route::post('case-examples', [CaseExampleController::class, 'store']);
    Route::get('case-examples/{id}', [CaseExampleController::class, 'show']);
    Route::post('case-examples/{id}', [CaseExampleController::class, 'update']);
    Route::delete('case-examples/{id}', [CaseExampleController::class, 'destroy']);

    Route::get('/chats/{chat_title_id}', [ChatController::class, 'getChats']);
    Route::post('/chats', [ChatController::class, 'sendMessage']);
    Route::post('/chats/{chatId}/thumbs', [ChatController::class, 'giveThumbs'])->name('chats.giveThumbs');
    Route::get('/admin/total-thumbs', [ChatController::class, 'getTotalThumbs'])->name('admin.totalThumbs');
    Route::post('/chats/{chatId}/regenerate', [ChatController::class, 'regenerateResponse'])->name('chats.regenerate');
    Route::get('/chats/export', [ChatController::class, 'exportChats'])->name('chats.export');



    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::get('/feedback/{id}', [FeedbackController::class, 'show']); // Admin fetches feedback by ID
    Route::delete('/feedback/{id}', [FeedbackController::class, 'destroy']); // Admin deletes feedback

    Route::get('/admin/total-ask-questions', [ChatController::class, 'getTotalMessages'])->name('admin.totalMessages');

    Route::get('/chat-titles', [ChatTitleController::class, 'index']); // Get all chat titles
    Route::post('/chat-titles', [ChatTitleController::class, 'store']); // Create a new chat title
    Route::get('/chat-titles/{id}', [ChatTitleController::class, 'show']); // Get a specific chat title
    Route::put('/chat-titles/{id}', [ChatTitleController::class, 'update']); // Update a specific chat title
    Route::delete('/chat-titles/{id}', [ChatTitleController::class, 'destroy']); // Delete a specific chat title

    Route::post('/chat-titles/search', [ChatTitleController::class, 'search']);
});



// TestJob::dispatch();
