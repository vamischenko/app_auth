<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Контроллер аутентификации через магические ссылки
 *
 * Обрабатывает вход пользователей в систему без пароля через одноразовые
 * магические ссылки, отправляемые на email. Ссылки действительны 30 минут
 * и могут быть использованы только один раз.
 */
class MagicLinkController extends Controller
{
    /**
     * Отображает форму запроса магической ссылки
     *
     * @return \Illuminate\View\View Представление формы запроса
     */
    public function showRequestForm()
    {
        return view('auth.magic-link.request');
    }

    /**
     * Отправляет магическую ссылку на указанный email
     *
     * Проверяет существование пользователя, удаляет старые неиспользованные ссылки,
     * создает новую магическую ссылку и отправляет её на email.
     * Для безопасности возвращает одинаковый ответ независимо от существования пользователя.
     *
     * @param Request $request HTTP запрос с email адресом
     * @return \Illuminate\Http\RedirectResponse Перенаправление с сообщением об отправке
     */
    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()->with('status', 'If an account exists with this email, you will receive a magic link.');
        }

        MagicLink::where('email', $request->email)
            ->where('used_at', null)
            ->delete();

        $magicLink = MagicLink::createForEmail($request->email);

        Mail::send('emails.magic-link', [
            'url' => route('magic-link.verify', ['token' => $magicLink->token]),
            'user' => $user,
        ], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Your Magic Login Link');
        });

        return redirect()->back()->with('status', 'If an account exists with this email, you will receive a magic link.');
    }

    /**
     * Проверяет и активирует магическую ссылку для входа
     *
     * Проверяет валидность токена магической ссылки (срок действия и использование),
     * находит пользователя по email, помечает ссылку как использованную и выполняет
     * вход в систему с установкой флага "Запомнить меня".
     *
     * @param Request $request HTTP запрос
     * @param string $token Токен магической ссылки
     * @return \Illuminate\Http\RedirectResponse Перенаправление на dashboard или страницу входа с ошибкой
     */
    public function verify(Request $request, string $token)
    {
        $magicLink = MagicLink::where('token', $token)->first();

        if (!$magicLink || !$magicLink->isValid()) {
            return redirect()->route('login')->withErrors(['error' => 'This magic link is invalid or has expired.']);
        }

        $user = User::where('email', $magicLink->email)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'User not found.']);
        }

        $magicLink->markAsUsed();

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
