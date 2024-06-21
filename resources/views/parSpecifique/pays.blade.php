@extends('layouts.app')


@section('content')


<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
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
                            // Implémentez la logique pour obtenir la date actuelle au format souhaité
                            var currentDate = new Date();
                            return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
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
                    @can("ajouter_ecran_" . $ecran->id)
                        <a  href="#" data-toggle="modal" data-target="#paysModal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                    @endcan

                   

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
            <div style="text-align: center;">
                <h5 class="card-title"> Liste des pays</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Pays</th>
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
                        <td>{{ $p->codeTel }}</td>
                        <td>
                            @if ($p->armoirie)
                                <img style="width: 30px; height: 30px;" src="{{ asset("armoiries/$p->armoirie") }}" alt="Armoirie du pays">
                            @endif
                        </td>
                        
                        <td>
                            @if ($p->flag)
                                <img style="width: 30px; height: 30px;" src="{{ asset("drapeaux/$p->flag") }}" alt="Drapeau du pays">
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    <li><a class="dropdown-item" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-plus-circle me-3"></i> Détails</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>




    <!-- Modal -->
    <div class="modal fade" id="paysModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement de Pays</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <!-- // Basic multiple Column Form section start -->
                    <section id="multiple-column-form">
                        <div class="row match-height">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <form class="form" method="POST" enctype="multipart/form-data" data-parsley-validate action="{{ route('pays.store') }}">
                                                @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                                <div class="row">
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="alpha2">Code
                                                                :</label>
                                                            <input type="text" class="form-control" id="code" name="code" placeholder="Code" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="alpha2">Code alpha-2
                                                                :</label>
                                                            <input type="text" class="form-control" id="alpha2" name="alpha2" placeholder="Alpha-2" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label class="form-label" for="alpha3">Code alpha-3
                                                                :</label>
                                                            <input type="text" placeholder="Code alpha-3" class="form-control" id="alpha3" name="alpha3" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label class="form-label" for="nom_en_gb">Nom (en anglais)
                                                                :</label>
                                                            <input type="text" class="form-control" id="nom_en_gb" name="nom_en_gb" placeholder="Nom anglais" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label class="form-label" for="nom_fr_fr">Nom (en français)
                                                                :</label>
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
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                    <button type="submit" class="btn btn-primary" id="enregistrerPays">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- // Basic multiple Column Form section end -->
                </div>

            </div>
        </div>
    </div>


</section>


<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des pays')
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-pays-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
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
