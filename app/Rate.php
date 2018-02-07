<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    //

     protected $fillable = [
        'value', 'country','country_code','number_set','type'];
}
