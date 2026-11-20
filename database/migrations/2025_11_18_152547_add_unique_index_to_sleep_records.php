<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Временно убираем возможные дубликаты
        $this->cleanupDuplicates();

        // Добавляем уникальный индекс
        Schema::table('sleep_records', function (Blueprint $table) {
            $table->unique(['user_id', 'date']);
        });
    }

    public function down()
    {
        Schema::table('sleep_records', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'date']);
        });
    }

    private function cleanupDuplicates()
    {
        // Для SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            $users = DB::table('users')->get();

            foreach ($users as $user) {
                $duplicateDates = DB::table('sleep_records')
                    ->where('user_id', $user->id)
                    ->select('date', DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->having('count', '>', 1)
                    ->pluck('date');

                foreach ($duplicateDates as $date) {
                    // Оставляем запись с наибольшим ID
                    $latestId = DB::table('sleep_records')
                        ->where('user_id', $user->id)
                        ->where('date', $date)
                        ->max('id');

                    // Удаляем все кроме самой новой
                    DB::table('sleep_records')
                        ->where('user_id', $user->id)
                        ->where('date', $date)
                        ->where('id', '!=', $latestId)
                        ->delete();
                }
            }
        }
    }
};
