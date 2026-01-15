{{-- Tableau Finance des Projets --}}
<div class="card shadow-sm mt-3">
    <div class="card-header bg-white">
        <h6 class="mb-0">Liste des Projets (Finance)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableProjetFinance" class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Code Projet</th>
                        <th>Libellé</th>
                        <th>Sous Domaine</th>
                        <th>Dernier Statut</th>
                        <th>Type</th>
                        <th>Montant (CFA)</th>
                        <th>Devise</th>
                        <th>Localité</th>
                        <th>Date Début Prévue</th>
                        <th>Date Fin Prévue</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalMontant = 0;
                    @endphp
                    @forelse($statutsProjets as $projet)
                        @php
                            $statutLib = $projet->dernierStatut?->statut?->libelle ?? '—';
                            $sousDom = $projet->sousDomaine?->lib_sous_domaine ?? '—';
                            $localites = $projet->localisations
                                ->map(fn($pl) => optional($pl->localite)->libelle ?? optional($pl->localite)->nom)
                                ->filter()
                                ->unique()
                                ->implode(', ');
                            $localites = $localites !== '' ? $localites : '—';
                            $dd = $projet->date_demarrage_prevue ? \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d-m-Y') : '—';
                            $df = $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d-m-Y') : '—';
                            $montant = (float)($projet->cout_projet ?? 0);
                            $totalMontant += $montant;
                            $montantFormatted = $montant > 0 ? number_format($montant, 0, ',', ' ') : '—';
                            $devise = $projet->devise?->code_long ?? '—';
                            $typeChar = substr($projet->code_projet, 6, 1);
                            $type = $typeChar == '1' ? 'Public' : ($typeChar == '2' ? 'Privé' : '—');
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $projet->code_projet }}</td>
                            <td>{{ $projet->libelle_projet ?? '—' }}</td>
                            <td>{{ $sousDom }}</td>
                            <td><span class="badge bg-primary">{{ $statutLib }}</span></td>
                            <td>{{ $type }}</td>
                            <td class="text-end fw-bold">{{ $montantFormatted }}</td>
                            <td>{{ $devise }}</td>
                            <td>{{ $localites }}</td>
                            <td>{{ $dd }}</td>
                            <td>{{ $df }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Aucun projet trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <td colspan="5" class="text-end fw-bold">Total:</td>
                        <td class="text-end fw-bold">{{ number_format($totalMontant, 0, ',', ' ') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        $('#tableProjetFinance').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: true,
            language: {
                url: "/assets/datatables/french.json"
            },
            columnDefs: [
                { targets: 5, className: "text-end" }
            ]
        });
    }
});
</script>

