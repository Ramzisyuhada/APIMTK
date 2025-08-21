<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // user_identifier, password, nama, gender, role
            ['1829310', '516',  'adam', 'laki-laki', 'guru'],
            ['1729910', '316',  'dani', 'laki-laki', 'guru'],
            ['9929910', '1216', 'seno', 'laki-laki', 'siswa'],
            ['9939310', '7716', 'lala', 'perempuan', 'siswa'],
        ];

        foreach ($rows as [$nomor, $pass, $nama, $gender, $role]) {
            User::updateOrCreate(
                ['user_identifier' => $nomor],
                [
                    'name'     => $nama,
                    'gender'   => $gender,
                    'role'     => $role,
                    'password' => Hash::make($pass), // selalu di-hash
                ]
            );
        }
    }
}
