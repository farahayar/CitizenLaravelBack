<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class post extends Model
{
    //
    protected $fillable = [
        'titre',
        'imageP	',
        'description',
        'signe',
        'valide'
    ];
    protected $table='post';

    /**
     * Relation many to one whidh post
     */
    public function region()
    {
        return $this->belongsTo('App\region');
    }

    /**
     * Relation many to one whidh user
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
