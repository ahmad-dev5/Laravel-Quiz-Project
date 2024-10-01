<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SetPasswordMail;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Notifications\PasswordSetupNotification;

class AdminController extends Controller
{
    // public function createManager(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Validate request data
    //         $request->validate([
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|string|email|max:255|unique:users',
    //             'role' => 'required|string|exists:roles,name',
    //         ]);

    //         // Create manager user with a temporary password
    //         $manager = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make(Str::random(10)), // Set a random temporary password
    //         ]);

    //         // Assign manager role using Spatie
    //         $manager->assignRole('manager');

    //         // Generate a unique token for password reset
    //         $token = Str::random(64);

    //         // Store the token in the password_resets table
    //         PasswordReset::updateOrCreate([
    //             'email' => $manager->email,
    //         ], [
    //             'token' => Hash::make($token), // Store a hashed version of the token
    //             'created_at' => now(),
    //         ]);

    //         // Send the password setup email
    //         Mail::to($manager->email)->send(new SetPasswordMail($manager, $token));

    //         DB::commit();

    //         return apiResponse(
    //             'Manager created successfully. Password setup link has been sent to the manager\'s email.',
    //             201,
    //             $manager
    //         );
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return apiResponse(
    //             'An error occurred while creating the manager: ' . $e->getMessage(),
    //             500
    //         );
    //     }
    // }
    public function makeRoles(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate request data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => 'required|string|in:manager,supervisor', // Accept only manager or supervisor
            ]);

            // Create user with a temporary password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(10)), // Set a random temporary password
            ]);

            // Assign the role (either 'manager' or 'supervisor') dynamically
            $user->assignRole($request->role);

            // Generate a unique token for password reset
            $token = Str::random(64);

            // Store the token in the password_resets table
            PasswordReset::updateOrCreate([
                'email' => $user->email,
            ], [
                'token' => Hash::make($token), // Store a hashed version of the token
                'created_at' => now(),
            ]);

            // Send the password setup email
            Mail::to($user->email)->send(new SetPasswordMail($user, $token));

            DB::commit();

            return apiResponse(
                ucfirst($request->role) . ' created successfully. Password setup link has been sent to the email.',
                201,
                $user
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(
                'An error occurred while creating the ' . $request->role . ': ' . $e->getMessage(),
                500
            );
        }
    }
    public function showAllStudents()
    {
        $students = Student::all();
        return apiResponse('All students retrieved successfully', 200, $students);
    }

    public function getStudents()
    {
        // Retrieve users with the 'student' role
        $students = User::role('student')->get();

        // Return the response using the helper function
        return apiResponse('Students retrieved successfully', 200, $students);
    }
}
