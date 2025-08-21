<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
 use HasFactory;

    protected $primaryKey = 'assessment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'assessment_id',    // A_001, dst
        'assessment_type',  // Exercise, Post_Test, ...
        'title',
        'file_url_soal',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!empty($model->assessment_id)) return;
            $last = static::where('assessment_id','like','A_%')->orderBy('assessment_id','desc')->first();
            $num = $last ? (int) str_replace('A_', '', $last->assessment_id) + 1 : 1;
            $model->assessment_id = 'A_' . str_pad($num, 3, '0', STR_PAD_LEFT);
        });
    }

    // RELATIONS
    public function questions() { return $this->hasMany(Question::class, 'assessment_id', 'assessment_id'); }
    public function submissions() { return $this->hasMany(Submission::class, 'assessment_id', 'assessment_id'); }
    public function grades() { return $this->hasMany(Grade::class, 'assessment_id', 'assessment_id'); } // jika kolom ini ada di grades
}
