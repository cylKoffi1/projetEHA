<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationStatut extends Model
{
    use HasFactory;

    protected $table = 'notification_statuts';
    protected $fillable = ['libelle'];
}
