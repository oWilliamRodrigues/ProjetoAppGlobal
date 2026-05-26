<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@globalsys.com.br'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin!2026'),
                'email_verified_at' => now(),
                'role'=> 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'operator@globalsys.com.br'],
            [
                'name' => 'Operador',
                'password' => Hash::make('operator!2026'),
                'email_verified_at' => now(),
                'role'=> 'operator',
            ],
        );

        User::factory()->count(10)->create();

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
