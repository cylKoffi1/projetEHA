<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Multiple</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .last-page {
            page-break-after: auto;
        }
    </style>
</head>
<body>
    @foreach($codes as $code)
        @if($type === 'projet')
            @php $projet = \App\Models\Projet::with([
                'localisations.localite.decoupage',
                'infrastructures.valeursCaracteristiques.caracteristique.unite',
                'actions',
                'financements.bailleur',
                'documents',
                'maitreOuvrage.acteur',
                'maitresOeuvre.acteur',
                'statuts.statut',
                'ChefProjet.acteur'
            ])->where('code_projet', $code)->first(); @endphp
            @include('pdf.projet', ['projet' => $projet])
        
        
            @elseif($type === 'acteur')
                @php
                    $chefs = \App\Models\Acteur::with('type')->whereHas('projetsChef', fn($q) => $q->where('code_projet', $code))->get();
                    $moas  = \App\Models\Acteur::with('type')->whereHas('projetsOuvrage', fn($q) => $q->where('code_projet', $code))->get();
                    $moes  = \App\Models\Acteur::with('type')->whereHas('projetsOeuvre', fn($q) => $q->where('code_projet', $code))->get();
                    $bailleurs = \App\Models\Acteur::with('type')->whereHas('projetsFinances', fn($q) => $q->where('code_projet', $code))->get();
                    $approbateurs = \App\Models\Acteur::with('type')->whereHas('projetsApprouves.etude', fn($q) => $q->where('code_projet', $code))->get();

                    $acteurs = $chefs
                        ->merge($moas)
                        ->merge($moes)
                        ->merge($bailleurs)
                        ->merge($approbateurs)
                        ->unique('code_acteur')
                        ->values();
                @endphp

                @foreach($acteurs as $acteur)
                    @include('pdf.acteur', compact('acteur'))
                    @if(!$loop->last)
                        <div class="page-break"></div>
                    @endif
                @endforeach


        
        
        @elseif($type === 'contrat')
            @php
            $contrat = \App\Models\Controler::with([
                'projet.localisations.localite.decoupage',
                'projet.maitreOuvrage.acteur',
                'acteur'
            ])
            ->where('code_projet', $code)
            ->orderByDesc('updated_at')
            ->first();
            @endphp

            @if($contrat)
                @include('contracts.fiche_chef_projet', ['contrat' => $contrat])
            @endif

        @elseif($type === 'infrastructure')
            @include('pdf.infrastructure', ['infrastructure' => \App\Models\Infrastructure::withAllRelations()->where('code', $code)->first()])
        @endif
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @else
            <div class="last-page"></div>
        @endif
    @endforeach
</body>
</html>

<!--

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Multiple</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .last-page {
            page-break-after: auto;
        }
    </style>
</head>
<body>
    @foreach($codes as $code)
        @if($type === 'projet')
            @php
                $projet = \App\Models\Projet::with([
                    'localisations.localite.decoupage',
                    'infrastructures.valeursCaracteristiques.caracteristique.unite',
                    'actions',
                    'financements.bailleur',
                    'documents',
                    'maitreOuvrage.acteur',
                    'maitresOeuvre.acteur',
                    'statuts.statut',
                    'ChefProjet.acteur'
                ])->where('code_projet', $code)->first();
            @endphp

            @if($projet)
                @include('pdf.projet', ['projet' => $projet])
            @endif

        @elseif($type === 'acteur')
            @php
                $relations = [
                    'type',
                    'secteurActiviteActeur.secteur',
                    'projetsChef.projet',
                    'projetsOuvrage.projet',
                    'projetsOeuvre.projet',
                    'projetsFinances.projet',
                    'projetsApprouves.etude.projet'
                ];

                $chefs = \App\Models\Acteur::with($relations)
                    ->whereHas('projetsChef', fn($q) => $q->where('code_projet', $code))->get();
                $moas = \App\Models\Acteur::with($relations)
                    ->whereHas('projetsOuvrage', fn($q) => $q->where('code_projet', $code))->get();
                $moes = \App\Models\Acteur::with($relations)
                    ->whereHas('projetsOeuvre', fn($q) => $q->where('code_projet', $code))->get();
                $bailleurs = \App\Models\Acteur::with($relations)
                    ->whereHas('projetsFinances', fn($q) => $q->where('code_projet', $code))->get();
                $approbateurs = \App\Models\Acteur::with($relations)
                    ->whereHas('projetsApprouves.etude', fn($q) => $q->where('code_projet', $code))->get();

                $acteurs = $chefs->merge($moas)->merge($moes)->merge($bailleurs)->merge($approbateurs)
                    ->unique('code_acteur')->values();
            @endphp

            @if($acteurs->isEmpty())
                <script>alert("Aucun acteur trouvé pour le projet {{ $code }}.");</script>
            @endif

            @foreach($acteurs as $acteur)
                @include('pdf.acteur', compact('acteur'))
                @if(!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach

        @elseif($type === 'contrat')
            @php
                $contrat = \App\Models\Controler::with([
                    'projet.localisations.localite.decoupage',
                    'projet.maitreOuvrage.acteur',
                    'acteur'
                ])
                ->where('code_projet', $code)
                ->orderByDesc('updated_at')
                ->first();
            @endphp

            @if($contrat)
                @include('contracts.fiche_chef_projet', ['contrat' => $contrat])
            @endif

        @elseif($type === 'infrastructure')
            @php
                $infra = \App\Models\Infrastructure::withAllRelations()
                    ->where('code', $code)->first();
            @endphp

            @if($infra)
                @include('pdf.infrastructure', ['infrastructure' => $infra])
            @endif
        @endif

        @if(!$loop->last)
            <div class="page-break"></div>
        @else
            <div class="last-page"></div>
        @endif
    @endforeach
</body>
</html>
  

-->