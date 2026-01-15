<?php 
// app/Services/CodeGenerator.php
namespace App\Services;

use App\Models\CodificationSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CodeGenerator
{
    /**
     * $entityType : 'projet', 'appui', 'etude', 'infra', 'renforcement', 'travaux_connexe'
     * $model      : instance du modèle en cours de création (non encore sauvegardé)
     */
    public function generate(string $entityType, Model $model): string
    {
        $country = $this->getCountryFromModel($model);

        $schema = CodificationSchema::where('entity_type', $entityType)
            ->where('country_alpha3', $country)
            ->where('active', true)
            ->firstOrFail();

        $pattern = $schema->pattern;

        // 1) Remplacer tout sauf la séquence
        $codeWithoutSeq = $this->replaceTokensExceptSeq($pattern, $model);

        // 2) Déterminer longueur de la séquence (SEQ2, SEQ3, etc.)
        preg_match('/\{SEQ(\d+)\}/', $pattern, $m);
        $seqLen = isset($m[1]) ? (int)$m[1] : 3;

        // 3) Préfixe pour recherche en BDD
        $prefix = str_replace(['{SEQ2}','{SEQ3}','{SEQ4}'], '', $codeWithoutSeq);

        // 4) Récupérer le dernier code existant pour ce préfixe
        $field = $this->getCodeFieldName($entityType, $model);

        $last = $model->newQuery()
            ->where($field, 'like', $prefix.'%')
            ->orderByDesc($field)
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->$field, -$seqLen);
            $newNumber  = str_pad($lastNumber + 1, $seqLen, '0', STR_PAD_LEFT);
        } else {
            $newNumber = str_pad(1, $seqLen, '0', STR_PAD_LEFT);
        }

        return str_replace(['{SEQ2}','{SEQ3}','{SEQ4}'], $newNumber, $codeWithoutSeq);
    }

    protected function getCountryFromModel(Model $model): string
    {
        if (isset($model->code_alpha3_pays)) return $model->code_alpha3_pays;
        if (isset($model->code_pays)) return $model->code_pays;

        // fallback : pays en session
        return session('pays_selectionne', 'CIV');
    }

    protected function getCodeFieldName(string $entityType, Model $model): string
    {
        // Permet d'utiliser le bon champ string
        return match ($entityType) {
            'projet'          => 'code_projet',
            'appui'           => 'code_projet_appui',
            'etude'           => 'code_projet_etude',
            'infra'           => 'code',               // Infrastructure
            'renforcement'    => 'code_renforcement',
            'travaux_connexe' => 'codeActivite',
            default           => $model->getKeyName(),
        };
    }

    protected function replaceTokensExceptSeq(string $pattern, Model $model): string
    {
        $country = $this->getCountryFromModel($model);

        $year = $this->getYear($model);
        $month = $this->getMonth($model);

        $replacements = [
            '{PAYS}'         => $country,
            '{TYPE}'         => $this->getTypeCode($model),
            '{DOMAINE}'      => $model->code_domaine ?? '',
            '{SOUS_DOMAINE}' => $model->code_sous_domaine ?? '',
            '{ANNEE}'        => $year,
            '{MOIS}'         => $month,
        ];

        return strtr($pattern, $replacements);
    }

    protected function getYear(Model $model): string
    {
        $date = $model->date_debut_previsionnel
            ?? $model->date_demarrage_prevue
            ?? $model->date_debut
            ?? now();

        return \Illuminate\Support\Carbon::parse($date)->format('Y');
    }

    protected function getMonth(Model $model): string
    {
        $date = $model->date_debut_previsionnel
            ?? $model->date_demarrage_prevue
            ?? $model->date_debut
            ?? now();

        return \Illuminate\Support\Carbon::parse($date)->format('m');
    }

    protected function getTypeCode(Model $model): string
    {
        // À adapter si besoin (ex: numéro de lot, code type d'étude, etc.)
        return $model->type_etude_code
            ?? $model->groupe_projet_code
            ?? '1';
    }
}
