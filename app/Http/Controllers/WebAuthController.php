<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class WebAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $response = Http::post('http://43.224.116.185:8010/api/v2/a/login', [
            'email'    => $request->email,
            'password' => $request->password,
        ]);


            if ($response->successful()) {
                $data = $response->json();

                if (!isset($data['token'])) {
                    return back()->withErrors(['email' => 'Invalid API response: Token missing.'])->withInput();
                }

                // Save JWT Token
                Session::put('jwt_token', $data['token']);

                // Fetch user info
                $userResponse = Http::withToken($data['token'])
                    ->get(env('API_URL') . 'http://43.224.116.185:8010/api/v2/get-my-info');

                if ($userResponse->successful()) {
                    $userData = $userResponse->json();

                    // Save user info in session
                    Session::put('user', $userData['user'] ?? $userData);

                    // ✅ Debug: Check if session saved properly
                    logger('Session saved: ', Session::all());

                    // Redirect to dashboard
                    return redirect()->route('dashboard');
                } else {
                    return back()->withErrors(['email' => 'Failed to fetch user info.'])->withInput();
                }
            } else {
                return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
            }
        // }

    }

    public function logout()
    {
        Session::forget(['jwt_token', 'user']);
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
