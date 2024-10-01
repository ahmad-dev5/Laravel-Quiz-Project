<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    // Add fillable fields to prevent mass assignment issues
    protected $fillable = ['title', 'description', 'activation_time', 'expiration_time'];

    // Define the relationship with questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    public function assignments()
    {
        return $this->hasMany(QuizAssignment::class, 'quiz_id');
    }
}
