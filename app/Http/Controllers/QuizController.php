<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\QuizAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class QuizController extends Controller
{
    // Create a new quiz with questions
    public function createQuiz(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:4|max:4', // 4 options for each question
            'questions.*.correct_answer' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Create the quiz
            $quiz = Quiz::create([
                'title' => $request->title,
                'description' => $request->description,
                'activation_time' => now()->addMinutes(2), // Activate after 2 minutes
                'expiration_time' => now()->addHours(24), // Expire after 24 hours
            ]);

            // Create questions for the quiz
            foreach ($request->questions as $q) {
                Question::create([
                    'quiz_id' => $quiz->id,
                    'question' => $q['question'],
                    'options' => json_encode($q['options']), // Store options in JSON format
                    'correct_answer' => $q['correct_answer'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Quiz created successfully',
                'quiz' => $quiz,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error creating quiz: ' . $e->getMessage()], 500);
        }
    }

    // Assign quiz to students
    public function assignQuiz(Request $request)
    {
        // Validate request data
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Loop through the provided student_ids
            foreach ($request->student_ids as $student_id) {
                // Find the user by ID
                $user = \App\Models\User::find($student_id);

                // Check if the user has the "student" role
                if (!$user->hasRole('student')) {
                    // If not a student, return an error message
                    return response()->json([
                        'message' => "Cannot assign quiz to user with ID {$student_id}. The user is not a student."
                    ], 400); // 400 Bad Request
                }

                // If the user is a student, assign the quiz
                QuizAssignment::create([
                    'quiz_id' => $request->quiz_id,
                    'student_id' => $student_id,
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Quiz assigned to students successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error assigning quiz: ' . $e->getMessage()], 500);
        }
    }


    // View quiz results (for admins or supervisors)
    public function viewQuizResults($quizId)
    {
        try {
            // Fetch the quiz along with its assignments and the students who attempted it
            $quiz = Quiz::with(['assignments.student', 'assignments.attempt'])->findOrFail($quizId);

            return response()->json([
                'message' => 'Quiz results retrieved successfully',
                'quiz' => $quiz,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving quiz results: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function getAllQuizzes()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Ensure only Admin, Supervisor, or Manager roles can access this method
        if (!$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return response()->json(['error' => 'You do not have permission to access this resource.'], 403);
        }

        // Get all quizzes with questions, including correct answers
        $quizzes = Quiz::with('questions')->get();

        // Return quizzes with correct answers for Admin, Supervisor, and Manager
        return response()->json([
            'message' => 'All quizzes retrieved successfully.',
            'quizzes' => $quizzes->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'questions' => $quiz->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question_text' => $question->question, // Get the question text from the 'question' field
                            'options' => $question->options,
                            'correct_answer' => $question->correct_answer // Show correct answer for all roles
                        ];
                    }),
                    'created_at' => $quiz->created_at,
                    'updated_at' => $quiz->updated_at
                ];
            })
        ], 200);
    }
}
