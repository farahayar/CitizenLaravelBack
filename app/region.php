<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class region extends Model
{
    //
    protected $fillable = [
        'nom_region	',
        'latitude',
        'longitude'
    ];
    protected $table='region';

    /**
     * Relation one to one whidh region
     */
    public function post()
    {
        return $this->hasOne('App\post');
    }
}
