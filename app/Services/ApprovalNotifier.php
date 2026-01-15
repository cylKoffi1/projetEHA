<?php

namespace App\Services;

use App\Mail\NotificationValidationProjet;
use App\Mail\ProjetRefuseNotification;
use App\Models\Acteur;
use App\Models\ActionApprobation;
use App\Models\InstanceApprobation;
use App\Models\InstanceEtape;
use App\Models\StatutEtapeInstance;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ApprovalNotifier
{
    /**
     * Notifie tous les approbateurs de l’étape active (EN_COURS).
     * $lastActorLabel = libellé (string) de la personne qui vient d’agir (ex: “Olive Dominique”).
     * $ctaUrl = URL du bouton. Par défaut: route('approbations.dashboard').
     */
    public function notifyActiveApprovers(
        InstanceApprobation $inst,
        ?string $lastActorLabel = null,
        ?string $ctaUrl = null
    ): void {
        // Étape active
        $active = $inst->etapes()
            ->where('statut_id', $this->statutEtapeId('EN_COURS'))
            ->with('etape.approbateurs')
            ->first();

        if (!$active) return;

        // Éviter double notification
        if ($active->notifie_le) return;

        $targets = $this->approverContactsForStep($active);
        if (empty($targets)) return;

        $codeProjet    = (string) $inst->id_cible;
        $libelleProjet = $inst->module_code.' • '.$inst->type_cible.' #'.$inst->id_cible;

        $ctaUrl = $ctaUrl ?: (route('approbations.dashboard') ?? url('/'));
        $lastActorLabel = $lastActorLabel ?: $this->guessLastActorLabel($inst) ?: 'Système';

        foreach ($targets as $dest) {
            Mail::to($dest['email'])->queue(
                new NotificationValidationProjet(
                    $codeProjet,
                    $libelleProjet,
                    $lastActorLabel,
                    $dest,       // array: code, email, libelle_court, libelle_long
                    $ctaUrl
                )
            );
        }

        $active->forceFill(['notifie_le' => now()])->save();
    }

    /**
     * Notifie la personne à qui l’on délègue l’étape.
     */
    public function notifyOnDelegation(
        InstanceEtape $si,
        string $delegateToCode,
        ?string $lastActorLabel = null,
        ?string $ctaUrl = null
    ): void {
        $inst = $si->instance()->first();
        if (!$inst) return;

        $dest = $this->contactFromActorCode($delegateToCode);
        if (!$dest) return;

        $codeProjet    = (string) $inst->id_cible;
        $libelleProjet = $inst->module_code.' • '.$inst->type_cible.' #'.$inst->id_cible;

        $ctaUrl = $ctaUrl ?: (route('approbations.dashboard') ?? url('/'));
        $lastActorLabel = $lastActorLabel ?: $this->guessLastActorLabel($inst) ?: 'Système';

        Mail::to($dest['email'])->queue(
            new NotificationValidationProjet(
                $codeProjet,
                $libelleProjet,
                $lastActorLabel,
                $dest,
                $ctaUrl
            )
        );
    }

    /**
     * Notifie le “propriétaire/demandeur” quand une instance est rejetée.
     */
    public function notifyOwnerOnRejection(InstanceApprobation $inst, ?string $comment = null): void
    {
        if ($owner = $this->resolveOwnerContact($inst)) {
            if (!empty($owner['email'])) {
                $codeProjet    = (string) $inst->id_cible;
                $libelleProjet = $inst->module_code.' • '.$inst->type_cible.' #'.$inst->id_cible;
    
                $label = trim(($owner['libelle_court'] ?? '').' '.($owner['libelle_long'] ?? ''));
                $label = $label !== '' ? $label : ($owner['code'] ?? $owner['email']);
    
                Mail::to($owner['email'])->queue(
                    new \App\Mail\ProjetRefuseNotification($codeProjet, $libelleProjet, (string) $comment, $label)
                );
            }
        }
    }
    

    /* ===================== Helpers ===================== */

    private function statutEtapeId(string $code): int
    {
        return StatutEtapeInstance::where('code', $code)->firstOrFail()->id;
    }

    /**
     * Devine le libellé du dernier acteur ayant posé une action sur l’instance.
     * Renvoie null si introuvable.
     */
    private function guessLastActorLabel(InstanceApprobation $inst): ?string
    {
        // Récupère la dernière action (toutes étapes confondues)
        $stepIds = $inst->etapes()->pluck('id');
        $last = ActionApprobation::whereIn('instance_etape_id', $stepIds)
            ->latest('id')
            ->first();

        if (!$last) return null;

        $code = $last->code_acteur;
        if (!$code) return null;

        $a = Acteur::where('code_acteur', $code)->first();
        if ($a) {
            $label = trim(implode(' ', array_filter([(string) $a->libelle_court, (string) $a->libelle_long])));
            if ($label !== '') return $label;
        }

        // Fallback via User lié
        $userEmail = User::whereHas('acteur', fn($q)=>$q->where('code_acteur',$code))->value('email');
        return $userEmail ?: $code;
    }

    /**
     * Résout les contacts destinataires pour une étape (emails dedup).
     */
    private function approverContactsForStep(InstanceEtape $si): array
    {
        $codes = $this->expandApproverCodes($si);
        if (empty($codes)) return [];

        $out = [];
        foreach ($codes as $code) {
            if ($contact = $this->contactFromActorCode($code)) {
                $out[] = $contact;
            }
        }

        // dédoublonnage par email
        $seen = [];
        return array_values(array_filter($out, function ($x) use (&$seen) {
            if (empty($x['email'])) return false;
            if (isset($seen[$x['email']])) return false;
            $seen[$x['email']] = true;
            return true;
        }));
    }

    private function contactFromActorCode(string $code): ?array
    {
        $a = Acteur::where('code_acteur', $code)->first();

        // email direct sur Acteur
        $email = $a?->email;

        // fallback: email du User rattaché
        if (!$email) {
            $email = User::whereHas('acteur', fn($q)=>$q->where('code_acteur',$code))->value('email');
        }

        if (!$email) return null;

        return [
            'code'          => $code,
            'email'         => $email,
            'libelle_court' => (string) ($a?->libelle_court ?? ''),
            'libelle_long'  => (string) ($a?->libelle_long ?? ''),
        ];
    }

    /**
     * Déploie la liste des codes approbateurs pour l’étape.
     * Gère ACTEUR / FIELD_ACTEUR (ROLE/GROUPE: à brancher si besoin).
     */
    private function expandApproverCodes(InstanceEtape $si): array
    {
        $snapshot = (array) ($si->instance->instantane ?? []);
        $codes = [];

        foreach ($si->etape->approbateurs as $a) {
            switch ($a->type_approbateur) {
                case 'ACTEUR':
                    if ($a->reference_approbateur) $codes[] = $a->reference_approbateur;
                    break;

                case 'FIELD_ACTEUR':
                    $field = (string) $a->reference_approbateur;
                    $code  = data_get($snapshot, $field);
                    if ($code) $codes[] = $code;
                    break;

                case 'ROLE':
                    // TODO: brancher la résolution par rôle si vous avez la table d’affectations
                    break;

                case 'GROUPE':
                    // TODO: brancher la résolution par groupe si vous avez la table de groupes
                    break;
            }
        }

        return array_values(array_unique(array_filter($codes)));
    }

    /**
     * Résout le “propriétaire/demandeur” d’une instance via l’instantané.
     */
    private function resolveOwnerContact(InstanceApprobation $inst): ?array
    {
        $snap = (array) ($inst->instantane ?? []);

        // email direct prioritaire
        foreach (['owner_email', 'demandeur_email', 'requester_email', 'created_by_email'] as $k) {
            if ($email = data_get($snap, $k)) {
                return ['email' => $email];
            }
        }

        // acteur -> email
        foreach (['owner_acteur_code','demandeur_acteur_code','chef_projet_code','requester_acteur_code','created_by_acteur_code'] as $k) {
            if ($code = data_get($snap, $k)) {
                if ($c = $this->contactFromActorCode($code)) return $c;
            }
        }

        // user -> email
        foreach (['owner_user_id','demandeur_user_id','requester_user_id','created_by','user_id'] as $k) {
            if ($uid = data_get($snap, $k)) {
                if ($email = User::whereKey($uid)->value('email')) {
                    $codeActeur = User::with('acteur:code_acteur')->find($uid)?->acteur?->code_acteur;
                    return ['email' => $email, 'code' => $codeActeur];
                }
            }
        }

        return null;
    }
}
