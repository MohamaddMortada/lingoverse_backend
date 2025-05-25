<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PronunciationFeedback extends Model
{
    use HasFactory;

    protected $table = 'pronunciation_feedback'; 

    protected $primaryKey = 'feedback_id'; 

    protected $fillable = [
        'user_id',
        'audio_url',
        'accuracy_score',
        'mistakes_highlighted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
