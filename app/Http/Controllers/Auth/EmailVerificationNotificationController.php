<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Контроллер отправки уведомления о верификации email
 *
 * Обрабатывает повторную отправку письма с ссылкой для верификации email адреса.
 * Используется когда пользователь не получил или потерял предыдущее письмо.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Отправляет новое уведомление о верификации email
     *
     * Проверяет, не подтвержден ли уже email. Если нет - отправляет новое
     * письмо с ссылкой для верификации.
     *
     * @param Request $request HTTP запрос
     * @return RedirectResponse Перенаправление на dashboard если email подтвержден, или назад с сообщением
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
