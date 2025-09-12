<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcLigne extends Model
{
    use HasFactory;
    protected $table = 'bc_lignes';
    protected $fillable = ['bc_id','materiau_id','qte_prevue','pu','taxe','remise'];
    protected $casts = ['qte_prevue'=>'decimal:3','pu'=>'decimal:2','taxe'=>'decimal:2','remise'=>'decimal:2'];
    public function bc()       { return $this->belongsTo(BonCommande::class,'bc_id'); }
    public function materiau() { return $this->belongsTo(Materiau::class,'materiau_id'); }
    public function receptions(){ return $this->hasMany(BrLigne::class,'bc_ligne_id'); }

}
