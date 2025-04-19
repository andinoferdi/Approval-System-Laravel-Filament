<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nama_role' => 'pegawai',
                'deskripsi' => 'Pegawai Biasa',
            ],
            [
                'nama_role' => 'manager',
                'deskripsi' => 'Atasan Langsung',
            ],
            [
                'nama_role' => 'kepala_departemen',
                'deskripsi' => 'kepala Departemen',
            ],
            [
                'nama_role' => 'hrd',
                'deskripsi' => 'Human Resources Department',
            ],
            [
                'nama_role' => 'direktur',
                'deskripsi' => 'Direktur',
            ],
            [
                'nama_role' => 'administrator',
                'deskripsi' => 'Administrator',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
