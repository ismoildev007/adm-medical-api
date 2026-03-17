<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('web.dashboard');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username yoki parol noto\'g\'ri.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $this->auditService->logAuthEvent('logged_in', $user, [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'username' => $user->username,
        ]);

        return redirect()->intended(route('web.dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->auditService->logAuthEvent('logged_out', $user, [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'username' => $user->username,
        ]);
        return redirect()->route('login');
    }
}
