<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use App\Models\QuizAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class QuizAssignmentController extends Controller
{
    // Get the quizzes assigned to the authenticated student
    public function getAssignedQuizzes()
    {
        $studentId = auth()->id();

        // Fetch assigned quizzes that have not been attempted by the student
        $assignedQuizzes = QuizAssignment::with('quiz')
            ->where('student_id', $studentId)
            ->whereDoesntHave('attempt', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->get();

        // Restore the response structure for compatibility with frontend
        $assignedQuizzesFormatted = $assignedQuizzes->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'quiz_id' => $assignment->quiz_id,
                'student_id' => $assignment->student_id,
                'assigned_at' => $assignment->assigned_at,
                'attempted_at' => $assignment->attempted_at,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at,
                'quiz' => [
                    'id' => $assignment->quiz->id,
                    'title' => $assignment->quiz->title,
                    'description' => $assignment->quiz->description,
                    'activation_time' => $assignment->quiz->activation_time,
                    'expiration_time' => $assignment->quiz->expiration_time,
                    'created_at' => $assignment->quiz->created_at,
                    'updated_at' => $assignment->quiz->updated_at,
                ]
            ];
        });

        return response()->json($assignedQuizzesFormatted, 200);
    }


    // Student attempts a quiz
    public function attemptQuiz($quizId)
    {
        $studentId = auth()->id();

        // Find the quiz assignment for the authenticated student
        $quizAssignment = QuizAssignment::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->first();

        if (!$quizAssignment) {
            return response()->json(['error' => 'Quiz assignment not found'], 404);
        }

        // Check if the quiz has already been attempted
        $existingAttempt = QuizAttempt::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->first();

        if ($existingAttempt) {
            return response()->json(['error' => 'Quiz has already been attempted.'], 403);
        }

        // Fetch the quiz along with its questions
        $quiz = Quiz::with('questions')->find($quizId);

        // Check if the quiz exists and is assigned to the student
        if (!$quiz) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        // Check if the quiz is active and not expired
        if (now()->lt($quiz->activation_time)) {
            return response()->json(['error' => 'Quiz has not been activated yet'], 403);
        }

        if (now()->gt($quiz->expiration_time)) {
            return response()->json(['error' => 'Quiz has expired'], 403);
        }

        // Return the quiz questions without revealing the correct answers
        $questions = $quiz->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question' => $question->question,
                'options' => json_decode($question->options), // Return options as an array
            ];
        });

        return response()->json([
            'message' => 'Quiz retrieved successfully.',
            'questions' => $questions,
        ], 200);
    }


    // Submit a quiz along with video recording (already done)
    public function submitQuiz(Request $request, $quizId)
    {
        DB::beginTransaction();
        try {
            // Debug Step: Start submit quiz process
            Log::info('Starting the submitQuiz process');

            // Retrieve the answers field from the request
            $answers = $request->input('answers');

            // Debug Step: Check if the answers are received from the request
            Log::info('Answers received from the request:', ['answers' => $answers]);

            if (!$answers) {
                Log::error('No answers provided in request');
                return response()->json(['error' => 'The answers field is required.'], 400);
            }

            // Manually decode the answers field since it is provided as JSON in Form-Data
            $decodedAnswers = json_decode($answers, true);

            // Debug: Check if the answers are decoded correctly
            if (is_null($decodedAnswers) || !is_array($decodedAnswers)) {
                Log::error('Invalid answers format:', ['answers' => $answers]);
                return response()->json(['error' => 'The answers field must contain valid JSON.'], 400);
            }

            // Logging the decoded answers
            Log::info('Decoded Answers:', $decodedAnswers);

            // Validate the video field separately
            $request->validate([
                'video' => 'nullable|file|mimes:mp4,mov,avi|max:1024000',
            ]);

            // Debug Step: Passed validation for video file
            Log::info('Passed video validation');

            // Find the quiz assignment for the authenticated student
            $quizAssignment = QuizAssignment::where('quiz_id', $quizId)
                ->where('student_id', auth()->id())
                ->firstOrFail();

            // Debug Step: Quiz Assignment found
            Log::info('Quiz assignment found', ['quiz_assignment_id' => $quizAssignment->id]);

            // Store the quiz attempt in the database
            $quizAttempt = QuizAttempt::create([
                'quiz_id' => $quizId,
                'student_id' => auth()->id(),
                'answers' => json_encode($decodedAnswers),
                'attempted_at' => now(),
            ]);

            // Debug Step: Quiz Attempt created
            Log::info('Quiz attempt created', ['quiz_attempt_id' => $quizAttempt->id]);

            // Fetch all the questions for the given quiz to calculate the score
            $questions = Question::where('quiz_id', $quizId)->get();

            // Debug Step: Questions retrieved
            Log::info('Questions retrieved for quiz', ['quiz_id' => $quizId, 'questions_count' => $questions->count()]);

            // Create a map of correct answers
            $correctAnswers = $questions->mapWithKeys(function ($question) {
                return [$question->id => $question->correct_answer];
            });

            // Logging the correct answers for verification
            Log::info('Correct Answers:', $correctAnswers->toArray());

            // Calculate the score
            $score = 0;
            foreach ($decodedAnswers as $questionId => $answer) {
                if (isset($correctAnswers[$questionId]) && $correctAnswers[$questionId] == $answer) {
                    $score++;
                }
            }

            // Debug Step: Calculated score
            Log::info('Calculated Score:', ['score' => $score]);

            // Logic for determining pass/fail
            $totalQuestions = $questions->count();
            $isPassed = $score >= ($totalQuestions / 2);

            // Debug Step: Calculated pass/fail status
            Log::info('Pass/Fail Status:', ['is_passed' => $isPassed]);

            // Calculate the time taken to complete the quiz
            $timeTaken = now()->diff($quizAssignment->assigned_at)->format('%H:%I:%S');

            // Update the quiz attempt with the calculated results
            $quizAttempt->update([
                'score' => $score,
                'is_passed' => $isPassed,
                'time_taken' => $timeTaken,
            ]);

            // Debug Step: Quiz Attempt updated with score and pass status
            Log::info('Quiz attempt updated', ['quiz_attempt_id' => $quizAttempt->id]);

            // If a video file was uploaded, store it
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('videos', 'public');
                $quizAttempt->update(['video_path' => $videoPath]);

                // Debug Step: Video path updated
                Log::info('Video path updated for quiz attempt', ['video_path' => $videoPath]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Quiz submitted successfully',
                'quiz_attempt' => $quizAttempt,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('An error occurred during quiz submission: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function getAllAssignedQuizzes()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Ensure only Admin, Supervisor, or Manager roles can access this method
        if (!$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return response()->json(['error' => 'You do not have permission to access this resource.'], 403);
        }

        // Fetch only assigned quizzes with student details
        $assignedQuizzes = QuizAssignment::with(['quiz', 'student'])
            ->whereNotNull('student_id')  // Ensure that the quiz is assigned to a student
            ->get();

        // Transform the data to return necessary details
        $quizzesData = $assignedQuizzes->map(function ($assignment) {
            return [
                'quiz_id' => $assignment->quiz->id,
                'quiz_title' => $assignment->quiz->title,
                'quiz_description' => $assignment->quiz->description,
                'student' => [
                    'student_id' => $assignment->student->id,
                    'student_name' => $assignment->student->name,
                    'student_email' => $assignment->student->email,
                ],
                'assigned_at' => $assignment->assigned_at,
                'attempted_at' => $assignment->attempted_at,
            ];
        });

        return response()->json([
            'message' => 'Assigned quizzes retrieved successfully.',
            'assigned_quizzes' => $quizzesData,
        ], 200);
    }
    // Add this function to the QuizAssignmentController
    public function getAllQuizResults()
    {
        try {
            // Fetch all quiz attempts with related student and quiz details
            $quizAttempts = QuizAttempt::with(['quiz', 'student'])->get();

            // Transform the data to return necessary details
            $quizResults = $quizAttempts->map(function ($attempt) {
                return [
                    'attempt_id' => $attempt->id,
                    'quiz' => [
                        'quiz_id' => $attempt->quiz->id,
                        'title' => $attempt->quiz->title,
                        'description' => $attempt->quiz->description,
                        'activation_time' => $attempt->quiz->activation_time,
                        'expiration_time' => $attempt->quiz->expiration_time,
                    ],
                    'student' => [
                        'student_id' => $attempt->student->id,
                        'student_name' => $attempt->student->name,
                        'student_email' => $attempt->student->email,
                    ],
                    'score' => $attempt->score,
                    'is_passed' => $attempt->is_passed,
                    'time_taken' => $attempt->time_taken,
                    'video_path' => $attempt->video_path,
                    'attempted_at' => $attempt->attempted_at,
                ];
            });

            return response()->json([
                'message' => 'All quiz results retrieved successfully.',
                'quiz_results' => $quizResults,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving quiz results: ' . $e->getMessage(),
            ], 500);
        }
    }
}
