<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminModel extends Model
{
    use HasFactory;

    protected $table = 'admin_models';
    protected $fillable = [
        'nip',
        'password',
        'tipe_admin',
    ];

    public $timestamps = false;

    public function pegawai()
    {
        return $this->belongsTo(PegawaiModel::class, 'nip', 'nip');
    }

    public function getNamaAttribute()
    {
        return $this->pegawai ? $this->pegawai->nama : '(Tidak Diketahui)';
    }

    public function getNipAttribute($value)
    {
        return $value ?? '-';
    }
    public function getTipeAdminAttribute($value)
    {
        return ucfirst($value ?? '-');
    }
    
}
