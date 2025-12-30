<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие подозрительного входа в систему
 *
 * Генерируется при обнаружении подозрительной активности:
 * - Вход с нового устройства
 * - Вход с нового IP-адреса
 * - Вход из другой страны/региона
 * - Множественные неудачные попытки входа
 *
 * Используется для отправки предупреждающих уведомлений пользователю.
 */
class SuspiciousLogin
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, чей аккаунт подвергся подозрительной попытке входа
     *
     * @var User
     */
    public User $user;

    /**
     * IP-адрес, с которого была попытка входа
     *
     * @var string
     */
    public string $ipAddress;

    /**
     * User agent браузера
     *
     * @var string|null
     */
    public ?string $userAgent;

    /**
     * Причина пометки входа как подозрительного
     *
     * @var string
     */
    public string $reason;

    /**
     * Создает новое событие
     *
     * @param User $user Пользователь
     * @param string $ipAddress IP-адрес
     * @param string $reason Причина подозрения
     * @param string|null $userAgent User agent
     */
    public function __construct(User $user, string $ipAddress, string $reason, ?string $userAgent = null)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->reason = $reason;
        $this->userAgent = $userAgent;
    }
}
