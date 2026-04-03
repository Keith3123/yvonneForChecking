<?php


namespace App\Factories;


use App\Models\User;


interface UserFactoryInterface
{
public function createUser(string $username, string $password, int $roleId): User;
public function createUserWithDefaults(string $username, string $password): User;
}