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
    ['232410013', '440', 'Ahza Rasyidan Alzam', 'laki-laki', 'siswa'],
    ['232410074', '441', 'Elyasa Kusnaedi Hasan', 'laki-laki', 'siswa'],
    ['232410079', '442', 'Fahira Ghaisani Hilmi', 'perempuan', 'siswa'],
    ['232410088', '443', 'Fajrul Falah Jauhar', 'laki-laki', 'siswa'],
    ['232410087', '444', 'Fardhandy Azriel Dwiki Putra', 'laki-laki', 'siswa'],
    ['232410101', '445', 'Fawwaz Muhammad Zaahiy', 'laki-laki', 'siswa'],
    ['232410104', '446', 'Firas Ghaisan Shairazy', 'laki-laki', 'siswa'],
    ['232410123', '447', 'Hify Andi Ibrahim', 'laki-laki', 'siswa'],
    ['232410124', '448', 'Hilbram Hannan Arbistha', 'laki-laki', 'siswa'],
    ['232410131', '449', 'Irgi Aidan Zeyafattah', 'laki-laki', 'siswa'],
    ['232410134', '450', 'Izzuddin Mahmud', 'laki-laki', 'siswa'],
    ['232410136', '451', 'Jibryl Ravy Praditya', 'laki-laki', 'siswa'],
    ['232410142', '452', 'Keenan Muhammad Izyan', 'laki-laki', 'siswa'],
    ['232410146', '453', 'Khaleda Ihsan', 'perempuan', 'siswa'],
    ['232410154', '454', 'Kynan Adila Machda', 'laki-laki', 'siswa'],
    ['232410159', '455', 'Maeko Kaysan Abigail', 'laki-laki', 'siswa'],
    ['232410175', '456', 'Muhammad Fadhil Dwi Putra', 'laki-laki', 'siswa'],
    ['232410182', '457', 'Muhammad Hamnas Fathurrahman', 'laki-laki', 'siswa'],
    ['232410183', '458', 'Muhammad Hanif Muttaqin', 'laki-laki', 'siswa'],
    ['232410185', '459', 'Muhammad Hisyam Raharja', 'laki-laki', 'siswa'],
    ['232410187', '460', 'Muhammad Kayis Mumtaz', 'laki-laki', 'siswa'],
    ['232410211', '461', 'Nafisa Putri Asyono', 'perempuan', 'siswa'],
    ['232410236', '462', 'Nisrina Hafiza Raina', 'perempuan', 'siswa'],
    ['232410263', '463', 'Raditya Firdaus Bahri', 'laki-laki', 'siswa'],
    ['232410272', '464', 'Salwa Faridatunnisa Nur Arrahmi', 'perempuan', 'siswa'],
    ['232410307', '465', 'Zarayanti Yasmin Solichin', 'perempuan', 'siswa'],
    ['1829310', '516', 'adam', 'laki-laki', 'guru'],
    ['1729910', '316', 'dani', 'laki-laki', 'guru'],
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
