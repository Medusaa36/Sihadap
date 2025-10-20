<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PegawaiModel;

class HomeModel extends Model
{
    public static function getTotalPegawai()
    {
        return PegawaiModel::count();
    }
}
