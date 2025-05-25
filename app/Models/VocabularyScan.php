<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyScan extends Model
{
    use HasFactory;

    protected $table = 'vocabulary_scans'; 

    protected $primaryKey = 'scan_id'; 

    protected $fillable = [
        'user_id',
        'image_url',
        'translated_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
