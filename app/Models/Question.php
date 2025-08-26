<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
     use HasFactory;
    protected $table = 'questions'; // pastikan nama tabel (opsional kalau konvensi)

    // Primary key bukan auto increment
    protected $primaryKey = 'question_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'question_id',
        'assessment_id',
        'question_number',
        'question_text',
    ];
public function getRouteKeyName()
{
    return 'question_id';
}

    // Auto-generate question_id: Q001, Q002, dst
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
