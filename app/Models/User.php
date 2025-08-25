<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Primary key kita string: user_identifier
    protected $primaryKey = 'user_identifier';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_identifier',
        'name',
        'phone',
        'gender',   // tambah gender sesuai sheet
        'role',
        'gayabelajar',

        'password',
    ];

    protected $hidden = [
        'password',
        // 'remember_token', // kolom ini nggak ada di skema kamu; aman jika dihapus
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed', // auto-hash saat set password
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Kalau user_identifier sudah diset (mis. dari seeder), JANGAN timpa
            if (!empty($model->user_identifier)) {
                return;
            }

            // Generate otomatis: A_001, A_002, ...
            $last = static::where('user_identifier', 'like', 'A_%')
                ->orderBy('user_identifier', 'desc')
                ->first();

            $newNumber = 1;
            if ($last) {
                $lastNumber = (int) str_replace('A_', '', $last->user_identifier);
                $newNumber  = $lastNumber + 1;
            }

            $model->user_identifier = 'A_' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        });
    }
}
