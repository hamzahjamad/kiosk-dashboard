<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * List users (for admin).
     */
    public function index(): JsonResponse
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'created_at']);

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user' => $user->only(['id', 'name', 'email', 'created_at']),
        ], 201);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (! empty($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user' => $user->only(['id', 'name', 'email', 'created_at']),
        ]);
    }

    /**
     * Delete a user. Prevent deleting yourself.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
