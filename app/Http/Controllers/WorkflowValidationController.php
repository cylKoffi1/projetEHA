<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\DemandeValidation;
use App\Models\ValidationDecision;
use App\Models\WorkflowEtape;
use App\Models\Notification;
use App\Models\ValidationStatut;
use App\Models\ElementValidation;
use App\Models\User;
use App\Mail\NotificationMail;
use App\Models\Ecran;

class WorkflowValidationController extends Controller
{
    public function afficherValidation(Request $request)
    {
        // Récupère l'ID de l'écran depuis la requête
        $ecran = Ecran::find($request->input('ecran_id'));

        // Retourne la vue avec l'écran
        return view('etudes_projets.workflow', compact('ecran'));
    }
    /**
     * 📌 1️⃣ Soumettre une demande de validation
     */
    public function soumettreDemande(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:validation_types,id',
            'workflow_id' => 'required|exists:workflows,id',
            'element_id' => 'required|exists:elements_validation,id',
        ]);

        $demande = DemandeValidation::create([
            'type_id' => $request->type_id,
            'workflow_id' => $request->workflow_id,
            'element_id' => $request->element_id,
            'statut_id' => ValidationStatut::where('libelle', 'en_attente')->first()->id,
            'utilisateur_id' => Auth::id(),
        ]);

        $this->envoyerNotification($demande->utilisateur_id, "Votre demande de validation a été soumise.");

        return response()->json(['message' => 'Demande soumise avec succès', 'demande' => $demande]);
    }

    /**
     * 📌 2️⃣ Afficher la liste des demandes en attente pour un utilisateur
     */
    public function demandesEnAttente()
    {
        $user = Auth::user();
        $demandes = DemandeValidation::where('statut_id', ValidationStatut::where('libelle', 'en_attente')->first()->id)
            ->whereHas('workflow.etapes', function ($query) use ($user) {
                $query->where('role_responsable', $user->role);
            })
            ->get();

        return response()->json(['demandes' => $demandes]);
    }

    /**
     * 📌 3️⃣ Valider une étape du workflow
     */
    public function validerEtape(Request $request, $id)
    {
        $request->validate([
            'etape_id' => 'required|exists:workflow_etapes,id',
            'commentaire' => 'nullable|string',
        ]);

        $demande = DemandeValidation::findOrFail($id);
        $etape = WorkflowEtape::findOrFail($request->etape_id);

        if (Auth::user()->role !== $etape->role_responsable) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de valider cette étape.'], 403);
        }

        ValidationDecision::create([
            'demande_id' => $demande->id,
            'etape_id' => $etape->id,
            'valideur_id' => Auth::id(),
            'statut_id' => ValidationStatut::where('libelle', 'validé')->first()->id,
            'commentaire' => $request->commentaire
        ]);

        // Vérifier si toutes les étapes sont validées
        $totalEtapes = WorkflowEtape::where('workflow_id', $demande->workflow_id)->count();
        $etapesValidees = ValidationDecision::where('demande_id', $demande->id)
            ->where('statut_id', ValidationStatut::where('libelle', 'validé')->first()->id)
            ->count();

        if ($etapesValidees >= $totalEtapes) {
            $demande->update(['statut_id' => ValidationStatut::where('libelle', 'validé')->first()->id]);
            $this->envoyerNotification($demande->utilisateur_id, "Votre demande de validation a été approuvée.");
        }

        return response()->json(['message' => 'Validation enregistrée avec succès']);
    }

    /**
     * 📌 4️⃣ Rejeter une demande de validation
     */
    public function rejeterDemande(Request $request, $id)
    {
        $request->validate([
            'etape_id' => 'required|exists:workflow_etapes,id',
            'commentaire' => 'required|string',
        ]);

        $demande = DemandeValidation::findOrFail($id);
        $etape = WorkflowEtape::findOrFail($request->etape_id);

        if (Auth::user()->role !== $etape->role_responsable) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de rejeter cette demande.'], 403);
        }

        ValidationDecision::create([
            'demande_id' => $demande->id,
            'etape_id' => $etape->id,
            'valideur_id' => Auth::id(),
            'statut_id' => ValidationStatut::where('libelle', 'rejeté')->first()->id,
            'commentaire' => $request->commentaire
        ]);

        $demande->update(['statut_id' => ValidationStatut::where('libelle', 'rejeté')->first()->id]);

        $this->envoyerNotification($demande->utilisateur_id, "Votre demande de validation a été rejetée.");

        return response()->json(['message' => 'Demande rejetée avec succès']);
    }

    /**
     * 📌 5️⃣ Envoyer une notification automatique
     */
    private function envoyerNotification($utilisateur_id, $message)
    {
        Notification::create([
            'utilisateur_id' => $utilisateur_id,
            'message' => $message,
            'statut_id' => 1, // "non_lu"
            'envoye' => 'non'
        ]);

        $user = User::find($utilisateur_id);
        if ($user) {
            Mail::to($user->email)->send(new NotificationMail($message));
            Notification::where('utilisateur_id', $utilisateur_id)
                ->update(['envoye' => 'oui']);
        }
    }

    /**
     * 📌 6️⃣ Afficher toutes les demandes de validation d'un utilisateur
     */
    public function mesDemandes()
    {
        $demandes = DemandeValidation::where('utilisateur_id', Auth::id())->get();
        return response()->json(['demandes' => $demandes]);
    }
}
