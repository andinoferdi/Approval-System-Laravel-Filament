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
        $employeeRoleId = Role::where('name', 'employee')->first()->id;
        $directManagerRoleId = Role::where('name', 'direct_manager')->first()->id;
        $deptHeadRoleId = Role::where('name', 'dept_head')->first()->id;
        $hrdRoleId = Role::where('name', 'hrd')->first()->id;
        $directorRoleId = Role::where('name', 'director')->first()->id;
        $adminRoleId = Role::where('name', 'administrator')->first()->id;
        
        // Create employee
        User::create([
            'name' => 'Employee',
            'email' => 'employee@employee',
            'password' => Hash::make('employee@employee'),
            'role_id' => $employeeRoleId,
            'position' => 'Software Developer',
            'department' => 'IT',
        ]);
        
        // Create manager
        User::create([
            'name' => 'Manager',
            'email' => 'manager@manager',
            'password' => Hash::make('manager@manager'),
            'role_id' => $directManagerRoleId,
            'position' => 'Tim Manager',
            'department' => 'IT',
        ]);
        
        // Create department head
        User::create([
            'name' => 'Department Head',
            'email' => 'depthead@depthead',
            'password' => Hash::make('depthead@depthead'),
            'role_id' => $deptHeadRoleId,
            'position' => 'Kepala Departemen',
            'department' => 'IT',
        ]);
        
        // Create HRD
        User::create([
            'name' => 'HRD',
            'email' => 'hrd@hrd',
            'password' => Hash::make('hrd@hrd'),
            'role_id' => $hrdRoleId,
            'position' => 'HR Manager',
            'department' => 'Human Resources',
        ]);
        
        // Create Director
        User::create([
            'name' => 'Direktor',
            'email' => 'director@director',
            'password' => Hash::make('director@director'),
            'role_id' => $directorRoleId,
            'position' => 'Director',
            'department' => 'Executive',
        ]);

        // Create admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin',
            'password' => Hash::make('admin@admin'),
            'role_id' => $adminRoleId,
        ]);
    }
}
