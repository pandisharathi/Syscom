<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use AppliesInstitutionScope;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(\Illuminate\Http\Request $request): View
    {
        $rolesQuery = Role::query()->orderBy('name');
        if (! $request->user()->isSuperAdmin()) {
            $rolesQuery->whereNotIn('slug', [User::ROLE_SUPER_ADMIN, 'admin']);
        }

        $institutions = [];
        if ($request->user()->isSuperAdmin()) {
            $institutions = \App\Models\Institution::query()->orderBy('name')->get();
        }

        return view('admin.users.index', [
            'roles' => $rolesQuery->get(),
            'institutions' => $institutions,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $q = User::query()->with(['role', 'institution'])->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['name', 'email', 'phone'],
            fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->role?->name,
                'institution' => $u->institution?->name,
                'status' => $u->status,
                'role_id' => $u->role_id,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, null, true);
        $data['password'] = Hash::make($data['password']);
        if (! $request->user()->isSuperAdmin()) {
            $data['institution_id'] = $request->user()->institution_id;
        }
        $user = User::query()->create($data);
        $this->activityLog->log('user.created', $user);

        return response()->json(['message' => 'Saved']);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            abort_unless((int) $user->institution_id === (int) $request->user()->institution_id, 403);
            abort_if($user->isSuperAdmin(), 403);
        }
        $data = $this->validated($request, $user->id, false);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        $this->activityLog->log('user.updated', $user);

        return response()->json(['message' => 'Updated']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->isSuperAdmin()) {
            abort(403);
        }
        if (! $request->user()->isSuperAdmin()) {
            abort_unless((int) $user->institution_id === (int) $request->user()->institution_id, 403);
        }
        $user->delete();
        $this->activityLog->log('user.deleted', $user);

        return response()->json(['message' => 'Deleted']);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can reset passwords.');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);
        
        $this->activityLog->log('user.password_reset', $user);

        return response()->json(['message' => 'Password reset successfully']);
    }

    private function validated(Request $request, ?int $userId = null, bool $isCreate = false): array
    {
        $rules = [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:191'],
            'email' => [
                $isCreate ? 'required' : 'sometimes',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [$isCreate ? 'required' : 'nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,inactive'],
        ];

        $data = $request->validate($rules);

        if ($request->user()->isSuperAdmin()) {
            return $data;
        }

        // Institution admins cannot assign super-admin role or other institutions
        unset($data['institution_id']);
        $role = Role::query()->findOrFail($data['role_id']);
        abort_if($role->slug === User::ROLE_SUPER_ADMIN, 403);

        return $data;
    }
}
