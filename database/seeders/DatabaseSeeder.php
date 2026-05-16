<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal — credenciales desde .env
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@mecaza.com')],
            [
                'name'     => env('ADMIN_NAME', 'Administrador'),
                'rol'      => 'administrador',
                'tel'      => env('ADMIN_TEL', '0000000000'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'mecaza2024')),
            ]
        );
    }
}
