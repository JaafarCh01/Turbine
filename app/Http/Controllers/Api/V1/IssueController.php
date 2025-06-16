<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Revision; // For route model binding
use App\Models\Issue;
use App\Enums\Severity; // For validation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class IssueController extends Controller
{
    /**
     * Display a global listing of all issues across all revisions.
     */
    public function indexAll(Request $request)
    {
        $query = Issue::with([
            'revision:id,revisionDate,turbineId,performedBy',
            'revision.turbine:id,name',
            'revision.performer:id,name'
        ]);

        // Filter by severity
        if ($request->has('severity') && $request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        // Filter by turbine
        if ($request->has('turbine_id') && $request->filled('turbine_id')) {
            $query->whereHas('revision', function ($q) use ($request) {
                $q->where('turbineId', $request->input('turbine_id'));
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->filled('date_from')) {
            $query->whereDate('reportedAt', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to') && $request->filled('date_to')) {
            $query->whereDate('reportedAt', '<=', $request->input('date_to'));
        }

        // Search in description
        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('description', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('reportedAt', 'desc')->paginate(15);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Revision $revision)
    {
        return $revision->issues()->orderBy('reportedAt', 'desc')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Revision $revision)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'severity' => ['required', new Enum(Severity::class)],
            'reportedAt' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $issue = $revision->issues()->create($validatedData);

        return response()->json($issue, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Issue $issue)
    {
        return $issue->load('revision:id,revisionDate');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Issue $issue)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string',
            'severity' => ['sometimes', 'required', new Enum(Severity::class)],
            'reportedAt' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $issue->update($validator->validated());
        return response()->json($issue);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Issue $issue)
    {
        $issue->delete();
        return response()->json(null, 204);
    }
}
