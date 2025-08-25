<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
    {
        $now = now();

        $rows = [
            [
                'submission_id'   => 'S003',
                'user_identifier' => '9929910',
                'assessment_id'   => 'A_001',
                'submitted_at'    => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]
        ];

        DB::table('submissions')->upsert($rows, ['submission_id']);
    }
}
