<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Компонент макета для аутентифицированных пользователей
 *
 * Представляет основной макет приложения для авторизованных пользователей.
 * Используется для отображения страниц, доступных только после входа в систему.
 */
class AppLayout extends Component
{
    /**
     * Возвращает представление компонента
     *
     * @return View Представление основного макета приложения
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
