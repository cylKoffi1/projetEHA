{{-- resources/views/approbations/history.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    /* En-tête collant pour gros historiques */
    .table-sticky thead th { position: sticky; top: 0; z-index: 2; background: #f8f9fa; }

    /* Chips / badges soft */
    .badge-chip{display:inline-block;padding:.15rem .5rem;border-radius:999px;font-size:12px;font-weight:600}

    /* Couleurs d’action */
    .chip-action-APPROUVER { background:#e6f4ea; color:#137333; } /* vert soft */
    .chip-action-REJETER   { background:#fce8e6; color:#c5221f; } /* rouge soft */
    .chip-action-DELEGUER  { background:#e8f0fe; color:#174ea6; } /* bleu soft */
    .chip-action-COMMENTER { background:#fff4e5; color:#8a6d3b; } /* orange soft */

    /* Couleurs de statuts */
    .chip-status-APPROUVE { background:#e6f4ea; color:#137333; }
    .chip-status-REJETE   { background:#fce8e6; color:#c5221f; }
    .chip-status-EN_COURS { background:#fff8e1; color:#8d6e00; }
    .chip-status-PENDING  { background:#eceff1; color:#455a64; }
    .chip-status-SAUTE    { background:#e0f7fa; color:#006064; }

    .text-muted-700{color:#6b7280}
    .nowrap{white-space:nowrap}
    /* Badges module / type */
    .chip-module { background:#e0f2fe; color:#0c4a6e; }  /* bleu clair */
    .chip-type   { background:#f3e8ff; color:#6b21a8; }  /* violet clair */

    /* Badges pour identifiants/étape (optionnel) */
    .chip-id     { background:#f1f5f9; color:#334155; }  /* gris clair */
    .chip-step   { background:#fff7ed; color:#9a3412; }  /* orange clair */

    /* Commentaire sur une ligne (ellipsis) + infobulle pour le texte complet */
    .comment-ellipsis{
        max-width: 360px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    @media (max-width: 992px){ .comment-ellipsis{ max-width: 220px; } }
</style>
@endpush

@section('content')
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h5 mb-0">Historique d’approbation</h1>
       
    </div>


    {{-- Tableau --}}
    <div class="table-responsive" >
        <table class="table table-sm align-middle table-sticky">
            <thead class="table-light">
                <tr>
                    <th class="nowrap">Date</th>
                    <th>Action</th>
                    <th>Acteur</th>
                    <th>Instance</th>
                    <th>Étape</th>
                    <th>Workflow</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $r)
                @php
                    $actorLabel = trim(($r->actor_short ?? '').' '.($r->actor_long ?? ''));
                    if ($actorLabel === '') $actorLabel = $r->actor_code ?: '—';

                    $actionCode = $r->action_code ?? '—';
                    $instanceStatus = $r->instance_status ?? '—';
                    $stepStatus = $r->step_status ?? '—';

                    $chipActionClass   = 'chip-action-'.preg_replace('/[^A-Z_]/','',$actionCode);
                    $chipInstClass     = 'chip-status-'.preg_replace('/[^A-Z_]/','',$instanceStatus);
                    $chipStepClass     = 'chip-status-'.preg_replace('/[^A-Z_]/','',$stepStatus);

                    $commentFull = (string)($r->action_comment ?? '');
                @endphp
                <tr>
                    <td class="nowrap small">
                        {{ \Carbon\Carbon::parse($r->action_at)->format('d/m/Y H:i') }}
                    </td>

                    <td>
                        <span class="badge-chip {{ $chipActionClass }}">{{ $actionCode }}</span>
                    </td>

                    <td>
                        <div>{{ $actorLabel }}</div>
                        <div class="text-muted-700 small">{{ $r->actor_code ?: '—' }}</div>
                    </td>

                    <td>
                        <div class="mb-1">
                            <span class="badge-chip chip-module">{{ $r->module_code }}</span>
                            <span class="badge-chip chip-type">{{ $r->type_cible }}</span>
                            <span class="badge-chip chip-id">#{{ $r->id_cible }}</span>
                        </div>
                        <div class="small">
                            Statut instance :
                            <span class="badge-chip {{ $chipInstClass }}">{{ $instanceStatus }}</span>
                        </div>
                    </td>


                    <td class="small">
                        Étape : {{ $r->step_id }}<br>
                        Statut <span class="badge-chip {{ $chipStepClass }}">{{ $stepStatus }}</span>
                    </td>

                    <td class="small">
                        <div>{{ $r->workflow_nom ?? '—' }}
                            <span class="text-muted-700">v{{ $r->version ?? '—' }}</span>
                        </div>
                    </td>

                    <td class="small">
                        @if($commentFull !== '')
                            {{-- Ellipsis en cellule + title pour voir la totalité au survol --}}
                            <div class="comment-ellipsis" title="{{ $commentFull }}">
                                {{ $commentFull }}
                            </div>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="text-muted">Aucun résultat pour ces filtres.</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
    // Active les tooltips Bootstrap si besoin (utilisés sur les titles)
    document.addEventListener('DOMContentLoaded', () => {
        const hasBootstrap = window.bootstrap && typeof bootstrap.Tooltip === 'function';
        if (hasBootstrap) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
    });
</script>

@endsection