<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TurbineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if this is a request for dropdown data (minimal fields)
        if ($request->has('for_dropdown') && $request->input('for_dropdown') === 'true') {
            $turbines = Turbine::select('id', 'name')->orderBy('name')->get();
            return response()->json($turbines);
        }

        // Full data for turbines list view
        $turbines = Turbine::select('id', 'name', 'location', 'status', 'created_at', 'updated_at')
            ->orderBy('name')
            ->get();
        
        return response()->json($turbines);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'commissioningDate' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:ACTIVE,INACTIVE,MAINTENANCE',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $turbine = Turbine::create($validator->validated());
        return response()->json($turbine, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Turbine $turbine)
    {
        return response()->json($turbine);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Turbine $turbine)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'commissioningDate' => 'sometimes|required|date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|required|string|in:ACTIVE,INACTIVE,MAINTENANCE',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $turbine->update($validator->validated());
        return response()->json($turbine);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Turbine $turbine)
    {
        $turbine->delete();
        return response()->json(null, 204);
    }
}
