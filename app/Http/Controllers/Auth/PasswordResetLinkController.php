<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Контроллер запроса ссылки для сброса пароля
 *
 * Обрабатывает запросы на отправку ссылки для сброса пароля на email пользователя.
 * Ссылка содержит временный токен для верификации при сбросе пароля.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Отображает форму запроса ссылки для сброса пароля
     *
     * @return View Представление формы "Забыли пароль?"
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Обрабатывает запрос на отправку ссылки для сброса пароля
     *
     * Отправляет email со ссылкой для сброса пароля на указанный адрес.
     * Проверяет наличие пользователя с таким email в базе данных.
     *
     * @param Request $request HTTP запрос с email адресом
     * @return RedirectResponse Перенаправление назад с сообщением об успехе или ошибке
     * @throws \Illuminate\Validation\ValidationException Если email не валиден
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
