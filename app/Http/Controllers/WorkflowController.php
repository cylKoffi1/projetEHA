<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function validerEtape(Request $request, $id)
{
    $decision = ValidationDecision::create([
        'demande_id' => $id,
        'etape_id' => $request->etape_id,
        'valideur_id' => Auth::id(),
        'statut_id' => 2, // validé
        'commentaire' => $request->commentaire
    ]);

    // ✅ Envoi automatique d'une notification
    NotificationService::envoyerNotification($decision->demande->utilisateur_id, "Votre validation a été acceptée.");

    return response()->json(['message' => 'Validation enregistrée', 'decision' => $decision]);
}
}
