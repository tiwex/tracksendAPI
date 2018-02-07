<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
	protected $fillable = [
        'user_id', 'name','status'];
    //
}
