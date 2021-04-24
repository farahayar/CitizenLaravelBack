<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class adminNotifications extends Model
{
    //
    protected $fillable = [
        
        "action",
        "notification"
    ];
    protected $table = 'admin_notifications';
}
