@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Suspension d’un projet</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('projets.suspension.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <label for="code_projet">Projet à suspendre *</label>
                        <select name="code_projet" class="form-control" required>
                            <option value="">-- Sélectionnez un projet --</option>
                            @foreach($projets as $projet)
                                <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }} - {{ $projet->libelle_projet }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="motif">Motif de la suspension *</label>
                        <textarea name="motif" class="form-control" rows="2" required placeholder="Expliquez la raison de la suspension..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning mt-3">Suspendre le projet</button>
            </form>
        </div>
    </div>

    <hr>

    <div class="card mt-4">
        <div class="card-header">
            <h5>📋 Projets suspendus</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="tableSuspendus">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Date suspension</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projetsSuspendus as $projet)
                        <tr>
                            <td>{{ $projet->code_projet }}</td>
                            <td>{{ $projet->libelle_projet }}</td>
                            <td>{{ $projet->statuts->date_statut ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableSuspendus', "Liste des projets suspendus");
    });
</script>
@endsection
