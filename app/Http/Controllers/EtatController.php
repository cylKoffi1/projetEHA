<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TCPDF;

class EtatController extends Controller
{
    public function generatePDF(Request $request)
    {
        // Désactiver la mise en cache de sortie
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Récupérer les données nécessaires
        $data = $this->fetchData();

        // Initialiser TCPDF
        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // Configurer le document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Laravel System');
        $pdf->SetTitle('État des Heures Complémentaires');
        $pdf->SetSubject('Rapport');
        $pdf->SetKeywords('Laravel, TCPDF, Rapport');
        $pdf->SetMargins(15, 63, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);

        // Ajouter une page
        $pdf->AddPage();

        // Ajouter du contenu
        $html = $this->generateHTMLTable($data['enseignants']);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Désactiver tous les buffers de sortie pour éviter les conflits
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Générer et envoyer le fichier PDF
        $pdf->Output('etat_heures_complementaires.pdf', 'I');
    }
    private function fetchData()
    {
        // Simuler les données
        return [
            'annee_academique' => '2023-2024',
            'etablissement' => 'Université XYZ',
            'departement' => 'Informatique',
            'date_start' => '01/01/2023',
            'date_end' => '31/12/2023',
            'enseignants' => [
                [
                    'matricule' => '001',
                    'nom' => 'John Doe',
                    'grade' => 'Professeur',
                    'statut' => 'Permanent',
                    'volume_hc' => 40,
                    'montant' => 150000,
                ],
                [
                    'matricule' => '002',
                    'nom' => 'Jane Smith',
                    'grade' => 'Assistant',
                    'statut' => 'Vacataire',
                    'volume_hc' => 20,
                    'montant' => 70000,
                ],
            ],
        ];
    }

    private function generateCustomHeader($pdf, $data)
    {
        $titre = "État des Heures Complémentaires des Enseignements Réalisés";

        $html = <<<EOD
<table>
    <tr>
        <td style="width: 70%"><h1>$titre</h1></td>
        <td style="width: 30%; text-align: right;">Année académique : {$data['annee_academique']}</td>
    </tr>
    <tr>
        <td>Département : {$data['departement']}</td>
        <td style="text-align: right;">Période : {$data['date_start']} - {$data['date_end']}</td>
    </tr>
</table>
EOD;

        $pdf->SetFont('helvetica', '', 12);
        $pdf->writeHTML($html, true, false, false, false, '');
    }

    private function generateHTMLTable($enseignants)
    {
        $table = '<table border="1" cellspacing="0" cellpadding="4">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th width="10%">Matricule</th>
                    <th width="40%">Nom</th>
                    <th width="15%">Grade</th>
                    <th width="15%">Statut</th>
                    <th width="10%">Volume HC</th>
                    <th width="10%">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($enseignants as $enseignant) {
            $table .= "<tr>
                <td>{$enseignant['matricule']}</td>
                <td>{$enseignant['nom']}</td>
                <td>{$enseignant['grade']}</td>
                <td>{$enseignant['statut']}</td>
                <td>{$enseignant['volume_hc']}</td>
                <td>" . number_format($enseignant['montant'], 0, ',', ' ') . "</td>
            </tr>";
        }

        $table .= '</tbody></table>';

        return $table;
    }



    private function getEnseignants()
    {
        // Exemple de données fictives
        return [
            [
                'matricule' => '001',
                'nom' => 'John Doe',
                'grade' => 'Professeur',
                'statut' => 'Permanent',
                'volume_hc' => 40,
                'montant' => 150000,
            ],
            [
                'matricule' => '002',
                'nom' => 'Jane Smith',
                'grade' => 'Assistant',
                'statut' => 'Vacataire',
                'volume_hc' => 20,
                'montant' => 70000,
            ],
        ];
    }
}
