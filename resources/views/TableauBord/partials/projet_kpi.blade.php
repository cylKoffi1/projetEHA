{{-- KPI Cards pour chaque statut --}}
@php
    $statusKeys = ['Prévu','En cours','Clôturés','Terminé','Suspendu','Annulé','Redémarré'];
    $colors = [
        'Prévu' => 'linear-gradient(to right, rgba(104,155,225,.9), #e7f1ff)',
        'En cours' => 'linear-gradient(to right, rgba(40,167,69,.9), #d4edda)',
        'Clôturés' => 'linear-gradient(to right, rgba(23,162,184,.9), #d1ecf1)',
        'Terminé' => 'linear-gradient(to right, rgba(0,123,255,.9), #cce5ff)',
        'Suspendu' => 'linear-gradient(to right, rgba(255,193,7,.9), #fff3cd)',
        'Annulé' => 'linear-gradient(to right, rgba(220,53,69,.9), #f8d7da)',
        'Redémarré' => 'linear-gradient(to right, rgba(108,117,125,.9), #e2e3e5)',
    ];
    $icons = [
        'Prévu' => 'fa-calendar',
        'En cours' => 'fa-spinner',
        'Clôturés' => 'fa-check-circle',
        'Terminé' => 'fa-flag-checkered',
        'Suspendu' => 'fa-pause-circle',
        'Annulé' => 'fa-times-circle',
        'Redémarré' => 'fa-redo',
    ];
@endphp

@foreach($statusKeys as $status)
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: {{ $colors[$status] ?? $colors['Prévu'] }};">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">{{ $status }}</h6>
                    <h6 class="mb-0">{{ number_format($projectStatusCounts[$status] ?? 0, 0, ',', ' ') }}</h6>
                </div>
                <i class="fas {{ $icons[$status] ?? 'fa-chart-pie' }} fa-2x"></i>
            </div>
        </div>
    </div>
@endforeach

