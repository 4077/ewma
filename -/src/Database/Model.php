<?php namespace ewma\Database;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    public $timestamps = false;

    protected $guarded = ['id'];
}
