<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие включения двухфакторной аутентификации
 *
 * Генерируется когда пользователь успешно настраивает и активирует 2FA.
 * Используется для логирования, отправки уведомлений и других действий
 * при включении дополнительного уровня безопасности.
 */
class TwoFactorEnabled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, который включил 2FA
     *
     * @var User
     */
    public User $user;

    /**
     * IP-адрес, с которого была включена 2FA
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
