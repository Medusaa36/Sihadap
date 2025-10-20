<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiModel extends Model
{
    use HasFactory;

    protected $table = 'pegawai_models';

    protected $primaryKey = 'nip';
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = [
        'nip',
        'nama',
        'jenis_kelamin',
        'password',
        'verifikasi_wajah',
    ];

    public function absensi()
    {
        return $this->hasMany(AbsensiModel::class, 'nip', 'nip');
    }

    public function admin()
    {
        return $this->hasMany(AdminModel::class, 'nip', 'nip');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($pegawai) {
            $pegawai->absensi()->delete();
        });
    }
}
