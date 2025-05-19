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
        $query = PDR::with(['turbine:id,name', 'creator:id,name', 'approver:id,name']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('turbineId')) {
            $query->where('turbineId', $request->turbineId);
        }
        if ($request->has('createdBy')) {
            $query->where('createdBy', $request->createdBy);
        }
        if ($request->has('approverId')) {
            $query->where('approverId', $request->approverId);
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
            'status' => ['sometimes', 'required', new Enum(PDRStatus::class)],
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

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PDR $pdr)
    {
        return $pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps', 'comments.user:id,name', 'generatedRevisions']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PDR $pdr)
    {
        $validator = Validator::make($request->all(), [
            'turbineId' => 'sometimes|required|uuid|exists:turbines,id',
            'status' => ['sometimes', 'required', new Enum(PDRStatus::class)],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pdr->update($validator->validated());
        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name', 'steps']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PDR $pdr)
    {
        if ($pdr->status === PDRStatus::APPROVED) {
            return response()->json(['message' => 'Cannot delete an approved PDR.'], 403);
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

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name']));
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

        return response()->json($pdr->load(['turbine:id,name', 'creator:id,name', 'approver:id,name']));
    }
}
