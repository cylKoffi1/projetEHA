@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">🔔 Mes Notifications</h2>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Message</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody id="notificationsTable">
            <!-- Notifications chargées dynamiquement -->
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        chargerNotifications();
    });

    function chargerNotifications() {
        fetch("/api/notifications")
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById("notificationsTable");
                tableBody.innerHTML = "";

                data.notifications.forEach(notification => {
                    let row = `
                        <tr>
                            <td>${notification.id}</td>
                            <td>${notification.message}</td>
                            <td>${notification.statut.libelle}</td>
                            <td>${new Date(notification.created_at).toLocaleString()}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => console.error("Erreur lors du chargement des notifications:", error));
    }
</script>
@endsection
