<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;   // <= penting!

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            ['grade_id' => 'G_001', 'submission_id' => 'S001', 'user_identifier' => '1829310', 'score' => 90, 'created_at' => $now, 'updated_at' => $now],
            ['grade_id' => 'G_002', 'submission_id' => 'S002', 'user_identifier' => '1829310', 'score' => 90, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('grades')->upsert($rows, ['grade_id']);
    }
}
