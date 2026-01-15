<?php

namespace App\Http\Controllers;

use App\Models\CodificationSchema;
use Illuminate\Http\Request;

class CodificationSchemaController extends Controller
{
    public function index()
    {
        $schemas = CodificationSchema::orderBy('pays_alpha3')
            ->orderBy('entity_type')
            ->get();

        // Types déjà utilisés (affichage à droite)
        $entityTypes = CodificationSchema::select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        // Liste “officielle” pour le select
        $entityTypeOptions = [
            'projet'           => 'Projet d’infrastructure',
            'appui'            => 'Projet d’appui',
            'etude'            => 'Étude de projet',
            'infra'            => 'Infrastructure',
            'renforcement'     => 'Renforcement de capacités',
            'travaux_connexe'  => 'Travaux connexes',
        ];

        // Pays courant depuis la session (tu peux adapter si besoin)
        $paysAlpha3 = session('pays_selectionne', 'CIV');

        return view('parSpecifique.CodificationEntite', compact(
            'schemas',
            'entityTypes',
            'entityTypeOptions',
            'paysAlpha3'
        ));
    }

    public function store(Request $request)
    {
        $id = $request->input('id');

        $data = $request->validate([
            // on va écraser pays_alpha3 après avec la valeur de la session
            'entity_type'     => ['required', 'string', 'max:50'],
            'name'            => ['required', 'string', 'max:255'],
            'pattern'         => ['required', 'string', 'max:255'],
            'token_separator' => ['nullable', 'string', 'max:5'],
            'active'          => ['nullable', 'boolean'],
        ]);

        // Pays imposé par la session (no code / non modifiable)
        $data['pays_alpha3'] = session('pays_selectionne', 'CIV');

        // Checkbox actif
        $data['active'] = $request->has('active') ? 1 : 0;

        if ($id) {
            $schema = CodificationSchema::findOrFail($id);
            $schema->update($data);
            return redirect()->route('codif.index')
                ->with('success', 'Schéma de codification mis à jour avec succès.');
        } else {
            CodificationSchema::create($data);
            return redirect()->route('codif.index')
                ->with('success', 'Schéma de codification créé avec succès.');
        }
    }

    public function destroy(CodificationSchema $schema)
    {
        $schema->delete();

        return redirect()->route('codif.index')
            ->with('success', 'Schéma supprimé.');
    }
}
