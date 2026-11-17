<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('health_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->comment('sleep, activity, nutrition, mental_health, general');
            $table->string('title');
            $table->text('description');
            $table->text('tips')->nullable()->comment('Tips separated by | character');
            $table->string('icon', 100)->nullable()->comment('FontAwesome icon class');
            $table->integer('priority')->default(0)->comment('Priority from 1-10');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index('category');
            $table->index('priority');
            $table->index('is_active');
            $table->index(['category', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('health_recommendations');
    }
};
