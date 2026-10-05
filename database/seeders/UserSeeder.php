<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ===============================
        // MAHASISWA
        // ===============================
        User::updateOrCreate(
            ['username' => 'mahasiswa.ti'],
            [
                'name' => 'Mahasiswa TI',
                'email' => 'mahasiswa.ti@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'prodi' => 'TI',
                'npm' => '1402021086',
                'telephonenumber' => '081234567890',
            ]
        );

        User::updateOrCreate(
            ['username' => 'mahasiswa.pdsi'],
            [
                'name' => 'Mahasiswa PdSI',
                'email' => 'mahasiswa.pdsi@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'prodi' => 'PdSI',
                'npm' => '1502021089',
                'telephonenumber' => '081234567891',
            ]
        );

        // ===============================
        // DOSEN
        // ===============================
        User::updateOrCreate(
            ['username' => 'dosen.pengaju'],
            [
                'name' => 'Dosen Pengaju',
                'email' => 'dosen.pengaju@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'dosen',
                'prodi' => 'PdSI',
                'nidn' => '531141195111',
                'telephonenumber' => '081234567892',
            ]
        );

        // ===============================
        // ADMIN TU
        // ===============================
        User::updateOrCreate(
            ['username' => 'admin.tu'],
            [
                'name' => 'Admin TU',
                'email' => 'admin.tu@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::create([
            'name' => 'Administrator',
            'username' => 'administrator',
            'email' => 'administrator@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'administrator'
        ]);


        // ===============================
        // KAPRODI
        // ===============================

        User::updateOrCreate(
            ['username' => 'kaprodi.ti'],
            [
                'name' => 'Kaprodi TI',
                'email' => 'kaprodi.ti@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'kaprodi',
                'prodi' => 'TI',
                'nidn' => '531141195112',
                'telephonenumber' => '085691435663',
            ]
        );

        User::updateOrCreate(
            ['username' => 'kaprodi.pdsi'],
            [
                'name' => 'Kaprodi PdSI',
                'email' => 'kaprodi.pdsi@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'kaprodi',
                'prodi' => 'PdSI',
                'nidn' => '531141195113',
                'telephonenumber' => '085691435663',
            ]
        );


        // ===============================
        // DEKAN
        // ===============================
        User::updateOrCreate(
            ['username' => 'dekan'],
            [
                'name' => 'Dekan FTI',
                'email' => 'dekan@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'dekan',
                'nidn' => '531141106114',
                'telephonenumber' => '0811849009',
            ]
        );

    }
}
