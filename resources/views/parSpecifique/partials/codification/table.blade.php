<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>
            Schémas existants
            <span class="badge bg-primary ms-2">{{ $schemas->count() }}</span>
        </h5>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th width="80">Pays</th>
                        <th>Type d'objet</th>
                        <th>Libellé</th>
                        <th>Pattern</th>
                        <th width="80">Sep.</th>
                        <th width="80">Actif</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schemas as $schema)
                        <tr>
                            <td class="text-muted">#{{ $schema->id }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $schema->pays_alpha3 }}</span>
                            </td>
                            <td>
                                <code class="text-primary">{{ $schema->entity_type }}</code>
                            </td>
                            <td>{{ $schema->name ?: '—' }}</td>
                            <td>
                                <code class="text-dark">{{ $schema->pattern }}</code>
                            </td>
                            <td class="text-center">
                                <code>{{ $schema->token_separator }}</code>
                            </td>
                            <td>
                                @if($schema->active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Oui
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle"></i> Non
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-edit"
                                            data-schema='@json($schema)'
                                            title="Éditer">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <form method="POST"
                                          action="{{ route('codif.destroy', $schema) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce schéma ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                <p class="mb-0">Aucun schéma défini pour le moment</p>
                                <small>Commencez par créer votre premier schéma ci-dessus</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>