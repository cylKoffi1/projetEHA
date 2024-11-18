<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paysUser extends Model
{
    use HasFactory;
    protected $table = 'pays_user';
    protected $fillable = ['code_pays', 'code_user'];
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'alpha3');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'code_user', 'code_personnel');
    }
}
