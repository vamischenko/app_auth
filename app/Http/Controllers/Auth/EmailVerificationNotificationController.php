<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
     * Ограничено 2 попытками в минуту для предотвращения спама.
     *
     * @param Request $request HTTP запрос
     * @return RedirectResponse Перенаправление на dashboard если email подтвержден, или назад с сообщением
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Rate limiting: максимум 2 попытки в минуту
        $key = 'send-verification:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Слишком много попыток. Пожалуйста, подождите {$seconds} секунд.",
            ]);
        }

        RateLimiter::hit($key, 60); // 1 минута

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
