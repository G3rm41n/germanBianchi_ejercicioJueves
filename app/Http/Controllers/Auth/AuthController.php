<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'username' => 'Username atau password tidak valid.',
                ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Guardar el registro de auditoría de forma manual y directa
        DB::table('login_audits')->insert([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'login_at' => now(),
        ]);

        return match ($user->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'hr'       => redirect()->route('hr.dashboard'),
            'employee' => redirect()->route('employee.dashboard'),
            default    => redirect()->route('login'),
        };
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
