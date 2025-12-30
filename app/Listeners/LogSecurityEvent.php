<?php

namespace App\Listeners;

use App\Events\PasswordChanged;
use App\Events\SuspiciousLogin;
use App\Events\TwoFactorDisabled;
use App\Events\TwoFactorEnabled;
use Illuminate\Support\Facades\Log;

/**
 * Слушатель для логирования событий безопасности
 *
 * Записывает все критические события безопасности в лог-файл для аудита.
 * Сохраняет информацию о пользователе, IP-адресе, user agent и времени события.
 */
class LogSecurityEvent
{
    /**
     * Обрабатывает событие включения 2FA
     *
     * @param TwoFactorEnabled $event Событие
     * @return void
     */
    public function handleTwoFactorEnabled(TwoFactorEnabled $event): void
    {
        Log::channel('security')->info('2FA enabled', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Обрабатывает событие отключения 2FA
     *
     * @param TwoFactorDisabled $event Событие
     * @return void
     */
    public function handleTwoFactorDisabled(TwoFactorDisabled $event): void
    {
        Log::channel('security')->warning('2FA disabled', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Обрабатывает событие изменения пароля
     *
     * @param PasswordChanged $event Событие
     * @return void
     */
    public function handlePasswordChanged(PasswordChanged $event): void
    {
        Log::channel('security')->info('Password changed', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Обрабатывает событие подозрительного входа
     *
     * @param SuspiciousLogin $event Событие
     * @return void
     */
    public function handleSuspiciousLogin(SuspiciousLogin $event): void
    {
        Log::channel('security')->warning('Suspicious login detected', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'reason' => $event->reason,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
