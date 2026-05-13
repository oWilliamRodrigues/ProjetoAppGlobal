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
                'password' => Hash::make('ChangeMe!2026'),
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'operator@globalsys.com.br'],
            [
                'name' => 'Operador',
                'password' => Hash::make('Operator!2026'),
                'email_verified_at' => now(),
            ],
        );

        User::factory()->count(10)->create();

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
