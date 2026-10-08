<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $departments = Department::withCount('users')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($w) use ($search) {
                    $w->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('cost_center', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name'        => ['required', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper(trim($validated['code']));

        Department::create($validated);

        return back()->with('success', 'Department created successfully.');
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:20',
                              Rule::unique('departments', 'code')->ignore($department->id)],
            'name'        => ['required', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper(trim($validated['code']));

        $department->update($validated);

        return back()->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Prevent deletion if the department is still assigned to users
        if ($department->users()->exists()) {
            return back()->withErrors([
                'delete' => 'Cannot delete: this department is still assigned to one or more users.',
            ]);
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }
}