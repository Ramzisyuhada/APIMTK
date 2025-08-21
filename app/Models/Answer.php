<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    // Primary Key
    protected $primaryKey = 'answer_id';
    public $incrementing = false; // karena answer_id = string (AN001, dst)
    protected $keyType = 'string';

    // Kolom yang bisa diisi mass-assignment
    protected $fillable = [
        'answer_id',
        'submission_id',
        'question_id',
        'answer_text',
    ];

    // Relasi ke Submission
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'submission_id');
    }

    // Relasi ke Question
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}
