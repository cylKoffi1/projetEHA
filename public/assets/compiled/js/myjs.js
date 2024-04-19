function initDataTable(userNameReplace, table, title) {
    // Chemin de l'image
    var imagePath;

    // Effectuer une requête AJAX pour récupérer le chemin de l'image encodée en base64
    $.ajax({
        url: '/getBase64Image',
        type: 'GET',
        async: false, // Attendre la réponse avant de continuer
        success: function(response) {
            imagePath = "data:image/png;base64," + response.base64Image;
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });

    var logo ="http://localhost:8000/betsa/assets/images/ehaImages/armoirie.png";
    var now = new Date();
    var dateTime = now.toLocaleString("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "numeric",
        minute: "numeric",
    });
    var lastColumnAction = $("#" + table + " thead tr:first-child th:last-child").text().trim() === "Action";

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
                        columns: lastColumnAction ? ":not(:last-child)" : "",// Exclure la dernière colonne de l'exportation
                },
            },
            {
                extend: "pdfHtml5",
                text: "Imprimer",
                orientation: 'landscape',
                title: "",
                filename: title,
                exportOptions: {
                    columns: function (index, data, node) {
                        // Vérifier si la dernière colonne est "Action"
                        var lastColumnAction = $("#" + table + " thead tr:first-child th").last().text().trim() === "Action";
                        // Exclure la dernière colonne si "Action" est présente
                        if (lastColumnAction && index === $(node).closest('tr').find('th').length - 1) {
                            return false;
                        }
                        return true;
                    }
                },
                customize: function (doc) {
                    var headerHeight = 20; // Hauteur du header en pourcentage
                    var footerHeight = 10; // Hauteur du footer en pourcentage
                    var bodyHeight = 70; // Hauteur du contenu principal en pourcentage

                    var headerMargin = [30, 30]; // Marge du header
                    var bodyMargin = [0, 0]; // Marge du contenu principal
                    var footerMargin = [10, 0]; // Marge du footer

                    var totalHeight = 100; // Hauteur totale de la page

                    // Calculer les hauteurs en points
                    var headerHeightPoints = (headerHeight / totalHeight) * 100;
                    var bodyHeightPoints = (bodyHeight / totalHeight) * 100;
                    var footerHeightPoints = (footerHeight / totalHeight) * 100;
                    /*text: [
                        'Image \n',
                            { text: ' \n ', fontSize: 9 },
                            { text: 'GERAC-EHA', fontSize: 9 }
                        ],*/
                        function htmlDecode(input) {
                            var doc = new DOMParser().parseFromString(input, "text/html");
                            return doc.documentElement.textContent;
                          }

                    // Ajuster les marges pour correspondre aux hauteurs
                    headerMargin[1] = headerHeightPoints;
                    bodyMargin[1] = bodyHeightPoints;
                    footerMargin[1] = footerHeightPoints;
                    // Définir le header avec les marges ajustées
                    doc['header'] = function() {
                        return {
                            columns: [
                                {
                                    alignment: 'center',
                                    table: {
                                        widths: ['33.33%', '33.33%', '33.33%'],hLineWidth: 0,
                                        border: [false,false,false],
                                        vLineWidth: 0,
                                        body: [
                                            // Première ligne
                                            [
                                                {
                                                    alignment: 'left',hLineWidth: 0,
                                                    border: false,
                                                    vLineWidth: 0,
                                                    stack: [
                                                        {
                                                            image: imagePath, // chemin de l'image
                                                            width: 25 // largeur de l'image en pourcentage
                                                        },
                                                        {
                                                            text: 'GERAC-EHA', // texte à afficher à côté de l'image
                                                            fontSize: 9 // taille de la police du texte
                                                        }
                                                    ]
                                                },
                                                {
                                                    text: [{ text: '\n' }, { text: title.toUpperCase(), bold: true, fontSize: 12 }],
                                                    alignment: 'center',hLineWidth: 0,
                                                    border: false,
                                                    vLineWidth: 0
                                                },
                                                {
                                                    text: [
                                                        'Impression le ' + dateTime,
                                                        { text: '\n' },
                                                        { text: '\nImprimé par: ' + htmlDecode(userName), fontSize: 9 }
                                                    ],
                                                    alignment: 'right',hLineWidth: 0,
                                                    border: false,
                                                    vLineWidth: 0
                                                },
                                            ]
                                        ],
                                    },hLineWidth: 0,
                                    border: false,
                                    vLineWidth: 0
                                },
                            ],
                        };
                    };



                    // Définir le footer avec les marges ajustées
                    doc['footer'] = function(page, pages) {
                        return {
                            columns: [
                                {
                                    // This is the right column
                                    alignment: 'right',
                                    text: ['page ', { text: page.toString() },  ' sur ', { text: pages.toString() }]
                                }
                            ],
                            margin: footerMargin
                        };
                    };
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
