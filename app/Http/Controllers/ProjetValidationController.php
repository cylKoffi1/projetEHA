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
use Exception;

class ProjetValidationController extends Controller
{
    public function index()
    {
        try {
            $code_acteur = auth()->user()->acteur_id;
            $country = session('pays_sel
            ectionne');
            $group = session('projet_selectionne');


            $projets = EtudeProject::join('projets', 'etudeprojects.code_projet', '=', 'projets.code_projet')
                ->join('projet_statut', 'projets.code_projet', '=', 'projet_statut.code_projet')
                ->where('etudeprojects.code_projet', 'like', $country . $group . '%')
                ->where('etudeprojects.valider', 0)
                ->where('projet_statut.type_statut', 1)
               
                ->select('etudeprojects.*')
                ->get();

            
        
            return view('etudes_projets.validation.validation', compact('projets'));
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
                'projet.infrastructures.infra',
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
        try {
            $approbation = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                ->where('code_acteur', auth()->user()->acteur_id)
                ->first();
    
            if (!$approbation) {
                $this->genererApprobations($codeProjet);
    
                $approbation = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                    ->where('code_acteur', auth()->user()->acteur_id)
                    ->first();
    
                if (!$approbation) {
                    return back()->with('error', 'Aucune approbation trouvée même après génération.');
                }
            }
    
            $precedents = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                ->where('num_ordre', '<', $approbation->num_ordre)
                ->where('statut_validation_id', '!=', 2)
                ->exists();
    
            if ($precedents) {
                return back()->with('error', 'Les approbateurs précédents doivent valider d’abord.');
            }
    
            $approbation->update([
                'statut_validation_id' => 2,
                'approved_at' => now(),
                'is_approved' => 1,
            ]);
    
            // Chercher l'approbateur suivant
            $suivant = ProjetApprobation::with('approbateur.acteur')
                ->where('codeEtudeProjets', $codeProjet)
                ->where('num_ordre', '>', $approbation->num_ordre)
                ->orderBy('num_ordre')
                ->first();
    
            if ($suivant && $suivant->approbateur && $suivant->approbateur->acteur) {
                // Envoyer email au suivant
                Mail::to($suivant->approbateur->acteur->email)->send(
                    new NotificationValidationProjet(
                        $codeProjet,
                        $etude->projet->libelle_projet ?? 'Projet',
                        auth()->user()->acteur?->libelle_long ?? 'Un approbateur'
                    )
                );
                
            } else {
                // ✅ TOUS ont validé
                $etude = EtudeProject::where('codeEtudeProjets', $codeProjet)->firstOrFail();
                $etude->valider = true;
                $etude->save();
            }
    
            return redirect()->route('projets.validation.index')->with('success', 'Projet validé.');
        } catch (Exception $e) {
            Log::error("Erreur validation projet [{$codeProjet}] : " . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue pendant la validation.');
        }
    }
    

    public function refuser(Request $request, $codeProjet)
    {
        try {
            $request->validate([
                'commentaire_refus' => 'required|string|max:1000',
            ]);

            $approbation = ProjetApprobation::where('codeEtudeProjets', $codeProjet)
                ->where('code_acteur', auth()->user()->acteur_id)
                ->firstOrFail();

            $approbation->update([
                'statut_validation_id' => 3,
                'commentaire_refus' => $request->commentaire_refus,
            ]);

            $emails = ProjetApprobation::with('approbateur.acteur')
                ->where('codeEtudeProjets', $codeProjet)
                ->get()
                ->pluck('approbateur.acteur.email')
                ->filter()
                ->toArray();

            $etude = EtudeProject::with('projet.maitreOuvrage', 'projet.maitresOeuvre')->where('codeEtudeProjets', $codeProjet)->first();

            if ($etude->projet->maitreOuvrage?->email) {
                $emails[] = $etude->projet->maitreOuvrage->email;
            }

            foreach ($etude->projet->maitresOeuvre ?? [] as $moe) {
                if ($moe->email) {
                    $emails[] = $moe->email;
                }
            }

            Mail::to($emails)->send(
                new ProjetRefuseNotification(
                    $codeProjet,
                    $etude->projet->libelle_projet ?? 'Projet',
                    $request->commentaire_refus,
                    auth()->user()->acteur?->libelle_long ?? 'Un approbateur'
                )
            );
            

            return redirect()->route('projets.validation.index')->with('error', 'Projet refusé.');
        } catch (Exception $e) {
            Log::error("Erreur refus projet [{$codeProjet}] : " . $e->getMessage());
            return back()->with('error', 'Erreur lors du refus du projet.');
        }
    }
}
