<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Контроллер управления профилем пользователя
 *
 * Обрабатывает операции редактирования, обновления и удаления профиля пользователя.
 */
class ProfileController extends Controller
{
    /**
     * Отображает форму редактирования профиля пользователя
     *
     * @param Request $request HTTP запрос
     * @return View Представление с формой редактирования профиля
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Обновляет информацию профиля пользователя
     *
     * При изменении email адреса сбрасывает статус верификации email.
     *
     * @param ProfileUpdateRequest $request Валидированный запрос с данными профиля
     * @return RedirectResponse Перенаправление на страницу редактирования профиля
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Удаляет учетную запись пользователя
     *
     * Требует подтверждения текущего пароля. После удаления выполняется выход
     * из системы, аннулирование сессии и регенерация CSRF токена.
     *
     * @param Request $request HTTP запрос
     * @return RedirectResponse Перенаправление на главную страницу
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
