<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Ambil role khusus guard web. */
    private function webRoles()
    {
        return Role::where('guard_name', 'web')->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $users = User::with(['department', 'roles'])
            ->when($request->search, function ($q, $s) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                                       ->orWhere('email', 'like', "%{$s}%"));
            })
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = $this->webRoles();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        return view('users.create', [
            'user'        => new User(),
            'roles'       => $this->webRoles(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'role'          => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $role = Role::where('name', $data['role'])->where('guard_name', 'web')->firstOrFail();

        DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'password'      => Hash::make($data['password']),
                'department_id' => $data['department_id'] ?? null,
            ]);

            $user->syncRoles([$role]);
        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user'        => $user->load('roles'),
            'roles'       => $this->webRoles(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => 'nullable|string|min:8|confirmed',
            'role'          => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Cegah Super Admin mencabut role dirinya sendiri
        if ($user->id === auth()->id() && $data['role'] !== 'Super Admin') {
            return back()->withErrors(['role' => 'Kamu tidak bisa mengubah role akunmu sendiri.'])->withInput();
        }

        $role = Role::where('name', $data['role'])->where('guard_name', 'web')->firstOrFail();

        DB::transaction(function () use ($data, $user, $role) {
            $user->fill([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'department_id' => $data['department_id'] ?? null,
            ]);

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
            $user->syncRoles([$role]);
        });

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}