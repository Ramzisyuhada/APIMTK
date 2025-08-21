<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question; // pakai Model Question
use Illuminate\Support\Facades\DB;  
class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
    {
        $rows = [
            [
                'assessment_id'   => 'A_001',
                'question_number' => '1',
                'question_text'   => 'Berapa Volume kubus jika diketahui ...',
            ],
            [
                'assessment_id'   => 'A_001',
                'question_number' => '2',
                'question_text'   => 'Berapa Luas Alas kubus jika diketahui ...',
            ],
            [
                'assessment_id'   => 'A_001',
                'question_number' => '3',
                'question_text'   => 'Berapa Luas permukaan kubus jika diketahui ...',
            ],
            [
                'assessment_id'   => 'A_001',
                'question_number' => '4',
                'question_text'   => 'Berapa Volume balok jika diketahui ...',
            ],
            [
                'assessment_id'   => 'A_002',
                'question_number' => null,
                'question_text'   => 'file telah di upload',
            ],
        ];

        foreach ($rows as $row) {
            Question::create($row);
        }
    }
}
