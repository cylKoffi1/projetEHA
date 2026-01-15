<?php

namespace App\Support;

use App\Models\User;
use App\Models\Acteur;

/**
 * Normalise/enrichit un snapshot pour les workflows :
 * - remplit owner_email / demandeur_email si on a un user_id ou un code acteur
 * - harmonise les clés (aliases -> standard)
 */
class SnapshotNormalizer
{
    /** Map d'aliases -> cibles standard */
    private const ALIASES = [
        // emails directs
        'requester_email'          => 'demandeur_email',
        'created_by_email'         => 'owner_email',
        // user ids
        'requester_user_id'        => 'demandeur_user_id',
        'created_by'               => 'owner_user_id',
        'user_id'                  => 'owner_user_id',
        // acteur codes
        'requester_acteur_code'    => 'demandeur_acteur_code',
        'created_by_acteur_code'   => 'owner_acteur_code',
        'chef_projet_code'         => 'owner_acteur_code', // souvent le “propriétaire”
    ];

    public function normalize(array $snap): array
    {
        $snap = $this->applyAliases($snap);

        // 1) Compléter email depuis user_id
        foreach (['owner_user_id', 'demandeur_user_id'] as $key) {
            $uid = data_get($snap, $key);
            if ($uid && empty($snap[$this->emailKeyFor($key)])) {
                $email = User::whereKey($uid)->value('email');
                if ($email) {
                    $snap[$this->emailKeyFor($key)] = $email;
                }
            }
        }

        // 2) Compléter email depuis acteur_code
        foreach (['owner_acteur_code', 'demandeur_acteur_code'] as $key) {
            $code = data_get($snap, $key);
            if ($code && empty($snap[$this->emailKeyFor($key)])) {
                $email = $this->emailFromActorCode($code);
                if ($email) {
                    $snap[$this->emailKeyFor($key)] = $email;
                }
            }
        }

        return $snap;
    }

    private function applyAliases(array $snap): array
    {
        foreach (self::ALIASES as $src => $dst) {
            if (array_key_exists($src, $snap) && !array_key_exists($dst, $snap)) {
                $snap[$dst] = $snap[$src];
            }
        }
        return $snap;
    }

    private function emailKeyFor(string $hintKey): string
    {
        return str_starts_with($hintKey, 'owner_') ? 'owner_email' : 'demandeur_email';
    }

    private function emailFromActorCode(string $code): ?string
    {
        // 1) email sur Acteur
        $a = Acteur::where('code_acteur', $code)->first();
        if (!empty($a?->email)) return $a->email;

        // 2) email sur User lié (fallback)
        return User::whereHas('acteur', fn($q)=>$q->where('code_acteur',$code))
            ->value('email') ?: null;
    }
}
