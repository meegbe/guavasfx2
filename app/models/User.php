<?php

namespace App\Models;

use Model;

class User extends Model
{

    /**
     * @var string
     */
    protected $connection = 'main';

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
}
