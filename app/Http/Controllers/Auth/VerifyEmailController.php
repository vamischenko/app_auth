<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Контроллер верификации email адреса
 *
 * Обрабатывает переход по ссылке верификации email из письма.
 * Помечает email как подтвержденный и генерирует событие Verified.
 */
class VerifyEmailController extends Controller
{
    /**
     * Помечает email адрес пользователя как подтвержденный
     *
     * Проверяет валидность ссылки верификации, помечает email как подтвержденный
     * в базе данных и генерирует событие Verified для дополнительной обработки.
     *
     * @param EmailVerificationRequest $request Специальный запрос с автоматической проверкой подписи
     * @return RedirectResponse Перенаправление на dashboard с параметром verified=1
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
