<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtudeProjectFile extends Model
{
    use HasFactory;

    protected $table = "etudeproject_files";
    protected $primaryKey = 'id';
    protected $fillable = ['codeEtudeProjets', 'file_path', 'file_name'];

     // Définir la relation inverse avec le projet
     public function project()
     {
         return $this->belongsTo(EtudeProject::class, 'codeEtudeProjets', 'codeEtudeProjets');
     }
 }
