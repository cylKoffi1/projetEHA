<?php

// app/Http/Controllers/ProjectStatusController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StatutProjet;

class ProjectStatusController extends Controller
{
    public function getProjectStatus($id)
    {
        $status = StatutProjet::find($id);

        if ($status) {
            return response()->json(['code' => $status->code, 'label' => $status->libelle]);
        } else {
            return response()->json(['error' => 'Statut du projet non trouvé.'], 404);
        }
    }
}
