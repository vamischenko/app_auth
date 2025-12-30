<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

/**
 * Сервис для управления двухфакторной аутентификацией (2FA)
 *
 * Инкапсулирует всю бизнес-логику работы с 2FA:
 * - Генерация секретных ключей и QR-кодов
 * - Верификация кодов из Google Authenticator
 * - Управление кодами восстановления
 * - Включение и отключение 2FA для пользователей
 */
class TwoFactorAuthService
{
    /**
     * Экземпляр Google2FA для генерации и верификации кодов
     *
     * @var Google2FA
     */
    protected Google2FA $google2fa;

    /**
     * Инициализирует сервис и создает экземпляр Google2FA
     */
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Генерирует новый секретный ключ для 2FA
     *
     * Создает случайный Base32-encoded секретный ключ, который будет
     * использоваться для генерации TOTP кодов.
     *
     * @return string Секретный ключ в формате Base32
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Генерирует URL для QR-кода настройки 2FA
     *
     * Создает URL в формате otpauth://, который можно преобразовать
     * в QR-код для сканирования в Google Authenticator.
     *
     * @param string $appName Название приложения (отображается в приложении)
     * @param string $email Email пользователя (идентификатор аккаунта)
     * @param string $secret Секретный ключ
     * @return string URL для генерации QR-кода
     */
    public function getQRCodeUrl(string $appName, string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl($appName, $email, $secret);
    }

    /**
     * Проверяет валидность 6-значного кода из Google Authenticator
     *
     * Сравнивает введенный код с кодом, сгенерированным на основе
     * секретного ключа и текущего времени. Учитывает временной дрейф.
     *
     * @param string $secret Секретный ключ пользователя
     * @param string $code 6-значный код из приложения
     * @return bool True, если код валиден
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Генерирует коды восстановления для 2FA
     *
     * Создает 8 случайных 8-символьных шестнадцатеричных кодов в верхнем регистре.
     * Эти коды используются для восстановления доступа при потере устройства с 2FA.
     *
     * @return array<int, string> Массив кодов восстановления
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    /**
     * Проверяет код восстановления и удаляет его при успешной проверке
     *
     * Ищет код в массиве кодов восстановления пользователя.
     * При нахождении удаляет использованный код из базы данных,
     * чтобы предотвратить повторное использование.
     *
     * @param User $user Пользователь
     * @param string $code Код восстановления для проверки
     * @return bool True, если код валиден и не был использован ранее
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = $user->google2fa_recovery_codes;

        if (!$recoveryCodes || !is_array($recoveryCodes)) {
            return false;
        }

        $key = array_search(strtoupper($code), $recoveryCodes);

        if ($key !== false) {
            // Удаляем использованный код
            unset($recoveryCodes[$key]);
            $user->update(['google2fa_recovery_codes' => array_values($recoveryCodes)]);
            return true;
        }

        return false;
    }

    /**
     * Включает двухфакторную аутентификацию для пользователя
     *
     * Сохраняет секретный ключ, коды восстановления и активирует 2FA.
     *
     * @param User $user Пользователь
     * @param string $secret Секретный ключ 2FA
     * @param array<int, string> $recoveryCodes Коды восстановления
     * @return bool True при успешном обновлении
     */
    public function enableTwoFactor(User $user, string $secret, array $recoveryCodes): bool
    {
        return $user->update([
            'google2fa_enabled' => true,
            'google2fa_secret' => $secret,
            'google2fa_recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Отключает двухфакторную аутентификацию для пользователя
     *
     * Удаляет секретный ключ, коды восстановления и деактивирует 2FA.
     *
     * @param User $user Пользователь
     * @return bool True при успешном обновлении
     */
    public function disableTwoFactor(User $user): bool
    {
        return $user->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
            'google2fa_recovery_codes' => null,
        ]);
    }

    /**
     * Проверяет, включена ли двухфакторная аутентификация у пользователя
     *
     * @param User $user Пользователь
     * @return bool True, если 2FA включена
     */
    public function isTwoFactorEnabled(User $user): bool
    {
        return (bool) $user->google2fa_enabled;
    }

    /**
     * Проверяет наличие секретного ключа у пользователя
     *
     * @param User $user Пользователь
     * @return bool True, если секретный ключ установлен
     */
    public function hasSecret(User $user): bool
    {
        return !empty($user->google2fa_secret);
    }

    /**
     * Получает секретный ключ пользователя
     *
     * @param User $user Пользователь
     * @return string|null Секретный ключ или null
     */
    public function getSecret(User $user): ?string
    {
        return $user->google2fa_secret;
    }

    /**
     * Получает количество оставшихся кодов восстановления
     *
     * @param User $user Пользователь
     * @return int Количество доступных кодов восстановления
     */
    public function getRemainingRecoveryCodesCount(User $user): int
    {
        $codes = $user->google2fa_recovery_codes;

        if (!$codes || !is_array($codes)) {
            return 0;
        }

        return count($codes);
    }
}
