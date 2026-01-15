{{-- Scripts Chart.js pour les graphiques --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        // Préparer les données pour les graphiques
        $statusKeys = ['Prévu','En cours','Clôturés','Terminé','Suspendu','Annulé','Redémarré'];
        $statusData = [];
        $typeData = ['Public' => 0, 'Privé' => 0];
        $montantData = [];
        
        foreach ($statusKeys as $status) {
            $statusData[$status] = $projectStatusCounts[$status] ?? 0;
        }
        
        foreach ($statutsProjets as $projet) {
            $typeChar = substr($projet->code_projet, 6, 1);
            if ($typeChar == '1') {
                $typeData['Public']++;
            } elseif ($typeChar == '2') {
                $typeData['Privé']++;
            }
            
            $statutLib = $projet->dernierStatut?->statut?->libelle ?? 'Prévu';
            if ($statutLib === 'Clôturé') $statutLib = 'Clôturés';
            $montant = (float)($projet->cout_projet ?? 0);
            if (!isset($montantData[$statutLib])) {
                $montantData[$statutLib] = 0;
            }
            $montantData[$statutLib] += $montant;
        }
    @endphp

    // Graphique Statut (Pie)
    const ctxStatut = document.getElementById('chartStatut');
    if (ctxStatut) {
        new Chart(ctxStatut, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($statusData)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($statusData)) !!},
                    backgroundColor: [
                        'rgba(104,155,225,0.8)',
                        'rgba(40,167,69,0.8)',
                        'rgba(23,162,184,0.8)',
                        'rgba(0,123,255,0.8)',
                        'rgba(255,193,7,0.8)',
                        'rgba(220,53,69,0.8)',
                        'rgba(108,117,125,0.8)',
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Graphique Type (Doughnut)
    const ctxType = document.getElementById('chartType');
    if (ctxType) {
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($typeData)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($typeData)) !!},
                    backgroundColor: [
                        'rgba(0,123,255,0.8)',
                        'rgba(40,167,69,0.8)',
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Graphique Montant par Statut (Bar)
    const ctxMontant = document.getElementById('chartMontant');
    if (ctxMontant) {
        new Chart(ctxMontant, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($montantData)) !!},
                datasets: [{
                    label: 'Montant (FCFA)',
                    data: {!! json_encode(array_values($montantData)) !!},
                    backgroundColor: 'rgba(0,123,255,0.8)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Montant: ' + new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

