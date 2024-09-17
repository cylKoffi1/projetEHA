@extends('layouts.app')
<style>
    .file-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        margin-bottom: 15px;
        position: relative;
        width: 150px;
        height: 150px;
    }
    .file-card img {
        max-width: 100px;
        max-height: 100px;
    }
    .file-card .file-name {
        margin-top: 100px;
        font-size: 12px;
    }
    .file-card .upload-icon {
        position: absolute;
        top: 10px;
        right: 22px;
        font-size: 24px;
        cursor: pointer;
    }
    #file-display {
        display: flex;
        flex-wrap: wrap;
    }
</style>
@section('content')

@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif
<section id="multiple-column-form">
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
                            <li class="breadcrumb-item active" aria-current="page">Validation</li>
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
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Validation de projet</h5>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            </div>
            <div class="card-content">
                <div class="col-12">

                    <div class="container">
                        @foreach ($projects as $project)
                            <div class="mb-4">
                                <h5>{{ $project->title }}</h5>

                                @if ($project->typeDemandeur === 'entreprise')
                                    <h2>Informations sur l'entreprise</h2>
                                    <p><strong>Nom de l'entreprise :</strong> {{ $project->entreprise->nomEntreprise }}</p>
                                    <p><strong>Raison sociale :</strong> {{ $project->entreprise->raisonSociale }}</p>
                                    <p><strong>Numéro d'immatriculation :</strong> {{ $project->entreprise->numeroImmatriculation }}</p>
                                    <p><strong>Adresse siège social :</strong> {{ $project->entreprise->adresseSiegeSocial }}</p>
                                    <p><strong>Numéro de téléphone :</strong> {{ $project->entreprise->numeroTelephone }}</p>
                                    <p><strong>Adresse email :</strong> {{ $project->entreprise->adresseEmail }}</p>
                                    <p><strong>Site web :</strong> {{ $project->entreprise->siteWeb }}</p>
                                    <p><strong>Nom du responsable du projet :</strong> {{ $project->entreprise->nomResponsableProjet }}</p>
                                    <p><strong>Fonction du responsable :</strong> {{ $project->entreprise->fonctionResponsable }}</p>
                                    <p><strong>Capital social :</strong> {{ $project->entreprise->capitalSocial }}</p>
                                    <p><strong>Informations supplémentaires 1 :</strong> {{ $project->entreprise->infoSupplementaire1 }}</p>
                                    <p><strong>Informations supplémentaires 2 :</strong> {{ $project->entreprise->infoSupplementaire2 }}</p>
                                @elseif ($project->typeDemandeur === 'particulier')
                                    <h2>Informations sur le particulier</h2>
                                    <p><strong>Nom et prénom :</strong> {{ $project->particulier->nomPrenom }}</p>
                                    <p><strong>Statut professionnel :</strong> {{ $project->particulier->statutProfessionnel }}</p>
                                    <p><strong>Numéro d'immatriculation individuelle :</strong> {{ $project->particulier->numeroImmatriculationIndividuelle }}</p>
                                    <p><strong>Adresse :</strong> {{ $project->particulier->adresseEntreprise }}</p>
                                    <p><strong>Numéro de téléphone :</strong> {{ $project->particulier->numeroTelephone }}</p>
                                    <p><strong>Adresse email :</strong> {{ $project->particulier->adresseEmail }}</p>
                                    <p><strong>Activité principale :</strong> {{ $project->particulier->activitePrincipale }}</p>
                                    <p><strong>Nom commercial :</strong> {{ $project->particulier->nomCommercial }}</p>
                                    <p><strong>Coordonnées bancaires :</strong> {{ $project->particulier->coordonneesBancaires }}</p>
                                    <p><strong>Références :</strong> {{ $project->particulier->references }}</p>
                                    <p><strong>Informations supplémentaires 3 :</strong> {{ $project->particulier->infoSupplementaire3 }}</p>
                                    <p><strong>Informations supplémentaires 4 :</strong> {{ $project->particulier->infoSupplementaire4 }}</p>
                                @endif

                                <h6>Documents associés</h6>
                                <ul class="list-unstyled">
                                @if($project->files && $project->files->count() > 0)
                                    @foreach ($project->files as $file)
                                        <li>
                                            <a href="{{ asset('storage/' . $file->file_path) }}" class="btn btn-link">
                                                {{ $file->file_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                @else
                                    <li>Aucun fichier disponible pour ce projet.</li>
                                @endif

                                </ul>

                                @if ($project->status === 'pending' && $project->current_approver === auth()->user()->approbateur->codeAppro)
                                    <form action="{{ route('projects.validate', $project->codeEtudeProjets) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">Valider le projet</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>


                </div>
            </div>
        </div>
    </div>
</section>

@endsection
