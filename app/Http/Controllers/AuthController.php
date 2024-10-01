<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the user with the provided credentials
        if (!$token = JWTAuth::attempt($credentials)) {
            return apiResponse('Invalid credentials', 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Get the user's role(s) and permissions
        $roles = $user->getRoleNames(); // Get role names as a collection
        $permissions = $user->getAllPermissions()->pluck('name')->toArray(); // Get permissions as an array

        // Get the first role as a string (or join if multiple roles are possible)
        $role = $roles->first(); // Get the first role in the collection

        // Filter the user data to exclude fields you don't want
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role, // Assign the single role as a string
            'permissions' => $permissions, // Add permissions to user data
        ];

        // Use the helper function for a success response
        return apiResponse(
            'Login successful',
            200,
            array_merge([
                'token' => $token,
            ], $userData) // Flatten the user data and merge it with the token
        );
    }



    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me()
    {
        return response()->json(Auth::user());
    }
}
