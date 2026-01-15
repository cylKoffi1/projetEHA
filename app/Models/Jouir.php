<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jouir extends Model
{
    protected $table = 'jouir';

    protected $fillable = [
        'code_projet',
        'code_Infrastructure',
    ];
    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class, 'code_Infrastructure', 'code');
    }
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    public function projetAppui()
    {
        return $this->belongsTo(AppuiProjet::class, 'code_projet', 'code_projet_appui');
    } 
    public function projetEtude()
    {
        return $this->belongsTo(EtudeProjet::class, 'code_projet', 'code_projet_etude');
    } 
    public $timestamps = true; // created_at & updated_at
}
