<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // First, check if the user exists and credentials are correct
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user is approved - applies to ALL roles including admin
            if (!$user->is_approved) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()
                    ->withInput($request->only('email'))
                    ->with('warning', 'Your account is pending admin approval. Please wait for an administrator to confirm your account before you can log in.');
            }

            $request->session()->regenerate();

            return match ($user->role) {
                'admin'   => redirect()->intended(route('admin.dashboard')),
                'teacher' => redirect()->intended(route('teacher.sessions.index')),
                'student' => redirect()->intended(route('student.attendance.index')),
                default   => redirect('/'),
            };
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
