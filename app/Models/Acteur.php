<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acteur extends Model
{
    use HasFactory;

    protected $table = 'acteur';
    protected $primaryKey = 'code_acteur';
    public $incrementing = true;
    protected $keyType = 'int'; // adapte si c'est un int
    public $timestamps = true;
    protected $fillable = [
        'code_acteur',
        'libelle_long',
        'libelle_court',
        'type_acteur',
        'email',
        'telephone',
        'adresse',
        'code_pays',
        'is_active',
        'Photo',              // <= on garde ce nom tel qu’en BDD
        'is_user',
        'type_financement',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        // Photo stockée en GridFS (id numérique/chaine) : route /fichiers/{id}
        if (!empty($this->Photo)) {
            // si URL absolue déjà fournie
            if (preg_match('~^https?://~i', (string)$this->Photo)) {
                return $this->Photo;
            }
            // si chemin relatif (ex: storage/xxx)
            if (str_starts_with((string)$this->Photo, 'storage/')) {
                return asset($this->Photo);
            }
            // sinon on considère que c’est un id GridFS
            return url('/fichiers/' . $this->Photo);
        }
        return null;
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'alpha3');
    }

    public function type()
    {
        return $this->belongsTo(TypeActeur::class, 'type_acteur', 'cd_type_acteur');
    }

    // Relation unique 1 acteur ↔ 1 user
    public function user()
    {
        return $this->hasOne(User::class, 'acteur_id', 'code_acteur');
    }

    public function personnePhysique()
    {
        return $this->hasOne(PersonnePhysique::class, 'code_acteur', 'code_acteur');
    }

    public function personneMorale()
    {
        return $this->hasOne(PersonneMorale::class, 'code_acteur', 'code_acteur');
    }

    public function secteurActiviteActeur()
    {
        return $this->hasMany(SecteurActiviteActeur::class, 'code_acteur', 'code_acteur');
    }

    public function secteurPrincipal()
    {
        return $this->hasOne(SecteurActiviteActeur::class, 'code_acteur', 'code_acteur')->latestOfMany();
    }

    public function representants()
    {
        return $this->hasMany(Representants::class, 'entreprise_id', 'code_acteur');
    }

    public function possederpiece()
    {
        return $this->hasMany(Possederpiece::class, 'idPersonnePhysique', 'code_acteur');
    }

    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('is_active', true);
        });
    }

    public function scopeWithInactive($query)
    {
        return $query->withoutGlobalScope('active');
    }

    // ---- autres relations projet : inchangées ----
    public function bailleurs()
    {
        return $this->hasMany(Financer::class, 'code_acteur', 'code_acteur');
    }

    public function projetsChef()
    {
        return $this->hasMany(Controler::class, 'code_acteur', 'code_acteur')->with('projet');
    }

    public function projetsOuvrage()
    {
        return $this->hasMany(Posseder::class, 'code_acteur', 'code_acteur')->with('projet');
    }

    public function projetsOeuvre()
    {
        return $this->hasMany(Executer::class, 'code_acteur', 'code_acteur')->with('projet');
    }

    public function projetsFinances()
    {
        return $this->hasMany(Financer::class, 'code_acteur', 'code_acteur')->with('projet');
    }

    public function projetsApprouves()
    {
        return $this->hasMany(ProjetApprobation::class, 'code_acteur', 'code_acteur')->with('etude.projet');
    }

    public function scopeCabinets($q){ 
        return $q->where('type_acteur','CAB'); 
    }

}
