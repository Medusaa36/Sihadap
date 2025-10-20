<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanModel extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_models';
    protected $primaryKey = 'kegiatan_id';
    public $timestamps = true;

    protected $fillable = [
        'keterangan',
        'tanggal',
    ];

    public function absensi()
    {
        return $this->hasMany(AbsensiModel::class, 'kegiatan_id', 'kegiatan_id');
    }
}
