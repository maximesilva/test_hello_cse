<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un utilisateur administrateur avec le rôle admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Création d\'un utilisateur administrateur');
        $this->newLine();

        $email = $this->ask('Quel est l\'email de l\'administrateur ?', 'admin@example.com');
        $firstName = $this->ask('Quel est le prénom de l\'administrateur ?', 'Super');
        $name = $this->ask('Quel est le nom de l\'administrateur ?', 'Admin');
        $password = $this->secret('Quel est le mot de passe de l\'administrateur ?');

        if (empty($password)) {
            $password = 'password';
            $this->warn('Aucun mot de passe fourni, utilisation du mot de passe par défaut : password');
        }

        // Crée le rôle admin s'il n'existe pas
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crée l'utilisateur admin
        $admin = User::firstOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => $name,
                'first_name' => $firstName,
                'password' => Hash::make($password),
                'status' => 'active',
            ]
        );

        // Assigne le rôle admin
        $admin->assignRole($adminRole);

        $this->newLine();
        $this->info('✅ L\'utilisateur administrateur a été créé avec succès !');
        $this->newLine();
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Email', $email],
                ['Prénom', $firstName],
                ['Nom', $name],
                ['Mot de passe', $password],
            ]
        );
    }
}
