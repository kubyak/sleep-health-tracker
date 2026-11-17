<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sleep_records', function (Blueprint $table) {
            // Добавляем составные индексы для часто используемых запросов
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'sleep_quality']);
        });

        Schema::table('activity_records', function (Blueprint $table) {
            // Добавляем составные индексы для часто используемых запросов
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'steps']);
            $table->index(['user_id', 'calories_burned']);
        });
    }

    public function down()
    {
        Schema::table('sleep_records', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['user_id', 'sleep_quality']);
        });

        Schema::table('activity_records', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['user_id', 'steps']);
            $table->dropIndex(['user_id', 'calories_burned']);
        });
    }
};
