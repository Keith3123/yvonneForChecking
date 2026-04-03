<?php


namespace App\Factories;


use App\Models\User;
use Illuminate\Support\Facades\Hash;


class ConcreteUserFactory implements UserFactoryInterface
{
    public function createUser(string $username, string $password, int $roleId): User
    {
        return new User([
            'username' => $username,

            // ✅ DO NOT HASH HERE (already hashed in controller)
            'password' => $password,

            'roleID' => $roleId,
            'status' => 1,
        ]);
    }

    public function createUserWithDefaults(string $username, string $password): User
    {
        return $this->createUser($username, $password, 2);
    }
}