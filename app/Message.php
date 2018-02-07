<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
     protected $fillable = [
        'provider_id', 'trans_id','campaign_id','channel_id','track_id','type','message','contact_id','recepient'];
}
