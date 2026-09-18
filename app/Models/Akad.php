<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akad extends Model
{
    protected $table = 'akad';
    protected $fillable = ['key', 'value', 'keterangan'];
}
