<?php

namespace App\Http\Controllers\Authentikasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function index(): View
    {
        return view('pages.authentikasi.forgot-password');
    }

    /**
     * Step 1 — Proses kirim email reset password
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        session(['reset_email' => $request->email]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? redirect()->route('auth.check-email')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Step 2 — Tampilkan halaman "Cek Email Anda".
     */
    public function checkEmail(): View|RedirectResponse
    {
        if (!session('reset_email')) {
            return redirect()->route('auth.forgot-password');
        }

        return view('pages.authentikasi.check-email');
    }

    /**
     * Step 2 — Kirim ulang email reset password.
     */
    public function resendEmail(): RedirectResponse
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('auth.forgot-password');
        }

        Http::post(config('app.api_url') . '/auth/forgot-password', [
            'email' => $email,
        ]);

        return back()->with('success', 'Email reset password telah dikirim ulang.');
    }

    /**
     * Step 3 — Tampilkan form buat password baru.
     */
    public function resetPasswordForm(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        Log::info('RESET LINK OPENED', [
            'token' => $token,
            'email' => $email,
            'full_url' => $request->fullUrl(),
        ]);

        if (!$token || !$email) {
            return redirect()
                ->route('auth.forgot-password')
                ->with('error', 'Link reset password tidak valid atau sudah kadaluarsa.');
        }

        return view('pages.authentikasi.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * STEP 3 — Proses simpan password baru
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->setRememberToken(
                    Str::random(60)
                );

                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors([
                'email' => __($status),
            ]);
        }

        session()->forget('reset_email');

        return redirect()->route('auth.reset-success');
    }

    /**
     * Step 4 — Tampilkan halaman sukses.
     */
    public function resetSuccess(): View
    {
        return view('pages.authentikasi.reset-success');
    }
}