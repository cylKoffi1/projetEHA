<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Synthèse – {{ $projet->code_projet }}</title>
<style>
  body{font-family:'DejaVu Sans',sans-serif;font-size:12px;color:#333;margin:20px}
  h1{font-size:18px;margin:0 0 6px}
  .kpi{display:flex;gap:10px;margin:10px 0}
  .card{border:1px solid #ddd;padding:10px;border-radius:6px}
  .grid{display:flex;gap:10px}
  .col{flex:1}
  table{width:100%;border-collapse:collapse;margin-top:8px}
  th,td{border:1px solid #eee;padding:6px;vertical-align:top}
  th{background:#f6f7fb}
  .badge{padding:2px 6px;border-radius:4px;background:#e8f1ff;display:inline-block}
</style>
</head>
<body>
  <h1>{{ $projet->libelle_projet }}</h1>
  <div>Code : <strong>{{ $projet->code_projet }}</strong> |
       Statut : <span class="badge">{{ $projet->statuts?->statut?->libelle ?? 'ND' }}</span></div>

  <div class="kpi">
    <div class="card">Budget<br><strong>{{ number_format($projet->cout_projet,0,',',' ') }} {{ $projet->code_devise }}</strong></div>
    <div class="card">Période<br>
      <strong>{{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d/m/Y') }}
        → {{ \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') }}</strong>
    </div>
    <div class="card">Durée<br>
      <strong>{{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->diffInDays($projet->date_fin_prevue) }} j</strong>
    </div>
  </div>

  <div class="grid">
    <div class="col card">
      <strong>Intervenants</strong>
      <div>MOA : {{ $projet->maitreOuvrage?->acteur?->libelle_court ?? '—' }}</div>
      <div>MOE :
        @if($projet->maitresOeuvre->count())
          {{ $projet->maitresOeuvre->pluck('acteur.libelle_court')->filter()->join(', ') }}
        @else — @endif
      </div>
    </div>
    <div class="col card">
      <strong>Financements</strong>
      <table>
        <thead><tr><th>Bailleur</th><th>Montant</th><th>Devise</th></tr></thead>
        <tbody>
        @forelse($projet->financements as $f)
          <tr>
            <td>{{ $f->bailleur?->libelle_court ?? '—' }}</td>
            <td style="text-align:right">{{ number_format($f->montant_finance,0,',',' ') }}</td>
            <td>{{ $f->devise }}</td>
          </tr>
        @empty
          <tr><td colspan="3">Aucun financement</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card" style="margin-top:10px">
    <strong>Actions majeures</strong>
    <ul style="margin:6px 0 0 16px">
      @foreach($projet->actions->take(5) as $a)
        <li>{{ $a->Action_mener }} ({{ $a->Quantite }} {{ $a->Unite }})</li>
      @endforeach
    </ul>
  </div>
</body>
</html>
