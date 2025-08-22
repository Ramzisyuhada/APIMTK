<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;

    protected $primaryKey = 'answer_id';
    public $incrementing = false; // ID tidak auto increment integer
    protected $keyType = 'string';

    protected $fillable = [
        'answer_id',
        'submission_id',
        'question_id',
        'answer_text',
    ];

    // Relasi ke Submission
    public function submission()
    {
        return $this->belongsTo(Submissions::class, 'submission_id', 'submission_id');
    }

    // Relasi ke Question
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    // AUTO GENERATE ID: AN0001, AN0002, dst
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->answer_id) {
                $last = self::orderBy('answer_id', 'desc')->first();
                if ($last) {
                    $lastNumber = (int)substr($last->answer_id, 2); // Ambil angka setelah 'AN'
                    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '0001';
                }
                $model->answer_id = 'AN' . $newNumber;
            }
        });
    }
}
