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

    .btn-primary.openDrawer.active {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
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
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Approuver projets</h5>

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
            @if(session('success') || session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    let type = "{{ session('success') ? 'success' : 'error' }}";
                    let message = "{{ session('success') ?? session('error') }}";

                    alert((type === 'success' ? '✅ ' : '❌ ') + message);
                });
            </script>
            @endif

            </div>
            <div class="card-content">
                <div class="col-12">

                    <div class="container">
                        <div class="row">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <input type="text" id="searchInput" class="form-control w-50" placeholder="Rechercher un projet...">
                                
                                <div class="d-flex align-items-center">
                                    <label class="me-2 mb-0">Afficher</label>
                                    <select id="itemsPerPage" class="form-select" style="width: auto;">
                                        <option value="6">6</option>
                                        <option value="9" selected>9</option>
                                        <option value="27">27</option>
                                        <option value="45">45</option>
                                    </select>
                                </div>
                            </div>
                            <div id="paginationControls" class="d-flex justify-content-center my-3"></div>


                        </div>
                        <div class="row">
                            @foreach ($projets as $approbation)
                                <div class="col-md-4 projet-card">
                                    <div class="card my-3">
                                        <div class="card-body position-relative">
                                            {{-- Étiquette de statut --}}
                                            

                                            <h6 class="code">{{ $approbation?->projet?->code_projet }}</h6>
                                            <h6 class="libelle">{{ $approbation?->projet?->libelle_projet }}</h6>
                                            <p class="commentaire">{{ $approbation?->projet?->commentaire }}</p>
                                           
                                            <a href="#" class="btn btn-primary openDrawer bottom-0 " data-code="{{ $approbation->codeEtudeProjets }}">Détails</a>
                                        </div>

                                    </div>
                                </div>

                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Drawer Bootstrap (ou panneau latéral custom) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="projetDetailDrawer" aria-labelledby="drawerLabel" style="width: 80%; height: calc(100vh - 90px); top: auto; overflow-y: auto;">
    <div class="offcanvas-header bg-light">
        <h5 id="drawerLabel">Détail du Projet</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body" id="drawerContent">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </div>
</div>

<script>
        $(document).ready(function() {
            initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table', 'Listes des renforcements de capacités');
        });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    const paginationControls = document.getElementById('paginationControls');
    const cards = Array.from(document.querySelectorAll('.projet-card'));

    let currentPage = 1;

    function getFilteredCards() {
        const filter = searchInput.value.toLowerCase();
        return cards.filter(card => {
            const code = card.querySelector('.code')?.textContent.toLowerCase() || '';
            const libelle = card.querySelector('.libelle')?.textContent.toLowerCase() || '';
            const commentaire = card.querySelector('.commentaire')?.textContent.toLowerCase() || '';
            return code.includes(filter) || libelle.includes(filter) || commentaire.includes(filter);
        });
    }

    function renderPagination(totalItems, itemsPerPage) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Précédent';
        prevBtn.className = 'btn btn-outline-primary mx-1';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            currentPage--;
            render();
        };

        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Suivant';
        nextBtn.className = 'btn btn-outline-primary mx-1';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => {
            currentPage++;
            render();
        };

        paginationControls.appendChild(prevBtn);
        const pageInfo = document.createElement('span');
        pageInfo.className = 'mx-3 align-self-center text-muted';
        pageInfo.textContent = `Page ${currentPage} sur ${totalPages}`;
        paginationControls.appendChild(pageInfo);
        paginationControls.appendChild(nextBtn);
    }

    function render() {
        const itemsPerPage = parseInt(itemsPerPageSelect.value);
        const filtered = getFilteredCards();

        cards.forEach(card => card.style.display = 'none');

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        filtered.slice(start, end).forEach(card => {
            card.style.display = '';
        });

        renderPagination(filtered.length, itemsPerPage);
    }

    searchInput.addEventListener('input', () => {
        currentPage = 1;
        render();
    });

    itemsPerPageSelect.addEventListener('change', () => {
        currentPage = 1;
        render();
    });

    render(); // init
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const drawer = new bootstrap.Offcanvas(document.getElementById('projetDetailDrawer'));
    
    document.querySelectorAll('.openDrawer').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const code = this.dataset.code;

            // Retirer la classe active de tous les boutons
            document.querySelectorAll('.openDrawer').forEach(btn => {
                btn.classList.remove('active');
            });

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');

            // Afficher loading
            const content = document.getElementById('drawerContent');
            content.innerHTML = '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';

            fetch(`{{ url('/')}}/projets/validation/${code}`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(err => {
                    content.innerHTML = '<div class="alert alert-danger">Erreur de chargement.</div>';
                });

            drawer.show();
        });
    });
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: "{{ session('success') }}",
            });
        @elseif(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
            });
        @endif
    });
</script>
@endsection
