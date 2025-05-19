<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\PDR;
use App\Models\Document;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    private function getCommentsForParent($parentModel, $perPage = 15)
    {
        return $parentModel->comments()
            ->with('user:id,name') // Eager load user details
            ->latest()
            ->paginate($perPage);
    }

    private function storeCommentForParent(Request $request, $parentModel)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $comment = $parentModel->comments()->create([
            'content' => $request->input('content'),
            'userId' => Auth::id(),
        ]);

        return response()->json($comment->load('user:id,name'), 201);
    }

    // For PDRs
    public function indexForPdr(PDR $pdr)
    {
        return $this->getCommentsForParent($pdr);
    }
    public function storeForPdr(Request $request, PDR $pdr)
    {
        return $this->storeCommentForParent($request, $pdr);
    }

    // For Documents
    public function indexForDocument(Document $document)
    {
        return $this->getCommentsForParent($document);
    }
    public function storeForDocument(Request $request, Document $document)
    {
        return $this->storeCommentForParent($request, $document);
    }

    // For Revisions
    public function indexForRevision(Revision $revision)
    {
        return $this->getCommentsForParent($revision);
    }
    public function storeForRevision(Request $request, Revision $revision)
    {
        return $this->storeCommentForParent($request, $revision);
    }

    // Standard CRUD for individual comments (if direct management is needed)
    public function show(Comment $comment)
    {
        // Add authorization: only comment owner or admin/relevant user can view?
        return $comment->load('user:id,name', 'commentable'); // Load user and parent
    }

    public function update(Request $request, Comment $comment)
    {
        // Authorization: Only comment owner should update.
        if ($comment->userId !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to update this comment.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $comment->update($request->only('content'));
        return response()->json($comment->load('user:id,name'));
    }

    public function destroy(Comment $comment)
    {
        // Authorization: Only comment owner or admin should delete.
        if ($comment->userId !== Auth::id() && !Auth::user()->isAdmin()) { // Assuming isAdmin() method on User
            // Or check based on role, e.g., if current user is moderator/admin
            return response()->json(['message' => 'Unauthorized to delete this comment.'], 403);
        }

        $comment->delete();
        return response()->json(null, 204);
    }
}
