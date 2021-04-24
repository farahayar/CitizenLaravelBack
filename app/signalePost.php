<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class signalePost extends Model
{
    //
    protected $fillable = [
        "user_id",
        "post_id",
        "raison",
        "accepte"
    ];
    protected $table='signale_post';
}
