<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие изменения пароля
 *
 * Генерируется когда пользователь изменяет свой пароль.
 * Используется для логирования и отправки уведомлений о критическом
 * изменении в настройках безопасности аккаунта.
 */
class PasswordChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, который изменил пароль
     *
     * @var User
     */
    public User $user;

    /**
     * IP-адрес, с которого был изменен пароль
     *
     * @var string|null
     */
    public ?string $ipAddress;

    /**
     * User agent браузера
     *
     * @var string|null
     */
    public ?string $userAgent;

    /**
     * Создает новое событие
     *
     * @param User $user Пользователь
     * @param string|null $ipAddress IP-адрес
     * @param string|null $userAgent User agent
     */
    public function __construct(User $user, ?string $ipAddress = null, ?string $userAgent = null)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}
