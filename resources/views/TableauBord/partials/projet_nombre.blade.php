{{-- Tableau Nombre de Projets --}}
<div class="card shadow-sm mt-3">
    <div class="card-header bg-white">
        <h6 class="mb-0">Liste des Projets (Nombre)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableProjetNombre" class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Code Projet</th>
                        <th>Libellé</th>
                        <th>Sous Domaine</th>
                        <th>Dernier Statut</th>
                        <th>Type</th>
                        <th>Coût (CFA)</th>
                        <th>Devise</th>
                        <th>Localité</th>
                        <th>Date Début Prévue</th>
                        <th>Date Fin Prévue</th>
                    </tr>
                </thead>
                <tbody>
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
                            $cout = is_null($projet->cout_projet) ? '—' : number_format((float)$projet->cout_projet, 0, ',', ' ');
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
                            <td style="text-align:right">{{ $cout }}</td>
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
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        $('#tableProjetNombre').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: true,
            language: {
                url: "/assets/datatables/french.json"
            }
        });
    }
});
</script>

