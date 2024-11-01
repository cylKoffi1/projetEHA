<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approbateur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'approbateur';
    protected $primaryKey = 'codeAppro';
    protected $fillable = ['code_personnel', 'numOrdre', 'codeStructure'];

    public function Personnel()
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }
    public function structure(){
        return $this->belongsTo(StructureRattachement::class, 'code_personnel','code_personnel');
    }
    public function projectApprovals()
{
    return $this->hasMany(ProjectApproval::class, 'codeAppro', 'codeAppro');
}

}
