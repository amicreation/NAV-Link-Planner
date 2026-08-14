<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate user.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            AuditLogService::log(
                action: 'LOGIN_FAILED',
                description: "Failed login attempt for email: {$credentials['email']}"
            );
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ], 401);
        }

        if ($user->status !== 'active') {
            AuditLogService::log(
                action: 'LOGIN_BLOCKED',
                description: "Deactivated user attempted login: {$user->email}",
                userId: $user->id,
                userName: $user->name
            );
            return response()->json([
                'status' => 'error',
                'message' => 'Account is deactivated. Please contact your system administrator.'
            ], 403);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);

        AuditLogService::log(
            action: 'USER_LOGIN',
            description: "User logged in successfully (Role: {$user->role})",
            userId: $user->id,
            userName: $user->name
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'mobile_number' => $user->mobile_number,
                'company' => $user->company,
                'designation' => $user->designation,
                'department' => $user->department,
                'status' => $user->status,
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLogService::log(
                action: 'USER_LOGOUT',
                description: 'User logged out',
                userId: $user->id,
                userName: $user->name
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * Return authenticated user profile.
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'mobile_number' => $user->mobile_number,
                'company' => $user->company,
                'designation' => $user->designation,
                'department' => $user->department,
                'status' => $user->status,
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        AuditLogService::log(
            action: 'PROFILE_UPDATED',
            description: 'Updated profile information',
            userId: $user->id,
            userName: $user->name
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password does not match.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        AuditLogService::log(
            action: 'PASSWORD_CHANGED',
            description: 'User changed their account password',
            userId: $user->id,
            userName: $user->name
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.'
        ]);
    }
}
