<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Очищаем дублирующиеся записи перед добавлением уникального индекса
        $this->cleanupDuplicateSleepRecords();
    }

    public function down()
    {
        // При откате миграции ничего не делаем, так как индекс будет удален в следующей миграции
    }

    private function cleanupDuplicateSleepRecords()
    {
        // Для SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Находим дубликаты и оставляем только самую новую запись для каждой даты
            $duplicates = DB::table('sleep_records')
                ->select('user_id', 'date', DB::raw('COUNT(*) as count'))
                ->groupBy('user_id', 'date')
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                // Находим ID самой новой записи (с наибольшим ID или created_at)
                $latestRecord = DB::table('sleep_records')
                    ->where('user_id', $duplicate->user_id)
                    ->where('date', $duplicate->date)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestRecord) {
                    // Удаляем все остальные записи для этой даты, кроме самой новой
                    DB::table('sleep_records')
                        ->where('user_id', $duplicate->user_id)
                        ->where('date', $duplicate->date)
                        ->where('id', '!=', $latestRecord->id)
                        ->delete();
                }
            }
        }
        // Для MySQL
        else {
            DB::statement('
                DELETE sr1 FROM sleep_records sr1
                INNER JOIN sleep_records sr2
                WHERE
                    sr1.id < sr2.id AND
                    sr1.user_id = sr2.user_id AND
                    sr1.date = sr2.date
            ');
        }
    }
};
