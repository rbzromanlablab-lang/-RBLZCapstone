<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePhoto extends Model
{
    protected $fillable = ['mime_type', 'contents'];

    protected $hidden = ['contents'];
}
