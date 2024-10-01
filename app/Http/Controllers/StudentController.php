<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\QuizAttempt;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Support\Carbon;
use App\Mail\StudentAcceptedMail;
use App\Mail\StudentRejectedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // Handle student form submission
    public function submitForm(Request $request)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:students',
                'phone_no' => 'required|string|max:15',
                'cv' => 'required|mimes:pdf|max:2048', // Validate only PDF files up to 2MB
            ]);

            // Handle the CV file upload
            $cvPath = null;
            if ($request->hasFile('cv')) {
                $cvPath = $request->file('cv')->store('cvs', 'public'); // Store CV in the 'cvs' folder in 'storage/app/public'
            }

            // Create a new student record
            $student = Student::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_no' => $request->phone_no,
                'cv_path' => $cvPath,
            ]);

            DB::commit(); // Commit the transaction

            // Use the helper function for success response
            return apiResponse('Student form submitted successfully.', 201, $student);
        } catch (\Exception $e) {
            DB::rollBack(); // Roll back the transaction in case of error
            return apiResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }





    public function acceptStudent($id)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            $student = Student::find($id);

            if (!$student) {
                return apiResponse('Student not found', 404);
            }

            $student->status = 'accepted';
            $student->save();

            // Create a user record in the users table after acceptance
            $user = \App\Models\User::create([
                'name' => $student->name,
                'email' => $student->email,
                'password' => bcrypt('password123'), // Placeholder password
            ]);

            // Assign student role or other roles if required
            $user->assignRole('student');

            // Generate a unique token for password reset
            $token = Str::random(64);

            // Store the token in the password_resets table
            PasswordReset::updateOrCreate([
                'email' => $student->email,
            ], [
                'token' => Hash::make($token), // Store a hashed version of the token
                'created_at' => now(),
            ]);

            // Generate password setup link to be sent in the email
            $passwordSetupLink = url('/set-password?token=' . $token . '&email=' . urlencode($student->email));

            // Send acceptance email with password setup link
            Mail::to($student->email)->send(new StudentAcceptedMail($student, $passwordSetupLink));

            DB::commit(); // Commit the transaction

            return apiResponse(
                'Student accepted successfully, and user account created. Email sent to set password.',
                200,
                $student
            );
        } catch (\Exception $e) {
            DB::rollBack(); // Roll back the transaction in case of error
            return apiResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }


    // Reject a student by ID
    public function rejectStudent($id)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            $student = Student::find($id);

            if (!$student) {
                return apiResponse('Student not found', 404);
            }

            $student->status = 'rejected';
            $student->save();

            // Send rejection email
            Mail::to($student->email)->send(new StudentRejectedMail($student));

            DB::commit(); // Commit the transaction

            // Use the helper function for success response
            return apiResponse('Student rejected successfully. Email sent.', 200, $student);
        } catch (\Exception $e) {
            DB::rollBack(); // Roll back the transaction in case of error
            return apiResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    public function setPassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:8', // 'password_confirmation' is required
        ]);

        // Find the password reset record by email
        $passwordReset = PasswordReset::where('email', $request->email)->first();

        // Check if the reset token exists and matches the provided token
        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return apiResponse('Invalid or expired token.', 400);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return apiResponse('User not found.', 404);
        }

        // Update the user's password
        $user->password = bcrypt($request->password);
        $user->save();

        // Delete the password reset token using the email, not id
        PasswordReset::where('email', $request->email)->delete();

        return apiResponse('Password has been updated successfully.', 200);
    }

    public function viewAttemptedQuizzes()
    {
        // Get the authenticated student
        $studentId = Auth::id();

        // Fetch quiz attempts for the authenticated student
        $quizAttempts = QuizAttempt::with(['quiz'])
            ->where('student_id', $studentId)
            ->get();

        // Transform the data to return necessary details
        $results = $quizAttempts->map(function ($attempt) {
            return [
                'quiz_id' => $attempt->quiz->id,
                'quiz_title' => $attempt->quiz->title,
                'score' => $attempt->score,
                'is_passed' => $attempt->is_passed,
                'attempted_at' => $attempt->attempted_at,
                'time_taken' => $attempt->time_taken,
            ];
        });

        return response()->json([
            'message' => 'Quiz results retrieved successfully.',
            'quizzes' => $results,
        ], 200);
    }
}
