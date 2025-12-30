<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Компонент макета для неавторизованных пользователей
 *
 * Представляет макет для гостевых страниц, таких как вход, регистрация,
 * восстановление пароля и другие публичные страницы приложения.
 */
class GuestLayout extends Component
{
    /**
     * Возвращает представление компонента
     *
     * @return View Представление гостевого макета
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
