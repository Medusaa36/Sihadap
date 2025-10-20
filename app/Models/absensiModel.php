<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiModel extends Model
{
    use HasFactory;

    protected $table = 'absensi_models';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nip',
        'waktu_absen',
        'status',
        'keterangan',
        'kegiatan_id', 
    ];

    public function kegiatan()
    {
        return $this->belongsTo(KegiatanModel::class, 'kegiatan_id', 'kegiatan_id');
    }
    
}
