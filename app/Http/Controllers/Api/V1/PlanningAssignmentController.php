<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Planning; // For route model binding
use App\Models\PlanningAssignment;
use App\Enums\Role; // For validation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class PlanningAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Planning $planning)
    {
        return $planning->assignments()->with('user:id,name')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Planning $planning)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|uuid|exists:users,id',
            'roleDansPlanning' => ['required', new Enum(Role::class)],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check for duplicate assignment (same user in same planning)
        $existingAssignment = $planning->assignments()
            ->where('userId', $request->input('userId'))
            ->first();

        if ($existingAssignment) {
            return response()->json(['message' => 'User is already assigned to this planning.'], 422);
        }

        $validatedData = $validator->validated();
        $assignment = $planning->assignments()->create($validatedData);

        return response()->json($assignment->load('user:id,name'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PlanningAssignment $assignment)
    {
        return $assignment->load('user:id,name', 'planning:id,startDate,endDate');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlanningAssignment $assignment)
    {
        // Typically, you might only update the role in an assignment.
        // Changing userId or planningId often means deleting and creating a new assignment.
        $validator = Validator::make($request->all(), [
            'roleDansPlanning' => ['sometimes', 'required', new Enum(Role::class)],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('roleDansPlanning')) {
            $assignment->update($validator->validated());
        }

        return response()->json($assignment->load('user:id,name'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlanningAssignment $assignment)
    {
        $assignment->delete();
        return response()->json(null, 204);
    }
}
