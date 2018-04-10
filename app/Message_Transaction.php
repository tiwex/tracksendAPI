<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message_transaction extends Model
{
    //
    protected $fillable = [
        'user_id','campaign_id','message_id','contact_id','recepient'];
}
