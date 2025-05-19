<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Enums\PlanningStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Allow filtering by status or turbineId if needed
        $query = Planning::with(['turbine:id,name', 'creator:id,name']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('turbineId')) {
            $query->where('turbineId', $request->turbineId);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'turbineId' => 'required|uuid|exists:turbines,id',
            'startDate' => 'required|date|after_or_equal:today',
            'endDate' => 'required|date|after_or_equal:startDate',
            'status' => ['required', new Enum(PlanningStatus::class)],
            // 'assignments' => 'nullable|array', // Handled separately if needed via PlanningAssignmentController
            // 'assignments.*.userId' => 'required_with:assignments|uuid|exists:users,id',
            // 'assignments.*.roleDansPlanning' => ['required_with:assignments', new Enum(Role::class)],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $validatedData['createdBy'] = auth()->id();

        $planning = Planning::create($validatedData);

        // If assignments are part of the request, create them. 
        // This is a basic implementation. A more robust way would be to use a dedicated endpoint or a service class.
        // if ($request->has('assignments')) {
        //     foreach ($request->input('assignments') as $assignmentData) {
        //         $planning->assignments()->create([
        //             'userId' => $assignmentData['userId'],
        //             'roleDansPlanning' => $assignmentData['roleDansPlanning'],
        //         ]);
        //     }
        // }

        return response()->json($planning->load(['turbine:id,name', 'creator:id,name', 'assignments.user:id,name']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Planning $planning)
    {
        return $planning->load(['turbine:id,name', 'creator:id,name', 'assignments.user:id,name']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Planning $planning)
    {
        $validator = Validator::make($request->all(), [
            'turbineId' => 'sometimes|required|uuid|exists:turbines,id',
            'startDate' => 'sometimes|required|date',
            'endDate' => 'sometimes|required|date|after_or_equal:startDate',
            'status' => ['sometimes', 'required', new Enum(PlanningStatus::class)],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Prevent updating startDate if planning is already IN_PROGRESS or COMPLETED, etc. (add business logic if needed)

        $planning->update($validator->validated());
        return response()->json($planning->load(['turbine:id,name', 'creator:id,name', 'assignments.user:id,name']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Planning $planning)
    {
        // Add business logic: e.g., cannot delete if status is IN_PROGRESS or has active assignments.
        // For now, simple deletion. If it has assignments, they might also need to be handled (cascade delete or restrict).
        // $planning->assignments()->delete(); // If cascade on delete is not set up at DB level
        $planning->delete();
        return response()->json(null, 204);
    }
}
