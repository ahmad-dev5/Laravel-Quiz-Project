<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\QuizAssignmentController;
use App\Http\Controllers\Auth\ManagerPasswordController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/submissions', [StudentController::class, 'submitForm']);

Route::post('/login', [AuthController::class, 'login']);

Route::put('/set-password', [StudentController::class, 'setPassword']);




Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});


// Route::get('/students', [AdminController::class, 'showAllStudents']);
    // Admin routes
    Route::middleware(['auth:api', 'role:admin'])->group(function () {
        Route::post('/admin/register', [AdminController::class, 'makeRoles']);
        Route::get('/students', [AdminController::class, 'showAllStudents']);
        // Route::post('/admin/assign-quiz', [AdminController::class, 'assignQuiz']);
        Route::post('/submissions/{id}/accept', [StudentController::class, 'acceptStudent']);
        Route::post('/submissions/{id}/reject', [StudentController::class, 'rejectStudent']);
        // Route::post('/admin/accept-student', [AdminController::class, 'acceptStudent']);
        // Route::post('/admin/reject-student', [AdminController::class, 'rejectStudent']);
    });

    // // Manager routes
    // Route::middleware('role:manager')->group(function () {
    //     Route::get('/manager/students', [ManagerController::class, 'getStudents']);
    //     Route::post('/manager/assign-quiz', [ManagerController::class, 'assignQuiz']);
    // });

    // Student routes
    // Route::middleware('role:student')->group(function () {
    //     Route::get('/student/quizzes', [StudentController::class, 'getQuizzes']);
    //     Route::post('/student/attempt-quiz', [StudentController::class, 'attemptQuiz']);
    // });




Route::middleware(['auth:api', 'role:admin|supervisor|manager'])->group(function () {
    Route::post('/quizzes', [QuizController::class, 'createQuiz'])->name('quizzes.create');
    Route::post('/quizzes/assign', [QuizController::class, 'assignQuiz'])->name('quizzes.assign');
    Route::get('/quizzes/{quiz}/results', [QuizController::class, 'viewQuizResults'])->name('quizzes.results');
    Route::get('/all-quizzes', [QuizController::class, 'getAllQuizzes']);
    Route::get('/quizzes/assigned-all', [QuizAssignmentController::class, 'getAllAssignedQuizzes']);
    Route::get('/showstudents', [AdminController::class, 'getStudents']);
    Route::get('/quizzes/results-all', [QuizAssignmentController::class, 'getAllQuizResults']);
});

// Student group (only students can attempt quizzes)
Route::middleware(['auth:api', 'role:student'])->group(function () {
    Route::get('/quizzes/assigned', [QuizAssignmentController::class, 'getAssignedQuizzes'])->name('quizzes.assigned');
    Route::get('/quizzes/{quiz}/attempt', [QuizAssignmentController::class, 'attemptQuiz'])->name('quizzes.attempt');
    Route::post('/quizzes/{quiz}/submit', [QuizAssignmentController::class, 'submitQuiz'])->name('quizzes.submit');
    Route::get('/student/quizzes/results', [StudentController::class, 'viewAttemptedQuizzes']);
});