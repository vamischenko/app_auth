<?php

namespace App\Providers;

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
     * Регистрирует обработчик события SocialiteWasCalled для расширения
     * Laravel Socialite дополнительными OAuth провайдерами:
     * - vkontakte - ВКонтакте
     * - yandex - Яндекс
     * - mailru - Mail.ru
     *
     * @return void
     */
    public function boot(): void
    {
        \Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('vkontakte', \SocialiteProviders\VKontakte\Provider::class);
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
            $event->extendSocialite('mailru', \SocialiteProviders\MailRu\Provider::class);
        });
    }
}
