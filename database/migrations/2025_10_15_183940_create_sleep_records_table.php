<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sleep_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('bedtime');
            $table->time('wakeup_time');
            $table->integer('sleep_duration')->comment('Duration in minutes');
            $table->integer('deep_sleep')->nullable()->comment('Deep sleep duration in minutes');
            $table->integer('light_sleep')->nullable()->comment('Light sleep duration in minutes');
            $table->integer('rem_sleep')->nullable()->comment('REM sleep duration in minutes');
            $table->integer('awake_time')->nullable()->comment('Awake time during night in minutes');
            $table->integer('sleep_quality')->default(0)->comment('Sleep quality percentage 0-100');
            $table->text('notes')->nullable()->comment('User notes about sleep');
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index(['user_id', 'date']);
            $table->index('date');
            $table->index('sleep_quality');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sleep_records');
    }
};
