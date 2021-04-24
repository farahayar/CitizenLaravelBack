<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class signaleUser extends Model
{
    //
    protected $fillable = [
        "user_id",
        "user_idToS",
        "raison",
        "accepte"
    ];
    protected $table='signale_user';
}
