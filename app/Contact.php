<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
    protected $fillable = [
        'user_id','phone','email', 'fname','lname','name', 'country','state','city','info'
    ];

}
