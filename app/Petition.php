<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{

    protected $fillable = ['remote_id', 'description', 'status', 'last_count', ];
    
}
