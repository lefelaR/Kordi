<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('APP_LOGIN_EMAIL', 'admin@kordicraftcreations.co.za');
        $password = env('APP_LOGIN_PASSWORD', 'kordicraft-demo');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => $password,
            ]
        );
    }
}
