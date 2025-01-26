<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chat_title_id',
        // 'message',
        'query',
        'response',
        'thumbs_up',
        'thumbs_down', // Add thumbs up and thumbs down fields
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatTitle()
    {
        return $this->belongsTo(ChatTitle::class);
    }
}
