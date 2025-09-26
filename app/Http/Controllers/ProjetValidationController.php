<?php

namespace App\Http\Controllers;

use App\Models\EtudeProject;
use App\Models\ProjetApprobation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationValidationProjet;
use App\Mail\ProjetRefuseNotification;
use App\Models\Approbateur;
use App\Models\Projet;
use App\Models\Ecran;
use Exception;
use Illuminate\Support\Facades\DB;

class ProjetValidationController extends Controller
{
    public function index(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        try {
            $code_acteur = auth()->user()->acteur_id;
            $country = session('pays_selectionne');
            $group = session('projet_selectionne');


            $projets = EtudeProject::join('projets', 'etudeprojects.code_projet', '=', 'projets.code_projet')
                ->join('projet_statut', 'projets.code_projet', '=', 'projet_statut.code_projet')
                ->where('etudeprojects.code_projet', 'like', $country . $group . '%')
                ->where('etudeprojects.valider', 0)
                ->where('projet_statut.type_statut', 1)
               
                ->select('etudeprojects.*')
                ->get();

            
        
            return view('etudes_projets.validation.validation', compact('projets', 'ecran'));
        } catch (Exception $e) {
            Log::error('Erreur chargement page validation projets : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement des projets.');
        }
    }

    public function show($codeProjet)
    {
        try {
            $etude = EtudeProject::with([
                'projet',
                'projet.localisations.localite.decoupage',
                'projet.infrastructures.infra.valeursCaracteristiques', // Charger les caractéristiques ici
                'projet.actions',
                'projet.maitreOuvrage',
                'projet.maitresOeuvre',
                'projet.financements',
                'projet.documents',
                'projet.statuts',
                'approbations.approbateur.acteur',
                'approbations.statutValidation',
                'projet.beneficiairesActeurs.acteur',
                'projet.beneficiairesLocalites.localite',
                'projet.beneficiairesInfrastructures.infrastructure',
            ])->where('codeEtudeProjets', $codeProjet)->firstOrFail();
    
            if (ProjetApprobation::where('codeEtudeProjets', $codeProjet)->count() === 0) {
                $this->genererApprobations($codeProjet);
                $etude->load('approbations.approbateur.acteur', 'approbations.statutValidation');
            }
    
            $approbations = ProjetApprobation::with(['approbateur.acteur', 'statutValidation'])
                ->where('codeEtudeProjets', $codeProjet)
                ->orderBy('num_ordre')
                ->get();
    
            return view('etudes_projets.validation.show', compact('etude', 'approbations'));
        } catch (Exception $e) {
            Log::error("Erreur affichage projet [{$codeProjet}] : " . $e->getMessage());
            return back()->with('error', 'Impossible de charger les détails du projet.');
        }
    }
    

    private function genererApprobations($codeEtudeProjets)
    {
        try {
            $listeApprobateurs = Approbateur::orderBy('numOrdre')
            ->where('codePays', session('pays_selectionne'))
            ->where('groupeProjetId', session('projet_selectionne'))->get();

            foreach ($listeApprobateurs as $appro) {
                ProjetApprobation::create([
                    'codeEtudeProjets' => $codeEtudeProjets,
                    'code_acteur' => $appro->code_acteur,
                    'num_ordre' => $appro->numOrdre,
                    'statut_validation_id' => 1,
                ]);
            }
        } catch (Exception $e) {
            Log::error("Erreur génération approbations pour [{$codeEtudeProjets}] : " . $e->getMessage());
        }
    }

    public function valider(Request $request, $codeProjet)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        return DB::transaction(function () use ($codeProjet, $ecran) {
            try {
                $acteurId = auth()->user()->acteur_id ?? null;
                Log::info('[VALIDATION] Début', ['codeEtudeProjets' => $codeProjet, 'acteur_id' => $acteurId]);

                $approbation = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                    ->where('code_acteur', $acteurId)
                    ->first();

                if (!$approbation) {
                    Log::warning('[VALIDATION] Aucune approbation pour cet acteur, génération…');
                    $this->genererApprobations($codeProjet);

                    $approbation = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                        ->where('code_acteur', $acteurId)
                        ->first();

                    if (!$approbation) {
                        return back()->with('error', 'Aucune approbation trouvée même après génération.');
                    }
                }

                $precedents = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                    ->where('num_ordre', '<', $approbation->num_ordre)
                    ->where('statut_validation_id', '!=', 2) // 2=validé
                    ->exists();

                if ($precedents) {
                    return back()->with('error', 'Les approbateurs précédents doivent valider d’abord.');
                }

                $approbation->update([
                    'statut_validation_id' => 2,
                    'approved_at'          => now(),
                    'is_approved'          => 1,
                ]);

                // Charger l'étude/projet pour mail & état
                $etude = EtudeProject::with('projet')
                    ->where('codeEtudeProjets', $codeProjet)->firstOrFail();

                // approbateur suivant
                $suivant = ProjetApprobation::with('approbateur.acteur')
                    ->where('codeEtudeProjets', $codeProjet)
                    ->where('num_ordre', '>', $approbation->num_ordre)
                    ->orderBy('num_ordre')
                    ->first();

                if ($suivant && $suivant->approbateur && $suivant->approbateur->acteur && $suivant->approbateur->acteur->email) {
                    $dest = $suivant->approbateur->acteur->email;
                    Log::info('[VALIDATION] Notification au suivant', [
                        'dest' => $dest,
                        'num_ordre' => $suivant->num_ordre
                    ]);

                    Mail::to($dest)->send(
                        new NotificationValidationProjet(
                            $codeProjet,
                            $etude->projet->libelle_projet ?? 'Projet',
                            auth()->user()->acteur?->libelle_long ?? 'Un approbateur'
                        )
                    );
                } else {
                    // Tous validés → marquer l’étude
                    $etude->valider = true; // grâce à la clé primaire correcte, WHERE codeEtudeProjets=...
                    $etude->save();
                    Log::info('[VALIDATION] Tous validés — étude marquée validée', ['codeEtudeProjets' => $codeProjet]);
                }

                return redirect()->route('projets.validation.index', ['ecran_id' => $ecran->id])->with('success', 'Projet validé.');
            } catch (\Throwable $e) {
                Log::error('[VALIDATION] ERREUR — rollback', [
                    'codeEtudeProjets' => $codeProjet,
                    'err' => $e->getMessage()
                ]);
                throw $e; // rollback automatique
            }
        });
    }

    
    

    public function refuser(Request $request, $codeProjet)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        try {
            $request->validate([
                'commentaire_refus' => 'required|string|max:1000',
            ]);
    
            $approbation = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                ->where('code_acteur', auth()->user()->acteur_id)
                ->firstOrFail();
    
            $approbation->update([
                'statut_validation_id' => 3,
                'commentaire_refus'    => $request->commentaire_refus,
            ]);
    
            $emails = ProjetApprobation::with('approbateur.acteur')
                ->where('codeEtudeProjets', $codeProjet)
                ->get()
                ->pluck('approbateur.acteur.email')
                ->filter()
                ->values()
                ->toArray();
    
            $etude = EtudeProject::with('projet.maitreOuvrage', 'projet.maitresOeuvre')
                ->where('codeEtudeProjets', $codeProjet)->first();
    
            if ($etude?->projet?->maitreOuvrage?->email) {
                $emails[] = $etude->projet->maitreOuvrage->email;
            }
            foreach ($etude->projet->maitresOeuvre ?? [] as $moe) {
                if (!empty($moe->email)) $emails[] = $moe->email;
            }
            $emails = array_values(array_unique(array_filter($emails)));
    
            Log::info('[REFUS] Envoi notification refus', [
                'codeEtudeProjets' => $codeProjet,
                'nb_dest' => count($emails),
                'dest' => $emails,
            ]);
    
            if (!empty($emails)) {
                Mail::to($emails)->send(
                    new ProjetRefuseNotification(
                        $codeProjet,
                        $etude->projet->libelle_projet ?? 'Projet',
                        $request->commentaire_refus,
                        auth()->user()->acteur?->libelle_long ?? 'Un approbateur'
                    )
                );
            } else {
                Log::warning('[REFUS] Aucun destinataire email trouvé', ['codeEtudeProjets' => $codeProjet]);
            }
    
            return redirect()->route('projets.validation.index', ['ecran_id' => $ecran->id])->with('error', 'Projet refusé.');
        } catch (Exception $e) {
            Log::error("[REFUS] Erreur refus projet [{$codeProjet}] : " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erreur lors du refus du projet.');
        }
    }
    
}
