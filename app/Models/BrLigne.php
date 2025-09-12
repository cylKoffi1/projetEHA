<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrLigne extends Model
{
    use HasFactory;
    protected $table = 'br_lignes';
    protected $fillable = ['br_id','bc_ligne_id','qte_recue'];
    protected $casts = ['qte_recue'=>'decimal:3'];
    public function br()      { return $this->belongsTo(BonReception::class,'br_id'); }
    public function bcLigne() { return $this->belongsTo(BcLigne::class,'bc_ligne_id'); }

}
