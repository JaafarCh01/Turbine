<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\TurbineController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\PlanningController;
use App\Http\Controllers\Api\V1\PdrController;
use App\Http\Controllers\Api\V1\RevisionController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\IssueController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PlanningAssignmentController;
use App\Http\Controllers\Api\V1\PdrStepController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\UserController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', function (Request $request) {
    return $request->user();
    });
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // API V1 routes
    Route::prefix('v1')->group(function () {
        Route::apiResource('turbines', TurbineController::class);
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::apiResource('documents', DocumentController::class);
        Route::apiResource('plannings', PlanningController::class);

        Route::post('pdrs/{pdr}/approve', [PdrController::class, 'approve'])->name('pdrs.approve');
        Route::post('pdrs/{pdr}/reject', [PdrController::class, 'reject'])->name('pdrs.reject');
        Route::apiResource('pdrs', PdrController::class);

        Route::apiResource('revisions', RevisionController::class);
        Route::apiResource('revisions.tasks', TaskController::class)->shallow();
        Route::apiResource('revisions.issues', IssueController::class)->shallow();

        // Routes for managing individual comments (show, update, destroy)
        Route::apiResource('comments', CommentController::class)->only([
            'show', 'update', 'destroy'
        ]);

        // Nested routes for listing and creating comments
        Route::get('pdrs/{pdr}/comments', [CommentController::class, 'indexForPdr'])->name('pdrs.comments.index');
        Route::post('pdrs/{pdr}/comments', [CommentController::class, 'storeForPdr'])->name('pdrs.comments.store');

        Route::get('documents/{document}/comments', [CommentController::class, 'indexForDocument'])->name('documents.comments.index');
        Route::post('documents/{document}/comments', [CommentController::class, 'storeForDocument'])->name('documents.comments.store');

        Route::get('revisions/{revision}/comments', [CommentController::class, 'indexForRevision'])->name('revisions.comments.index');
        Route::post('revisions/{revision}/comments', [CommentController::class, 'storeForRevision'])->name('revisions.comments.store');

        // Nested resources for PlanningAssignments and PdrSteps
        Route::apiResource('plannings.assignments', PlanningAssignmentController::class)->shallow();
        Route::apiResource('pdrs.steps', PdrStepController::class)->shallow();

        // Notification routes
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // User list route (for dropdowns etc.)
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
});
