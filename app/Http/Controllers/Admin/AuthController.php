<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email','password'))) {

            return redirect()->route('dashboard')
                ->with('success','Login Successful');
        }

        return back()->with('error','Invalid Email or Password');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    // SEND RESET LINK
    public function sendReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success','Reset link sent to your email')
            : back()->withErrors(['email' => __($status)]);
    }

    // SHOW RESET FORM
    public function showReset(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    // UPDATE PASSWORD
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->remember_token = Str::random(60);
                $user->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success','Password reset successful')
            : back()->withErrors(['email' => __($status)]);
    }
}
