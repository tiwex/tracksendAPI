<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message_Transaction extends Model
{
    //
    protected $fillable = [
        'user_id','campaign_id','type','message','is_sent','is_clicked','schedule_at'];
}
