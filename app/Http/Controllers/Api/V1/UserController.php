<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Basic list of users, primarily for selection in dropdowns
        // You might want to add pagination if the user list is very large,
        // but for selection purposes, a simpler, non-paginated list might be acceptable
        // if the number of users isn't excessive.

        $query = User::query()->select(['id', 'name'])->orderBy('name');

        if ($request->has('per_page') && $request->per_page === '1000') { // Simple check for a "fetch all" type request from frontend filters
            $users = $query->get();
            return response()->json($users); // Return as a flat array if per_page is 1000
        }

        // Default to pagination if not fetching all
        $users = $query->paginate($request->input('per_page', 15));

        return response()->json($users);
    }
} 