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
                'name' => 'employee',
                'description' => 'Regular employee',
            ],
            [
                'name' => 'direct_manager',
                'description' => 'Direct manager of employees',
            ],
            [
                'name' => 'dept_head',
                'description' => 'Department head',
            ],
            [
                'name' => 'hrd',
                'description' => 'Human Resources Department',
            ],
            [
                'name' => 'director',
                'description' => 'Company director',
            ],
            [
                'name' => 'administrator',
                'description' => 'System administrator',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
