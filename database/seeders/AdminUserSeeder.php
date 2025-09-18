<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur admin par défaut
        User::firstOrCreate(
            ['email' => 'admin@evostat.com'],
            [
                'firstname' => 'Admin',
                'lastname' => 'EvoStat',
                'pseudo' => 'admin',
                'email' => 'admin@evostat.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'weight' => 70,
                'gender' => 'male',
                'age' => 30,
            ]
        );

        $this->command->info('Utilisateur admin créé avec succès !');
        $this->command->info('Email: admin@evostat.com');
        $this->command->info('Mot de passe: admin123');
        $this->command->warn('⚠️  IMPORTANT: Changez le mot de passe après la première connexion !');
    }
}
