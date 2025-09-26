@extends('layouts.app')

@section('content')
@isset($ecran)
    @can("consulter_ecran_" . $ecran->id)
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style:none; text-align:right; padding:5px;">
                        <span id="date-now" style="color:#34495E;"></span>
                    </li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion financière</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Décaissements bailleurs</a></li>
                        </ol>
                    </nav>
                    <script>
                        setInterval(() => {
                            const el = document.getElementById('date-now');
                            if (el) el.textContent = new Date().toLocaleString();
                        }, 1000);
                    </script>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Formulaire Nouveau / Éditer --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Nouveau / Éditer un décaissement</strong></div>
            <div class="card-body">

                {{-- Le formulaire est visible si on peut AU MOINS consulter.
                     Les boutons d’action sont restreints plus bas via @can ajouter/modifier --}}
                <form id="decForm" method="POST" action="{{ route('gf.decaissements.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="formMethod">
                    <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Projet *</label>
                            <select name="code_projet" id="code_projet" class="form-select" required>
                                <option value="">— Sélectionnez —</option>
                                @foreach($projets as $p)
                                    <option value="{{ $p->code_projet }}">{{ $p->code_projet }} - {{ $p->libelle_projet }}</option>
                                @endforeach
                            </select>
                            @error('code_projet') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Financement</label>
                            <select name="financer_id" id="financer_id" class="form-select">
                                <option value="">— Aucun —</option>
                            </select>
                            <small class="text-muted d-block">Sélectionner un engagement pour verrouiller le bailleur et la devise.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Bailleur *</label>
                            <select name="code_acteur" id="code_acteur" class="form-select" required>
                                <option value="">— Sélectionnez —</option>
                                {{-- Rempli après sélection projet/financement --}}
                            </select>
                            @error('code_acteur') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Devise</label>
                            <input type="text" name="devise" id="devise" class="form-control" placeholder="XOF, USD..." readonly>
                        </div>

                        {{-- >>>>>>>>>>>>   BANQUE   <<<<<<<<<<<< --}}
                        <div class="col-md-4">
                            <label class="form-label">Banque</label>
                            <select name="banqueId" id="banqueId" class="form-select">
                                <option value="">— Non précisé —</option>
                                @foreach($banques as $b)
                                    @php
                                        $label = trim(($b->sigle ?: $b->nom).' '.($b->libelle_pays ?? ''));
                                    @endphp
                                    <option value="{{ $b->id }}">
                                        {{ $b->sigle ?: $b->nom }}
                                        @if($b->est_internationale) (Internationale) @elseif($b->code_pays) ({{ $b->code_pays }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('banqueId') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date décaissement</label>
                            <input type="date" name="date_decaissement" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Tranche N°</label>
                            <input type="number" name="tranche_no" class="form-control" min="1" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Montant décaissé</label>
                            <input type="text" name="montant" class="form-control montant-input" required inputmode="decimal">
                            <small id="montant-max-info" class="text-muted d-block mt-1"></small>
                            @error('montant') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Référence</label>
                            <input type="text" name="reference" class="form-control" placeholder="Ref. ou note interne">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Commentaire</label>
                            <textarea name="commentaire" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-3">

                        {{-- Bouton ENREGISTRER = droit ajouter --}}
                        @can("ajouter_ecran_" . $ecran->id)
                            <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
                        @endcan

                        {{-- Bouton METTRE À JOUR = droit modifier (le JS switchera le texte si update) --}}
                        @can("modifier_ecran_" . $ecran->id)
                            <button type="submit" class="btn btn-primary d-none" id="updateBtn">Mettre à jour</button>
                        @endcan

                        <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Annuler édition</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Liste (consultation) --}}
        <div class="card">
            <div class="card-header"><strong>Décaissements enregistrés</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered" id="tableDecs" style="width:100%">
                    <thead>
                        <tr>
                            <th>Projet</th>
                            <th>Bailleur</th>
                            <th class="col-1">Tranche N°</th>
                            <th style="text-align: right;">Montant</th>
                            <th>Date décaissement</th>
                            <th>Référence</th>
                            <th style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($decaissements as $d)
                            @php
                                $bailleurNom = trim(($d->bailleur?->libelle_court ?? '') . ' ' . ($d->bailleur?->libelle_long ?? ''));
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $d->code_projet }}</div>
                                    <small class="text-muted">{{ $d->projet?->libelle_projet }}</small>
                                </td>
                                <td>{{ $bailleurNom ?: '—' }}</td>
                                <td><small class="text-muted">Tranche {{ $d->tranche_no ?? '—' }}</small></td>
                                <td style="text-align: right;">
                                    {{ number_format($d->montant, 2, ',', ' ') }} {{ $d->devise }}
                                </td>
                                <td>{{ $d->date_decaissement?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $d->reference ?? '—' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">

                                        {{-- EDIT = droit modifier --}}
                                        @can("modifier_ecran_" . $ecran->id)
                                        <button class="btn btn-outline-primary btn-edit"
                                            data-id="{{ $d->id }}"
                                            data-code_projet="{{ $d->code_projet }}"
                                            data-financer_id="{{ $d->financer_id }}"
                                            data-code_acteur="{{ $d->code_acteur }}"
                                            data-bailleur_label="{{ $bailleurNom }}"
                                            data-devise="{{ $d->devise }}"
                                            data-reference="{{ $d->reference }}"
                                            data-tranche_no="{{ $d->tranche_no }}"
                                            data-montant="{{ $d->montant }}"
                                            data-date_demande="{{ optional($d->date_demande)->format('Y-m-d') }}"
                                            data-date_decaissement="{{ optional($d->date_decaissement)->format('Y-m-d') }}"
                                            data-banque_id="{{ $d->banqueId }}"
                                            data-commentaire="{{ $d->commentaire }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endcan

                                        {{-- DELETE = droit supprimer --}}
                                        @can("supprimer_ecran_" . $ecran->id)
                                        <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete('{{ route('gf.decaissements.destroy', $d->id) }}?ecran_id={{ $ecran->id }}', () => location.reload())">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-2">
                    {{ $decaissements->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
    // … (tes helpers waitUntil, format/unformat, findBestFinancementOption restent)
    (function(){
        const $form        = $('#decForm');
        const $proj        = $('#code_projet');
        const $fin         = $('#financer_id');
        const $act         = $('#code_acteur');
        const $dev         = $('#devise');
        const $banque      = $('#banqueId');
        const $submitBtn   = $('#submitBtn');
        const $updateBtn   = $('#updateBtn');
        const $cancelEdit  = $('#cancelEdit');

        // Récupération des financements par projet
        $proj.on('change', function(){
            const code = this.value;
            $fin.html('<option value="">— Aucun —</option>');
            $act.html('<option value="">— Sélectionnez —</option>');
            if(!code) return;

            fetch(`{{ route('gf.decaissements.financementsByProjet','__CODE__') }}`.replace('__CODE__', code))
                .then(r => r.json())
                .then(list => {
                    const seen = new Set();
                    list.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.text  = `${item.montant_fmt} ${item.devise}`;
                        opt.dataset.bailleurId    = item.bailleur_id || '';
                        opt.dataset.bailleurLabel = item.bailleur_label || '';
                        opt.dataset.devise        = item.devise || '';
                        opt.dataset.montant       = item.montant || 0;
                        $fin.append(opt);

                        if (item.bailleur_id && !seen.has(item.bailleur_id)) {
                            seen.add(item.bailleur_id);
                            $act.append(new Option(item.bailleur_label, item.bailleur_id));
                        }
                    });
                })
                .catch(() => alert('Erreur de chargement', 'error'));
        });

        // Sélection financement → verrouille bailleur + devise + calcule tranche
        $fin.on('change', function(){
            const opt = this.selectedOptions[0];
            if (!opt) return;

            const bailleurId    = opt.dataset.bailleurId;
            const bailleurLabel = opt.dataset.bailleurLabel;
            const devise        = opt.dataset.devise;
            const montantFinance = parseFloat(opt.dataset.montant || 0);

            $('#montant-max-info').text(`Maximum autorisé : ${montantFinance.toLocaleString('fr-FR')} ${devise || ''}`);

            if (bailleurId) {
                if (!$act.find(`option[value="${bailleurId}"]`).length) {
                    $act.append(new Option(bailleurLabel, bailleurId));
                }
                $act.val(bailleurId).prop('readonly', true);
            } else {
                $act.prop('readonly', false).val('');
            }

            if (devise) $dev.val(devise);

            updateNextTranche();
        });

        function updateNextTranche(){
            const code_projet = $proj.val();
            const financer_id = $fin.val();
            if (!code_projet) return;

            fetch(`{{ route('gf.decaissements.nextTranche') }}?code_projet=${encodeURIComponent(code_projet)}&financer_id=${encodeURIComponent(financer_id)}`)
                .then(r => r.json())
                .then(data => {
                    $('input[name="tranche_no"]').val(data.next || 1);
                })
                .catch(() => alert('Erreur lors de la récupération du numéro de tranche.', 'error'));
        }

        function formatMontant(value) {
            const parts = value.replace(/[^\d.,]/g, '').split(',');
            const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            const decPart = parts[1] ? ',' + parts[1] : '';
            return intPart + decPart;
        }

        function unformatMontant(value) {
            return value.replace(/\s/g, '').replace(',', '.');
        }

        $('.montant-input').on('input', function() {
            const pos = this.selectionStart;
            const len = this.value.length;
            const formatted = formatMontant(this.value);
            this.value = formatted;
            this.setSelectionRange(pos + (formatted.length - len), pos + (formatted.length - len));
        });

        // Soumission
        $form.on('submit', function(e){
            e.preventDefault();

            const selectedOption = $fin[0]?.selectedOptions[0];
            const montantFinance = selectedOption ? parseFloat(selectedOption.dataset.montant || 0) : Infinity; // si pas d'engagement, on ne bloque pas ici
            const montantField   = $('input[name="montant"]');
            const montantSaisi   = parseFloat(unformatMontant(montantField.val() || '0'));

            if (!isFinite(montantFinance) || isNaN(montantSaisi) || montantSaisi <= 0) {
                Swal.fire({icon:'warning', title:'Montant invalide'});
                return false;
            }

            if (isFinite(montantFinance) && montantSaisi > montantFinance) {
                Swal.fire({icon:'warning', title:'Montant trop élevé', text:'Le montant décaissé dépasse le financement disponible.'});
                return false;
            }

            // Remplacer le champ par la valeur numérique brute
            montantField.val(montantSaisi);

            const method = $('#formMethod').val().toUpperCase();
            const action = $form.attr('action');

            // Afficher le bon bouton selon mode
            $submitBtn.prop('disabled', true);
            $updateBtn.prop('disabled', true);

            $.ajax({
                url: action,
                type: method,
                data: $form.serialize(),
                success: function(res){
                    if (res.ok) {
                        Swal.fire({icon:'success', title: res.message});
                        setTimeout(() => location.reload(), 700);
                    } else {
                        Swal.fire({icon:'warning', title: res.message || 'Avertissement'});
                    }
                },
                error: function(xhr){
                    const msg = xhr.responseJSON?.message || 'Erreur serveur';
                    let details = '';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        details = Object.values(xhr.responseJSON.errors).map(arr=>arr.join('<br>')).join('<br>');
                    }
                    Swal.fire({icon:'error',title:msg, html:details});
                },
                complete: function(){
                    $submitBtn.prop('disabled', false);
                    $updateBtn.prop('disabled', false);
                }
            });
        });

        // Édition
        $('.btn-edit').on('click', async function(){
            const d = this.dataset;

            // Passer le form en mode update
            $('#decForm').attr('action', '{{ route("gf.decaissements.update", "__ID__") }}'.replace('__ID__', d.id));
            $('#formMethod').val('PUT');

            // Afficher le bon bouton selon droits
            @can("ajouter_ecran_" . $ecran->id)  $('#submitBtn').addClass('d-none'); @endcan
            @can("modifier_ecran_" . $ecran->id) $('#updateBtn').removeClass('d-none'); @endcan

            $('#cancelEdit').removeClass('d-none');

            // 1) Sélectionner le projet
            $('#code_projet').val(d.code_projet).trigger('change');

            // 2) Attendre le remplissage de la liste financements
            try {
                await waitUntil(() => $('#financer_id option').length > 1, {timeout: 5000});

                // 3) Sélection par financer_id si dispo
                let selected = false;
                if (d.financer_id) {
                    $('#financer_id').val(d.financer_id);
                    if ($('#financer_id').val() !== d.financer_id) {
                        const tmp = new Option(`${d.financer_id}`, d.financer_id);
                        $('#financer_id').append(tmp).val(d.financer_id);
                    }
                    $('#financer_id').trigger('change');
                    selected = true;
                }

                // 4) Sinon, meilleure option par bailleur/devise
                if (!selected && d.code_acteur) {
                    const opt = findBestFinancementOption(d.code_acteur, d.devise || '');
                    if (opt) {
                        $('#financer_id').val(opt.value).trigger('change');
                        selected = true;
                    }
                }

                // 5) Sinon au moins le bailleur
                if (!selected && d.code_acteur) {
                    if (!$('#code_acteur').find(`option[value="${d.code_acteur}"]`).length) {
                        $('#code_acteur').append(new Option(d.bailleur_label || d.code_acteur, d.code_acteur));
                    }
                    $('#code_acteur').val(d.code_acteur);
                    if (d.devise) $('#devise').val(d.devise);
                    if (typeof updateNextTranche === 'function') updateNextTranche();
                }
            } catch (e) {
                if (d.code_acteur) {
                    if (!$('#code_acteur').find(`option[value="${d.code_acteur}"]`).length) {
                        $('#code_acteur').append(new Option(d.bailleur_label || d.code_acteur, d.code_acteur));
                    }
                    $('#code_acteur').val(d.code_acteur);
                }
                if (d.devise) $('#devise').val(d.devise);
            }

            // 6) Remplir les autres champs
            $('input[name="reference"]').val(d.reference || '');
            $('input[name="tranche_no"]').val(d.tranche_no || '');
            $('input[name="montant"]').val(formatMontant(d.montant || ''));
            $('input[name="date_demande"]').val(d.date_demande || '');
            $('input[name="date_decaissement"]').val(d.date_decaissement || '');
            $('textarea[name="commentaire"]').val(d.commentaire || '');

            // Banque
            if (d.banque_id) {
                $('#banqueId').val(d.banque_id);
                if ($('#banqueId').val() !== d.banque_id) {
                    // au cas où la banque n’était pas chargée (rare)
                    const opt = new Option(d.banque_id, d.banque_id);
                    $('#banqueId').append(opt).val(d.banque_id);
                }
            }
        });

        // Annuler édition
        $cancelEdit.on('click', function(){
            $form.attr('action', '{{ route("gf.decaissements.store") }}');
            $('#formMethod').val('POST');

            @can("ajouter_ecran_" . $ecran->id)  $('#submitBtn').removeClass('d-none'); @endcan
            $('#updateBtn').addClass('d-none');

            $(this).addClass('d-none');
            $form[0].reset();
            $act.prop('readonly', false).html('<option value="">— Sélectionnez —</option>');
            $fin.html('<option value="">— Aucun —</option>');
            $('#montant-max-info').text('');
            $('#banqueId').val('');
        });

        // Init DataTable
        $(document).ready(function() {
            if (typeof initDataTable === 'function') {
                initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableDecs', 'Liste des décaissements bailleur');
            }
        });
    })();
    </script>
    @endcan
@endisset
@endsection
