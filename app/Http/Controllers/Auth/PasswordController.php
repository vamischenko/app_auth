<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Контроллер управления паролем пользователя
 *
 * Обрабатывает обновление пароля аутентифицированного пользователя
 * с проверкой текущего пароля для безопасности.
 */
class PasswordController extends Controller
{
    /**
     * Обновляет пароль пользователя
     *
     * Требует подтверждения текущего пароля и валидации нового пароля
     * согласно правилам безопасности (подтверждение нового пароля обязательно).
     *
     * @param Request $request HTTP запрос с текущим и новым паролем
     * @return RedirectResponse Перенаправление назад с сообщением об успехе
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
