<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = [
        'quiz_id', 
        'student_id', 
        'answers', 
        'score', 
        'is_passed', 
        'time_taken', 
        'video_path', 
        'attempted_at'
    ];
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    // Define the relationship between QuizAttempt and User
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
