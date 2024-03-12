<?php
namespace App\Helpers;
use App\Models\Personnel;

class CodeGenerator
{
    public static function generateCode($length = 5): string
    {
        // Chiffres possibles pour le code
        $characters = '0123456789';

        do {
            // Générer une chaîne aléatoire
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (Personnel::where('code_personnel', $randomString)->exists());

        return $randomString;
    }
}