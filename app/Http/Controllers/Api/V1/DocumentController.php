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
use Illuminate\Support\Facades\Log;

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
        Log::debug('DocumentController@store: Received request', $request->all());

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,png|max:10240', // Max 10MB
            'type' => ['required', new Enum(DocumentType::class)],
            'category' => ['required', new Enum(DocumentCategory::class)],
            'turbineId' => 'required|uuid|exists:turbines,id',
        ]);

        if ($validator->fails()) {
            Log::warning('DocumentController@store: Validation failed', $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }
        Log::debug('DocumentController@store: Validation passed');

        if (!$request->hasFile('file')) {
            Log::error('DocumentController@store: File not present in request after validation passed.');
            return response()->json(['file' => ['File not found in request.']], 422);
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            Log::error('DocumentController@store: Uploaded file is not valid.', ['error' => $file->getError()]);
            return response()->json(['file' => ['Uploaded file is not valid.', 'PHP Upload Error Code: ' . $file->getError()]], 422);
        }

        Log::debug('DocumentController@store: File details', [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'isValid' => $file->isValid(),
            'error' => $file->getError(), // Should be 0 if valid
        ]);

        // Store file in 'documents' directory, filename will be original name
        // Temporarily simplified path for testing
        $filename = $file->getClientOriginalName();
        $directory = 'documents'; // Simplified path
        Log::debug('DocumentController@store: Attempting to store file', ['directory' => $directory, 'filename' => $filename]);

        try {
            $filePath = $file->storeAs($directory, $filename, 'public');
            if ($filePath) {
                Log::info('DocumentController@store: File stored successfully', ['path' => $filePath]);
            } else {
                Log::error('DocumentController@store: storeAs returned false/null.');
                // This case might indicate a configuration or permission issue not throwing an exception
                // but Storage::put might be more explicit or throw.
                // Let's ensure our validation message remains consistent with what was observed.
                return response()->json(['file' => ['The file failed to upload due to a storage issue.']], 422);
            }
        } catch (\Exception $e) {
            Log::error('DocumentController@store: Exception during file storage', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['file' => ['The file failed to upload due to an exception.', 'error' => $e->getMessage()]], 500); // 500 for server error
        }

        $document = Document::create([
            'title' => $request->input('title'),
            'fileData' => $filePath, // Storing the path
            'type' => $request->input('type'),
            'category' => $request->input('category'),
            'uploadDate' => now(),
            'uploadedBy' => auth()->id(),
            'turbineId' => $request->input('turbineId'),
        ]);
        Log::info('DocumentController@store: Document created in DB', ['document_id' => $document->id]);

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
