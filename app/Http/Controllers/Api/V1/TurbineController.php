<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use Illuminate\Http\Request;
use App\Enums\TurbineStatus;
use Illuminate\Validation\Rule;

class TurbineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Turbine::orderBy('name')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:turbines,name',
            'location' => 'required|string|max:255',
            'status' => ['required', Rule::enum(TurbineStatus::class)],
        ]);

        $turbine = Turbine::create($validated);
        return response()->json($turbine, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Turbine $turbine)
    {
        return $turbine;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Turbine $turbine)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('turbines')->ignore($turbine->id)],
            'location' => 'sometimes|required|string|max:255',
            'status' => ['sometimes', 'required', Rule::enum(TurbineStatus::class)],
        ]);

        $turbine->update($validated);
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
