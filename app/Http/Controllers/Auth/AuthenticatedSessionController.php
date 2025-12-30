<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Контроллер управления аутентифицированными сеансами
 *
 * Обрабатывает вход пользователей в систему, включая проверку двухфакторной
 * аутентификации, и выход из системы с полной очисткой сессии.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Отображает страницу входа в систему
     *
     * @return View Представление формы входа
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Обрабатывает запрос на аутентификацию пользователя
     *
     * Выполняет аутентификацию пользователя. Если у пользователя включена
     * двухфакторная аутентификация, выполняет выход и перенаправляет на страницу
     * ввода 2FA кода. В противном случае регенерирует сессию и перенаправляет
     * на предполагаемую страницу или на dashboard.
     *
     * @param LoginRequest $request Валидированный запрос на вход
     * @return RedirectResponse Перенаправление на страницу 2FA или dashboard
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuth()) {
            Auth::logout();

            $request->session()->put('2fa_required', true);
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));

            return redirect()->route('2fa.challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Завершает аутентифицированный сеанс пользователя
     *
     * Выполняет выход из системы, аннулирует текущую сессию и регенерирует
     * CSRF токен для предотвращения повторного использования сессии.
     *
     * @param Request $request HTTP запрос
     * @return RedirectResponse Перенаправление на главную страницу
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
