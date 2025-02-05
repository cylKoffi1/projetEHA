@extends('layouts.app')

@section('content')
<div class="container mt-5">
<div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Etudes projets </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Approbation</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Approuver</li>
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
    <h2 class="mb-4">Validation des Demandes</h2>

    <!-- Affichage des messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Élément</th>
                <th>Statut</th>
                <th>Utilisateur</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="demandesTable">
            <!-- Les demandes seront chargées dynamiquement ici -->
        </tbody>
    </table>
</div>

<!-- Modal de Validation -->
<div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="validationModalLabel">Validation de la Demande</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="demandeId">
                <label for="commentaire">Commentaire :</label>
                <textarea id="commentaire" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btnRejeter">Rejeter</button>
                <button type="button" class="btn btn-success" id="btnValider">Valider</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        chargerDemandes();
    });

    function chargerDemandes() {
        fetch("/api/workflow/demandes-en-attente")
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById("demandesTable");
                tableBody.innerHTML = "";

                data.demandes.forEach(demande => {
                    let row = `
                        <tr>
                            <td>${demande.id}</td>
                            <td>${demande.type.libelle}</td>
                            <td>${demande.element.reference}</td>
                            <td>${demande.statut.libelle}</td>
                            <td>${demande.utilisateur_id}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="ouvrirModal(${demande.id})">📝 Valider</button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => console.error("Erreur lors du chargement des demandes:", error));
    }

    function ouvrirModal(demandeId) {
        document.getElementById("demandeId").value = demandeId;
        $("#validationModal").modal("show");
    }

    document.getElementById("btnValider").addEventListener("click", function () {
        traiterValidation("valider");
    });

    document.getElementById("btnRejeter").addEventListener("click", function () {
        traiterValidation("rejeter");
    });

    function traiterValidation(action) {
        const demandeId = document.getElementById("demandeId").value;
        const commentaire = document.getElementById("commentaire").value;
        const url = action === "valider" ? `/api/workflow/valider/${demandeId}` : `/api/workflow/rejeter/${demandeId}`;

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ etape_id: 1, commentaire })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            $("#validationModal").modal("hide");
            chargerDemandes();
        })
        .catch(error => console.error("Erreur:", error));
    }
</script>
@endsection
