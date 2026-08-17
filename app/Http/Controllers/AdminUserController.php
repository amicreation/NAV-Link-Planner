<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Ensure current user is admin.
     */
    private function checkAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized. Administrator access required.');
        }
    }

    /**
     * Display a listing of all users.
     */
    public function index(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $query = User::query()->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $users = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'technical', 'engineer'])],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['created_by_id'] = Auth::id();

        $user = User::create($data);

        AuditLogService::log(
            action: 'USER_CREATED',
            description: "Admin created user '{$user->name}' ({$user->email}) with role {$user->role}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => $user
        ], 201);
    }

    /**
     * Display specified user.
     */
    public function show(User $user): JsonResponse
    {
        $this->checkAdmin();

        return response()->json([
            'status' => 'success',
            'data' => $user->load('projects')
        ]);
    }

    /**
     * Update specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->checkAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'technical', 'engineer'])],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user->update($data);

        AuditLogService::log(
            action: 'USER_UPDATED',
            description: "Admin updated user '{$user->name}' ({$user->email})"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => $user
        ]);
    }

    /**
     * Reset password for user.
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $this->checkAdmin();

        $request->validate([
            'password' => ['required', 'string', 'min:8']
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        AuditLogService::log(
            action: 'USER_PASSWORD_RESET',
            description: "Admin reset password for user '{$user->name}' ({$user->email})"
        );

        return response()->json([
            'status' => 'success',
            'message' => "Password reset successfully for user {$user->name}."
        ]);
    }

    /**
     * Remove specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->checkAdmin();

        if ($user->id === Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete your own active administrator account.'
            ], 422);
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $user->delete();

        AuditLogService::log(
            action: 'USER_DELETED',
            description: "Admin deleted user '{$userName}' ({$userEmail})"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.'
        ]);
    }

    /**
     * Get system audit logs.
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->limit(100)->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }
}
