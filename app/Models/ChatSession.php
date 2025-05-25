<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ChatSession extends Model
{
    use HasFactory;

    protected $table = 'chat_sessions'; 

    protected $fillable = [
        'user_id',
        'session_date',
        'ai_feedback',
        'duration',
    ];

}
