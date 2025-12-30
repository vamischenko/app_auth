<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Контроллер подтверждения пароля
 *
 * Обрабатывает подтверждение пароля пользователя для доступа к защищенным
 * операциям (например, изменение настроек безопасности, удаление аккаунта).
 * Подтверждение действительно в течение определенного времени.
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Отображает форму подтверждения пароля
     *
     * @return View Представление формы подтверждения пароля
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Проверяет и подтверждает пароль пользователя
     *
     * Проверяет соответствие введенного пароля текущему паролю пользователя.
     * При успешной проверке сохраняет время подтверждения в сессии.
     *
     * @param Request $request HTTP запрос с паролем
     * @return RedirectResponse Перенаправление на предполагаемую страницу или dashboard
     * @throws ValidationException Если пароль неверен
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
