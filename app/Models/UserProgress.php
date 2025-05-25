<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $table = 'user_progress'; 

    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'user_id',
        'language',
        'current_level',
        'total_points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
