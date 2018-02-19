<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Send_group extends Model
{
    //
      
    protected $fillable = [
        'name', 'user_id','campaign_id','group_id'];
}
