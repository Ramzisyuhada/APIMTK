<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
 use HasFactory;

    protected $primaryKey = 'grade_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'grade_id',        // G_001, dst
        'submission_id',
        'user_identifier',
        'score',
        // 'assessment_id', // aktifkan kalau kolom ini ada di tabel grades
        'grade_at',        // aktifkan kalau kolom ini ada; kalau tidak, hapus
    ];

    protected $casts = [
        'grade_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!empty($model->grade_id)) return;
            $last = static::orderBy('grade_id', 'desc')->first();
            $num = $last ? (int) str_replace('G_', '', $last->grade_id) + 1 : 1;
            $model->grade_id = 'G_' . str_pad($num, 3, '0', STR_PAD_LEFT);
        });
    }

    // RELATIONS
    public function submission() { return $this->belongsTo(Submission::class, 'submission_id', 'submission_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_identifier', 'user_identifier'); }
    // public function assessment() { return $this->belongsTo(Assessment::class, 'assessment_id', 'assessment_id'); } // kalau kolomnya ada
}
