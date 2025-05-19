<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PDR;
use App\Models\PdrStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PdrStepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PDR $pdr)
    {
        return $pdr->steps()->orderBy('ordre')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PDR $pdr)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'mandatory' => 'required|boolean',
            'ordre' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check for duplicate ordre within the same PDR
        $existingStep = $pdr->steps()->where('ordre', $request->input('ordre'))->first();
        if ($existingStep) {
            return response()->json(['message' => 'A step with this order already exists for this PDR.'], 422);
        }

        $validatedData = $validator->validated();
        $pdrStep = $pdr->steps()->create($validatedData);

        return response()->json($pdrStep, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PdrStep $step)
    {
        return $step->load('pdr');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PdrStep $step)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string',
            'mandatory' => 'sometimes|required|boolean',
            'ordre' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // If ordre is being updated, check for duplicates within the same PDR, excluding current step
        if ($request->has('ordre') && $request->input('ordre') !== $step->ordre) {
            $existingStepWithOrder = PDR::find($step->pdrId)->steps()
                ->where('ordre', $request->input('ordre'))
                ->where('id', '!=', $step->id)
                ->first();
            if ($existingStepWithOrder) {
                return response()->json(['message' => 'A step with this order already exists for this PDR.'], 422);
            }
        }

        $step->update($validator->validated());
        return response()->json($step);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PdrStep $step)
    {
        $step->delete();
        return response()->json(null, 204);
    }
}
