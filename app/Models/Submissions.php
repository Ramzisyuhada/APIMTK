<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submissions extends Model
{
    use HasFactory;

    // Pastikan nama tabel sesuai
    protected $table = 'submissions';

    // PK yang benar untuk submissions
    protected $primaryKey = 'submission_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang boleh diisi
    protected $fillable = [
        'submission_id',
        'nomor_identitas',
        'assessment_id',
        'file_url_jawaban',
        'user_identifier'

    ];
  public function user()
    {
        return $this->belongsTo(User::class, 'user_identifier', 'user_identifier');
    }
    // Agar route model binding pakai submission_id
    public function getRouteKeyName()
    {
        return 'submission_id';
    }
 public function answers()
    {
        return $this->hasMany(Answer::class, 'submission_id', 'submission_id');
    }
    // Auto-generate ID: S0001, S0002, ...
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->submission_id) {
                $last = self::orderBy('submission_id', 'desc')->first();
                $next = $last ? ((int) substr($last->submission_id, 1)) + 1 : 1;
                $model->submission_id = 'S' . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
