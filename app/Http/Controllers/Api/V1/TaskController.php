<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Revision; // For route model binding
use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Revision $revision)
    {
        return $revision->tasks()->orderBy('plannedAt')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Revision $revision)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:65535', // Text field
            'plannedAt' => 'required|date',
            'doneAt' => 'nullable|date|after_or_equal:plannedAt',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        // $validatedData['revisionId'] will be set automatically by the relationship

        $task = $revision->tasks()->create($validatedData);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return $task->load('revision:id,revisionDate'); // Optionally load revision details
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string|max:65535',
            'plannedAt' => 'sometimes|date',
            'doneAt' => 'nullable|date|after_or_equal:plannedAt',
            'status' => ['sometimes', 'required', new Enum(TaskStatus::class)],
            'ordre' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task->update($validator->validated());
        return response()->json($task->load('revision:id,revisionDate'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }
}
