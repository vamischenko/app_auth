<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Контроллер напоминания о верификации email
 *
 * Отображает страницу с просьбой подтвердить email адрес.
 * Если email уже подтвержден, перенаправляет на dashboard.
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Отображает страницу напоминания о верификации email
     *
     * Проверяет, подтвержден ли email пользователя. Если подтвержден - перенаправляет
     * на предполагаемую страницу или dashboard, иначе показывает форму верификации.
     *
     * @param Request $request HTTP запрос
     * @return RedirectResponse|View Перенаправление на dashboard или представление верификации
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
