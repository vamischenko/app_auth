<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Контроллер OAuth аутентификации через социальные сети
 *
 * Обрабатывает аутентификацию пользователей через внешние OAuth провайдеры:
 * Google, GitHub, Facebook, ВКонтакте, Яндекс и Mail.ru.
 * Автоматически создает новых пользователей или связывает существующие учетные записи.
 */
class SocialAuthController extends Controller
{
    /**
     * Список поддерживаемых OAuth провайдеров
     *
     * @var array<int, string>
     */
    protected array $providers = [
        'google',
        'github',
        'facebook',
        'vkontakte',
        'yandex',
        'mailru',
    ];

    /**
     * Перенаправляет пользователя на страницу аутентификации провайдера
     *
     * @param string $provider Название OAuth провайдера
     * @return \Symfony\Component\HttpFoundation\RedirectResponse Перенаправление на OAuth провайдера
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Обрабатывает callback от OAuth провайдера
     *
     * Получает данные пользователя от провайдера и выполняет одно из действий:
     * 1. Обновляет токены существующего OAuth пользователя
     * 2. Связывает OAuth с существующей учетной записью по email
     * 3. Создает новую учетную запись с автоматической верификацией email
     *
     * @param string $provider Название OAuth провайдера
     * @return \Illuminate\Http\RedirectResponse Перенаправление на dashboard или страницу входа при ошибке
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Authentication failed. Please try again.']);
        }

        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if (!$user) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'oauth_provider' => $provider,
                    'oauth_id' => $socialUser->getId(),
                    'oauth_token' => $socialUser->token,
                    'oauth_refresh_token' => $socialUser->refreshToken ?? null,
                    'avatar' => $socialUser->getAvatar(),
                ]);
            } else {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'oauth_provider' => $provider,
                    'oauth_id' => $socialUser->getId(),
                    'oauth_token' => $socialUser->token,
                    'oauth_refresh_token' => $socialUser->refreshToken ?? null,
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            $user->update([
                'oauth_token' => $socialUser->token,
                'oauth_refresh_token' => $socialUser->refreshToken ?? null,
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
