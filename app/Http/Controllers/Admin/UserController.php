<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserCreatedMail;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('users-view'), 403);

        $users = User::with('unit')->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('users-create'), 403);

        $units = Unit::all();
        $roles = Role::all();
        return view('admin.users.create',compact('units','roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()?->can('users-create'), 403);

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
        'role_id' => 'required',
        // 'password' => 'required|min:5',
        'image' => 'nullable|image|mimes:jpg,jpeg,png'

    ],[
    'email.unique' => 'This email is already registered.',
    ]);

    //  NIC as password
    $plainPassword = $request->nic;

    $imageName = null;

    // image upload
    if ($request->hasFile('image')) {

        $imageName = time().'.'.$request->image->extension();

        $request->image->move(public_path('assets/images/profiles'), $imageName);
    }

    // insert user
    $user = User::create([
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
        'role_id' => $request->role_id,
        'password' => Hash::make($plainPassword),
        'force_password_change' => 1,
    ]);

    $role = Role::find($request->role_id);
    if ($role) {
        $user->syncRoles($role->name);
    }

    try {
        Mail::to($user->email)->send(new UserCreatedMail($user, $plainPassword));

        return redirect()->route('users.index')
                     ->with('success','User created & email sent');
    } catch (\Exception $e) {
        \Log::error('User creation email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

        return back()
                     ->withInput()
                     ->with('error','User created but welcome email could not be sent. Please check mail settings.');
    }

    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_unless((int) $id === auth()->id() || auth()->user()?->can('users-view'), 403);

        $user = User::with('unit')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_unless(auth()->user()?->can('users-edit'), 403);

        $user = User::findOrFail($id);
        $units=Unit::all();
        $roles=Role::all();
        return view('admin.users.edit', compact('user','units','roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_unless($request->user()?->can('users-edit'), 403);

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
        'role_id' => 'required',
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
    $user->role_id = $request->role_id;

    if ($request->role_id) {
        $role = Role::find($request->role_id);
        if ($role) {
            $user->syncRoles($role->name);
        }
    } else {
        $user->syncRoles([]);
    }

    $user->save();

    return redirect()->route('users.index')
            ->with('success','User Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_unless(auth()->user()?->can('users-delete'), 403);

        $user = User::findOrFail($id);

        // delete image
        if ($user->image && file_exists(public_path('assets/images/profiles/'.$user->image))) {
            unlink(public_path('assets/images/profiles/'.$user->image));
        }

        $user->delete();

        return redirect()->route('users.index');
    }

    public function changePassword()
    {
        return view('auth.change_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // update password
        $user->password = Hash::make($request->password);

        // remove force flag
        $user->force_password_change = 0;

        $user->save();

        // logout user
        Auth::logout();

        return redirect()->route('login')
                ->with('success','Password changed successfully. Please login again.');
    }
}
