<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Agency;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function index(): View
    {
        $users = User::with('agency')->orderBy('created_at', 'desc')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $agencies = Agency::active()->orderBy('name')->get();

        return view('admin.users.create', compact('agencies'));
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Agency-based access is no longer used in role assignment.
        $data['agency_id'] = null;

        $user = User::create($data);

        $this->audit->log(
            userId: $request->user()->id,
            action: 'created',
            tableName: 'users',
            recordId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'agency_id' => $user->agency_id],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    public function edit(User $user): View
    {
        $agencies = Agency::active()->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'agencies'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $oldValues = $user->only(['name', 'email', 'role', 'agency_id']);

        $data = $request->safe()->only(['name', 'email', 'role', 'agency_id']);

        // Agency-based access is no longer used in role assignment.
        $data['agency_id'] = null;

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);

        $this->audit->log(
            userId: $request->user()->id,
            action: 'updated',
            tableName: 'users',
            recordId: $user->id,
            oldValues: $oldValues,
            newValues: $user->only(['name', 'email', 'role', 'agency_id']),
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;

        $this->audit->log(
            userId: Auth::id(),
            action: 'deleted',
            tableName: 'users',
            recordId: $user->id,
            oldValues: $user->only(['name', 'email', 'role', 'agency_id']),
        );

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$userName}\" deleted successfully.");
    }
}
