<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления индексов в таблицы базы данных
 *
 * Добавляет индексы для оптимизации часто используемых запросов:
 * - В таблице users: составной индекс для OAuth аутентификации
 * - В таблице magic_links: индексы для поиска активных ссылок и проверки срока действия
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     *
     * Добавляет индексы для:
     * - Быстрого поиска пользователей по OAuth провайдеру и ID
     * - Быстрого поиска неиспользованных магических ссылок по email
     * - Быстрой очистки истекших магических ссылок
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Индекс для поиска пользователя по OAuth провайдеру и ID
            $table->index(['oauth_provider', 'oauth_id'], 'users_oauth_provider_id_index');
        });

        Schema::table('magic_links', function (Blueprint $table) {
            // Индекс для поиска активных (неиспользованных) ссылок по email
            $table->index(['email', 'used_at'], 'magic_links_email_used_at_index');

            // Индекс для быстрой проверки и удаления истекших ссылок
            $table->index('expires_at', 'magic_links_expires_at_index');
        });
    }

    /**
     * Откатить миграцию
     *
     * Удаляет все добавленные индексы
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_oauth_provider_id_index');
        });

        Schema::table('magic_links', function (Blueprint $table) {
            $table->dropIndex('magic_links_email_used_at_index');
            $table->dropIndex('magic_links_expires_at_index');
        });
    }
};
