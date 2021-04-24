<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class abonnement extends Model
{
    protected $fillable = [
        "abonne_id",
        "suivi_id"
    ];
    protected $table='abonnement';
}
