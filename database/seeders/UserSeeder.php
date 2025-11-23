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
 ['232410397', '001', 'ADITIA HANNUR', 'laki-laki', 'siswa'],
    ['232410401', '002', 'AINUN HAKIEMAH', 'perempuan', 'siswa'],
    ['232410217', '003', 'AJI KULDIYAGUNG', 'laki-laki', 'siswa'],
    ['232410254', '004', 'AKHMAD BAIHAQI', 'laki-laki', 'siswa'],
    ['232410292', '005', 'ANDHIKA YUDHISTIRA', 'laki-laki', 'siswa'],
    ['232410219', '006', 'ANGGITA FARA FAUZIAH', 'perempuan', 'siswa'],
    ['232410042', '007', 'AULIA LISABILLA PUTRI', 'perempuan', 'siswa'],
    ['232410403', '008', 'DAVINA', 'perempuan', 'siswa'],
    ['232410405', '009', 'DEYLAND ZLAVINHA AHMAD', 'laki-laki', 'siswa'],
    ['232410367', '010', 'DIMAS ADI SUCIPTO', 'laki-laki', 'siswa'],
    ['232410192', '011', 'DWI VIRLI AULIYA', 'perempuan', 'siswa'],
    ['232410228', '012', 'FAZRI DEWANGGA SETIAWAN PUTERA', 'laki-laki', 'siswa'],
    ['232410088', '013', 'GINA AULIA', 'perempuan', 'siswa'],
    ['232410409', '014', 'HAFINA SAI ASATUN', 'perempuan', 'siswa'],
    ['232410264', '015', 'HANI WULAN DARI', 'perempuan', 'siswa'],
    ['232410232', '016', 'HIKMAH NUR FADILAH', 'perempuan', 'siswa'],
    ['232410195', '017', 'HISYAM FAHMIL ISLAM', 'laki-laki', 'siswa'],
    ['232410013', '018', 'IKA NURJANAH', 'perempuan', 'siswa'],
    ['232410301', '019', 'INDI ZAHROTUSSYITA', 'perempuan', 'siswa'],
    ['232410411', '020', 'INES AFRELIA', 'perempuan', 'siswa'],
    ['232410343', '021', 'KEINA AZZAHRA BARLIAN', 'perempuan', 'siswa'],
    ['232410018', '022', 'LAILA FITRI', 'perempuan', 'siswa'],
    ['232410163', '023', 'LUTHFIYATUL MAFTUHAH', 'perempuan', 'siswa'],
    ['242511430', '024', 'MOHAMMAD FAHZRI', 'laki-laki', 'siswa'],
    ['232410313', '025', 'MUHAMMAD FAIZ SANOVA', 'laki-laki', 'siswa'],
    ['232410349', '026', 'NAZWA ERSA ALMAGHFIRA', 'perempuan', 'siswa'],
    ['232410350', '027', 'PANDU PERDIYANSYAH', 'laki-laki', 'siswa'],
    ['232410242', '028', 'PATMA', 'perempuan', 'siswa'],
    ['232410421', '029', 'PUTRI RAINA DEWI', 'perempuan', 'siswa'],
    ['232410354', '030', 'RAHMA NUR AZIZAH', 'perempuan', 'siswa'],
    ['232410028', '031', 'RAIHAN FIRJATULLAH HARTONO', 'laki-laki', 'siswa'],
    ['232410102', '032', 'ROHANA APRILIANA SARI', 'perempuan', 'siswa'],
    ['232410283', '033', 'SHAFIRA ZANUBA', 'perempuan', 'siswa'],
    ['232410068', '034', 'SHELYA NAZWA', 'perempuan', 'siswa'],
    ['232410250', '035', 'VENNY LAURA ANDRIYANI', 'perempuan', 'siswa'],
    ['232410108', '036', 'YOGA WIJAYA', 'laki-laki', 'siswa'],
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
