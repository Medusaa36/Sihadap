<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class sidebarModel extends Authenticatable
{
    protected $table = 'admin_models'; 
    protected $fillable = ['nip', 'password', 'tipe_admin'];
    public $timestamps = false;

    protected $hidden = ['password'];
}
