<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawai = Role::where('nama_role', 'pegawai')->first()->id;
        $manager = Role::where('nama_role', 'manager')->first()->id;
        $kepalaDepartemen = Role::where('nama_role', 'kepala_departemen')->first()->id;
        $hrd = Role::where('nama_role', 'hrd')->first()->id;
        $direktur = Role::where('nama_role', 'direktur')->first()->id;
        $admin = Role::where('nama_role', 'administrator')->first()->id;
        
        // Create employee
        User::create([
            'name' => 'pegawai',
            'email' => 'pegawai@pegawai',
            'password' => Hash::make('pegawai@pegawai'),
            'role_id' => $pegawai,
            'position' => 'Software Developer',
            'department' => 'IT',
        ]);
        
        // Create manager
        User::create([
            'name' => 'Manager',
            'email' => 'manager@manager',
            'password' => Hash::make('manager@manager'),
            'role_id' => $manager,
            'position' => 'Tim Manager',
            'department' => 'IT',
        ]);
        
        // Create department head
        User::create([
            'name' => 'Kepala Departemen',
            'email' => 'kadep@kadep',
            'password' => Hash::make('kadep@kadep'),
            'role_id' => $kepalaDepartemen,
            'position' => 'Kepala Departemen',
            'department' => 'IT',
        ]);
        
        // Create HRD
        User::create([
            'name' => 'HRD',
            'email' => 'hrd@hrd',
            'password' => Hash::make('hrd@hrd'),
            'role_id' => $hrd,
            'position' => 'HR Manager',
            'department' => 'Human Resources',
        ]);
        
        // Create Director
        User::create([
            'name' => 'Direktur',
            'email' => 'direktur@direktur',
            'password' => Hash::make('direktur@direktur'),
            'role_id' => $direktur,
            'position' => 'Director',
            'department' => 'Executive',
        ]);

        // Create admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@admin',
            'password' => Hash::make('admin@admin'),
            'role_id' => $admin,
        ]);
    }
}
