<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('steps')->default(0);
            $table->integer('calories_burned')->default(0);
            $table->integer('active_minutes')->default(0)->comment('Active minutes per day');
            $table->decimal('distance', 8, 2)->default(0)->comment('Distance in kilometers');
            $table->integer('heart_rate')->nullable()->comment('Average heart rate');
            $table->string('workout_type', 50)->nullable()->comment('Type of workout: walking, running, cycling, etc.');
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index(['user_id', 'date']);
            $table->index('date');
            $table->index('steps');
            $table->index('workout_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_records');
    }
};
