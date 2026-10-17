<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            ['name' => 'Demo Customer', 'email' => 'customer@ticketflow.demo', 'role' => 'customer'],
            ['name' => 'Demo Customer 2', 'email' => 'customer2@ticketflow.demo', 'role' => 'customer'],
            ['name' => 'Demo Agent', 'email' => 'agent@ticketflow.demo', 'role' => 'agent'],
            ['name' => 'Demo Admin', 'email' => 'admin@ticketflow.demo', 'role' => 'admin'],
        ];

        foreach ($demoUsers as $demoUser) {
            $user = User::firstOrNew([
                'email' => $demoUser['email'],
            ]);

            $user->name = $demoUser['name'];
            $user->password = 'Demo1234!';
            $user->role = $demoUser['role'];

            $user->save();
        }
    }
}
