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
        $query = Revision::query();

        $query->with([
            'pdr:id,title',
            'turbine:id,name',
            'performer:id,name',
            'tasks:id,revisionId,status'
        ]);

        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('pdr', function ($pdrQuery) use ($searchTerm) {
                      $pdrQuery->where('title', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('turbine', function ($turbineQuery) use ($searchTerm) {
                      $turbineQuery->where('name', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('performer', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by turbine
        if ($request->has('turbine_id') && $request->filled('turbine_id')) {
           $query->where('turbineId', $request->input('turbine_id'));
        }

        // Filter by date range
        if ($request->has('date_from') && $request->filled('date_from')) {
            $query->whereDate('revisionDate', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to') && $request->filled('date_to')) {
            $query->whereDate('revisionDate', '<=', $request->input('date_to'));
        }

        // Filter by PDR presence
        if ($request->has('has_pdr')) {
            $hasPdr = filter_var($request->input('has_pdr'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hasPdr === true) {
                $query->whereNotNull('pdr_id');
            } elseif ($hasPdr === false) {
                $query->whereNull('pdr_id');
            }
        }

        $revisions = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        return response()->json($revisions); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implement store method for ad-hoc revision creation if needed.
        return response()->json(['message' => 'Store method not implemented'], 501);
    }

    /**
     * Display the specified resource.
     */
    public function show(Revision $revision)
    {
        $revision->load([
            'turbine:id,name',
            'pdr:id,title',
            'performer:id,name',
            'tasks',
            'issues.user:id,name',
            'comments.user:id,name'
        ]);
        
        return response()->json($revision);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revision $revision)
    {
        $validatedData = $request->validate([
            'turbineId' => 'sometimes|required|uuid|exists:turbines,id',
            'revisionDate' => 'sometimes|required|date',
            'pdr_id' => 'sometimes|nullable|uuid|exists:pdrs,id',
            'performedBy' => 'sometimes|required|uuid|exists:users,id',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed,cancelled'
        ]);

        $revision->update($validatedData);
        
        // Load relationships for the response
        $revision->load([
            'turbine:id,name',
            'pdr:id,title',
            'performer:id,name',
            'tasks',
            'issues.user:id,name',
            'comments.user:id,name'
        ]);

        return response()->json($revision);
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
