<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Rubriques;

class Sidebar extends Component
{
    public $rubriques;

    public function __construct()
    {
        $this->rubriques = Rubriques::with('sousMenus.ecrans')->orderBy('ordre')->get();
    }

    public function render()
    {
        return view('layouts.sidebar'); // Assurez-vous que le chemin est correct
    }
}
