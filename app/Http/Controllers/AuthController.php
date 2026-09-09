<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        $loginInput = trim((string) ($request->input('login') ?: $request->input('email')));
        $password = (string) $request->input('password');
        $remember = $request->boolean('remember');

        // Look up by email, phone, or direct ID
        $user = User::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->orWhere('id', $loginInput)
            ->first();

        // Also allow worker ID like "WORKER-001" or numeric worker id
        if (!$user && preg_match('/\d+/', $loginInput, $matches)) {
            $numericId = (int) $matches[0];
            $worker = Worker::find($numericId);
            if ($worker) {
                $user = $worker->user;
            }
        }

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'No. ID Pekerjaan / Email atau kata sandi tidak sesuai.',
        ])->onlyInput('login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
            ]);
            $user->assignRole($data['role']);
            $user->wallet()->create();

            if ($data['role'] === 'worker') {
                $user->worker()->create([
                    'city' => $data['city'],
                    'headline' => 'Casual Worker',
                    'verification_status' => 'pending',
                ]);
            } else {
                $user->company()->create([
                    'name' => $data['company_name'],
                    'slug' => Str::slug($data['company_name']).'-'.Str::lower(Str::random(5)),
                    'city' => $data['city'],
                ]);
            }

            return $user;
        });

        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat.');
    }

    public function forgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ], [
            'login.required' => 'Silakan masukkan No. ID Pekerjaan atau Email Anda.',
        ]);

        $loginInput = trim((string) $request->input('login'));
        $user = User::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->orWhere('id', $loginInput)
            ->first();

        if (!$user && preg_match('/\d+/', $loginInput, $matches)) {
            $numericId = (int) $matches[0];
            $worker = Worker::find($numericId);
            if ($worker) {
                $user = $worker->user;
            }
        }

        if (!$user) {
            return back()->withErrors([
                'login' => 'Akun dengan No. ID / Email tersebut tidak ditemukan dalam sistem.',
            ])->withInput();
        }

        // Generate token and record in password_reset_tokens
        $token = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Redirect directly to the reset password form with pre-filled token
        return redirect()->route('password.reset', ['token' => $token, 'email' => $user->email])
            ->with('status', 'Akun ditemukan atas nama ' . $user->name . ' (' . $user->email . '). Silakan masukkan kata sandi baru Anda di bawah.');
    }

    public function resetPassword(Request $request, string $token): View
    {
        $email = $request->query('email', old('email', ''));
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function updatePasswordFromReset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors([
                'email' => 'Token reset kata sandi tidak valid atau telah kedaluwarsa. Silakan ulangi proses lupa kata sandi.',
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Pengguna tidak ditemukan.']);
        }

        $user->update(['password' => $request->password]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('home');
    }
}

