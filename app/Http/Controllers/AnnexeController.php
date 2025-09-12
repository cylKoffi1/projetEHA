<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\EtudeProject;
use App\Models\Infrastructure;
use App\Models\Projet;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;

class AnnexeController extends Controller
{
    public function index()
    {
        
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');
        $projets = Projet::with(['statuts.statut', 'maitreOuvrage.acteur'])
        ->where('code_projet', 'like', $country . $group . '%')                 
        ->orderBy('created_at', 'desc')
        ->get();

        return view('projets.Annexe.editionProjet', compact('projets'));
    }
    public function exportProjet($code)
    {
        $projet = Projet::with([
            'localisations.localite.decoupage',
            'infrastructures.infra.valeursCaracteristiques.caracteristique.unite',
            'actions',
            'financements.bailleur.secteurActiviteActeur.secteur', // <-- ajouter ceci
            'documents',
            'maitreOuvrage.acteur',
            'maitresOeuvre.acteur',
            'statuts.statut',
            'ChefProjet.acteur',
        ])->where('code_projet', $code)->firstOrFail();
        
        
    
        $pdf = PDF::loadView('pdf.projet', compact('projet'))
                  ->setPaper('a4', 'portrait');
    
        return $pdf->stream("projet-{$code}.pdf");
    }
    
    
    

    public function exportActeur($code)
    {
        $acteur = Acteur::with([
            'type',
            'secteurActiviteActeur.secteur',
            'projets'
        ])->where('code_acteur', $code)->firstOrFail();

        $pdf = PDF::loadView('pdf.acteur', compact('acteur'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream("acteur-{$code}.pdf");
    }

    public function exportContrat($code)
    {
        $contrat = Contrat::with([
            'projet.localisations.localite',
            'acteur',
            'employeur'
        ])->findOrFail($code);

        $pdf = PDF::loadView('pdf.contrat-chef-projet', compact('contrat'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream("contrat-{$code}.pdf");
    }


    public function exportInfrastructure($code)
    {
        $infrastructure = Infrastructure::with([
            'familleInfrastructure',
            'projetInfrastructure.projet',
            'projetInfrastructure.localisation.localite',
            'valeursCaracteristiques.caracteristique',
            'valeursCaracteristiques.unite',
            'documents'
        ])->where('code', $code)->firstOrFail();

        $pdf = PDF::loadView('pdf.infrastructure', compact('infrastructure'))
                  ->setPaper('a4', 'landscape');

        return $pdf->stream("infrastructure-{$code}.pdf");
    }

    public function exportMultiple(Request $request)
    {
        $request->validate([
            'projets' => 'required',
            'type' => 'required|in:projets,acteur,contrat,infrastructure'
        ]);
    
        $codes = json_decode($request->input('projets'), true);
        $type = $request->input('type');
    
        if (!$codes || count($codes) == 0) {
            return back()->with('error', 'Aucun projet sélectionné.');
        }
    
        $pdf = PDF::loadView('pdf.multiple', compact('codes', 'type'))
                  ->setPaper('a4', 'portrait');
    
        return $pdf->download("export-{$type}.pdf");
    }
    
    
    public function show($codeProjet)
    {
        try {
            $projet = Projet::with([
                'localisations.localite.decoupage',
                // Charger les caracs au bon endroit (sur infra) + leurs relations
                'infrastructures.infra.valeursCaracteristiques.caracteristique.unite',
                'actions',
                'maitreOuvrage.acteur',
                'maitreOuvrage.secteur',                      // ← si Posseder a une relation secteur()
                'maitresOeuvre.acteur',
                'maitresOeuvre.secteurActivite',      // nécessaire pour la vue
                'financements.bailleur',      // nécessaire pour la vue
                'documents',
                'statuts.statut',             // si tu affiches libellé du statut
                'beneficiairesActeurs.acteur',
                'beneficiairesLocalites.localite',
                'beneficiairesInfrastructures.infrastructure',
            ])->where('code_projet', $codeProjet)->firstOrFail();
    
            return view('projets.Annexe.show', compact('projet'));
        } catch (\Exception $e) {
            Log::error("Erreur affichage projet [{$codeProjet}] : " . $e->getMessage());
            return back()->with('error', 'Impossible de charger les détails du projet.');
        }
    }
    
    
}

