{{-- resources/views/parSpecifique/acteurs/_list.blade.php --}}
@php
    use Illuminate\Support\Str;
    $view = request('view', 'cards');
@endphp

@if($view === 'table')
    {{-- ===== VUE TABLEAU ===== --}}
    <div id="tableView" class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle table-striped table-bordered" id="table1" style="width:100%">
                <thead>
                    <tr>
                        <th>Acteur</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Pays</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($acteurs as $acteur)
                    @php
                        $photoUrl = $acteur->photo_url;
                        $displayName = trim(($acteur->libelle_court ?? '') . ' ' . ($acteur->libelle_long ?? ''));
                        if (!$displayName) { $displayName = trim(($acteur->nom ?? '') . ' ' . ($acteur->prenom ?? '')); }
                        $parts = preg_split('/\s+/', $displayName ?: '', -1, PREG_SPLIT_NO_EMPTY);
                        $initials = strtoupper((function($p){ $a = isset($p[0]) ? mb_substr($p[0],0,1) : ''; $b = isset($p[1]) ? mb_substr($p[1],0,1) : ''; return ($a.$b) ?: 'NA'; })($parts));
                        $palette = ['#fde68a','#bbf7d0','#c7d2fe','#fecaca','#fbcfe8','#bfdbfe','#e9d5ff'];
                        $seed = is_numeric($acteur->code_acteur) ? intval($acteur->code_acteur) : crc32($displayName ?: 'seed');
                        $bg = $palette[$seed % count($palette)];
                    @endphp
                    <tr class="actor-row"
                        data-name="{{ Str::lower(($acteur->libelle_court ?? '') . ' ' . ($acteur->libelle_long ?? '')) }}"
                        data-email="{{ Str::lower($acteur->email ?? '') }}"
                        data-phone="{{ Str::lower($acteur->telephone ?? $acteur->telephone_mobile ?? '') }}"
                        data-typeacteur="{{ $acteur->type_acteur }}"
                        data-typefin="{{ $acteur->type_financement }}"
                        data-status="{{ $acteur->is_active ? 'active' : 'inactive' }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm {{ $photoUrl ? 'has-photo' : '' }}" style="background: {{ $photoUrl ? '#e5e7eb' : $bg }}">
                                    <img src="{{ $photoUrl ?: '' }}" alt="Photo de {{ $displayName ?: 'Acteur' }}" loading="lazy" onerror="this.style.display='none'; this.closest('.avatar')?.classList.remove('has-photo');">
                                    <span class="initials">{{ $initials }}</span>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $acteur->libelle_court ?? $acteur->nom ?? '—' }}</div>
                                    <div class="text-muted small">{{ $acteur->libelle_long ?? $acteur->prenom ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><a href="mailto:{{ $acteur->email }}">{{ $acteur->email ?: '—' }}</a></td>
                        <td>{{ $acteur->telephone ?? $acteur->telephone_mobile ?? '—' }}</td>
                        <td>{{ $acteur->pays->nom_fr_fr ?? '—' }}</td>
                        <td>{{ $acteur->type->libelle_type_acteur ?? 'Type n/d' }}</td>
                        <td>
                            @if($acteur->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @can('modifier_ecran_' . $ecran->id)
                            <a href="#" class="btn btn-sm btn-outline-primary btn-edit" data-id="{{ $acteur->code_acteur }}"><i class="bi bi-pencil-square"></i></a>
                            @endcan
                            @can('supprimer_ecran_' . $ecran->id)
                                @if ($acteur->is_active)
                                <a href="#" 
                                    class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="{{ $a->code_acteur }}"
                                    data-url="{{ route('acteurs.destroy', $a->code_acteur) }}">
                                    <i class="bi bi-trash"></i>
                                </a>
                                @else
                                <a href="#"
                                    class="btn btn-sm btn-outline-success btn-restore"
                                    data-id="{{ $a->code_acteur }}"
                                    data-url="{{ route('acteurs.restore', $a->code_acteur) }}">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                                @endif
                            @endcan
                            <form id="delete-form-{{ $acteur->code_acteur }}" action="{{ route('acteurs.destroy', $acteur->code_acteur) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                            <form id="restore-form-{{ $acteur->code_acteur }}" action="{{ route('acteurs.restore', ['id' => $acteur->code_acteur, 'ecran_id' => $ecran->id]) }}" method="POST" style="display:none;">@csrf @method('PATCH')</form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun acteur trouvé.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    {{-- ===== VUE CARTES ===== --}}
    <div id="cardsView">
        <div id="cardsContainer" class="row g-3">
            @forelse ($acteurs as $acteur)
                @php
                    $photoUrl = $acteur->photo_url;
                    $displayName = trim(($acteur->libelle_court ?? '') . ' ' . ($acteur->libelle_long ?? ''));
                    if (!$displayName) { $displayName = trim(($acteur->nom ?? '') . ' ' . ($acteur->prenom ?? '')); }
                    $parts = preg_split('/\s+/', $displayName ?: '', -1, PREG_SPLIT_NO_EMPTY);
                    $initials = strtoupper((function($p){ $a = isset($p[0]) ? mb_substr($p[0],0,1) : ''; $b = isset($p[1]) ? mb_substr($p[1],0,1) : ''; return ($a.$b) ?: 'NA'; })($parts));
                    $palette = ['#fde68a','#bbf7d0','#c7d2fe','#fecaca','#fbcfe8','#bfdbfe','#e9d5ff'];
                    $seed = is_numeric($acteur->code_acteur) ? intval($acteur->code_acteur) : crc32($displayName ?: 'seed');
                    $bg = $palette[$seed % count($palette)];
                @endphp

                <div class="col-12 col-md-6 col-xl-4 actor-card-wrapper"
                     data-name="{{ Str::lower(($acteur->libelle_court ?? '') . ' ' . ($acteur->libelle_long ?? '')) }}"
                     data-email="{{ Str::lower($acteur->email ?? '') }}"
                     data-phone="{{ Str::lower($acteur->telephone ?? $acteur->telephone_mobile ?? '') }}"
                     data-typeacteur="{{ $acteur->type_acteur }}"
                     data-typefin="{{ $acteur->type_financement }}"
                     data-status="{{ $acteur->is_active ? 'active' : 'inactive' }}">
                    <div class="actor-card">
                        <div class="actor-cover"></div>
                        <div class="actor-ribbon">{{ $acteur->is_active ? 'ACTIF' : 'INACTIF' }}</div>

                        <div class="actor-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar {{ $photoUrl ? 'has-photo' : '' }}" style="background: {{ $photoUrl ? '#e5e7eb' : $bg }}">
                                    <img src="{{ $photoUrl ?: '' }}" alt="Photo de {{ $displayName ?: 'Acteur' }}" loading="lazy" onerror="this.style.display='none'; this.closest('.avatar')?.classList.remove('has-photo');">
                                    <span class="initials">{{ $initials }}</span>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="me-2">
                                            <h4 class="actor-name">
                                                {{ $acteur->libelle_court ?? $acteur->nom ?? '—' }}
                                                <small class="text-muted fw-normal">{{ $acteur->libelle_long ?? $acteur->prenom ?? '' }}</small>
                                            </h4>
                                            <div class="actor-meta">
                                                <span class="me-2"><span class="status-dot {{ $acteur->is_active ? 'status-active' : 'status-inactive' }}"></span>{{ $acteur->is_active ? 'Actif' : 'Inactif' }}</span>
                                                <span class="me-2"><i class="bi bi-geo-alt me-1"></i>{{ $acteur->pays->nom_fr_fr ?? '—' }}</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">{{ $acteur->type->libelle_type_acteur ?? 'Type n/d' }}</div>
                                            <div class="small text-muted">Statut : {{ $acteur->type_financement ?? 'n/d' }}</div>
                                        </div>
                                    </div>

                                    <hr class="my-2">

                                    <div class="row g-2 small">
                                        <div class="col-12"><i class="bi bi-envelope me-1"></i><a href="mailto:{{ $acteur->email }}">{{ $acteur->email ?: '—' }}</a></div>
                                        <div class="col-12"><i class="bi bi-telephone me-1"></i><span>{{ $acteur->telephone ?? $acteur->telephone_mobile ?? '—' }}</span></div>
                                        <div class="col-12"><i class="bi bi-briefcase me-1"></i><span>{{ $acteur?->user?->fonctionUtilisateur?->libelle_fonction ?? 'Fonction n/d' }}</span></div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="small text-muted">{{ $acteur->adresse ?: '—' }}</div>
                                        <div class="actor-actions">
                                            @can('modifier_ecran_' . $ecran->id)
                                            <a href="#" class="btn-edit" title="Modifier" data-id="{{ $acteur->code_acteur }}"><i class="bi bi-pencil-square"></i></a>
                                            @endcan

                                            @can('supprimer_ecran_' . $ecran->id)
                                                @if ($acteur->is_active)
                                                <a href="#" class="btn-delete" title="Désactiver" data-id="{{ $acteur->code_acteur }}"><i class="bi bi-x-circle text-danger"></i></a>
                                                @else
                                                <a href="#" class="btn-restore" title="Réactiver" data-id="{{ $acteur->code_acteur }}" data-ecran-id="{{ $ecran->id }}"><i class="bi bi-check-circle text-success"></i></a>
                                                @endif
                                            @endcan

                                            <form id="delete-form-{{ $acteur->code_acteur }}" action="{{ route('acteurs.destroy', $acteur->code_acteur) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                                            <form id="restore-form-{{ $acteur->code_acteur }}" action="{{ route('acteurs.restore', ['id' => $acteur->code_acteur, 'ecran_id' => $ecran->id]) }}" method="POST" style="display:none;">@csrf @method('PATCH')</form>
                                        </div>
                                    </div>
                                </div>
                            </div> {{-- /line --}}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info mb-0">Aucun acteur trouvé pour ce pays.</div></div>
            @endforelse
        </div>

        {{-- Pagination Laravel uniquement pour les cards --}}
        @if($acteurs instanceof \Illuminate\Contracts\Pagination\Paginator || $acteurs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {!! $acteurs->appends(array_merge(request()->except('page'), ['view' => 'cards']))->onEachSide(1)->links('pagination::bootstrap-5') !!}
            </div>
        @endif
    </div>
@endif
