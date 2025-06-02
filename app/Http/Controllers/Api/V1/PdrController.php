<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PDR;
use App\Enums\PDRStatus;
use App\Models\PdrStep;
use App\Models\Revision;
use App\Models\Task;
use App\Enums\RevisionStatus;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PdrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PDR::with([
            'turbine:id,name', 
            'creator:id,name', 
            'approver:id,name', 
            'assignedUsers:id,name',
            'revision.performer:id,name',
            'revision.tasks'
        ]);

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
            'steps' => 'nullable|array',
            'steps.*.description' => 'required_with:steps|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $validatedData['createdBy'] = Auth::id();
        if (!isset($validatedData['status'])) {
            $validatedData['status'] = PDRStatus::DRAFT;
        }

        $stepsData = $validatedData['steps'] ?? [];
        unset($validatedData['steps']);

        $pdr = PDR::create($validatedData);

        if (!empty($stepsData)) {
            foreach ($stepsData as $index => $stepDatum) {
                $pdr->steps()->create([
                    'description' => $stepDatum['description'],
                    'ordre' => $index + 1,
                ]);
            }
        }

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PDR $pdr)
    {
        return $pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps', 'comments.user:id,name', 'revision.performer:id,name', 'revision.tasks', 'assignedUsers:id,name']);
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
            'steps' => 'nullable|array',
            'steps.*.id' => 'nullable|uuid',
            'steps.*.description' => 'required_with:steps|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        $stepsData = null;
        if ($request->has('steps')) {
            $stepsData = $validatedData['steps'] ?? [];
        }
        unset($validatedData['steps']);

        $pdr->update($validatedData);

        if ($stepsData !== null) {
            $pdr->steps()->delete();
            if (!empty($stepsData)) {
                foreach ($stepsData as $index => $stepDatum) {
                    $pdr->steps()->create([
                        'description' => $stepDatum['description'],
                        'ordre' => $index + 1,
                    ]);
                }
            }
        }

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps', 'revision.performer:id,name', 'revision.tasks']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PDR $pdr)
    {
        if ($pdr->status === PDRStatus::APPROVED && $pdr->revision()->exists()) {
            return response()->json(['message' => 'Cannot delete an approved PDR that has an associated revision. Please address the revision first.'], 403);
        }
         if ($pdr->status === PDRStatus::APPROVED) {
            // This case implies an approved PDR without a revision, which shouldn't happen with the new workflow
            // but as a safeguard:
            return response()->json(['message' => 'Cannot delete an approved PDR. Consider its status or archive if necessary.'], 403);
        }

        if ($pdr->revision()->exists()) { // This handles non-approved PDRs with revisions (shouldn't happen)
            return response()->json(['message' => 'Cannot delete this PDR because it is linked to a revision. Please delete or unlink the revision first.'], 409);
        }

        $pdr->delete();
        return response()->json(null, 204);
    }

    public function approve(Request $request, PDR $pdr)
    {
        if ($pdr->status !== PDRStatus::SUBMITTED) {
            return response()->json(['message' => 'PDR must be in submitted status to be approved.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'performed_by_user_id' => 'required|uuid|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($pdr->revision()->exists()) {
            return response()->json(['message' => 'This PDR has already been approved and has an associated revision.'], 409);
        }

        DB::transaction(function () use ($pdr, $request) {
            $pdr->status = PDRStatus::APPROVED;
            $pdr->approverId = Auth::id();
            $pdr->approvedAt = now();
            $pdr->save();

            $revision = $pdr->revision()->create([
                'turbineId' => $pdr->turbineId,
                'revisionDate' => now(), 
                'performedBy' => $request->input('performed_by_user_id'),
                'status' => RevisionStatus::PENDING,
            ]);

            foreach ($pdr->steps as $step) {
                $revision->tasks()->create([
                    'description' => $step->description,
                    'ordre' => $step->ordre,
                    'status' => TaskStatus::TODO,
                ]);
            }
        });

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'assignedUsers:id,name', 'revision.tasks', 'revision.performer:id,name', 'steps']));
    }

    public function reject(Request $request, PDR $pdr)
    {
        if ($pdr->status !== PDRStatus::SUBMITTED) {
            return response()->json(['message' => 'PDR must be in submitted status to be rejected.'], 422);
        }
        
        if ($pdr->revision()->exists()) {
            return response()->json(['message' => 'This PDR cannot be rejected as it already has an associated revision.'], 409);
        }

        $pdr->status = PDRStatus::REJECTED;
        $pdr->approverId = Auth::id();
        $pdr->save();

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'assignedUsers:id,name', 'comments']));
    }
}
