<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submissions extends Model
{
     protected $primaryKey = 'question_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'submission_id',
        'nomor_identitas',
        'assessment_id',
        'file_url_jawaban',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = self::orderBy('question_id', 'desc')->first();

            if ($last) {
                $lastNumber = (int) str_replace('Q', '', $last->question_id);
                $newNumber  = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $model->question_id = 'Q' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        });
    }
}
