<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;   // <= penting!

class AssessmentSeeder extends Seeder
{
       public function run(): void
    {
        $rows = [
            [
                'assessment_id'    => 'A_001',
                'assessment_type'  => 'Exercise',
                'title' => 'Exercise_1',
                'file_url_soal'    => null, // kosong di baris pertama
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'assessment_id'    => 'A_002',
                'assessment_type'  => 'Post_Test',
                'title' => 'Post_Test_1',
                'file_url_soal'    => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        DB::table('assessments')->upsert($rows, ['assessment_id']);
    }
}
