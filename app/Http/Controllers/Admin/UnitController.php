<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->get();
        return view('admin.units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // Validation
    $request->validate([
        'unit_name'      => 'required|string|max:150|unique:units,unit_name',
        'description'    => 'nullable|string|max:500',
        'incharge_name'  => 'nullable|string|max:150',
        'status'         => 'required|in:0,1',
    ]);

    // Insert
    Unit::create([
        'unit_name'     => $request->unit_name,
        'description'   => $request->description,
        'incharge_name' => $request->incharge_name,
        'status'        => $request->status,
    ]);

    // Redirect
    return redirect()->route('units.index')
        ->with('success','Clinic Unit Created Successfully');
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
        $unit = Unit::findOrFail($id);
        return view('admin.units.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
        'unit_name'      => 'required|string|max:150|unique:units,unit_name,'.$id,
        'description'    => 'nullable|string|max:500',
        'incharge_name'  => 'nullable|string|max:150',
        'status'         => 'required|in:0,1',
    ]);

    $unit->update($request->all());

    return redirect()->route('units.index')
            ->with('success','Unit Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $units = Unit::findOrFail($id);
        $units->delete();

        return redirect()->route('units.index');
    }
}
