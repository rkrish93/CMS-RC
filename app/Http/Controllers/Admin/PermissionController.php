<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::with('group')->get();
        $groups = PermissionGroup::all();
        return view('admin.permissions.index',compact('permissions','groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = PermissionGroup::all();
        return view('admin.permissions.create',compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'group_id' => 'nullable|exists:permission_groups,id',
        ]);

        Permission::create([
            'name' => $request->name,
            'group_id' => $request->group_id,
            'guard_name'=>'web',
        ]);

        return redirect()->route('permissions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);
        $groups = PermissionGroup::all();

        return view('admin.permissions.edit',compact('permission','groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Permission::findOrFail($id)->update([
            'name'=>$request->name,
            'group_id'=>$request->group_id
        ]);

        return redirect()->route('permissions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Permission::findOrFail($id)->delete();
        return back();
    }
}
