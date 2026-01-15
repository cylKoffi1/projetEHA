@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ============================================================
         TITRE
    ============================================================ --}}
    <h4 class="mb-3">Détail — Nombre de Projets</h4>
    <p class="text-muted">
        Liste des projets correspondant aux filtres sélectionnés dans le tableau de bord.
    </p>


    {{-- ============================================================
         CARD PRINCIPALE
    ============================================================ --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">Liste des Projets</h6>
        </div>

        <div class="card-body">

            {{-- ===================== TABLE ===================== --}}
            <div class="table-responsive">
                <table id="tbDetailProjetNombre" class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Code Projet</th>
                            <th>Désignation</th>
                            <th>Sous Domaine</th>
                            <th>Dernier Statut</th>
                            <th>Type</th>
                            <th>Coût (CFA)</th>
                            <th>Devise</th>
                            <th>Localité</th>
                            <th>Date Dernier Statut</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($projets as $p)
                            @php
                                $dernier = $p->dernierStatut->statut->libelle ?? '-';
                                $dateDernier = $p->dernierStatut->date_statut ?? null;
                            @endphp

                            <tr>
                                <td class="fw-bold">{{ $p->code_projet }}</td>

                                <td>{{ $p->designation ?? '-' }}</td>

                                <td>{{ $p->sousDomaine->libelle ?? '-' }}</td>

                                {{-- Dernier statut --}}
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $dernier }}
                                    </span>
                                </td>

                                {{-- Type Public / Privé via SUBSTR --}}
                                <td>
                                    @php
                                        $t = substr($p->code_projet,6,1);
                                        $type = $t=='1' ? 'Public' : ($t=='2'?'Privé':'-');
                                    @endphp
                                    {{ $type }}
                                </td>

                                {{-- Coût projet (juste information, même si on est en "nombre") --}}
                                <td>{{ number_format($p->cout_projet ?? 0,0,',',' ') }}</td>

                                {{-- Devise --}}
                                <td>{{ $p->devise->libelle ?? '-' }}</td>

                                {{-- Localités du projet --}}
                                <td>
                                    @if($p->localisations)
                                        @foreach($p->localisations as $loc)
                                            <div>{{ $loc->localite->nom ?? '' }}</div>
                                        @endforeach
                                    @endif
                                </td>

                                {{-- Date dernier statut --}}
                                <td>
                                    @if($dateDernier)
                                        {{ \Carbon\Carbon::parse($dateDernier)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div> {{-- card-body --}}
    </div> {{-- card --}}

</div> {{-- container --}}



{{-- ============================================================
     DATATABLES SCRIPT
============================================================ --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    $('#tbDetailProjetNombre').DataTable({
        pageLength: 25,
        ordering: true,
        responsive: true,
        language: {
            url: "/assets/datatables/french.json"
        }
    });

});

</script>

@endsection
