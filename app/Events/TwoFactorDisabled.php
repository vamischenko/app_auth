<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие отключения двухфакторной аутентификации
 *
 * Генерируется когда пользователь отключает 2FA.
 * Используется для логирования критического изменения в настройках безопасности
 * и отправки предупреждающих уведомлений.
 */
class TwoFactorDisabled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, который отключил 2FA
     *
     * @var User
     */
    public User $user;

    /**
     * IP-адрес, с которого была отключена 2FA
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
