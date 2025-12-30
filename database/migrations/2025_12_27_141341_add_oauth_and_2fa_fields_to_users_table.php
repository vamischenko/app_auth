<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('oauth_provider')->nullable()->after('password');
            $table->string('oauth_id')->nullable()->after('oauth_provider');
            $table->text('oauth_token')->nullable()->after('oauth_id');
            $table->text('oauth_refresh_token')->nullable()->after('oauth_token');
            $table->string('avatar')->nullable()->after('oauth_refresh_token');

            $table->boolean('google2fa_enabled')->default(false)->after('avatar');
            $table->string('google2fa_secret')->nullable()->after('google2fa_enabled');
            $table->text('google2fa_recovery_codes')->nullable()->after('google2fa_secret');

            $table->index(['oauth_provider', 'oauth_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['oauth_provider', 'oauth_id']);
            $table->dropColumn([
                'oauth_provider',
                'oauth_id',
                'oauth_token',
                'oauth_refresh_token',
                'avatar',
                'google2fa_enabled',
                'google2fa_secret',
                'google2fa_recovery_codes',
            ]);
        });
    }
};
