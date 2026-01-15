@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ============================================================
         TITRE
    ============================================================ --}}
    <h4 class="mb-3">Détail — Finances des Projets</h4>
    <p class="text-muted">
        Montants financiers des projets correspondant aux filtres sélectionnés dans le tableau de bord.
    </p>


    {{-- ============================================================
         CARD PRINCIPALE
    ============================================================ --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Liste des Projets et Montants</h6>

                {{-- Bouton export Excel ou PDF si tu le veux --}}
                {{-- <a href="#" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a> --}}
            </div>
        </div>

        <div class="card-body">

            {{-- ===================== TABLE ===================== --}}
            <div class="table-responsive">
                <table id="tbDetailProjetFinance" class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Code Projet</th>
                            <th>Désignation</th>
                            <th>Sous Domaine</th>
                            <th>Dernier Statut</th>
                            <th>Type</th>
                            <th>Montant (CFA)</th>
                            <th>Devise</th>
                            <th>Localité</th>
                            <th>Date Dernier Statut</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($projets as $p)
                            @php
                                $dernier   = $p->dernierStatut->statut->libelle ?? '-';
                                $dateLast  = $p->dernierStatut->date_statut ?? null;
                            @endphp

                            <tr>
                                <td class="fw-bold">{{ $p->code_projet }}</td>

                                <td>{{ $p->designation ?? '-' }}</td>

                                <td>{{ $p->sousDomaine->libelle ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $dernier }}
                                    </span>
                                </td>

                                {{-- Type Public / Privé --}}
                                <td>
                                    @php
                                        $t = substr($p->code_projet,6,1);
                                        $type = $t=='1' ? 'Public' : ($t=='2'?'Privé':'-');
                                    @endphp
                                    {{ $type }}
                                </td>

                                {{-- MONTANT FINANCIER --}}
                                <td class="text-end fw-bold">
                                    {{ number_format($p->cout_projet ?? 0, 0, ',', ' ') }}
                                </td>

                                <td>{{ $p->devise->libelle ?? '-' }}</td>

                                <td>
                                    @if($p->localisations)
                                        @foreach($p->localisations as $loc)
                                            <div>{{ $loc->localite->nom ?? '' }}</div>
                                        @endforeach
                                    @endif
                                </td>

                                <td>
                                    @if($dateLast)
                                        {{ \Carbon\Carbon::parse($dateLast)->format('d/m/Y') }}
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

    $('#tbDetailProjetFinance').DataTable({
        pageLength: 25,
        ordering: true,
        responsive: true,
        language: {
            url: "/assets/datatables/french.json"
        },
        columnDefs: [
            { targets: 5, className: "text-end" } // montant
        ]
    });

});

</script>

@endsection
