<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Enums\Role; // Import your Role enum

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): Response
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Assuming 'role' is optional on registration, defaults to USER
            // If role needs to be specified, add validation for it:
            // 'role' => ['sometimes', 'required', \Illuminate\Validation\Rule::enum(Role::class)]
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            // The 'passwordHash' attribute is automatically hashed via the model cast
            'passwordHash' => $validated['password'],
            // Default role if not provided, or use $validated['role'] if included in validation
            'role' => $request->role ? Role::from($request->role) : Role::USER,
        ]);

        // Optionally log the user in immediately after registration
        // Auth::login($user);
        // $request->session()->regenerate();

        return response()->noContent(201); // Return 201 Created
    }

    /**
     * Authenticate user and start session.
     */
    public function login(Request $request): Response
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Note: We are logging in against the 'passwordHash' column in the DB
        // but Laravel's Auth::attempt expects 'password' in the credentials array.
        // It automatically hashes the provided password and compares it to the stored hash.
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Return the authenticated user or just success status
            // return response()->json(Auth::user());
            return response()->noContent();
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out (invalidate session).
     */
    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request): Response
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->save();

        return response()->noContent();
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): Response
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->passwordHash)) {
                    $fail('The :attribute is incorrect.');
                }
            }],
            'password' => ['required', 'string', Rules\Password::defaults(), 'confirmed'],
        ]);

        $user->passwordHash = $validated['password']; // The mutator in User model will hash this
        $user->save();

        return response()->noContent();
    }
}
