<?php
namespace App\Services;

use App\Models\User;
use App\Models\Manager;

class UserService
{
    public function createManager(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole('manager');

        Manager::create(['user_id' => $user->id]);

        return $user;
    }
}
