<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class usersNotifications extends Model
{
    //
    protected $fillable = [
        "user_id",
        "action_id",
        "action",
        "notification"
    ];
    protected $table = 'users_notifications';
}
