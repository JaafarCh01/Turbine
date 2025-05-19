<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Enums\DocumentType;
use App\Enums\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Document::with(['uploader:id,name', 'turbine:id,name'])->latest()->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,png|max:10240', // Max 10MB
            'type' => ['required', new Enum(DocumentType::class)],
            'category' => ['required', new Enum(DocumentCategory::class)],
            'turbineId' => 'required|uuid|exists:turbines,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = $request->file('file');
        // Store file in 'documents/{turbineId}' directory, filename will be original name
        $filePath = $file->storeAs('documents/' . $request->input('turbineId'), $file->getClientOriginalName(), 'public');

        $document = Document::create([
            'title' => $request->input('title'),
            'fileData' => $filePath, // Storing the path
            'type' => $request->input('type'),
            'category' => $request->input('category'),
            'uploadDate' => now(),
            'uploadedBy' => auth()->id(),
            'turbineId' => $request->input('turbineId'),
        ]);

        return response()->json($document->load(['uploader:id,name', 'turbine:id,name']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        return $document->load(['uploader:id,name', 'turbine:id,name', 'comments']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        // For now, only allow updating metadata, not the file itself to keep it simple.
        // File replacement would require deleting the old file and uploading a new one.
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => ['sometimes', 'required', new Enum(DocumentType::class)],
            'category' => ['sometimes', 'required', new Enum(DocumentCategory::class)],
            // turbineId is not typically changed for an existing document.
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $document->update($validator->validated());
        return response()->json($document->load(['uploader:id,name', 'turbine:id,name']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        // Delete the associated file from storage before deleting the model
        if (Storage::disk('public')->exists($document->fileData)) {
            Storage::disk('public')->delete($document->fileData);
        }
        $document->delete();
        return response()->json(null, 204);
    }

    // Optional: Add a download method
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->fileData)) {
            return response()->json(['message' => 'File not found.'], 404);
        }
        return Storage::disk('public')->download($document->fileData, $document->title . '.' . pathinfo($document->fileData, PATHINFO_EXTENSION));
    }
}
