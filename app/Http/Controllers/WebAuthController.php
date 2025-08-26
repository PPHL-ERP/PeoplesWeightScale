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

    public function loginold(Request $request)
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
    public function login(Request $request)
    {
        // 1) Allow email OR username in the same input
        $request->validate([
            'email'    => ['required', 'string', 'max:100'], // can be email or username
            'password' => ['required', 'string'],
        ]);

        $login    = $request->input('email');     // single input for both
        $password = $request->input('password');

        // API endpoints (don’t prepend env() to a full URL)
        $authUrl = 'http://43.224.116.185:8010/api/v2/a/login';
        $meUrl   = 'http://43.224.116.185:8010/api/v2/get-my-info';

        $isEmail    = filter_var($login, FILTER_VALIDATE_EMAIL);
        $firstKey   = $isEmail ? 'email' : 'username';
        $secondKey  = $isEmail ? null : 'email'; // only retry if it looked like username

        $tryLogin = function (string $key) use ($authUrl, $login, $password) {
            return Http::post($authUrl, [
                $key       => $login,
                'password' => $password,
            ]);
        };

        $response = $tryLogin($firstKey);

        if (! $response->successful() && $secondKey) {
            $response = $tryLogin($secondKey);
        }

        if (! $response->successful()) {

            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        $data = $response->json();
        if (! isset($data['token'])) {
            return back()->withErrors(['email' => 'Invalid API response: Token missing.'])->withInput();
        }

        // 5) Save token
        Session::put('jwt_token', $data['token']);

        // 6) Fetch user info
        $userResponse = Http::withToken($data['token'])->get($meUrl);
        if (! $userResponse->successful()) {
            return back()->withErrors(['email' => 'Failed to fetch user info.'])->withInput();
        }

        $userData = $userResponse->json();
        Session::put('user', $userData['user'] ?? $userData);

        // Optional: debug
        // logger('Session saved', ['keys' => array_keys(Session::all())]);

        return redirect()->route('dashboard');
    }
    public function logout()
    {
        Session::forget(['jwt_token', 'user']);
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
