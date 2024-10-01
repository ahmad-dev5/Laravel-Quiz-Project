<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Define all permissions
        $permissions = [
            'User can add manager',
            'User can view/accept/reject student requests',
            'User can view assigned quizzes to students',
            'User can assign quizzes to students',
            'User can view quiz results',
            'User can view student details',
            'User can view all quizzes',
            'User can view Assigned Quizzes',
            'User can view Attempted Quizzes',
            'User can view Pending Quizzes',
            'User can view Assigned Quiz Results',
            'User can attempt quizzes',
        ];

        // Loop through each permission and create it if it doesn't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);

        }

        // Fetch Roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $studentRole = Role::where('name', 'student')->first();
        $supervisorRole = Role::where('name', 'supervisor')->first(); // Fetch supervisor role

        // Assign permissions to Admin
        if ($adminRole) {
            $adminRole->syncPermissions([
                'User can add manager',
                'User can view/accept/reject student requests',
                'User can view assigned quizzes to students',
                'User can assign quizzes to students',
                'User can view quiz results',
                'User can view student details',
                'User can view all quizzes',
                
            ]);
        }

        // Assign permissions to Supervisor
        if ($supervisorRole) {
            $supervisorRole->syncPermissions([
                'User can view/accept/reject student requests',
                'User can view assigned quizzes to students',
                'User can assign quizzes to students',
                'User can view quiz results',
                'User can view student details',
                'User can view all quizzes',
            ]);
        }

        // Assign permissions to Manager
        if ($managerRole) {
            $managerRole->syncPermissions([
                'User can view assigned quizzes to students',
                'User can assign quizzes to students',
                'User can view quiz results',
                'User can view student details',
                'User can view all quizzes',
            ]);
        }

        // Assign permissions to Student
        if ($studentRole) {
            $studentRole->syncPermissions([
                'User can view Assigned Quizzes',
                'User can view Attempted Quizzes',
                'User can view Pending Quizzes',
                'User can view Assigned Quiz Results',
                'User can attempt quizzes',
            ]);
        }
    }
}
