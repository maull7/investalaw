<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->search, fn ($q, $search) => $q->whereAny(['name', 'email'], 'like', "%{$search}%"))
            ->latest()
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['permissions'] = $data['permissions'] ?? [];

        $user = User::create($data);

        UserActivityLog::log('created', User::class, $user->id, "Menambahkan user {$user->name} ({$user->email}) dengan role {$user->role}");

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        $logs = $user->activityLogs()
            ->latest()
            ->paginate(20);

        return view('users.show', compact('user', 'logs'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['permissions'] = $data['permissions'] ?? [];

        $changes = collect($data)->except('permissions')->filter(fn ($val, $key) => $val != $user->$key);

        $user->update($data);

        $desc = "Memperbarui user {$user->name}";
        if ($changes->isNotEmpty()) {
            $desc .= ' ('.$changes->keys()->implode(', ').')';
        }

        UserActivityLog::log('updated', User::class, $user->id, $desc);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdmin() && User::where('role', 'admin')->count() === 1) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $name = $user->name;
        $user->delete();

        UserActivityLog::log('deleted', User::class, $user->id, "Menghapus user {$name}");

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
