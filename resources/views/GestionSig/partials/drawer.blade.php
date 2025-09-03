<!-- resources/views/GestionSig/partials/drawer.blade.php -->

<div id="drawerOverlay" class="drawer-overlay"></div>
<div id="projectDrawer" class="drawer" role="dialog" aria-modal="true">
    <div class="drawer-header">
        <div class="row-top">
            <span id="drawerTitle" class="drawer-title">Détails</span>
            <button class="drawer-close" type="button" onclick="window.closeProjectDrawer()" aria-label="Fermer">×</button>
        </div>
        <div class="breadcrumb" id="drawerBreadcrumb">—</div>
        <div>
            <span class="badge gray" id="drawerLevel">Niveau —</span>
            <span class="badge" id="drawerFilter">Filtre: cumul</span>
            <span class="badge green" id="drawerDomain">Domaine: Tous</span>
        </div>

        <div class="mt-2">
            <input type="text" id="drawerSearch" class="form-control" placeholder="Rechercher…">
        </div>
    </div>

    <div class="drawer-body">
        <div class="mb-2 small text-muted" id="drawerMeta"></div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code projet</th>
                        <th>Libellé</th>
                        <th>Coût</th>
                    </tr>
                </thead>
                <tbody id="drawerTableBody">
                    <tr>
                        <td colspan="4" class="text-center">Sélectionnez une cellule de la carte…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
