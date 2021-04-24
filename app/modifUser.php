<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class modifUser extends Model
{
    //
    
    protected $fillable = [
        "user_id",
        "post_id",
        "descriptionM"
    ];
    protected $table='modif_user';
}
