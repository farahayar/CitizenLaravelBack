<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class enregistrerPost extends Model
{
    protected $fillable = [
        "user_id",
        "post_id"
    ];
    protected $table='enregistrer_post';
}
