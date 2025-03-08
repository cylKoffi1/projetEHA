<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Acteur;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run()
    {
        // Liste des utilisateurs à insérer
        $utilisateurs = [
            [
                'nom' => 'Diouf',
                'prenom' => 'Lamine',
                'email' => 'lamine.diouf@example.com',
                'login' => 'diouf.lamine',
            ],
            [
                'nom' => 'LumunBa',
                'prenom' => 'Christelle',
                'email' => 'christelle.lumunba@example.com',
                'login' => 'lumunba.christelle',
            ],
            [
                'nom' => 'Keita',
                'prenom' => 'Aminata',
                'email' => 'aminata.keita@example.com',
                'login' => 'keita.aminata',
            ],
            [
                'nom' => 'Nbrabi',
                'prenom' => 'Jule',
                'email' => 'jule.nbrabi@example.com',
                'login' => 'nbrabi.jule',
            ],
            [
                'nom' => 'Seyni',
                'prenom' => 'Abdoul',
                'email' => 'abdoul.seyni@example.com',
                'login' => 'seyni.abdoul',
            ],
            [
                'nom' => 'Malloum',
                'prenom' => 'Mohamed',
                'email' => 'mohamed.malloum@example.com',
                'login' => 'malloum.mohamed',
            ],
            [
                'nom' => 'N\'dayi',
                'prenom' => 'Shimène',
                'email' => 'shimene.ndayi@example.com',
                'login' => 'ndayi.shimene',
            ],
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean.dupont@example.com',
                'login' => 'dupont.jean',
            ],
        ];

        foreach ($utilisateurs as $utilisateur) {
            // Récupérer l'acteur correspondant
            $acteur = Acteur::where('libelle_long', "{$utilisateur['prenom']} }")->first();

            if ($acteur) {
                User::create([
                    'acteur_id' => $acteur->code_acteur,
                    'groupe_utilisateur_id' => 'uc',
                    'groupe_projet_id' => 'BAT',
                    'login' => $utilisateur['login'],
                    'password' => Hash::make('123456789'),
                    'email' => $utilisateur['email'],
                    'is_active' => 1,
                    'must_change_password' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                echo "Utilisateur ajouté : {$utilisateur['email']}\n";
            } else {
                echo "⚠️ Acteur non trouvé pour {$utilisateur['nom']} {$utilisateur['prenom']} !\n";
            }
        }
    }
}
