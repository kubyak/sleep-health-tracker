<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_health_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Основная информация
            $table->integer('age')->nullable();
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->decimal('height', 5, 2)->nullable()->comment('Height in cm');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Медицинские данные
            $table->string('blood_type', 3)->nullable()->comment('A+, A-, B+, B-, AB+, AB-, O+, O-');
            $table->integer('resting_heart_rate')->nullable()->comment('Resting heart rate (bpm)');
            $table->integer('blood_pressure_systolic')->nullable()->comment('Systolic blood pressure');
            $table->integer('blood_pressure_diastolic')->nullable()->comment('Diastolic blood pressure');
            $table->decimal('cholesterol_level', 5, 2)->nullable()->comment('Cholesterol level (mmol/L)');
            $table->decimal('glucose_level', 5, 2)->nullable()->comment('Glucose level (mmol/L)');

            // Цели здоровья
            $table->integer('sleep_goal')->default(480)->comment('Sleep goal in minutes (8 hours)');
            $table->integer('steps_goal')->default(10000)->comment('Daily steps goal');
            $table->integer('calories_goal')->default(500)->comment('Daily calories burn goal');
            $table->integer('active_minutes_goal')->default(30)->comment('Daily active minutes goal');

            // Медицинская история
            $table->json('health_conditions')->nullable()->comment('Array of health conditions');
            $table->json('medications')->nullable()->comment('Array of medications');
            $table->json('allergies')->nullable()->comment('Array of allergies');
            $table->string('emergency_contact')->nullable()->comment('Emergency contact information');

            $table->timestamps();

            // Уникальный индекс для одного профиля на пользователя
            $table->unique('user_id');

            // Индексы для оптимизации
            $table->index('gender');
            $table->index('age');
            $table->index('blood_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_health_data');
    }
};
