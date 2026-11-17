<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_health_data', function (Blueprint $table) {
            // Проверяем и добавляем только отсутствующие столбцы

            if (!Schema::hasColumn('user_health_data', 'blood_type')) {
                $table->string('blood_type', 3)->nullable()->after('gender');
            }

            if (!Schema::hasColumn('user_health_data', 'resting_heart_rate')) {
                $table->integer('resting_heart_rate')->nullable()->after('blood_type');
            }

            if (!Schema::hasColumn('user_health_data', 'blood_pressure_systolic')) {
                $table->integer('blood_pressure_systolic')->nullable()->after('resting_heart_rate');
            }

            if (!Schema::hasColumn('user_health_data', 'blood_pressure_diastolic')) {
                $table->integer('blood_pressure_diastolic')->nullable()->after('blood_pressure_systolic');
            }

            if (!Schema::hasColumn('user_health_data', 'cholesterol_level')) {
                $table->decimal('cholesterol_level', 5, 2)->nullable()->after('blood_pressure_diastolic');
            }

            if (!Schema::hasColumn('user_health_data', 'glucose_level')) {
                $table->decimal('glucose_level', 5, 2)->nullable()->after('cholesterol_level');
            }

            if (!Schema::hasColumn('user_health_data', 'health_conditions')) {
                $table->json('health_conditions')->nullable()->after('glucose_level');
            }

            if (!Schema::hasColumn('user_health_data', 'medications')) {
                $table->json('medications')->nullable()->after('health_conditions');
            }

            if (!Schema::hasColumn('user_health_data', 'allergies')) {
                $table->json('allergies')->nullable()->after('medications');
            }

            if (!Schema::hasColumn('user_health_data', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('allergies');
            }
        });
    }

    public function down()
    {
        // В down методе можно оставить пустым или удалить столбцы при откате
        Schema::table('user_health_data', function (Blueprint $table) {
            // Опционально: удалить добавленные столбцы при откате миграции
        });
    }
};
