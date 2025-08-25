<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
       // $now = now();
        // $rows = [
        //     ['answer_id'=>'AN001','submission_id'=>'S001','question_id'=>'Q001','answer_text'=>'1288','created_at'=>$now,'updated_at'=>$now],
        //     ['answer_id'=>'AN002','submission_id'=>'S001','question_id'=>'Q002','answer_text'=>'192', 'created_at'=>$now,'updated_at'=>$now],
        //     ['answer_id'=>'AN003','submission_id'=>'S001','question_id'=>'Q003','answer_text'=>'40',  'created_at'=>$now,'updated_at'=>$now],
        //     ['answer_id'=>'AN004','submission_id'=>'S001','question_id'=>'Q004','answer_text'=>'2631','created_at'=>$now,'updated_at'=>$now],
        //      ['answer_id'=>'AN005','submission_id'=>'S003','question_id'=>'Q001','answer_text'=>'1288','created_at'=>$now,'updated_at'=>$now],
        //     ['answer_id'=>'AN006','submission_id'=>'S003','question_id'=>'Q002','answer_text'=>'192', 'created_at'=>$now,'updated_at'=>$now],
        //     ['answer_id'=>'AN007','submission_id'=>'S003','question_id'=>'Q003','answer_text'=>'40',  'created_at'=>$now,'updated_at'=>$now],
        //     ['answer_id'=>'AN008','submission_id'=>'S003','question_id'=>'Q004','answer_text'=>'2631','created_at'=>$now,'updated_at'=>$now],
        // ];
        // DB::table('answers')->upsert($rows, ['answer_id']);
    }
}
