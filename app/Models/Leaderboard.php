<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Leaderboard extends Model
{
    use HasFactory;

    protected $table = 'leaderboard'; 

    protected $primaryKey = 'leaderboard_id'; 

    protected $fillable = [
        'user_id',
        'rank',
        'total_score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
