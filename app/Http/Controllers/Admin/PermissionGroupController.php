<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;

class PermissionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = PermissionGroup::withCount('permissions')->latest()->get();
        return view('admin.permission_groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permission_groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_name'=>'required'
        ]);

        PermissionGroup::create([
            'group_name'=>$request->group_name
        ]);

        return redirect()->route('permission-groups.index');
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
        $group = PermissionGroup::findOrFail($id);
        return view('admin.permission_groups.edit',compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $group = PermissionGroup::findOrFail($id);

        $group->update([
            'group_name'=>$request->group_name
        ]);

        return redirect()->route('permission-groups.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        PermissionGroup::findOrFail($id)->delete();
        return back();
    }
}
