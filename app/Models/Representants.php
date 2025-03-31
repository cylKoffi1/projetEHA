<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representants extends Model
{
    use HasFactory;
    protected $table = 'representants';

    protected $primaryKey = 'id';
    public $timestamps = false; 
    protected $fillable = ['entreprise_id', 'representant_id', 'role', 'idPays', 'date_représentation'];
    
    public function scopeLegaux($query)
    {
        return $query->where('role', 'Représentant Légal');
    }
    
    public function scopeContacts($query)
    {
        return $query->where('role', 'Personne de Contact');
    }
    
}
