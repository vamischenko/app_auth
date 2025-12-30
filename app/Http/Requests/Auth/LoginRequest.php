<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Запрос на вход в систему
 *
 * Обрабатывает валидацию и аутентификацию пользователя при входе.
 * Включает защиту от брутфорса с ограничением количества попыток входа (5 попыток).
 */
class LoginRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения этого запроса
     *
     * @return bool Всегда возвращает true, так как запрос доступен всем
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации для запроса
     *
     * Правила валидации:
     * - email: обязательное поле, строка, валидный email
     * - password: обязательное поле, строка
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Выполняет попытку аутентификации с учетными данными из запроса
     *
     * Проверяет ограничение количества попыток, затем пытается аутентифицировать пользователя.
     * При неудачной попытке увеличивает счетчик попыток входа.
     * При успешной аутентификации очищает счетчик попыток.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException Если аутентификация не удалась
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Проверяет, что запрос на вход не ограничен по количеству попыток
     *
     * Разрешено максимум 5 попыток входа. При превышении лимита
     * генерируется событие Lockout и выбрасывается исключение с указанием
     * времени ожидания до следующей попытки.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException Если превышен лимит попыток
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Возвращает ключ для ограничения частоты запросов
     *
     * Ключ формируется из email адреса (в нижнем регистре и транслитерированного)
     * и IP адреса пользователя для уникальной идентификации попыток входа.
     *
     * @return string Уникальный ключ для throttling
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
