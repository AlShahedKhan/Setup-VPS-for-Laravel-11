<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $credentials;

    /**
     * Create a new job instance.
     *
     * @param array $credentials
     */
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Execute the job.
     *
     * @return array
     */
    public function handle(): array
    {
        try {
            Log::info('Attempting login for user', ['email' => $this->credentials['email']]);

            // Attempt to authenticate
            if (!$token = JWTAuth::attempt($this->credentials)) {
                Log::warning('Invalid credentials provided.', ['email' => $this->credentials['email']]);
                return [
                    'error' => 'Invalid credentials.',
                    'status_code' => 401,
                ];
            }

            $user = auth()->user();

            if (!$user) {
                Log::error('Authenticated user not found.');
                return [
                    'error' => 'User not found.',
                    'status_code' => 404,
                ];
            }

            // Check user approval status for non-admins
            if (!$user->is_admin && $user->is_approved == 0) {
                Log::warning('Login attempt for unapproved account.', ['user_id' => $user->id]);
                return [
                    'error' => 'Your account is pending admin approval.',
                    'status_code' => 403,
                ];
            }

            // Generate token with additional claims
            $token = JWTAuth::claims([
                'role' => $user->is_admin ? 'admin' : 'user',
            ])->fromUser($user);

            Log::info('Login successful.', ['user_id' => $user->id]);

            return [
                'token' => $token,
                'user' => $user->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('An error occurred during login.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Unexpected error occurred.',
                'status_code' => 500,
            ];
        }
    }
}
