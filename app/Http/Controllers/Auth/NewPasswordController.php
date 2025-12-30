<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Контроллер установки нового пароля
 *
 * Обрабатывает процесс сброса пароля по ссылке из email.
 * Проверяет токен сброса, валидирует новый пароль и обновляет его в базе данных.
 */
class NewPasswordController extends Controller
{
    /**
     * Отображает форму установки нового пароля
     *
     * @param Request $request HTTP запрос с токеном и email из ссылки
     * @return View Представление формы сброса пароля
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Обрабатывает запрос на установку нового пароля
     *
     * Проверяет валидность токена сброса, обновляет пароль пользователя,
     * генерирует новый remember_token и отправляет событие PasswordReset.
     *
     * @param Request $request HTTP запрос с токеном, email и новым паролем
     * @return RedirectResponse Перенаправление на страницу входа при успехе или назад с ошибками
     * @throws \Illuminate\Validation\ValidationException Если валидация не прошла
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
