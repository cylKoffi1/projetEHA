<?php

namespace App\Support;

trait HasWorkflowBinding
{
    /** Code module libre (par défaut = nom du vendor/app) */
    public function workflowModuleCode(): string
    {
        // Par défaut : "APP" – surcharge si besoin dans le modèle
        return property_exists($this, 'workflowModule') ? $this->workflowModule : 'APP';
    }

    /** Type d’objet libre (par défaut = nom de classe) */
    public function workflowTypeCode(): string
    {
        return property_exists($this, 'workflowType') ? $this->workflowType : class_basename($this);
    }

    /** Identifiant business pour la liaison (par défaut = id) */
    public function workflowTargetId(): string
    {
        return (string) $this->getKey();
    }

    /** Snapshot arbitraire pour les règles/approbateurs dynamiques */
    public function workflowSnapshot(): array
    {
        return []; // surcharge dans chaque modèle pour exposer les champs utiles
    }
}
