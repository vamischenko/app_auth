<?php

namespace App\Providers;

use App\Events\PasswordChanged;
use App\Events\SuspiciousLogin;
use App\Events\TwoFactorDisabled;
use App\Events\TwoFactorEnabled;
use App\Listeners\LogSecurityEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

/**
 * Сервис-провайдер приложения
 *
 * Регистрирует сервисы приложения и настраивает дополнительные провайдеры OAuth.
 * Расширяет Laravel Socialite поддержкой российских социальных сетей:
 * ВКонтакте, Яндекс и Mail.ru.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы приложения
     *
     * Метод вызывается перед загрузкой приложения для регистрации
     * сервисов в контейнере зависимостей.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Выполняет начальную загрузку сервисов приложения
     *
     * Регистрирует обработчики событий:
     * - Расширяет Laravel Socialite дополнительными OAuth провайдерами
     *   (ВКонтакте, Яндекс, Mail.ru)
     * - Регистрирует слушатели событий безопасности для логирования
     *   критических действий (2FA, изменение пароля, подозрительные входы)
     *
     * @return void
     */
    public function boot(): void
    {
        // Регистрация дополнительных OAuth провайдеров
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('vkontakte', \SocialiteProviders\VKontakte\Provider::class);
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
            $event->extendSocialite('mailru', \SocialiteProviders\MailRu\Provider::class);
        });

        // Регистрация слушателей событий безопасности
        Event::listen(TwoFactorEnabled::class, [LogSecurityEvent::class, 'handleTwoFactorEnabled']);
        Event::listen(TwoFactorDisabled::class, [LogSecurityEvent::class, 'handleTwoFactorDisabled']);
        Event::listen(PasswordChanged::class, [LogSecurityEvent::class, 'handlePasswordChanged']);
        Event::listen(SuspiciousLogin::class, [LogSecurityEvent::class, 'handleSuspiciousLogin']);
    }
}
