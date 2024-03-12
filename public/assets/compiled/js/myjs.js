function initDataTable(userNameReplace, table, title) {
    var logo =
        "http://localhost:8000/betsa/assets/images/ehaImages/armoirie.png";
    var now = new Date();
    var dateTime = now.toLocaleString("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "numeric",
        minute: "numeric",
    });
    var userName = userNameReplace;
    $("#" + table).DataTable({
        fixedColumns: true,
        language: {
            processing: "Traitement en cours...",
            search: "",
            searchPlaceholder: "Rechercher",
            lengthMenu: "Afficher _MENU_ lignes",
            info: "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            infoEmpty:
                "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
            infoFiltered:
                "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            infoPostFix: "",
            loadingRecords: "Chargement en cours...",
            zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            emptyTable: "Aucune donnée disponible dans le tableau",
            paginate: {
                first: "Premier",
                previous: "Pr&eacute;c&eacute;dent",
                next: "Suivant",
                last: "Dernier",
            },
            aria: {
                sortAscending:
                    ": activer pour trier la colonne par ordre croissant",
                sortDescending:
                    ": activer pour trier la colonne par ordre décroissant",
            },
        },
        select: {
            items: "cell",
            info: false,
        },
        scrollX: true,
        dom: "Bfrtip",
        lengthMenu: [
            [10, 25, 50, -1],
            ["10", "25", "50", "Tout"],
        ],

        buttons: [
            {
                extend: "pageLength",
                text(text) {
                    return "Afficher les lignes";
                },
            },
            {
                extend: "excelHtml5",
                text: "Exporter",
                title: title,
                exportOptions: {
                    columns: ":not(:last-child)", // Exclure la dernière colonne de l'exportation
                },
            },
            {
                extend: "print",
                title: "",
                text: "Imprimer",
                orientation: "portrait",
                pageSize: "A4",
                exportOptions: {
                    columns: ":not(:last-child)", // Exclure la dernière colonne de l'impression
                },
                customize: function (win) {
                    // Récupérer le nombre de colonnes
                    var numColumns = $("#" + table)
                        .DataTable()
                        .columns()
                        .header().length;
                    $(win.document.body).append('<style>@page { size: portrait; }</style>');
                    // Changer l'orientation si le nombre de colonnes est supérieur à 6
                    var pageSize = numColumns > 6 ? 'A3' : 'A4';

                    // Chemin de l'image
                    var imagePath = logo;

                    // Générer dynamiquement l'en-tête
                    var header =
                        "<tr><th colspan='" + numColumns + "'>" +
                        "<div class='container'>" +
                        "<div class='row'>" +
                        "<div class='col text-left'>" +
                        "<img src='" + imagePath + "' style='width: 70px; height: 50px; border-radius: 50px;' alt='Logo'>" +
                        "</div>" +
                        "<div class='col text-right'>" +
                        "<h>Impression le </h>" +
                        dateTime +
                        "</div>" +
                        "</div>" +

                        "<div class='row'>" +
                        "<div class='col text-center'>" +
                        "<h3>" + title + "</h3>" +
                        "</div>" +
                        "</div>" +

                        "<div class='row'>" +
                        "<div class='col text-left'>" +
                        "<p>GERAC-EHA</p>" +
                        "</div>" +
                        "<div class='col text-right'>" +
                        "<p>Imprimé par: " + userName + "</p>" +
                        "</div>" +
                        "</div>" +
                        "</div></th></tr>";
                    //Ajouter l'en-tête personnalisé
                    $(win.document.body).find("thead").prepend(header);


                    //Personnaliser le pied de page

                    var footer =
                        '<div style="text-align:right; margin-top: 10px;">' +
                        '<p style="font-size: 12px; margin: 0;">Date impression: ' +
                        dateTime +
                        "</p>" +
                        '<p style="font-size: 12px; margin: 0;">Imprimé par: ' +
                        userName +
                        "</p>" +
                        "</div>";
                        // Ajouter la numérotation des pages

                    // Ajouter le pied de page personnalisé
                    $(win.document.body).find("tfoot").html(footer);

                    // Appliquer l'orientation et la taille de la page
                    $(win.document.body).css({
                        'orientation': 'landscape',
                        'pageSize': pageSize
                    });
                },
            },
        ],
    });
}

function goBack() {
    $(document).ready(function() {
         window.history.back();
    })
}
// Ajoutez cette fonction dans votre fichier JavaScript
function logout(formId) {
    document.getElementById(formId).submit();
}

function showSelect(selectId) {
    // Hide all selects
    document.getElementById("bailleur").style.display = "none";
    document.getElementById("agence").style.display = "none";
    document.getElementById("ministere").style.display = "none";

    // Show the selected select
    document.getElementById(selectId).style.display = "block";
}

function showSelect_r(selectId) {
    $("#niveau_acces_id").prop("disabled", false);
    console.log(selectId);
    if (selectId === "na") {
        document.getElementById("reg").style.display = "none";
        document.getElementById("dis").style.display = "none";
        document.getElementById("dep").style.display = "none";
        // Show the selected select
        document.getElementById("na").style.display = "block";
        document.getElementById("niveau_acces_id_label").innerHTML = "Pays";
        $("#niveau_acces_id").val("na");
    }
    if (selectId === "di") {
        document.getElementById("reg").style.display = "none";
        document.getElementById("na").style.display = "none";
        document.getElementById("dep").style.display = "none";
        // Show the selected select
        document.getElementById("dis").style.display = "block";
        document.getElementById("niveau_acces_id_label").innerHTML = "District";
        $("#niveau_acces_id").val("di");
    }
    if (selectId === "re") {
        document.getElementById("dis").style.display = "none";
        document.getElementById("na").style.display = "none";
        document.getElementById("dep").style.display = "none";
        // Show the selected select
        document.getElementById("reg").style.display = "block";
        document.getElementById("niveau_acces_id_label").innerHTML = "Région";
        $("#niveau_acces_id").val("re");
    }
    if (selectId === "de") {
        document.getElementById("dis").style.display = "none";
        document.getElementById("na").style.display = "none";
        document.getElementById("reg").style.display = "none";
        // Show the selected select
        document.getElementById("dep").style.display = "block";
        document.getElementById("niveau_acces_id_label").innerHTML =
            "Departement";
        $("#niveau_acces_id").val("de");
    }
}
function updateSousDomaine(selectElement) {
    var selectedDomaine = selectElement.val();

    // Effectuez une requête AJAX pour obtenir les sous-domaines
    $.ajax({
        type: "GET",
        url: "/admin/get-sous_domaines/" + selectedDomaine,
        success: function (data) {
            console.log(data);
            var sousDomainesSelect = $("#sous_domaine"); // Correction: Utilisation de l'ID directement

            sousDomainesSelect.empty(); // Effacez les options précédentes

            // Ajoutez les options des sous-domaines récupérés
            $.each(data.sous_domaines, function (key, value) {
                sousDomainesSelect.append(
                    $("<option>", {
                        value: key,
                        text: value,
                    })
                );
            });

            sousDomainesSelect.trigger("change");
        },
    });
}

function getGroupeUserByFonctionId(selectElement) {
    var selectedFonction = selectElement.val();

    // Effectuez une requête AJAX pour obtenir les sous-domaines
    $.ajax({
        type: "GET",
        url: "/admin/get-groupes/" + selectedFonction,
        success: function (data) {
            console.log(data);
            var groupess = $("#group_user"); // Correction: Utilisation de l'ID directement

            groupess.empty(); // Effacez les options précédentes

            // Ajoutez les options des sous-domaines récupérés
            $.each(data.groupes, function (key, value) {
                groupess.append(
                    $("<option>", {
                        value: key,
                        text: value,
                    })
                );
            });

            groupess.trigger("change");
        },
    });
}

function reloadSidebar() {
    $.ajax({
        url: "/admin/initSidebar",
        type: "GET",
        dataType: "json",
        success: function (resp) {

            console.log(resp.rubriques);
            $("#menu_sidebar").html(resp.rubriques);
        },
        error: function (resp) {
            console.log("Something went wronggg");
        },
    });
}
