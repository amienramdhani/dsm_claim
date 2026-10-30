<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function login(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string', 'max:100'], 'pin' => ['required', 'digits_between:6,12']]);
        $user = \App\Models\User::where('username', $data['username'])->where('is_active', true)->first();
        if (!$user || !password_verify($data['pin'], $user->pin_hash)) return back()->withErrors(['username' => 'Data masuk tidak valid.'])->onlyInput('username');
        Auth::login($user); $request->session()->regenerate(); return redirect()->intended(route('claims.index'));
    }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
}
