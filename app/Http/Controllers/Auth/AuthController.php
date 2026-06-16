<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\InviteAccessService;
use App\Services\InviteCodeGenerator;
use App\Services\UnauthorizedAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show(Request $request, InviteAccessService $inviteAccess)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.index', [
            'canRegister' => $inviteAccess->hasValidContext($request),
        ]);
    }

    public function login(LoginRequest $request, InviteAccessService $inviteAccess)
    {
        $data = $request->validated();
        $user = User::where('mobile', $data['mobile'])->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل یا رمز عبور درست نیست.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'mobile' => 'حساب شما غیرفعال است. لطفا با مدیر کاپ هماهنگ کنید.',
            ]);
        }

        Auth::login($user);
        $inviteAccess->clearContext($request);
        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended');
        $redirect = is_string($intended) && str_starts_with($intended, url('/'))
            ? $intended
            : ($user->is_admin ? route('news-admin.dashboard') : route('dashboard'));

        return response()->json([
            'message' => 'ورود انجام شد.',
            'redirect' => $redirect,
        ]);
    }

    public function register(
        RegisterRequest $request,
        InviteCodeGenerator $inviteCodeGenerator,
        InviteAccessService $inviteAccess,
    ) {
        $inviteAccess->requireContext($request);
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'mobile' => $data['mobile'],
            'password' => $data['password'],
            'invite_code' => $inviteCodeGenerator->make(),
            'mobile_verified_at' => now(),
        ]);

        $inviteAccess->completeRegistration($user, $request);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'عضویت شما کامل شد.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function logout(Request $request, UnauthorizedAccessService $unauthorizedAccess)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $unauthorizedAccess->deny();
    }
}
