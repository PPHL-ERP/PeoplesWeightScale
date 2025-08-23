<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class WebAuthController extends Controller
{
    // API base URL (ইচ্ছা করলে .env এ API_BASE সেট করে নাও)
    private string $base;

    public function __construct()
    {
        //$this->base = rtrim(config('services.remote_api.base', env('API_BASE', 'http://43.224.116.185:8010/api/v2/')), '/') . '/';
        $this->base = rtrim(config('services.remote_api.base'), '/') . '/';

    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        // 1) API login
        $response = Http::post($this->base.'a/login', [
            'email'    => $cred['email'],
            'password' => $cred['password'],
        ]);

        if (! $response->successful()) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        $payload = $response->json();
        $token   = data_get($payload, 'token');

        if (! $token) {
            return back()->withErrors(['email' => 'Invalid API response: token missing'])->withInput();
        }

        // 2) API user info
        $userResp = Http::withToken($token)->get($this->base.'get-my-info');
        if (! $userResp->successful()) {
            return back()->withErrors(['email' => 'Failed to fetch user info'])->withInput();
        }

        $info       = $userResp->json();
        $remoteUser = data_get($info, 'user', $info);

        $email = data_get($remoteUser, 'email', $cred['email']);
        $name  = data_get($remoteUser, 'name', data_get($remoteUser, 'userName', $email));
        $image = data_get($remoteUser, 'image');

        // 3) লোকাল User ensure + আপডেট
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt(Str::random(32))]
        );
        // keep local info updated
        $user->name  = $name ?: $user->name;
        if ($image) $user->image = $image;
        $user->save();

        // 4) Laravel auth guard এ লগইন
        Auth::login($user, $request->boolean('remember'));

        // 5) আগের মতই session কীগুলো রাখি (যদি app এর অন্য জায়গায় লাগে)
        Session::put('jwt_token', $token);
        Session::put('user', ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Session::forget(['jwt_token', 'user']);

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}