<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
  public function run(): void
{
    $this->call([
        UserSeeder::class,        // isi users (1829310, dst)
        AssessmentSeeder::class,  // isi A_001, A_002
        QuestionSeeder::class,    // isi Q001–Q005 (FK ke A_001/ A_002)
       //SubmissionsSeeder::class,  // isi S001 (FK ke users & assessments)
       // AnswerSeeder::class,      // BARU isi answers (FK ke submissions & questions)
               // GradeSeeder::class,       // G_001, G_002

    ]);
}
}
