<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class pushNotifToken extends Model
{
    //
    protected $fillable = [
        "user_id",
        "pushNotToken"
    ];
    protected $table='push_notif_tokens';
}
