<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
    <style type="text/css">
        html, body {
            height: 100%;
            padding: 0px;
            margin: 0px;
            overflow: hidden;
        }
        #controls {
            padding: 10px;
            background: #f4f4f4;
        }
    </style>
</head>
<body>
    <div id="controls">
        <label for="scale_select">Choisir l'échelle de temps :</label>
        <select id="scale_select">
            <option value="day">Jour</option>
            <option value="week">Semaine</option>
            <option value="month">Mois</option>
            <option value="quarter">Trimestre</option>
            <option value="year">Année</option>
        </select>
    </div>

    <div id="gantt_here" style="width:100%; height:90%;"></div>

    <script type="text/javascript">
        gantt.config.xml_date = "%Y-%m-%d %H:%i:%s"; // format pour charger les données
        gantt.config.date_format = "%d %F %Y"; // format d'affichage des dates dans les tâches
        // Configurer la locale en français
        gantt.i18n.setLocale({
            date: {
                month_full: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                    "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
                month_short: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil",
                    "Aoû", "Sep", "Oct", "Nov", "Déc"],
                day_full: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
                day_short: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"]
            },
            labels: {
                new_task: "Nouvelle tâche",
                icon_save: "Enregistrer",
                icon_cancel: "Annuler",
                icon_details: "Détails",
                icon_edit: "Modifier",
                icon_delete: "Supprimer",
                gantt_save_btn: "Enregistrer",
                gantt_cancel_btn: "Annuler",
                gantt_delete_btn: "Supprimer",
                confirm_closing: "Vos modifications seront perdues, êtes-vous sûr ?",
                confirm_deleting: "La tâche sera supprimée définitivement, êtes-vous sûr ?",
                section_description: "Description",
                section_time: "Période",
                section_type: "Type",

                /* Colonnes de la grille */
                column_wbs: "WBS",
                column_text: "Nom de la tâche",
                column_start_date: "Date de début",
                column_duration: "Durée",
                column_add: "",

                /* Confirmation pour les liens */
                link: "Lien",
                confirm_link_deleting: "sera supprimé",
                link_start: " (début)",
                link_end: " (fin)",

                type_task: "Tâche",
                type_project: "Projet",
                type_milestone: "Jalon",

                minutes: "Minutes",
                hours: "Heures",
                days: "Jours",
                weeks: "Semaines",
                months: "Mois",
                years: "Années",

                /* Popup de messages */
                message_ok: "OK",
                message_cancel: "Annuler",

                /* Contraintes */
                section_constraint: "Contrainte",
                constraint_type: "Type de contrainte",
                constraint_date: "Date de contrainte",
                asap: "Dès que possible",
                alap: "Aussi tard que possible",
                snet: "Ne pas commencer avant",
                snlt: "Ne pas commencer après",
                fnet: "Ne pas terminer avant",
                fnlt: "Ne pas terminer après",
                mso: "Doit commencer le",
                mfo: "Doit terminer le",

                /* Gestion des ressources */
                resources_filter_placeholder: "tapez pour filtrer",
                resources_filter_label: "cacher les vides"
            }
        });

        // Configuration de l'échelle de temps
        gantt.config.start_date = new Date(new Date().getFullYear() - 10, 0, 1);  // 25 ans dans le passé
        gantt.config.end_date = new Date(new Date().getFullYear() + 35, 11, 31);  // 25 ans dans le futur







        gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
        gantt.init("gantt_here");

        // Charge les données de l'API
        gantt.load("/api/data");

        // Configure le dataProcessor pour synchroniser les actions (CRUD)
        var dp = new gantt.dataProcessor("/api/");
        dp.init(gantt);
        dp.setTransactionMode("REST");

        dp.attachEvent("onAfterUpdate", function(id, action, tid, response) {
            if (action === "inserted") {
                $('#alertMessage').text('Ajout éffectué.');
                $('#alertModal').modal('show');
                console.log("Succès:", response);
            } else if (action === "updated") {
                $('#alertMessage').text('Modification effectué.');
                $('#alertModal').modal('show');
            } else if (action === "deleted") {
                $('#alertMessage').text('Suppression effectué.');
                $('#alertModal').modal('show');
            } else if (action === "error") {
                console.log("Échec:", response);
                alert("Une erreur est survenue lors de l'opération : " + response); // Alerte pour les erreurs
            }
        });


        // Nouvelle méthode pour configurer les échelles
        function setScaleConfig(scale) {
            switch (scale) {
                case "day":
                    gantt.config.scales = [
                        {unit: "day", step: 1, format: "%d %M"},
                    ];
                    break;
                case "week":
                    gantt.config.scales = [
                        {unit: "week", step: 1, format: "Semaine #%W"},
                        {unit: "day", step: 1, format: "%d %M"}
                    ];
                    break;
                case "month":
                    gantt.config.scales = [
                        {unit: "month", step: 1, format: "%F %Y"},
                        {unit: "week", step: 1, format: "Semaine #%W"}
                    ];
                    break;
                case "quarter":
                    gantt.config.scales = [
                        {unit: "quarter", step: 1, format: function(date) {
                            var month = date.getMonth();
                            var q_num = Math.floor(month / 3) + 1;
                            return "T" + q_num;  // Trimestre
                        }},
                        {unit: "month", step: 1, format: "%M"}
                    ];
                    break;
                case "year":
                    gantt.config.scales = [
                        {unit: "year", step: 1, format: "%Y"},
                        {unit: "month", step: 1, format: "%M"}
                    ];
                    break;
            }
            gantt.render();  // Réinitialise l'échelle avec les nouvelles configurations
        }

        // Liste déroulante pour le changement d'échelle
        document.getElementById("scale_select").addEventListener("change", function() {
            var scale = this.value;
            setScaleConfig(scale);
        });

        // Définir l'échelle initiale
        setScaleConfig("day");
    </script>
</body>
</html>
