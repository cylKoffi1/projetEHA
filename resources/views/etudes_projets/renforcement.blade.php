@extends('layouts.app')


@section('content')
@isset($ecran)
@can("consulter_ecran_" . $ecran->id)

    <style>
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 6px;
            font-size: 80%;
            color: #dc3545;
        }

        /* --- Harmonisation select2 avec Bootstrap --- */
        .select2-container--default .select2-selection--multiple {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: 38px;
            height: auto;
            padding: 0.25rem 0.5rem;
            font-size: 13px;
            font-family: inherit;
            box-shadow: none;
            line-height: 1.5;
        }

        /* Evite l'affichage des mini "cards" trop grossiers */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #dee2e6;
            color: #212529;
            border: none;
            border-radius: 0.25rem;
            padding: 2px 6px;
            margin: 3px 3px 0 0;
            font-size: 0.85rem;
        }

        /* Focus harmonieux */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }

        /* Supprime croix trop imposante */
        .select2-container--default .select2-selection__choice__remove {
            color: #6c757d;
            margin-right: 3px;
            font-size: 0.85rem;
        }

        /* Placeholder style */
        .select2-container--default .select2-search--inline .select2-search__field {
            font-size: 13px;
            font-family: inherit;
            margin-top: 0.25rem;
        }

        .offcanvas.offcanvas-end {
            top: 87px !important;
            width: 90% !important;
            height: calc(100vh - 90px) !important;
        }
    </style>
    <div class="container-fluid py-3">

        {{-- Header / fil d’ariane --}}
        <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Etudes projets</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Renforcement de capacité</li>
                        </ol>
                        <div class="row">
                            <script>
                                setInterval(function() {
                                    document.getElementById('date-now').textContent = getCurrentDate();
                                }, 1000);

                                function getCurrentDate() {
                                    var currentDate = new Date();
                                    return currentDate.toLocaleString();
                                }
                            </script>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>


        {{-- =================== FORMULAIRE (create + edit) =================== --}}
        <div class="card mb-4">
            <div class="card-header">
                <strong id="formTitle">Formulaire de renforcement</strong>
            </div>
            <div class="card-body">
                <form id="renfoForm" method="POST" action="{{ url('/renforcementProjet/store') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="code_renforcement" name="code_renforcement">
                    {{-- statut par défaut en création --}}
                    <input type="hidden" id="statutId" name="statutId" value="plan">

                    <div class="accordion" id="renfoAccordion">

                        {{-- 1) Informations générales --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="h-info">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#acc-info" aria-expanded="true" aria-controls="acc-info">
                                    1) Informations générales
                                </button>
                            </h2>
                            <div id="acc-info" class="accordion-collapse collapse show" data-bs-parent="#renfoAccordion">
                                <div class="accordion-body row g-3">
                                    <div class="col-lg-8">
                                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                                        <input type="text" name="titre" id="titre" class="form-control" placeholder="Ex : Formation en gestion de chantier" required>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Type d’action <span class="text-danger">*</span></label>
                                        <select name="actionTypeId" id="actionTypeId" class="form-select" required>
                                            <option value="">— Choisir un type —</option>
                                            @foreach($actionTypes as $t)
                                                <option value="{{ $t->id }}">{{ $t->Libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Thématique</label>
                                        <input type="text" name="thematique" id="thematique" class="form-control" placeholder="Ex : Sécurité au travail">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Public cible</label>
                                        <input type="text" name="public_cible" id="public_cible" class="form-control" placeholder="Ex : Ingénieurs, techniciens BTP">
                                    </div>
                                    <div class="col-lg-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" id="description" rows="3" class="form-control" placeholder="Brève description des objectifs et du contenu"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2) Période & logistique --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="h-time">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc-time" aria-controls="acc-time">
                                    2) Période & logistique
                                </button>
                            </h2>
                            <div id="acc-time" class="accordion-collapse collapse" data-bs-parent="#renfoAccordion">
                                <div class="accordion-body row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Date début</label>
                                        <input type="date" name="date_debut" id="date_debut" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date fin</label>
                                        <input type="date" name="date_fin" id="date_fin" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Lieu</label>
                                        <input type="text" name="lieu" id="lieu" class="form-control" placeholder="Ex : Abidjan, Salle A">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Modalité</label>
                                        <select name="modaliteId" id="modaliteId" class="form-select">
                                            <option value="">— Sélectionner une modalité —</option>
                                            @foreach($modalites as $m)
                                                <option value="{{ $m->id }}">{{ $m->Libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Organisme</label>
                                        <input type="text" name="organisme" id="organisme" class="form-control" placeholder="Ex : Institut National du BTP">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Intervenants</label>
                                        <input type="text" name="intervenants" id="intervenants" class="form-control" placeholder="Ex : Dr. Koné, M. Diallo">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3) Affectations --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="h-aff">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc-affect" aria-controls="acc-affect">
                                    3) Affectations
                                </button>
                            </h2>
                            <div id="acc-affect" class="accordion-collapse collapse" data-bs-parent="#renfoAccordion">
                                <div class="accordion-body row g-3">
                                    <div class="col-lg-6">
                                        <label class="form-label">Projets associés</label>
                                        <select name="projets[]" id="projets" multiple class="form-select select2-multiple"" data-placeholder="Sélectionnez des projets liés">
                                            @foreach($projets as $p)
                                                <option value="{{ $p->code_projet }}">{{ $p->code_projet }} — {{ $p->libelle_projet }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Bénéficiaires</label>
                                        <select name="beneficiaires[]" id="beneficiaires" multiple class="form-select select2-multiple"" data-placeholder="Acteurs ou organisations bénéficiaires" required>
                                            @foreach($beneficiaires as $b)
                                                <option value="{{ $b->code_acteur }}">{{ $b->libelle_court }} {{ $b->libelle_long }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4) Participants & finances --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="h-pf">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc-partfin" aria-controls="acc-partfin">
                                    4) Participants & finances
                                </button>
                            </h2>
                            <div id="acc-partfin" class="accordion-collapse collapse" data-bs-parent="#renfoAccordion">
                                <div class="accordion-body row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Participants prévus</label>
                                        <input type="number" name="nb_participants_prev" class="form-control" placeholder="Ex : 50">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Participants effectifs</label>
                                        <input type="number" name="nb_participants_effectif" class="form-control" placeholder="Ex : 47">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Coût prévisionnel (XOF)</label>
                                        <input type="number" step="0.01" name="cout_previsionnel" class="form-control" placeholder="Ex : 2500000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Coût réel (XOF)</label>
                                        <input type="number" step="0.01" name="cout_reel" class="form-control" placeholder="Ex : 2350000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Source de financement</label>
                                        <input type="text" name="source_financement" class="form-control" placeholder="Ex : Banque mondiale / État">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 5) Suivi & qualité (affiché seulement en édition) --}}
                        <div class="accordion-item d-none" id="acc-suivi-wrap">
                            <h2 class="accordion-header" id="h-suivi">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc-suivi" aria-controls="acc-suivi">
                                    5) Suivi & qualité
                                </button>
                            </h2>
                            <div id="acc-suivi" class="accordion-collapse collapse" data-bs-parent="#renfoAccordion">
                                <div class="accordion-body row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Statut</label>
                                        {{-- en édition seulement : on affiche un select si tu veux permettre le changement ici --}}
                                        <select id="statutId_edit" class="form-select" disabled>
                                            @foreach($statuts as $s)
                                                <option value="{{ $s->Id }}">{{ $s->Libelle }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">Le statut se change via les actions du tableau ci-dessous.</div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Motif annulation (si annulée)</label>
                                        <input type="text" id="motif_annulation_edit" class="form-control" placeholder="Ex : Financement indisponible, crise sanitaire…">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 6) Pièces jointes --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="h-files">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc-files" aria-controls="acc-files">
                                    6) Pièces jointes
                                </button>
                            </h2>
                            <div id="acc-files" class="accordion-collapse collapse" data-bs-parent="#renfoAccordion">
                                <div class="accordion-body">
                                    <label class="form-label">Fichiers</label>
                                    <input type="file" name="pieces[]" class="form-control" multiple>
                                    <div class="form-text">Supports, listes d’émargement, rapports, photos…</div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="sticky-actions text-end mt-3">
                        <button type="button" id="cancelEdit" class="btn btn-outline-secondary me-2 d-none">Annuler l’édition</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =================== TOOLBAR STATUTS + LISTE =================== --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <strong>Liste des renforcements de capacités</strong>
                <div class="status-toolbar">
                    @php
                        $map = [
                            ''      => ['label'=>'Tous',       'class'=>'btn-outline-secondary'],
                            'plan'  => ['label'=>'Planifiés',  'class'=>'btn-outline-primary'],
                            'enc'   => ['label'=>'En cours',   'class'=>'btn-outline-info'],
                            'achv'  => ['label'=>'Achevés',    'class'=>'btn-outline-success'],
                            'annul' => ['label'=>'Annulés',    'class'=>'btn-outline-danger'],
                            'repr'  => ['label'=>'Reportés',   'class'=>'btn-outline-warning'],
                        ];
                    @endphp
                    @foreach($map as $k=>$cfg)
                        @php $active = (string)($statutFilter ?? '') === (string)$k; @endphp
                        <a class="btn btn-sm {{ $active ? 'btn-dark' : $cfg['class'] }}"
                        href="{{ request()->fullUrlWithQuery(['statut'=>$k ?: null]) }}">
                            {{ $cfg['label'] }}
                            @if($k && ($stats[$k] ?? 0) > 0)
                                <span class="badge bg-secondary">{{ $stats[$k] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Titre</th>
                                <th>Période</th>
                                <th>Type / Modalité / Statut</th>
                                <th>Projets</th>
                                <th>Bénéficiaires</th>
                                <th>Fichier</th>
                                <th style="width:1%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($renforcements as $r)
                                @php
                                    $badgeClass = [
                                        'plan'  => 'bg-primary',
                                        'enc'   => 'bg-info text-dark',
                                        'achv'  => 'bg-success',
                                        'annul' => 'bg-danger',
                                        'repr'  => 'bg-warning text-dark',
                                    ][$r->statutId] ?? 'bg-secondary';
                                @endphp
                                <tr>
                                    <td>{{ $r->code_renforcement }}</td>
                                    <td>{{ $r->titre }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($r->date_debut)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($r->date_fin)->format('d/m/Y') }}</td>
                                    <td>
                                        <div><strong>Type</strong> : {{ $r->actionType?->Libelle ?? '—' }}</div>
                                        <div><strong>Modalité</strong> : {{ $r->modalite?->Libelle ?? '—' }}</div>
                                        <div><strong>Statut</strong> :
                                            <span class="badge {{ $badgeClass }}">{{ $r->statut?->Libelle ?? $r->statutId }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($r->projets->isNotEmpty())
                                            <ul class="mb-0">
                                                @foreach($r->projets as $p)
                                                    <li>{{ $p->code_projet }} — {{ $p->libelle_projet }}</li>
                                                @endforeach
                                            </ul>
                                        @else <em class="text-muted">Aucun</em> @endif
                                    </td>
                                    <td>
                                        @if($r->beneficiaires->isNotEmpty())
                                            <ul class="mb-0">
                                                @foreach($r->beneficiaires as $b)
                                                    <li>{{ $b->libelle_court }} {{ $b->libelle_long }}</li>
                                                @endforeach
                                            </ul>
                                        @else <em class="text-muted">Aucun</em> @endif
                                    </td>
                                    <td>
                                    @if($r->fichiers->isNotEmpty())
                                        <ul class="mb-0">
                                        @foreach($r->fichiers as $f)
                                            <li class="d-flex align-items-center gap-2">
                                            <a href="{{ route('fichiers.download', $f->id) }}" target="_blank">
                                                {{ $f->filename }}
                                            </a>
                                            <small class="text-muted">
                                                ({{ $f->mime_type }}, {{ number_format(($f->size_bytes ?? 0)/1024, 0, '', ' ') }} Ko)
                                            </small>
                                            @can("modifier_ecran_" . $ecran->id)
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFile({{ $f->id }})" title="Supprimer">
                                                <i class="bi bi-x"></i>
                                                </button>
                                            @endcan
                                            </li>
                                        @endforeach
                                        </ul>
                                    @endif
                                    </td>


                                    <td class="text-nowrap">
                                        <div class="btn-group btn-group-sm">
                                            @can("modifier_ecran_" . $ecran->id)
                                            {{-- Éditer --}}
                                            <button class="btn btn-outline-primary"
                                                title="Modifier"
                                                onclick="enterEditMode(@js([
                                                    'code' => $r->code_renforcement,
                                                    'titre' => $r->titre,
                                                    'description' => $r->description,
                                                    'actionTypeId' => $r->actionTypeId,
                                                    'thematique' => $r->thematique,
                                                    'public_cible' => $r->public_cible ?? null,
                                                    'date_debut' => $r->date_debut,
                                                    'date_fin' => $r->date_fin,
                                                    'lieu' => $r->lieu,
                                                    'modaliteId' => $r->modaliteId,
                                                    'organisme' => $r->organisme,
                                                    'intervenants' => $r->intervenants,
                                                    'nb_participants_prev' => $r->nb_participants_prev,
                                                    'nb_participants_effectif' => $r->nb_participants_effectif,
                                                    'cout_previsionnel' => $r->cout_previsionnel,
                                                    'cout_reel' => $r->cout_reel,
                                                    'source_financement' => $r->source_financement,
                                                    'statutId' => $r->statutId,
                                                    'motif_annulation' => $r->motif_annulation,
                                                    'projets' => $r->projets->pluck('code_projet'),
                                                    'beneficiaires' => $r->beneficiaires->pluck('code_acteur')
                                                ]))">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            {{-- Actions statut rapides --}}
                                            @if($r->statutId === 'plan')
                                                <button class="btn btn-outline-info" title="Démarrer"
                                                        onclick="quickStatus(@js($r->code_renforcement), 'enc')">
                                                    ▶
                                                </button>
                                            @endif
                                            @if($r->statutId === 'enc')
                                                <button class="btn btn-outline-success" title="Achever"
                                                        onclick="quickStatus(@js($r->code_renforcement), 'achv')">
                                                    ✓
                                                </button>
                                            @endif
                                            @if(!in_array($r->statutId, ['annul','achv']))
                                                <button class="btn btn-outline-warning" title="Reporter"
                                                        onclick="quickStatus(@js($r->code_renforcement), 'repr')">
                                                    ⏳
                                                </button>
                                                <button class="btn btn-outline-danger" title="Annuler"
                                                        onclick="openCancelModal(@js($r->code_renforcement))">
                                                    ✖
                                                </button>
                                            @endif
                                            @endcan

                                            @can("supprimer_ecran_" . $ecran->id)
                                            <button class="btn btn-outline-danger" title="Supprimer"
                                                    onclick="deleteRenforcement(@js($r->code_renforcement))">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                               
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- pagination --}}
                <div class="mt-3">
                    {{ $renforcements->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- =================== MODAL ANNULATION =================== --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="cancelForm" class="modal-content" onsubmit="submitCancel(event)">
        @csrf
        @method('PUT')
        <div class="modal-header">
            <h5 class="modal-title">Annuler l’action</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cancel_code">
            <div class="mb-2">Veuillez indiquer le <strong>motif d’annulation</strong> :</div>
            <textarea id="cancel_reason" class="form-control" rows="3" required placeholder="Ex : Financement indisponible, grève, crise sanitaire…"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-danger">Confirmer l’annulation</button>
        </div>
        </form>
    </div>
    </div>
@endcan
@endisset
<script>
        $(document).ready(function() {
            initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des renforcements de capacité');

        });
</script>
<script>
async function deleteFile(id) {
  if (!confirm('Supprimer ce fichier ?')) return;
  try {
    const res = await fetch("{{ route('fichiers.destroy', 0) }}".replace('/0', '/'+id), {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' }
    });
    const data = await res.json().catch(()=> ({}));
    if (!res.ok || data.ok === false) {
      alert(data.message || "Erreur lors de la suppression du fichier.");
      return;
    }
    alert(data.message || "Fichier supprimé.");
    location.reload();
  } catch (e) {
    console.error(e);
    alert("Erreur réseau.");
  }
}

</script>

<script>
    /* ===================== OUTILS GÉNÉRAUX ===================== */

    // Horloge en haut
    setInterval(() => {
    const el = document.getElementById('date-now');
    if (el) el.textContent = new Date().toLocaleString();
    }, 1000);

    // Harmonisation Select2 (multi)
    $('.select2-multiple').select2({
    placeholder: "Sélectionner une ou plusieurs options",
    width: '100%',
    allowClear: true,
    dropdownAutoWidth: true
    });

    // Vérif ordre dates (début <= fin)
    function checkDateOrder() {
    const sVal = $('#date_debut').val();
    const eVal = $('#date_fin').val();
    if (!sVal || !eVal) {
        $('#date_fin')[0]?.setCustomValidity('');
        $('#date_fin').removeClass('is-invalid');
        return true;
    }
    const s = new Date(sVal);
    const e = new Date(eVal);
    if (e < s) {
        $('#date_fin')[0].setCustomValidity('La date de fin doit être ≥ à la date de début.');
        $('#date_fin').addClass('is-invalid');
        return false;
    }
    $('#date_fin')[0].setCustomValidity('');
    $('#date_fin').removeClass('is-invalid');
    return true;
    }
    $('#date_debut,#date_fin').on('change', checkDateOrder);


    /* ===================== MODE ÉDITION / CRÉATION ===================== */

    window.enterEditMode = function (payload) {
    // Badge & titres
    $('#modeBadge').removeClass('bg-secondary').addClass('bg-warning').text('Mode : Édition');
    $('#formTitle').text('Modifier un renforcement');
    $('#cancelEdit').removeClass('d-none');

    // Remplissage du form
    $('#code_renforcement').val(payload.code || '');
    $('#titre').val(payload.titre || '');
    $('#description').val(payload.description || '');
    $('#actionTypeId').val(payload.actionTypeId || '').trigger('change');
    $('#thematique').val(payload.thematique || '');
    $('#public_cible').val(payload.public_cible || '');
    $('#date_debut').val((payload.date_debut || '').substring(0, 10));
    $('#date_fin').val((payload.date_fin || '').substring(0, 10));
    $('#lieu').val(payload.lieu || '');
    $('#modaliteId').val(payload.modaliteId || '').trigger('change');
    $('#organisme').val(payload.organisme || '');
    $('#intervenants').val(payload.intervenants || '');
    $('[name="nb_participants_prev"]').val(payload.nb_participants_prev || '');
    $('[name="nb_participants_effectif"]').val(payload.nb_participants_effectif || '');
    $('[name="cout_previsionnel"]').val(payload.cout_previsionnel || '');
    $('[name="cout_reel"]').val(payload.cout_reel || '');
    $('[name="source_financement"]').val(payload.source_financement || '');
    $('#statutId').val(payload.statutId || 'plan');

    $('#projets').val((payload.projets || []).map(String)).trigger('change');
    $('#beneficiaires').val((payload.beneficiaires || []).map(String)).trigger('change');

    // Section "Suivi & qualité" visible (statut non modifiable ici)
    $('#acc-suivi-wrap').removeClass('d-none');
    $('#statutId_edit').val(payload.statutId || '').trigger('change');
    $('#motif_annulation_edit').val(payload.motif_annulation || '');

    // Méthode + action update
    const form = document.getElementById('renfoForm');
    form.action = "{{ url('/renforcementProjet/update') }}/" + encodeURIComponent(payload.code);
    let method = form.querySelector('input[name="_method"]');
    if (!method) {
        method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        form.appendChild(method);
    }
    method.value = 'PUT';

    // Ouvrir la 1ère section + remonter
    const first = document.getElementById('acc-info');
    if (first && !first.classList.contains('show')) new bootstrap.Collapse(first, { toggle: true });
    window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Réinitialiser vers mode création
    $('#cancelEdit').on('click', function () {
    const form = document.getElementById('renfoForm');
    form.reset();
    $('#projets,#beneficiaires,#actionTypeId,#modaliteId').val(null).trigger('change');
    $('#modeBadge').removeClass('bg-warning').addClass('bg-secondary').text('Mode : Création');
    $('#formTitle').text('Formulaire de renforcement');
    $('#cancelEdit').addClass('d-none');
    form.action = "{{ url('/renforcementProjet/store') }}";
    const method = form.querySelector('input[name="_method"]');
    if (method) method.remove();
    $('#code_renforcement').val('');
    $('#acc-suivi-wrap').addClass('d-none');
    form.classList.remove('was-validated');
    collapseAll();
    });

    function collapseAll() {
    document.querySelectorAll('#renfoAccordion .accordion-collapse.show').forEach(el => {
        new bootstrap.Collapse(el, { toggle: true });
    });
    }


    /* ===================== SUBMIT AJAX (UN SEUL HANDLER) ===================== */

    $('#renfoForm').off('submit').on('submit', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const form = this;
        form.classList.add('was-validated');
        if (!form.checkValidity() || !checkDateOrder()) return;

        const fd = new FormData(form);               // récupère tous les champs (y compris [] )
        const url = form.action;                      // /store ou /update/{code}
        // ⚠️ Toujours POST : pour l’update, _method=PUT est déjà présent en hidden
        const httpMethod = 'POST';

        try {
            const res = await fetch(url, {
            method: httpMethod,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: fd
            });

            if (res.status === 422) {
            const data = await res.json().catch(()=> ({}));
            const msgs = [];
            if (data?.errors) Object.values(data.errors).forEach(arr => arr.forEach(m => msgs.push(`- ${m}`)));
            else if (data?.message) msgs.push(data.message);
            else msgs.push('Données invalides.');
            alert("Veuillez corriger les erreurs suivantes :\n\n" + msgs.join('\n'));
            return;
            }

            if (!res.ok) {
            const txt = await res.text().catch(()=> '');
            alert("Erreur serveur ("+res.status+").\n"+txt);
            return;
            }

            const data = await res.json().catch(()=> ({}));
            alert(data.message || 'Opération réussie.');
            location.reload();
        } catch (err) {
            console.error(err);
            alert("Erreur réseau. Vérifiez votre connexion.");
        }
    });



    /* ===================== ACTIONS (DELETE / STATUT / ANNULATION) ===================== */

    // Suppression
    window.deleteRenforcement = async function (code) {
    if (!confirm('Confirmez-vous la suppression ?')) return;
    try {
        const res = await fetch("{{ url('/renforcementProjet/delete') }}/" + encodeURIComponent(code), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' }
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
        alert((data && data.message) ? data.message : 'Erreur lors de la suppression.', 'error');
        return;
        }
        alert(data.message || 'Renforcement supprimé.');
        location.reload();
    } catch (e) {
        console.error(e);
        alert('Erreur réseau.', 'error');
    }
    };

    // Statut rapide (plan → enc → achv / repr / annul)
    window.quickStatus = async function (code, statutId) {
    if (!code || !statutId) return;
    if (statutId === 'annul') { openCancelModal(code); return; }

    try {
        const res = await fetch("{{ url('/renforcementProjet/status') }}/" + encodeURIComponent(code), {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ statutId })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) {
        alert(data.message || 'Mise à jour du statut échouée.', 'error');
        return;
        }
        alert(data.message || 'Statut mis à jour.');
        location.reload();
    } catch (e) {
        console.error(e);
        alert('Mise à jour du statut échouée.', 'error');
    }
    };


    /* ===================== MODAL ANNULATION (avec motif) ===================== */

    let cancelModal;
    function ensureCancelModal() {
    if (!cancelModal) {
        cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    }
    }

    window.openCancelModal = function (code) {
    ensureCancelModal();
    $('#cancel_code').val(code);
    $('#cancel_reason').val('');
    cancelModal.show();
    };

    window.submitCancel = async function (e) {
    e.preventDefault();
    const code = $('#cancel_code').val();
    const motif = $('#cancel_reason').val().trim();
    if (!motif) {
        alert("Le motif d’annulation est requis.", 'warning');
        return;
    }

    try {
        const res = await fetch("{{ url('/renforcementProjet/status') }}/" + encodeURIComponent(code), {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ statutId: 'annul', motif_annulation: motif })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) {
        alert(data.message || 'Annulation échouée.', 'error');
        return;
        }
        cancelModal.hide();
        alert(data.message || 'Action annulée.');
        location.reload();
    } catch (err) {
        console.error(err);
        alert("Annulation échouée.", 'error');
    }
    };
</script>

@endsection
