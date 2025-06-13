<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/calendar' // Google Calendar
            ])
            ->with(['prompt' => 'consent', 'access_type' => 'offline']) //　同意プロンプト強制
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'password' => bcrypt(Str::random(16)), // ランダムパスワード
            ]
        );

        // トークン保存（上書き）
        $user->google_access_token = $googleUser->token;
        $user->google_refresh_token = $googleUser->refreshToken ?? $user->google_refresh_token; // 初回だけ取得可
        $user->save();

        Auth::login($user);

        return redirect('/events'); // ログイン後のリダイレクト先
    }
}
