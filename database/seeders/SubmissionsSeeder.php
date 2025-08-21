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
                'submission_id'   => 'S001',
                'user_identifier' => '1829310', // harus ada di users
                'assessment_id'   => 'A_001',   // harus ada di assessments
                'submitted_at'    => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'submission_id'   => 'S002',
                'user_identifier' => '1829310',
                'assessment_id'   => 'A_002',
                'submitted_at'    => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        DB::table('submissions')->upsert($rows, ['submission_id']);
    }
}
