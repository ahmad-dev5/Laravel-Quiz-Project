<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'assigned_at',
        'answers',
        'video_path',
        'attempted_at',
        'score',
        'is_passed',
        'time_taken'
    ];

    // Define the relationship between QuizAssignment and Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class,'quiz_id');
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    
    public function attempt()
    {
        return $this->hasOne(QuizAttempt::class, 'quiz_id', 'quiz_id')
            ->whereColumn('student_id', 'student_id');
    }
    // public function quizAttempt()
    // {
    //     return $this->hasOne(QuizAttempt::class, 'quiz_id', 'quiz_id')
    //                 ->where('student_id', $this->student_id);
    // }
}
