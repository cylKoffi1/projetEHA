<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>


<table class="table table-striped table-bordered">
    <thead>
        <tr>
            @foreach ($currentHeaders['main'] as $header)
                <th>{{ $header }}</th>
            @endforeach
            @foreach ($resultats as $table => $result)
                @foreach ($result['columns'] as $column)
                    <th>{{ ucfirst($column) }}</th>
                @endforeach
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($resultats as $table => $result)
            @foreach ($result['data'] as $row)
                <tr>
                    <td>{{ $row->code }}</td> <!-- Mettez à jour selon votre logique -->
                    <!-- Ajoutez ici d'autres colonnes en fonction de votre modèle -->
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe 3');
    });
</script>
