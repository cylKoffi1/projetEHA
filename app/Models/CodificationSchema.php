<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodificationSchema extends Model
{
    protected $table = 'codification_schemas';

    protected $fillable = [
        'pays_alpha3',
        'entity_type',
        'name',
        'pattern',
        'token_separator',
        'active',
    ];

    /**
     * Retourne les tokens utilisés dans le pattern.
     * Ex : {PAYS}_{DOMAINE}_{ANNEE}_{SEQ3}
     */
    public function getTokensAttribute()
    {
        preg_match_all('/\{([A-Z0-9_]+)\}/', $this->pattern, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Vérifie si un token (ex : SEQ3) est utilisé dans ce schéma.
     */
    public function usesToken(string $token): bool
    {
        return in_array($token, $this->tokens);
    }

    /**
     * Génère un code selon le schéma.
     * Cette fonction utilise un tableau associatif de valeurs.
     *
     * Exemple d’utilisation :
     * $schema->generate([
     *      'PAYS' => 'CIV',
     *      'DOMAINE' => 'EHA',
     *      'TYPE' => 'RF',
     *      'ANNEE' => 2025,
     *      'SEQ3' => 8
     * ]);
     */
    public function generate(array $values): string
    {
        $code = $this->pattern;
        $sep  = $this->token_separator ?? '_';

        foreach ($values as $key => $val) {
            // Padding automatique pour les séquences
            if (preg_match('/^SEQ(\d)$/', $key, $m)) {
                $length = intval($m[1]);
                $val = str_pad($val, $length, '0', STR_PAD_LEFT);
            }

            $code = str_replace('{'.$key.'}', $val, $code);
        }

        // Nettoyage si certains tokens n’ont pas été fournis
        return preg_replace('/\{[A-Z0-9_]+\}/', '-', $code);
    }

    /**
     * Relation vers le pays si tu veux lier automatiquement (optionnel)
     */
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_alpha3', 'alpha3');
    }
}
