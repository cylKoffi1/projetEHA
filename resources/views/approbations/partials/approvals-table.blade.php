{{-- resources/views/approbations/partials/approvals-table.blade.php --}}
<div class="table-responsive">
    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="{{ $tableId }}">
        <thead>
            <tr>
                @if($showActions)
                <th style="width:40px">
                    <input type="checkbox" id="chk-all-{{ $tableId }}" class="checkbox-modern" aria-label="Tout sélectionner">
                </th>
                @else
                <th style="width:40px"></th>
                @endif
                <th>Objet</th>
                <th>Workflow</th>
                <th>Étape</th>
                <th>Approbateurs</th>
                <th>Statut</th>
                @if($showActions)
                <th style="width:80px">Actions</th>
                @else
                <th style="width:60px">Détails</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr data-row='@json($r)'
                data-url="{{ route('approbations.objectView', ['module'=>$r['module'],'type'=>$r['type'],'id'=>$r['target_id']]) }}">
                
                @if($showActions)
                <td>
                    <input type="checkbox" class="chk-row checkbox-modern" aria-label="Sélectionner la ligne">
                </td>
                @else
                <td></td>
                @endif
                
                <td>
                    <div class="fw-bold text-dark">{{ $r['target_id'] }}</div>
                    <div class="text-muted small text-dark">{{ $r['module'] }} • {{ $r['type'] }}</div>
                    @if(!empty($r['created_at']))
                    <div class="text-muted small">
                        <i class="far fa-clock"></i>
                        {{ \Carbon\Carbon::parse($r['created_at'])->diffForHumans() }}
                    </div>
                    @endif
                </td>
                <td>
                    <div class="fw-medium">{{ $r['workflow_name'] }}</div>
                    <div class="text-muted small">Version {{ $r['version'] ?? '—' }}</div>
                </td>
                <td>
                    <span class="badge-modern badge-light">Étape {{ $r['step_pos'] }}</span>
                </td>
                <td>
                    <div class="avatar-stack">
                        @foreach(($r['approvers'] ?? []) as $ap)
                            @php
                                $st    = $ap['status'] ?? 'PENDING';
                                $class = match($st){ 
                                    'APPROUVE'=>'avatar-success',
                                    'REJETE'=>'avatar-danger',
                                    'EN_COURS'=>'avatar-warning', 
                                    default=>'avatar-secondary' 
                                };

                                // Construction du tooltip
                                $short = trim($ap['short'] ?? '');
                                $long  = trim($ap['long'] ?? '');
                                $full  = trim($short.' '.$long);
                                if ($full === '') $full = $ap['label'] ?? $ap['code'] ?? '—';
                                $tooltip = $full;
                                
                                // Statut pour le tooltip
                                $statusText = match($st) {
                                    'APPROUVE' => '✓ Approuvé',
                                    'REJETE' => '✗ Rejeté', 
                                    'EN_COURS' => '⌛ En cours',
                                    default => '⏳ En attente'
                                };
                                $tooltip .= " • " . $statusText;
                            @endphp

                            <div class="avatar {{ $class }}"
                                data-bs-toggle="tooltip"                                 
                                title="{{ $tooltip }}"
                                aria-label="{{ $tooltip }}">
                                {{ $ap['initials'] }}
                            </div>
                        @endforeach
                    </div>
                </td>
                <td>
                    @switch($r['status_code'])
                        @case('EN_COURS')
                            <span class="badge-modern badge-warning">
                                <i class="fas fa-spinner fa-spin"></i> En cours
                            </span>
                            @break
                        @case('PENDING')
                            <span class="badge-modern badge-secondary">
                                <i class="fas fa-clock"></i> En attente
                            </span>
                            @break
                        @case('APPROUVE')
                            <span class="badge-modern badge-success">
                                <i class="fas fa-check-circle"></i> Approuvé
                            </span>
                            @break
                        @case('REJETE')
                            <span class="badge-modern badge-danger">
                                <i class="fas fa-times-circle"></i> Rejeté
                            </span>
                            @break
                        @default
                            <span class="badge-modern badge-light">{{ $r['status_code'] ?? '—' }}</span>
                    @endswitch
                </td>
                <td>
                    @if($showActions)
                    <div class="quick-actions">
                        <button type="button" class="btn-modern btn-success btn-approve"
                                data-bs-toggle="tooltip"  title="Approuver cette demande" aria-label="Approuver">
                            <i class="fas fa-check"></i>
                        </button>

                        <button type="button" class="btn-modern btn-danger btn-reject"
                                data-bs-toggle="tooltip" title="Rejeter cette demande" aria-label="Rejeter">
                            <i class="fas fa-times"></i>
                        </button>

                        <button type="button" class="btn-modern btn-primary btn-delegate"
                                data-bs-toggle="tooltip"  title="Déléguer cette approbation" aria-label="Déléguer">
                            <i class="fas fa-user-friends"></i>
                        </button>

                        <button type="button" class="btn-modern btn-outline btn-view"
                                data-bs-toggle="tooltip" title="Consulter les détails" aria-label="Consulter">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @else
                    <div class="quick-actions">
                        <button type="button" class="btn-modern btn-outline btn-view"
                                data-bs-toggle="tooltip" title="Consulter les détails" aria-label="Consulter">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $showActions ? '7' : '6' }}">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4>{{ $emptyMessage }}</h4>
                        @if($showActions)
                        <div class="mt-3 p-3" style="background: var(--light); border-radius: 8px; max-width: 600px; margin: 1rem auto;">
                            <p class="mb-2"><strong><i class="fas fa-lightbulb text-warning"></i> Astuce pour commencer :</strong></p>
                            <ul class="text-start" style="margin: 0; padding-left: 1.5rem;">
                                <li>Les demandes nécessitant votre validation apparaîtront ici</li>
                                <li>Utilisez les boutons d'action pour approuver, rejeter ou déléguer</li>
                                <li>Double-cliquez sur une ligne pour voir les détails complets</li>
                                <li>Besoin d'aide ? Cliquez sur le bouton <strong>"Aide"</strong> en haut à droite</li>
                            </ul>
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>