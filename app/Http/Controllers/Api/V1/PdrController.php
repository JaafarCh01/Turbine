<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PDR;
use App\Enums\PDRStatus;
use App\Models\PdrStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Auth;

class PdrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PDR::with(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'assignedUsers:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('turbineId')) {
            $query->where('turbineId', $request->turbineId);
        }
        if ($request->filled('createdBy')) {
            $query->where('createdBy', $request->createdBy);
        }
        if ($request->filled('approverId')) {
            $query->where('approverId', $request->approverId);
        }
        if ($request->filled('title_contains')) {
            $query->where('title', 'like', '%' . $request->title_contains . '%');
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
            'title' => 'required|string|max:255',
            'status' => ['sometimes', 'required', new Enum(PDRStatus::class)],
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'uuid|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $validatedData['createdBy'] = Auth::id();
        if (!isset($validatedData['status'])) {
            $validatedData['status'] = PDRStatus::DRAFT;
        }

        $pdr = PDR::create($validatedData);

        if ($request->has('assigned_user_ids')) {
            $pdr->assignedUsers()->sync($request->input('assigned_user_ids'));
        }

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps', 'assignedUsers:id,name']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PDR $pdr)
    {
        return $pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps', 'comments.user:id,name', 'generatedRevisions', 'assignedUsers:id,name']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PDR $pdr)
    {
        $validator = Validator::make($request->all(), [
            'turbineId' => 'sometimes|required|uuid|exists:turbines,id',
            'title' => 'sometimes|required|string|max:255',
            'status' => ['sometimes', 'required', new Enum(PDRStatus::class)],
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'uuid|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pdr->update($validator->validated());

        if ($request->has('assigned_user_ids')) {
            $pdr->assignedUsers()->sync($request->input('assigned_user_ids'));
        }

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps', 'assignedUsers:id,name']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PDR $pdr)
    {
        if ($pdr->status === PDRStatus::APPROVED) {
            return response()->json(['message' => 'Cannot delete an approved PDR.'], 403);
        }

        // Check for linked revisions
        if ($pdr->generatedRevisions()->exists()) {
            return response()->json(['message' => 'Cannot delete this PDR because it is linked to one or more revisions. Please delete or unlink the revisions first.'], 409); // 409 Conflict
        }

        $pdr->delete();
        return response()->json(null, 204);
    }

    public function approve(Request $request, PDR $pdr)
    {
        if ($pdr->status !== PDRStatus::PENDING_APPROVAL) {
            return response()->json(['message' => 'PDR is not pending approval.'], 422);
        }

        $pdr->status = PDRStatus::APPROVED;
        $pdr->approverId = Auth::id();
        $pdr->approvedAt = now();
        $pdr->save();

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'assignedUsers:id,name']));
    }

    public function reject(Request $request, PDR $pdr)
    {
        if ($pdr->status !== PDRStatus::PENDING_APPROVAL) {
            return response()->json(['message' => 'PDR is not pending approval.'], 422);
        }

        $pdr->status = PDRStatus::REJECTED;
        $pdr->approverId = Auth::id();
        $pdr->approvedAt = null;
        $pdr->save();

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'assignedUsers:id,name']));
    }
}
