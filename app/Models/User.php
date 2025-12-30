<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Модель пользователя
 *
 * Представляет пользователя системы с поддержкой множественных методов аутентификации:
 * - Стандартная аутентификация (email/пароль)
 * - OAuth аутентификация через социальные сети
 * - Двухфакторная аутентификация (2FA) через Google Authenticator
 * - Верификация email адреса
 *
 * @property int $id
 * @property string $name Имя пользователя
 * @property string $email Email адрес
 * @property string $password Хешированный пароль
 * @property string|null $oauth_provider Провайдер OAuth (google, github, facebook, vkontakte, yandex, mailru)
 * @property string|null $oauth_id ID пользователя у OAuth провайдера
 * @property string|null $oauth_token Токен доступа OAuth
 * @property string|null $oauth_refresh_token Токен обновления OAuth
 * @property string|null $avatar URL аватара пользователя
 * @property bool $google2fa_enabled Включена ли двухфакторная аутентификация
 * @property string|null $google2fa_secret Секретный ключ для 2FA
 * @property array|null $google2fa_recovery_codes Коды восстановления для 2FA
 * @property \Illuminate\Support\Carbon|null $email_verified_at Дата верификации email
 * @property string|null $remember_token Токен для функции "Запомнить меня"
 * @property \Illuminate\Support\Carbon|null $created_at Дата создания
 * @property \Illuminate\Support\Carbon|null $updated_at Дата последнего обновления
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Атрибуты, которые можно массово назначать
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'oauth_provider',
        'oauth_id',
        'oauth_token',
        'oauth_refresh_token',
        'avatar',
        'google2fa_enabled',
        'google2fa_secret',
        'google2fa_recovery_codes',
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
        'google2fa_recovery_codes',
        'oauth_token',
        'oauth_refresh_token',
    ];

    /**
     * Возвращает атрибуты, которые должны быть приведены к определенным типам
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google2fa_enabled' => 'boolean',
            'google2fa_recovery_codes' => 'array',
        ];
    }

    /**
     * Проверяет, включена ли и настроена ли двухфакторная аутентификация
     *
     * @return bool True, если 2FA включена и секретный ключ установлен
     */
    public function hasEnabledTwoFactorAuth(): bool
    {
        return $this->google2fa_enabled && !empty($this->google2fa_secret);
    }
}
