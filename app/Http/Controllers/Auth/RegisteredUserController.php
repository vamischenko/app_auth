<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NotCompromisedPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Контроллер регистрации пользователей
 *
 * Обрабатывает процесс регистрации новых пользователей в системе,
 * включая валидацию данных, создание учетной записи и автоматический вход.
 */
class RegisteredUserController extends Controller
{
    /**
     * Отображает страницу регистрации
     *
     * @return View Представление формы регистрации
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Обрабатывает запрос на регистрацию нового пользователя
     *
     * Валидирует входные данные, создает нового пользователя с хешированным паролем,
     * генерирует событие Registered для отправки email верификации и выполняет
     * автоматический вход в систему.
     *
     * @param Request $request HTTP запрос с данными регистрации
     * @return RedirectResponse Перенаправление на dashboard
     * @throws \Illuminate\Validation\ValidationException Если валидация не прошла
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults(), new NotCompromisedPassword()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
