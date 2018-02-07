<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tracker extends Model
{
    //
    protected $fillable = [
        'url_id', 'campaign_id','status'];
}
