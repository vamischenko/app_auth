<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Запрос на обновление профиля пользователя
 *
 * Валидирует данные при обновлении профиля пользователя,
 * включая имя и email адрес. Проверяет уникальность email
 * с игнорированием текущего пользователя.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Возвращает правила валидации для запроса
     *
     * Правила валидации:
     * - name: обязательное поле, строка, максимум 255 символов
     * - email: обязательное поле, строка в нижнем регистре, валидный email,
     *   максимум 255 символов, уникальный (за исключением текущего пользователя)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
