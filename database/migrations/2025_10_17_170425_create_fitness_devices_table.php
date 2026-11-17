<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fitness_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Основная информация
            $table->string('device_type'); // google_fit, apple_health, etc.
            $table->string('device_name');
            $table->string('api_provider');

            // Данные аутентификации
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Статус
            $table->boolean('is_connected')->default(false);
            $table->timestamp('last_sync_at')->nullable();

            // Настройки
            $table->json('sync_settings')->nullable();
            $table->json('raw_data')->nullable();

            $table->timestamps();

            // Индексы
            $table->index(['user_id', 'api_provider']);
            $table->index('is_connected');
            $table->index('last_sync_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fitness_devices');
    }
};
