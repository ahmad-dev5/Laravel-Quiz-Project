<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create the admin user
        $admin = User::firstOrCreate([
            'email' => 'ahmadali@gmail.com', 
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('alphacharlie'), // Set a default password
        ]);

        // Assign the 'admin' role to the user
        $admin->assignRole($adminRole);

        $this->command->info('Admin user created successfully!');
    }
}
