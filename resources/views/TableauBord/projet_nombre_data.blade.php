@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <h4 class="mb-3">Détail — {{ ucfirst($acteur) }} — {{ $statut }}</h4>

    <div class="card shadow-sm">
        <div class="card-body p-3">

            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Code Projet</th>
                        <th>Libellé</th>
                        <th>Type</th>
                        <th>Acteur</th>
                        <th>Statut</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($projets as $p)
                    <tr>
                        <td>{{ $p->code_projet }}</td>
                        <td>{{ $p->libelle }}</td>
                        <td>{{ $p->type }}</td>
                        <td>{{ ucfirst($acteur) }}</td>
                        <td>{{ $p->statut }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
