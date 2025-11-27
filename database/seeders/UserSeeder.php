<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        User::firstOrCreate(
            ['email' => 'admin@example.com'], 
            [
                'name' => 'Admin',
                // Contraseña: 'password'
                'password' => Hash::make('password'), 
                'email_verified_at' => now(), 
            ]
        );
    }
}