<?php

    return [
        // Liste de classes Eloquent autorisées à apparaître dans le picker modèle.
        // Laisser vide pour autoriser tous les modèles découverts (risqué en prod).
        'allowed_models' => [
             'App\Models\Projet',
            // 'App\Models\Infrastructure',
            // 'App\Models\BudgetDemande',
        ],
        // Cache TTL (minutes) pour la liste des modèles scannés
        'models_cache_ttl' => 60,
    ];
