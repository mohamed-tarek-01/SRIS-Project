<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrafficRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Redirect based on role and e_wallet completion will be handled by middleware,
            // but we can just redirect to dashboard here.
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'national_id' => ['required', 'string', 'unique:users'],
        ]);

        // Check if national_id exists in TrafficRecords
        $hasCar = TrafficRecord::where('national_id', $request->national_id)->exists();

        if (!$hasCar) {
            throw ValidationException::withMessages([
                'national_id' => ['Your National ID is not registered with any vehicles in the Traffic Authority.'],
            ]);
        }

        $otp = rand(100000, 999999);
        $request->session()->put('registration_data', $validatedData);
        $request->session()->put('registration_otp', $otp);

        \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\OtpMail($otp));

        return redirect()->route('verify.otp');
    }

    public function showVerifyOtp(Request $request)
    {
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register');
        }
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'numeric'],
        ]);

        $storedOtp = $request->session()->get('registration_otp');
        $data = $request->session()->get('registration_data');

        if (!$storedOtp || !$data || $request->otp != $storedOtp) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'national_id' => $data['national_id'],
            'role' => 'user',
        ]);

        $request->session()->forget(['registration_data', 'registration_otp']);

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}
