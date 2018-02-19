<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    //
    
    protected $fillable = [
        'name', 'user_id','provider_id','channel_id','status'];
   
}
