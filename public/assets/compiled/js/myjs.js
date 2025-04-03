function initDataTable(userNameReplace, table, title) {
    let imagePath;

    // Charger l'image base64 de manière synchrone
    loadImage();

    const now = new Date();
    const dateTime = formatDate(now);
    const userName = userNameReplace;
    const lastColumnAction = isLastColumnAction(table);

    initializeDataTable(table, title, dateTime, userName, lastColumnAction);

    function loadImage() {
        $.ajax({
            url: 'http://127.0.0.1:8000/getBase64Image',
            type: 'GET',
            async: false,
            success: function (response) {
                imagePath = "data:image/png;base64," + response.base64Image;
            },
            error: function (xhr, status, error) {
                console.error("Erreur lors du chargement de l'image :", error);
            }
        });
    }

    function formatDate(date) {
        return date.toLocaleString("fr-FR", {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "numeric",
            minute: "numeric",
        });
    }

    function isLastColumnAction(table) {
        return $("#" + table + " thead tr:first-child th:last-child").text().trim().toLowerCase() === "action";
    }

    function initializeDataTable(table, title, dateTime, userName, lastColumnAction) {
        $("#" + table).DataTable({
            fixedColumns: true,
            language: getLanguageSettings(),
            select: { items: "cell", info: false },
            scrollX: true,
            dom: "Bfrtip",
            lengthMenu: [[10, 25, 50, -1], ["10", "25", "50", "Tout"]],
            buttons: getButtons(title, dateTime, userName, lastColumnAction)
        });
    }

    function getLanguageSettings() {
        return {
            processing: "Traitement en cours...",
            search: "",
            searchPlaceholder: "Rechercher",
            lengthMenu: "Afficher _MENU_ lignes",
            info: "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
            infoEmpty: "Aucun élément à afficher",
            infoFiltered: "(filtré de _MAX_ éléments au total)",
            loadingRecords: "Chargement...",
            zeroRecords: "Aucun résultat trouvé",
            emptyTable: "Aucune donnée disponible",
            paginate: {
                first: "Premier",
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier",
            },
            aria: {
                sortAscending: ": activer pour trier croissant",
                sortDescending: ": activer pour trier décroissant",
            },
        };
    }

    function getButtons(title, dateTime, userName, lastColumnAction) {
        return [
            {
                extend: "pageLength",
                text: "Afficher les lignes",
            },
            {
                extend: "excelHtml5",
                text: "Exporter",
                title: title,
                exportOptions: {
                    columns: lastColumnAction ? ":not(:last-child)" : "",
                },
            },
            {
                text: "Imprimer",
                action: function (e, dt, node, config) {
                    generatePDF(title, dateTime, userName, lastColumnAction);
                }
            }
        ];
    }

    function generatePDF(title, dateTime, userName, lastColumnAction) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'pt', 'a4');
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        const marginX = 40;
        const logoWidth = 40;
        const logoHeight = 35;

        // === 💠 FOND BLEU HEADER ===
        const headerHeight = 90;
        doc.setFillColor(0, 0, 70);
        doc.rect(0, 0, pageWidth, headerHeight, 'F');

        // === 📝 TEXTE DU HEADER SUR LE FOND BLEU ===
        doc.setTextColor(255, 255, 255); // texte blanc
        doc.setFont("helvetica", "bold");
        doc.setFontSize(10);
        doc.text("BTP-PROJECT", marginX, 20);
        doc.text(`Impression le : ${dateTime}`, pageWidth - marginX, 20, { align: "right" });

        // Titre centré
        doc.setFontSize(13);
        doc.text(title.toUpperCase(), pageWidth / 2, 40, { align: "center" });

        // Logo à gauche
        doc.addImage(imagePath, 'PNG', marginX, 50, logoWidth, logoHeight);

        // "Imprimé par" à droite
        doc.setTextColor(255, 255, 255); // texte blanc
        doc.setFont("helvetica", "bold");
        doc.setFontSize(10);
        doc.setFont("helvetica", "normal");
        doc.text(`Imprimé par : ${$('<div>').html(userName).text()}`, pageWidth - marginX, 70, { align: "right" });

        // === 🔢 TABLEAU ===
        const columns = getColumns(table, lastColumnAction);
        const rows = getRows(table, columns, lastColumnAction);

        doc.autoTable({
            startY: headerHeight + 20,
            head: [columns.map(col => col.header)],
            body: rows.map(row => columns.map(col => row[col.dataKey])),
            styles: {
                fontSize: 9,
                cellPadding: 4,
                overflow: 'linebreak'
            },
            headStyles: {
                fillColor: [240, 240, 240],
                textColor: 20,
                fontStyle: 'bold'
            },
            margin: { top: 90, left: marginX, right: marginX },
            didDrawPage: function (data) {
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(100);
                doc.text(`Page ${doc.internal.getCurrentPageInfo().pageNumber} sur ${pageCount}`, pageWidth / 2, pageHeight - 20, { align: 'center' });
            }
        });

        doc.save(title + '.pdf');
    }

    function getColumns(table, lastColumnAction) {
        const columns = [];
        $("#" + table + " thead th").each(function () {
            const text = $(this).text().trim();
            if (!lastColumnAction || text.toLowerCase() !== "action") {
                columns.push({ header: text, dataKey: text });
            }
        });
        return columns;
    }

    function getRows(table, columns, lastColumnAction) {
        const rows = [];
        $("#" + table + " tbody tr").each(function () {
            const row = {};
            $(this).find("td").each(function (index) {
                if (!lastColumnAction || index !== $(this).closest("tr").find("td").length - 1) {
                    row[columns[index].dataKey] = $(this).text().trim();
                }
            });
            rows.push(row);
        });
        return rows;
    }
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
