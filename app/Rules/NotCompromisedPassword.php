<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

/**
 * Правило валидации: пароль не скомпрометирован
 *
 * Проверяет пароль через API Have I Been Pwned (HIBP) для определения,
 * не был ли пароль утечен в известных взломах баз данных.
 * Использует k-Anonymity модель для безопасной проверки без передачи пароля.
 *
 * @see https://haveibeenpwned.com/API/v3#PwnedPasswords
 */
class NotCompromisedPassword implements ValidationRule
{
    /**
     * Минимальное количество упоминаний в утечках для отклонения
     *
     * @var int
     */
    protected int $threshold;

    /**
     * Создает новое правило валидации
     *
     * @param int $threshold Минимальный порог упоминаний (по умолчанию 1)
     */
    public function __construct(int $threshold = 1)
    {
        $this->threshold = $threshold;
    }

    /**
     * Проверяет валидность пароля
     *
     * @param string $attribute Имя атрибута
     * @param mixed $value Значение для проверки
     * @param Closure $fail Callback для регистрации ошибки
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $count = $this->checkPassword($value);

        if ($count >= $this->threshold) {
            $fail("Этот пароль был обнаружен в {$count} утечках данных и не может быть использован. Пожалуйста, выберите другой пароль.");
        }
    }

    /**
     * Проверяет пароль через API Have I Been Pwned
     *
     * Использует k-Anonymity модель: отправляет только первые 5 символов SHA-1 хеша,
     * получает список всех хешей с таким префиксом и проверяет локально.
     *
     * @param string $password Пароль для проверки
     * @return int Количество упоминаний в утечках (0 если не найден или при ошибке API)
     */
    protected function checkPassword(string $password): int
    {
        try {
            // Генерируем SHA-1 хеш пароля
            $hash = strtoupper(sha1($password));
            $prefix = substr($hash, 0, 5);
            $suffix = substr($hash, 5);

            // Запрашиваем API с первыми 5 символами хеша
            $response = Http::timeout(3)
                ->withHeaders([
                    'Add-Padding' => 'true', // Защита от timing attacks
                ])
                ->get("https://api.pwnedpasswords.com/range/{$prefix}");

            if (!$response->successful()) {
                // При ошибке API разрешаем пароль (fail-open)
                return 0;
            }

            // Парсим ответ и ищем наш хеш
            $lines = explode("\r\n", $response->body());

            foreach ($lines as $line) {
                [$hashSuffix, $count] = explode(':', $line);

                if ($hashSuffix === $suffix) {
                    return (int) $count;
                }
            }

            // Пароль не найден в утечках
            return 0;

        } catch (\Exception $e) {
            // При любой ошибке разрешаем пароль (fail-open)
            // Логируем ошибку для мониторинга
            \Log::warning('HIBP API check failed', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
