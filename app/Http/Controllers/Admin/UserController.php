<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('unit')->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = Unit::all();
        return view('admin.users.create',compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd ($request->all());
    $request->validate([
        'fname' => 'required',
        'lname' => 'required',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|unique:users,phone',
        'nic' => 'required|string|max:12|unique:users,nic',
        'designation' => 'required',
        'unit_id' => 'required',
        'join_date' => 'required|date',
        'status'=> 'required|in:0,1',
        'password' => 'required|min:5',
        'image' => 'nullable|image|mimes:jpg,jpeg,png'
    ]);

    $imageName = null;

    // image upload
    if ($request->hasFile('image')) {

        $imageName = time().'.'.$request->image->extension();

        $request->image->move(public_path('assets/images/profiles'), $imageName);
    }

    // insert user
    User::create([
        'fname' => $request->fname,
        'lname' => $request->lname,
        'email' => $request->email,
        'phone' => $request->phone,
        'nic' => $request->nic,
        'designation' => $request->designation,
        'unit_id' => $request->unit_id,
        'join_date' => $request->join_date,
        'status' => $request->status,
        'image' => $imageName,
        'password' => Hash::make($request->password),
    ]);

        return redirect()->route('users.index')
                     ->with('success','User Created Successfully');
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
        $user = User::findOrFail($id);
        $units=Unit::all();
        return view('admin.users.edit', compact('user','units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

    $request->validate([
        'fname' => 'required',
        'lname' => 'required',
        'email' => 'required|email|unique:users,email,'.$id,
        'phone' => 'required|unique:users,phone,'.$id,
        'nic' => 'required|max:12|unique:users,nic,'.$id,
        'designation' => 'required',
        'unit_id' => 'required',
        'join_date' => 'required|date',
        'status'=> 'required|in:0,1',
    ]);

    if($request->password)
    {
        $user->password = bcrypt($request->password);
    }

    if ($request->hasFile('image'))
    {
        if ($user->image && file_exists(public_path('assets/images/profiles/'.$user->image))) {
            unlink(public_path('assets/images/profiles/'.$user->image));
        }

        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('assets/images/profiles'), $imageName);
        $user->image = $imageName;
    }

    $user->fname = $request->fname;
    $user->lname = $request->lname;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->nic = $request->nic;
    $user->designation = $request->designation;
    $user->join_date = $request->join_date;
    $user->unit_id = $request->unit_id;
    $user->status = $request->status;

    $user->save();

    return redirect()->route('users.index')
            ->with('success','User Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

    // delete image
    if ($user->image && file_exists(public_path('assets/images/profiles/'.$user->image))) {
        unlink(public_path('assets/images/profiles/'.$user->image));
    }

    $user->delete();

    return redirect()->route('users.index');
    }
}
