@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;">
                    <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span>
                </li>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Paramètre spécifiques</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pays</li>
                    </ol>
                </nav>
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
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                <h5 class="card-title">
                    Ajout d'un pays
                </h5>

                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
        
        <div class="card-body">
            <!-- Formulaire d'ajout directement sur la page -->
            <section id="multiple-column-form">
                <div class="row match-height">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <form class="form" id="paysForm" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="alpha2">Code :</label>
                                                    <input type="text" class="form-control" id="code" name="code" placeholder="Code" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="alpha2">Code alpha-2 :</label>
                                                    <input type="text" class="form-control" id="alpha2" name="alpha2" placeholder="Alpha-2" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="alpha3">Code alpha-3 :</label>
                                                    <input type="text" placeholder="Code alpha-3" class="form-control" id="alpha3" name="alpha3" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="alpha3">Devise :</label>
                                                    <select name="code_devise" id="code_devise" class="form-control">
                                                        <option value="">Selectionner la devise</option>
                                                        @foreach ($code_devises as $code_devise)
                                                            <option value="{{ $code_devise->code_long }}">{{ $code_devise->code_long }}: {{ $code_devise->libelle }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="nom_en_gb">Nom (en anglais) :</label>
                                                    <input type="text" class="form-control" id="nom_en_gb" name="nom_en_gb" placeholder="Nom anglais" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="nom_fr_fr">Nom (en français) :</label>
                                                    <input type="text" class="form-control" id="nom_fr_fr" name="nom_fr_fr" placeholder="Nom français" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label for="codeTel">Code téléphonique :</label>
                                                    <input type="text" class="form-control" id="codeTel" placeholder="Code téléphonique" name="codeTel">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label for="armoirie" class="form-label">Armoirie</label>
                                                    <input class="form-control" type="file" id="armoirie" name="armoirie">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label for="drapeaux" class="form-label">Drapeaux</label>
                                                    <input class="form-control" type="file" id="drapeaux" name="flag">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label for="zoom max" class="form-label">zoom max</label>
                                                    <input class="form-control" type="text" id="zoomMa" name="zoomMa" >
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mandatory">
                                                    <label for="zoom min" class="form-label">zoom min</label>
                                                    <input class="form-control" type="text" id="zoomMi" name="zoomMi">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary me-1 mb-1" id="submitBtn">Enregistrer</button>
                                                <button type="button" onclick="resetForm()" class="btn btn-light-secondary me-1 mb-1">Réinitialiser</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Liste des pays -->
            <div style="text-align: center; margin-top: 30px;">
                <h5 class="card-title">Liste des pays</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Pays</th>
                            <th>Devise</th>
                            <th>Code tel</th>
                            <th>Armoirie</th>
                            <th>Drapeau</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pays as $p)
                        <tr>
                            <td>{{ $p->code }}</td>
                            <td>{{ $p->nom_fr_fr }}</td>
                            <td>{{ $p->code_devise }}</td>
                            <td>{{ $p->codeTel }}</td>
                            <td>
                                @if ($p->armoirie_url)
                                    <img style="width: 30px; height: 30px;"
                                        src="{{ $p->armoirie_url  }}"
                                        alt="Armoirie du pays">
                                @endif
                            </td>
                            <td>
                                @if ($p->flag_url)
                                    <img style="width: 30px; height: 30px;"
                                        src="{{ $p->flag_url }}"
                                        alt="Drapeau du pays">
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                        <span style="color: white"></span>
                                    </a>
                                    <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                        <li>
                                            <a class="dropdown-item" onclick="loadPaysData({{ $p->id }})">
                                                <i class="bi bi-pencil-square me-3"></i> Modifier
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('pays.destroy', $p->id) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item btn-supprimer" onclick="return confirms(event,'Êtes-vous sûr de vouloir supprimer ce pays ?')">
                                                    <i class="bi bi-trash3-fill me-3"></i> Supprimer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}
.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
}

</style>
<script>
let currentEditId = null;

// Fonction pour charger les données d'édition
function loadPaysData(id) {
    currentEditId = id;
    
    fetch(`{{ url("/") }}/pays/${id}/edit`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            // Remplir le formulaire
            document.getElementById('code').value = data.code;
            document.getElementById('alpha2').value = data.alpha2;
            document.getElementById('alpha3').value = data.alpha3;
            document.getElementById('nom_en_gb').value = data.nom_en_gb;
            document.getElementById('nom_fr_fr').value = data.nom_fr_fr;
            document.getElementById('codeTel').value = data.codeTel;
            document.getElementById('code_devise').value = data.code_devise;
            document.getElementById('zoomMa').value = data.maxZoom;
            document.getElementById('zoomMi').value = data.minZoom;
            
            // Changer le texte et la classe du bouton submit
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.textContent = 'Modifier';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-warning');
            
            // Ajouter un champ caché pour l'ID
            if (!document.getElementById('pays_id')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.id = 'pays_id';
                input.name = 'id';
                document.querySelector('form').appendChild(input);
            }
            document.getElementById('pays_id').value = id;
            
            // Changer l'action du formulaire
            document.getElementById('paysForm').action = `/pays/${id}`;
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des données');
        });
}

// Gestion de la soumission du formulaire
document.getElementById('paysForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url =  currentEditId ? `{{ url("/") }}/pays/${currentEditId}` : '{{ url("/") }}/pays';
    const method = currentEditId ? 'PUT' : 'POST';
    
    // Ajouter la méthode HTTP correcte pour Laravel
    formData.append('_method', method === 'PUT' ? 'PUT' : 'POST');
    
    fetch(url, {
        method: 'POST', // Toujours POST mais avec _method override
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            alert(data.error, 'error');
        } else {
            alert(data.success || 'Opération réussie', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert(error.message || 'Une erreur est survenue', 'error');
    });
});

// Réinitialiser le formulaire
function resetForm() {
    currentEditId = null;
    document.getElementById('paysForm').reset();
    document.getElementById('paysForm').action = '/pays';
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.textContent = 'Enregistrer';
    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    
    const idInput = document.getElementById('pays_id');
    if (idInput) idInput.remove();
}
</script>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()?->acteur?->code_acteur }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des pays')
        
        $('#code').on('input', function() {
            var code = $(this).val();
            
            $.ajax({
                url: '{{ url("/") }}check-pays-code',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    code: code
                },
                success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerPays').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerPays').prop('disabled', false);
                    }
                }
            });
        });
    });


</script>
@endsection