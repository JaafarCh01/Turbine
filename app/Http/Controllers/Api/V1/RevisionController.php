<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Revision::with(['turbine:id,name', 'linkedPdr:id', 'performer:id,name']);

        if ($request->has('turbineId')) {
            $query->where('turbineId', $request->turbineId);
        }
        if ($request->has('linkedPdrId')) {
            $query->where('linkedPdrId', $request->linkedPdrId);
        }
        if ($request->has('performedBy')) {
            $query->where('performedBy', $request->performedBy);
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
            'revisionDate' => 'required|date',
            'linkedPdrId' => 'nullable|uuid|exists:pdrs,id',
            // 'performedBy' is set automatically
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $validatedData['performedBy'] = Auth::id();

        $revision = Revision::create($validatedData);

        return response()->json($revision->load(['turbine:id,name', 'linkedPdr:id', 'performer:id,name', 'tasks', 'issues']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Revision $revision)
    {
        return $revision->load(['turbine:id,name', 'linkedPdr:id', 'performer:id,name', 'tasks', 'issues', 'comments.user:id,name']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revision $revision)
    {
        $validator = Validator::make($request->all(), [
            'turbineId' => 'sometimes|required|uuid|exists:turbines,id',
            'revisionDate' => 'sometimes|required|date',
            'linkedPdrId' => 'sometimes|nullable|uuid|exists:pdrs,id',
            // performedBy is not typically updatable, or only by admins
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $revision->update($validator->validated());
        return response()->json($revision->load(['turbine:id,name', 'linkedPdr:id', 'performer:id,name', 'tasks', 'issues']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revision $revision)
    {
        // Business logic: Can revisions be deleted? If so, what about linked tasks/issues?
        // $revision->tasks()->delete(); // If cascade not set and they should be deleted
        // $revision->issues()->delete();
        // $revision->comments()->delete();
        $revision->delete();
        return response()->json(null, 204);
    }
}
