<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Модель магической ссылки для входа без пароля
 *
 * Представляет одноразовую ссылку для аутентификации пользователя по email
 * без необходимости ввода пароля. Ссылка действительна в течение 30 минут
 * и может быть использована только один раз.
 *
 * @property int $id
 * @property string $email Email адрес пользователя
 * @property string $token Уникальный токен ссылки
 * @property \Illuminate\Support\Carbon $expires_at Дата и время истечения срока действия
 * @property \Illuminate\Support\Carbon|null $used_at Дата и время использования ссылки
 * @property \Illuminate\Support\Carbon|null $created_at Дата создания
 * @property \Illuminate\Support\Carbon|null $updated_at Дата последнего обновления
 */
class MagicLink extends Model
{
    /**
     * Атрибуты, которые можно массово назначать
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used_at',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Создает новую магическую ссылку для указанного email
     *
     * Генерирует случайный токен длиной 64 символа и устанавливает
     * срок действия ссылки 30 минут с текущего момента.
     *
     * @param string $email Email адрес пользователя
     * @return self Созданная магическая ссылка
     */
    public static function createForEmail(string $email): self
    {
        return self::create([
            'email' => $email,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    /**
     * Проверяет, действительна ли магическая ссылка
     *
     * Ссылка считается действительной, если срок её действия не истёк
     * и она ещё не была использована.
     *
     * @return bool True, если ссылка действительна
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && is_null($this->used_at);
    }

    /**
     * Помечает магическую ссылку как использованную
     *
     * Устанавливает текущую дату и время в поле used_at,
     * что делает ссылку недействительной для повторного использования.
     *
     * @return void
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
