<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create roles for the 'api' guard
        // Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        // Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        // Role::firstOrCreate(['name' => 'student', 'guard_name' => 'api']);
        // Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'api']);

        Role::insert([
            ['name' => 'admin', 'guard_name' => 'api'],
            ['name' => 'manager', 'guard_name' => 'api'],
            ['name' => 'student', 'guard_name' => 'api'],
            ['name' => 'supervisor', 'guard_name' => 'api']
        ]);
    }
}
