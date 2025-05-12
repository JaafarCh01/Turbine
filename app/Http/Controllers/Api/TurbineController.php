<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use Illuminate\Http\Request;

class TurbineController extends Controller
{
    public function index()
    {
        return Turbine::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'status' => 'required|in:ACTIVE,MAINTENANCE,INACTIVE',
        ]);

        return Turbine::create($request->all());
    }

    public function show($id)
    {
        return Turbine::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $turbine = Turbine::findOrFail($id);
        $turbine->update($request->all());
        return $turbine;
    }

    public function destroy($id)
    {
        Turbine::findOrFail($id)->delete();
        return response()->noContent();
    }
}