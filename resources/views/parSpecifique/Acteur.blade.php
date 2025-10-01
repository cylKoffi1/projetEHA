{{-- resources/views/acteurs/index.blade.php --}}
@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Str;
@endphp

<style>
    :root{
        --accent:#4f46e5; --accent-2:#22c55e; --accent-3:#ef4444; --ink:#0f172a; --sub:#64748b;
        --card-grad-1:#f8fafc; --card-grad-2:#f1f5f9; --card-grad-3:#eef2ff;
        --panel-top: 90px; --panel-bg:#f8fafc; --panel-head:#312e81;
    }
    .toolbar-sticky{ position: sticky; top: 0; z-index: 2; background:#fff; border-bottom:1px solid #eee; padding:.75rem 1rem; margin:-1rem -1rem 1rem; }
    .actor-card{ border:1px solid #e5e7eb; border-radius:1rem; transition:transform .15s, box-shadow .2s; height:100%; overflow:hidden; background:linear-gradient(135deg,var(--card-grad-1),var(--card-grad-2)); box-shadow:0 6px 24px rgba(15,23,42,.04); position:relative; isolation:isolate; }
    .actor-card:hover{ transform:translateY(-2px); box-shadow:0 10px 28px rgba(15,23,42,.08); }
    .actor-cover{ height:56px; background:linear-gradient(90deg,#fff,#eef2ff 45%,#e0e7ff); border-bottom:1px solid #e5e7eb; }
    .actor-ribbon{ position:absolute; top:10px; right:-40px; rotate:45deg; background:#e2e8f0; color:#0f172a; font-size:.72rem; letter-spacing:.02em; padding:.3rem 2.2rem; box-shadow:0 2px 10px rgba(0,0,0,.08); text-transform:uppercase; font-weight:700; pointer-events:none; }
    .actor-card-wrapper[data-status="active"] .actor-ribbon{ background:linear-gradient(90deg,#16a34a,#22c55e); color:#fff; }
    .actor-card-wrapper[data-status="inactive"] .actor-ribbon{ background:linear-gradient(90deg,#ef4444,#f97316); color:#fff; }
    .actor-card::before{ content:""; position:absolute; inset:0 0 auto 0; height:4px; z-index:1; background:linear-gradient(90deg,transparent,transparent); }
    .actor-card-wrapper[data-status="active"] .actor-card::before{ background:linear-gradient(90deg,#16a34a,#22c55e); }
    .actor-card-wrapper[data-status="inactive"] .actor-card::before{ background:linear-gradient(90deg,#ef4444,#f97316); }
    .actor-body{ padding:1rem; } .actor-name{ font-weight:800; margin:0; font-size:1.05rem; color:var(--ink) } .actor-meta{ font-size:.85rem; color:var(--sub) }
    .actor-actions a{ display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:.6rem; border:1px solid #e5e7eb; margin-left:.25rem; text-decoration:none; background:#fff; box-shadow:0 1px 2px rgba(15,23,42,.04); }
    .actor-actions a:hover{ background:#f1f5f9; border-color:#d1d5db }
    .status-dot{ width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:.4rem } .status-active{ background:#22c55e } .status-inactive{ background:#ef4444 }
    .actor-card-wrapper[data-typeacteur] .actor-card{ background: linear-gradient(135deg,var(--card-grad-1) 0%, var(--card-grad-3) 100%); }
    .avatar{ width:56px; height:56px; border-radius:50%; border:2px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,.08); display:flex; align-items:center; justify-content:center; font-weight:800; color:#111827; overflow:hidden; margin-top:-36px; background:#e5e7eb; flex:0 0 auto; position:relative; }
    .avatar img{ width:100%; height:100%; object-fit:cover; display:none; } .avatar span.initials{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; }
    .avatar.has-photo img{ display:block; } .avatar.has-photo span.initials{ display:none; } .avatar-sm{ width:40px; height:40px; margin-top:0; }
    .badge-light{ background:#eef2ff; color:#3730a3; border:1px solid #e0e7ff; font-weight:600; }
    .offcanvas.offcanvas-end.panel-boosted{ top:var(--panel-top)!important; bottom:0!important; height:calc(100vh - var(--panel-top))!important; width:min(980px,92vw); background:var(--panel-bg); box-shadow:-8px 0 30px rgba(15,23,42,.12); border-left:0; display:flex; flex-direction:column; overflow:hidden; }
    .offcanvas-header{ border-bottom:0; padding:0; position:relative; flex:0 0 auto; }
    .offcanvas-header .offcanvas-title{ width:100%; color:#fff; font-weight:800; letter-spacing:.2px; background:radial-gradient(1200px 400px at -10% -100%, #6366f1 0%, #4338ca 40%, var(--panel-head) 100%); padding:1rem 1.25rem; }
    .offcanvas-header .btn-close{ position:absolute; right:.75rem; top:.75rem; filter:invert(1) brightness(2); }
    .offcanvas-body{margin-bottom: 50px; padding:1rem 1.25rem; background:linear-gradient(180deg,#fff 0%, var(--panel-bg) 50%, #fff 100%); overflow:auto; flex:1 1 auto; }
    .panel-section{ background:#fff; border:1px solid #e5e7eb; border-radius:.9rem; padding:.9rem; box-shadow:0 4px 14px rgba(15,23,42,.05); margin-bottom:.85rem; }
    .panel-section>.title{ font-weight:700; color:#111827; font-size:.95rem; display:flex; align-items:center; gap:.5rem; margin-bottom:.5rem; }
    .panel-section>.title .dot{ width:8px; height:8px; border-radius:50%; background:var(--accent); display:inline-block }
    .offcanvas-footer{ border-top:1px solid #e5e7eb; padding:.75rem 1rem; background:#fff; box-shadow:0 -6px 18px rgba(15,23,42,.06); position:sticky; bottom:0; z-index:5; flex:0 0 auto; }
    .photo-preview{ width:120px; height:120px; border:2px dashed #c7d2fe; border-radius:.9rem; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#eef2ff; transition:border-color .2s, background .2s; }
    .photo-preview:hover{ background:#e0e7ff; border-color:#818cf8; }
    .photo-preview img{ max-width:100%; max-height:100%; display:none; object-fit:cover }
    .table thead th{ white-space:nowrap; } .view-toggle .btn{ min-width:120px; }
</style>

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
                <h3><i class="bi bi-arrow-return-left return" onclick="history.back()"></i>Plateforme</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Paramètre spécifique</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Acteurs</li>
                    </ol>
                </nav>
                <script>
                    function getCurrentDate(){ return new Date().toLocaleString(); }
                    setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=getCurrentDate(); },1000);
                </script>
            </div>
        </div>
    </div>
</div>

<section class="section">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Veuillez corriger les erreurs :</div>
            <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="toolbar-sticky d-flex flex-wrap align-items-center gap-2">
                <div class="me-auto d-flex flex-wrap align-items-center gap-2">
                    <div class="input-group" style="max-width:420px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher (nom, email, téléphone, type)...">
                    </div>

                    <div class="btn-group" role="group" aria-label="Filtres" id="statusFilterGroup">
                        <button class="btn btn-outline-secondary active" data-filter="all" id="filterAll">Tous</button>
                        <button class="btn btn-outline-success" data-filter="active" id="filterActive">Actifs</button>
                        <button class="btn btn-outline-danger" data-filter="inactive" id="filterInactive">Inactifs</button>
                    </div>

                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group view-toggle" role="group" aria-label="Vues">
                        <a class="btn btn-primary" id="btnViewCards" href="{{ request()->fullUrlWithQuery(['view' => 'cards', 'page' => null]) }}"><i class="bi bi-grid-3x3-gap me-1"></i> Cartes</a>
                        <a class="btn btn-outline-primary" id="btnViewTable" href="{{ request()->fullUrlWithQuery(['view' => 'table', 'page' => null]) }}"><i class="bi bi-table me-1"></i> Tableau</a>
                    </div>
                    @can('ajouter_ecran_' . $ecran->id)
                    <button id="btnCreate" class="btn btn-success">
                        <i class="bi bi-layout-sidebar-inset-reverse me-1"></i> Nouvel acteur
                    </button>
                    @endcan
                </div>
            </div>

            <div class="mt-3" id="advancedFilters" style="display:none;">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Type d’acteur</label>
                        <select class="form-select" id="filterTypeActeur">
                            <option value="">Tous les types</option>
                            @foreach ($TypeActeurs as $TypeActeur)
                                <option value="{{ $TypeActeur->cd_type_acteur }}">{{ $TypeActeur->libelle_type_acteur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1">Statut (type_financement)</label>
                        <select class="form-select" id="filterTypeFin">
                            <option value="">Tous</option>
                            @foreach($typeFinancements as $typeFin)
                                <option value="{{ $typeFin->code_type_financement }}">{{ $typeFin->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-outline-dark" id="toggleFilters"><i class="bi bi-sliders me-1"></i> Masquer les filtres</button>
                    </div>
                </div>
            </div>
            <div class="mt-2"><a href="#" id="toggleFiltersLink"><i class="bi bi-sliders"></i> Filtres avancés</a></div>
        </div>
    </div>

    @can('consulter_ecran_' . $ecran->id)
        <div id="listWrapper">
            @include('parSpecifique.acteurs._list') {{-- <= partial liste (cards/table) --}}
        </div>
    @endcan
</section>

@can('ajouter_ecran_' . $ecran->id)
    {{-- ====== OFFCANVAS FORMULAIRE ====== --}}
    <div class="offcanvas offcanvas-end panel-boosted" tabindex="-1" id="acteurPanel" aria-labelledby="acteurPanelLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="acteurPanelLabel">Nouvel acteur</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
        </div>

        <form id="acteurForm" action="{{ route('acteurs.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">
            <input type="hidden" id="acteur-id" name="id">

            <div class="offcanvas-body">
                {{-- Contexte --}}
                <div class="panel-section">
                    <div class="title"><span class="dot"></span>Contexte</div>
                    <div class="row g-3 align-items-start mb-1">
                        <div class="col-md-4">
                            <label for="code_pays" class="form-label">Pays</label>
                            <input type="hidden" name="code_pays" id="code_pays" value="{{ $pays->alpha3 }}">
                            <input type="text" name="pays" class="form-control" value="{{ $pays->nom_fr_fr }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type d'acteur *</label>
                            <select class="form-select" id="type_acteur" name="type_acteur" required>
                                <option value="">Sélectionner le type d'acteur</option>
                                @foreach ($TypeActeurs as $TypeActeur)
                                    <option value="{{ $TypeActeur->cd_type_acteur }}">{{ $TypeActeur->libelle_type_acteur }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <center>
                                <label class="form-label">Photo / Logo</label>
                                <div class="photo-preview"><img id="photo-preview" alt="Preview"></div>
                                <input type="file" id="photo" name="photo" class="form-control mt-2" accept="image/*">
                            </center>
                           
                        </div>
                    </div>
                </div>

                {{-- Paramètres généraux --}}
                <div class="panel-section">
                    <div class="title"><span class="dot"></span>Paramètres généraux</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Statut (type financement) *</label>
                            <select class="form-select" name="type_financement" id="type_financement" required>
                                <option value="">Sélectionner le statut</option>
                                @foreach($typeFinancements as $typeFin)
                                    <option value="{{ $typeFin->code_type_financement }}">{{ $typeFin->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Acteur *</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type_personne" id="personnePhysique" value="physique">
                                    <label class="form-check-label" for="personnePhysique">Personne Physique</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type_personne" id="personneMorale" value="morale">
                                    <label class="form-check-label" for="personneMorale">Personne Morale (Entreprise)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== ENTREPRISE ========== --}}
                <div class="panel-section mt-2 d-none" id="entrepriseFields">
                    <div class="title"><span class="dot"></span>Entreprise</div>

                    <ul class="nav nav-tabs" id="entrepriseTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#entreprise-general" type="button">Infos Générales</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#entreprise-legal" type="button">Infos Juridiques</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#entreprise-contact" type="button">Contact</button></li>
                    </ul>

                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="entreprise-general">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom complet (Raison sociale) *</label>
                                    <input type="text" class="form-control" name="libelle_long">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom abrégé *</label>
                                    <input type="text" class="form-control" name="libelle_court">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date de création *</label>
                                    <input type="date" class="form-control" name="date_creation">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Secteur d'activité</label>
                                    <lookup-multiselect name="secteurActivite" id="secteurActivite">
                                        @foreach ($SecteurActivites as $SecteurActivite)
                                            <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                        @endforeach
                                    </lookup-multiselect>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Forme Juridique *</label>
                                    <select name="FormeJuridique" id="FormeJuridique" class="form-select">
                                        <option value="">Sélectionnez...</option>
                                        @foreach ($formeJuridiques as $formeJuridique)
                                            <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="entreprise-legal">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Numéro d’Immatriculation *</label>
                                    <input type="text" class="form-control" name="NumeroImmatriculation">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">NIF</label>
                                    <input type="text" class="form-control" name="nif">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RCCM</label>
                                    <input type="text" class="form-control" name="rccm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Capital Social</label>
                                    <input type="number" class="form-control" name="CapitalSocial">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Numéro d'agrément</label>
                                    <input type="text" class="form-control" name="Numéroagrement" id="Numéroagrement">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="entreprise-contact">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Code postal</label>
                                    <input type="text" class="form-control" name="CodePostaleEntreprise">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Adresse postale</label>
                                    <input type="text" class="form-control" name="AdressePostaleEntreprise">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Adresse Siège</label>
                                    <input type="text" class="form-control" name="AdresseSiègeEntreprise">
                                </div>

                                <hr class="my-2">

                                <div class="col-md-3">
                                    <label class="form-label">Représentant Légal *</label>
                                    <lookup-select name="nomRL[]" id="nomRL">
                                        @foreach ($acteurRepres as $acteurRepre)
                                            <option value="{{ $acteurRepre->code_acteur }}">{{ $acteurRepre->libelle_court }} {{ $acteurRepre->libelle_long }}</option>
                                        @endforeach
                                    </lookup-select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="emailRL" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Téléphone 1 *</label>
                                    <input type="text" name="telephone1RL" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Téléphone 2</label>
                                    <input type="text" name="telephone2RL" class="form-control">
                                </div>

                                <hr class="my-2">

                                <div class="col-md-3">
                                    <label class="form-label">Personnes de Contact</label>
                                    <lookup-multiselect name="nomPC" id="nomPC">
                                        @foreach ($acteurRepres as $acteurRepre)
                                            <option value="{{ $acteurRepre->code_acteur }}">{{ $acteurRepre->libelle_court }} {{ $acteurRepre->libelle_long }}</option>
                                        @endforeach
                                    </lookup-multiselect>
                                </div>
                                <div class="col-md-9 d-flex flex-wrap" id="contactContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== INDIVIDU ========== --}}
                <div class="panel-section mt-2 d-none" id="individuFields">
                    <div class="title"><span class="dot"></span>Individu</div>

                    <ul class="nav nav-tabs" id="individuTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#individu-general" type="button">Infos Personnelles</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#individu-contact" type="button">Contact</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#individu-admin" type="button">Administratif</button></li>
                    </ul>

                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="individu-general">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Prénom *</label><input type="text" name="prenom" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Date de Naissance</label><input type="date" name="date_naissance" id="date_naissance" class="form-control"></div>
                                <div class="col-md-4">
                                    <label class="form-label">Genre</label>
                                    <select name="genre" id="genre" class="form-select">
                                        <option value="">Sélectionnez...</option>
                                        @foreach ($genres as $genre)
                                            <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Situation Matrimoniale</label>
                                    <select class="form-select" name="situationMatrimoniale" id="situationMatrimoniale">
                                        <option value="">Sélectionnez...</option>
                                        @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                            <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pays d'origine *</label>
                                    <lookup-select name="nationnalite" id="nationnalite-select">
                                        @foreach ($tousPays as $tousPay)
                                            <option value="{{ $tousPay->id }}">{{ $tousPay->nom_fr_fr }}</option>
                                        @endforeach
                                    </lookup-select>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="individu-contact">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Email *</label><input type="email" name="emailI" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Code postal</label><input type="text" name="CodePostalI" id="CodePostalI" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Adresse postale</label><input type="text" name="AdressePostaleIndividu" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Adresse siège *</label><input type="text" name="adresseSiegeIndividu" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Téléphone Bureau *</label><input type="text" name="telephoneBureauIndividu" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label">Téléphone mobile *</label><input type="text" name="telephoneMobileIndividu" class="form-control"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="individu-admin">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Pièce d’Identité</label>
                                    <select class="form-select" name="piece_identite" id="piece_identite">
                                        <option value="">Sélectionner...</option>
                                        @foreach($Pieceidentite as $Pieceidentit)
                                            <option value="{{ $Pieceidentit->idPieceIdent }}">{{ $Pieceidentit->libelle_long }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4"><label class="form-label">Numéro Pièce</label><input type="text" class="form-control" name="numeroPiece"></div>
                                <div class="col-md-4"><label class="form-label">Date d’établissement</label><input type="date" class="form-control" name="dateEtablissement"></div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-3"><label class="form-label">Date d’expiration</label><input type="date" class="form-control" name="dateExpiration"></div>
                                <div class="col-md-3"><label class="form-label">Numéro Fiscal</label><input type="text" class="form-control" name="numeroFiscal"></div>
                                <div class="col-md-3">
                                    <label class="form-label">Secteur d'activité</label>
                                    <lookup-multiselect name="SecteurActI" id="SecteurActI">
                                        @foreach ($SecteurActivites as $SecteurActivite)
                                            <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                        @endforeach
                                    </lookup-multiselect>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fonction</label>
                                    <select name="fonctionUser" id="fonctionUser" class="form-select">
                                        <option value="">Sélectionner la fonction</option>
                                        @foreach ($fonctionUtilisateurs as $fonctionUtilisateur)
                                            <option value="{{ $fonctionUtilisateur->code }}">{{ $fonctionUtilisateur->libelle_fonction }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div> {{-- /offcanvas-body --}}

            <div class="offcanvas-footer d-flex justify-content-between">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Fermer</button>
                <button type="submit" id="submit-button" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
@endcan

{{-- ======================= JS ======================= --}}
<script>
(function(){
    const listWrapper = document.getElementById('listWrapper');

    function initTableEnhancer(){
        try{
            const urlObj = new URL(window.location.href);
            const view = urlObj.searchParams.get('view') || 'cards';
            if (view !== 'table') return;

            if (typeof initDataTable !== 'function') return;

            const table = document.getElementById('table1');
            if (!table) return;

            if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(table)) {
                jQuery(table).DataTable().destroy();
            }

            // libellé libre
            initDataTable('{{ auth()->user()?->acteur?->code_acteur }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des acteurs');
        }catch(e){ console.error(e); }
    }

    async function ajaxLoad(url, push=true){
        try{
            const res = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}, credentials:'same-origin' });
            if(!res.ok){ throw new Error('HTTP '+res.status); }
            const html = await res.text();
            listWrapper.innerHTML = html;

            if (push) history.pushState({ajax:true}, '', url);

            if (typeof applyFilters === 'function') applyFilters();

            const urlObj = new URL(window.location.href);
            const view = urlObj.searchParams.get('view') || 'cards';
            const bCards = document.getElementById('btnViewCards');
            const bTable = document.getElementById('btnViewTable');
            if (bCards && bTable){
                if (view === 'table'){
                    bTable.classList.remove('btn-outline-primary'); bTable.classList.add('btn-primary');
                    bCards.classList.remove('btn-primary'); bCards.classList.add('btn-outline-primary');
                } else {
                    bCards.classList.remove('btn-outline-primary'); bCards.classList.add('btn-primary');
                    bTable.classList.remove('btn-primary'); bTable.classList.add('btn-outline-primary');
                }
            }

            initTableEnhancer();

            const top = listWrapper.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top, behavior:'smooth' });
        }catch(err){ console.error(err); }
    }

    document.addEventListener('click', function(e){
        const a = e.target.closest('#listWrapper .pagination a, #btnViewCards, #btnViewTable');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href === '#') return;
        e.preventDefault();
        ajaxLoad(href, true);
    });

    window.addEventListener('popstate', function(){
        ajaxLoad(location.href, false);
    });

    document.addEventListener('DOMContentLoaded', initTableEnhancer);
})();
</script>

<script>
(function(){
    function qs(s, r=document){ return r.querySelector(s); }
    function qsa(s, r=document){ return Array.from(r.querySelectorAll(s)); }
    const ROOT = @json(url('/'));

    // 18 ans min
    document.addEventListener('DOMContentLoaded', () => {
        const dn = qs('#date_naissance');
        if (!dn) return;
        const today = new Date();
        const minDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        dn.setAttribute('max', minDate.toISOString().split('T')[0]);
        dn.addEventListener('change', function(){
            const selected = new Date(this.value);
            if (selected > minDate) { alert('Vous devez avoir au moins 18 ans.'); this.value = ''; }
        });
    });

    // Preview photo
    document.addEventListener('DOMContentLoaded', () => {
        const input = qs('#photo'), preview = qs('#photo-preview');
        if (!input || !preview) return;
        input.addEventListener('change', (e)=>{
            const f = e.target.files?.[0];
            if (!f) { preview.src=''; preview.style.display='none'; return; }
            const reader = new FileReader();
            reader.onload = ev => { preview.src = ev.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(f);
        });
    });

    // Toggle PF/PM
    function togglePersonneFields(){
        const pp = qs('#personnePhysique'), pm = qs('#personneMorale');
        const entreprise = qs('#entrepriseFields'), individu = qs('#individuFields');
        if (!pp || !pm || !entreprise || !individu) return;
        if (pp.checked) { individu.classList.remove('d-none'); entreprise.classList.add('d-none'); }
        if (pm.checked) { entreprise.classList.remove('d-none'); individu.classList.add('d-none'); }
    }
    document.addEventListener('DOMContentLoaded', () => {
        ['#personnePhysique','#personneMorale'].forEach(id=>{
            const el = qs(id);
            if (el) el.addEventListener('change', togglePersonneFields);
        });
    });

    // lookup helpers
    function waitAndSetSelectedValues(lookup, values, tries=30){
        const attempt = () => {
            if (!lookup || typeof lookup.setSelectedValues !== 'function'){
                if (tries>0) setTimeout(()=>attempt(--tries), 100);
                return;
            }
            lookup.setSelectedValues(values || []);
        };
        attempt();
    }
    function setLookupValue(id, value){
        const lookup = qs('#'+id);
        if (!lookup) return;
        if (typeof lookup.setSelectedValue === 'function') lookup.setSelectedValue(value ?? '');
        else if (lookup.shadowRoot){
            const sel = lookup.shadowRoot.querySelector('select');
            if (sel) sel.value = value ?? '';
        }
    }

    // Right panel instance
    const panelEl = document.getElementById('acteurPanel');
    const acteurPanel = panelEl ? new bootstrap.Offcanvas(panelEl) : null;

    // Reset form
    function resetForm(){
        const form = qs('#acteurForm'); if (!form) return;
        form.reset();
        qs('#method').value = 'POST';
        form.action = @json(route('acteurs.store'));
        qs('#acteurPanelLabel').textContent = 'Nouvel acteur';
        qs('#submit-button').textContent = 'Enregistrer';
        const preview = qs('#photo-preview'); if (preview){ preview.src=''; preview.style.display='none'; }
        const pp = qs('#personnePhysique'), pm = qs('#personneMorale');
        if (pp) pp.checked = false; if (pm) pm.checked = false;
        qs('#entrepriseFields').classList.add('d-none');
        qs('#individuFields').classList.add('d-none');
    }

    // Helpers
    function setVal(sel, v){ const el = (typeof sel==='string'? qs(sel): sel); if(el){ el.value = (v ?? ''); } }
    function fillIf(selector, dataKey, data){ if(qs(selector)) setVal(selector, data?.[dataKey]); }

    // Ouvrir panel création
    document.addEventListener('DOMContentLoaded', ()=>{
        const btnCreate = qs('#btnCreate');
        if (btnCreate && acteurPanel){
            btnCreate.addEventListener('click', (e)=>{ e.preventDefault(); resetForm(); acteurPanel.show(); });
        }
    });

    // Édition
    function openEdit(id){
        fetch(`${ROOT}/acteurs/${id}/edit`)
            .then(r => { if(!r.ok) throw new Error('Erreur réseau'); return r.json(); })
            .then(data => {
                resetForm();
                const form = qs('#acteurForm'); if (!form) return;

                const actorId = data.id ?? id;
                setVal('#acteur-id', actorId);

                // >>> Correctifs clés:
                fillIf('#type_acteur', 'type_acteur_code', data);   // code (ind/etp/...)
                fillIf('#type_financement', 'type_financement', data);
                if (data.code_pays) setVal('#code_pays', data.code_pays);

                if (data.photo_url){
                    const pv = qs('#photo-preview');
                    pv.src = data.photo_url;
                    pv.style.display = 'block';
                }

                // PF/PM (radio)
                const tpers = data.type_personne ?? ''; // 'physique' | 'morale'
                if (tpers === 'physique'){
                    const r = qs('#personnePhysique'); if (r) r.checked = true; togglePersonneFields();

                    setVal('input[name="nom"]', data.libelle_court ?? '');
                    setVal('input[name="prenom"]', data.libelle_long ?? '');
                    setVal('input[name="emailI"]', data.email ?? '');
                    setVal('#date_naissance', data.date_naissance);
                    if (qs('#nationnalite-select')?.setSelectedValue) qs('#nationnalite-select').setSelectedValue(data.nationnalite ?? '');
                    setVal('#CodePostalI', data.CodePostalI);
                    setVal('input[name="AdressePostaleIndividu"]', data.AdressePostaleIndividu);
                    setVal('input[name="adresseSiegeIndividu"]', data.adresseSiegeIndividu);
                    setVal('input[name="telephoneBureauIndividu"]', data.telephoneBureauIndividu);
                    setVal('input[name="telephoneMobileIndividu"]', data.telephoneMobileIndividu);
                    setVal('input[name="numeroFiscal"]', data.numeroFiscal);
                    setVal('#genre', data.genre);
                    setVal('#situationMatrimoniale', data.situationMatrimoniale);
                    setVal('#piece_identite', data.piece_identite);
                    setVal('input[name="numeroPiece"]', data.numeroPiece);
                    setVal('input[name="dateEtablissement"]', data.dateEtablissement);
                    setVal('input[name="dateExpiration"]', data.dateExpiration);
                    setVal('#fonctionUser', data.fonctionUser);
                    waitAndSetSelectedValues(qs('#SecteurActI'), data.SecteurActI);
                } else if (tpers === 'morale'){
                    const r = qs('#personneMorale'); if (r) r.checked = true; togglePersonneFields();

                    setVal('input[name="libelle_long"]', data.libelle_long);
                    setVal('input[name="libelle_court"]', data.libelle_court);
                    setVal('input[name="date_creation"]', data.date_creation);
                    setVal('#FormeJuridique', data.FormeJuridique);
                    setVal('input[name="NumeroImmatriculation"]', data.NumeroImmatriculation);
                    setVal('input[name="nif"]', data.nif);
                    setVal('input[name="rccm"]', data.rccm);
                    setVal('input[name="CapitalSocial"]', data.CapitalSocial);
                    setVal('input[name="Numéroagrement"]', data.Numéroagrement);
                    setVal('input[name="CodePostaleEntreprise"]', data.CodePostaleEntreprise);
                    setVal('input[name="AdressePostaleEntreprise"]', data.AdressePostaleEntreprise);
                    setVal('input[name="AdresseSiègeEntreprise"]', data.AdresseSiègeEntreprise);
                    setVal('input[name="emailRL"]', data.emailRL);
                    setVal('input[name="telephone1RL"]', data.telephone1RL);
                    setVal('input[name="telephone2RL"]', data.telephone2RL);

                    waitAndSetSelectedValues(qs('#secteurActivite'), data.secteurActivite);
                    waitAndSetSelectedValues(qs('#nomPC'), data.nomPC);
                    const rl = Array.isArray(data.nomRL) ? data.nomRL[0] : data.nomRL;
                    if (rl) setTimeout(()=> setLookupValue('nomRL', rl), 300);
                }

                // mode update
                form.action = `${ROOT}/acteurs/${actorId}`;
                qs('#method').value = 'PUT';
                qs('#acteurPanelLabel').textContent = 'Modifier l’acteur';
                qs('#submit-button').textContent = 'Modifier';

                acteurPanel?.show();
            })
            .catch(err => { console.error(err); alert("Erreur lors du chargement de l'acteur."); });
    }
    window.openEdit = openEdit;
    // Filtres client (réutilisé après AJAX)
    window.applyFilters = function(){
        const searchEl = qs('#searchInput');
        const btnAll = qs('#filterAll'), btnAct = qs('#filterActive'), btnInact = qs('#filterInactive');
        const typeSel = qs('#filterTypeActeur'), finSel = qs('#filterTypeFin');

        const q = (searchEl?.value || '').trim().toLowerCase();
        const status = document.querySelector('#statusFilterGroup [data-filter].active')?.getAttribute('data-filter') || 'all';
        const fType = typeSel?.value || '';
        const fFin  = finSel?.value || '';

        const cards = Array.from(document.querySelectorAll('.actor-card-wrapper'));
        cards.forEach(card=>{
            const hay = [card.dataset.name, card.dataset.email, card.dataset.phone].join(' ').toLowerCase();
            const okQ = !q || hay.includes(q);
            const okStatus = status==='all' || card.dataset.status === status;
            const okType = !fType || card.dataset.typeacteur === fType;
            const okFin  = !fFin  || card.dataset.typefin === fFin;
            card.style.display = (okQ && okStatus && okType && okFin) ? '' : 'none';
        });

        const rows = Array.from(document.querySelectorAll('#table1 .actor-row'));
        rows.forEach(row=>{
            const hay = [row.dataset.name, row.dataset.email, row.dataset.phone].join(' ').toLowerCase();
            const okQ = !q || hay.includes(q);
            const okStatus = status==='all' || row.dataset.status === status;
            const okType = !fType || row.dataset.typeacteur === fType;
            const okFin  = !fFin  || row.dataset.typefin === fFin;
            row.style.display = (okQ && okStatus && okType && okFin) ? '' : 'none';
        });
    };

    // Activation filtres + refresh + toggle filtres
    (function(){
        const btnAll = qs('#filterAll'), btnAct = qs('#filterActive'), btnInact = qs('#filterInactive');
        [btnAll,btnAct,btnInact].forEach(btn=>{
            if (!btn) return;
            btn.addEventListener('click', e=>{
                e.preventDefault();
                [btnAll,btnAct,btnInact].forEach(b=>b?.classList.remove('active'));
                btn.classList.add('active');
                window.applyFilters();
            });
        });
        qs('#searchInput')?.addEventListener('input', window.applyFilters);
        qs('#filterTypeActeur')?.addEventListener('input', window.applyFilters);
        qs('#filterTypeFin')?.addEventListener('input', window.applyFilters);

        const advanced = document.getElementById('advancedFilters');
        document.getElementById('toggleFiltersLink')?.addEventListener('click', (e)=>{ e.preventDefault(); advanced.style.display = (advanced.style.display==='none' || !advanced.style.display) ? 'block' : 'none'; });
        document.getElementById('toggleFilters')?.addEventListener('click', (e)=>{ e.preventDefault(); advanced.style.display='none'; });
    })();
})();

(function(){
    // Base URL robuste (priorise window.ROOT si déjà présent dans ta page)
    const ROOT = (window.ROOT || @json(url('/')) || window.location.origin).replace(/\/+$/,'');

    document.addEventListener('click', function(e){
        const $ = (s, r=document)=>r.querySelector(s);

        // ===== EDIT =====
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn){
            e.preventDefault();
            const id = editBtn.getAttribute('data-id');
            if (id) window.openEdit(id);
            return;
        }

        // ===== DELETE (désactiver) via confirmDelete (DELETE) =====
        const delBtn = e.target.closest('.btn-delete');
        if (delBtn){
            e.preventDefault();
            const id = delBtn.getAttribute('data-id');
            if (!id) return;

            // URL depuis formulaire caché (fallback propre)
            const form = $(`#delete-form-${CSS.escape(id)}`);
            const url  = form?.getAttribute('action') || `${ROOT}/acteurs/${id}`;

            confirmDelete(
                url,
                // onSuccess : recharge simple et fiable
                () => window.location.reload(),
                {
                    title: 'Désactiver cet acteur ?',
                    text: 'Vous pourrez le réactiver plus tard.',
                    confirmButtonText: 'Oui, désactiver',
                    successMessage: 'Acteur désactivé avec succès.',
                    errorMessage: 'Échec de la désactivation.'
                }
            );
            return;
        }

        // ===== RESTORE (réactiver) via confirmPatch (PATCH override) =====
        const resBtn = e.target.closest('.btn-restore');
        if (resBtn){
            e.preventDefault();
            const id = resBtn.getAttribute('data-id');
            if (!id) return;

            // URL de réactivation (formulaire caché si présent)
            const form = $(`#restore-form-${CSS.escape(id)}`);
            const url  = form?.getAttribute('action') || `${ROOT}/admin/acteurs/${id}/restore`;

            confirmPatch(
                url,
                // onSuccess : recharge
                () => window.location.reload(),
                {
                    title: 'Réactiver cet acteur ?',
                    text: 'L’acteur sera de nouveau visible et utilisable.',
                    confirmButtonText: 'Oui, réactiver',
                    successMessage: 'Acteur réactivé avec succès.',
                    errorMessage: 'Échec de la réactivation.'
                }
            );
            return;
        }
    });
})();

    function confirmPatch(url, onSuccess, messages = {}) {
        confirmAction({
            title: messages.title || 'Êtes-vous sûr ?',
            text: messages.text || 'Confirmer cette action ?',
            confirmButtonText: messages.confirmButtonText || 'Oui, confirmer',
            cancelButtonText: messages.cancelButtonText || 'Annuler'
        }, function () {
            $.ajax({
                url: url,
                method: 'POST', // override en PATCH
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: {
                    _method: 'PATCH',
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    alert(messages.successMessage || 'Action effectuée avec succès.', 'success');
                    if (typeof onSuccess === 'function') onSuccess();
                },
                error: function (xhr) {
                    const msg = xhr?.responseJSON?.error
                        || xhr?.responseJSON?.message
                        || xhr?.responseText
                        || messages.errorMessage
                        || 'Une erreur est survenue.';
                    alert(msg, 'error');
                }
            });
        });
    }
</script>

@endsection
