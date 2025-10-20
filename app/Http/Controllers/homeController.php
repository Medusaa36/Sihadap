<?php

namespace App\Http\Controllers;

use App\Models\AbsensiModel;
use App\Models\PegawaiModel;
use Illuminate\Support\Facades\DB;
use App\Models\KegiatanModel;

class HomeController extends Controller
{
    public function index()
    {
        $totalPegawai = PegawaiModel::count();

        
        $dataAbsensi = AbsensiModel::select(
                DB::raw('kegiatan_models.tanggal as tanggal'),
                'kegiatan_models.keterangan',
                'absensi_models.status',
                DB::raw('COUNT(DISTINCT absensi_models.nip) as jumlah')
            )
            ->join('kegiatan_models', 'kegiatan_models.kegiatan_id', '=', 'absensi_models.kegiatan_id')
            ->groupBy('kegiatan_models.tanggal', 'kegiatan_models.keterangan', 'absensi_models.status')
            ->get();

        $grafikArr = [];

        foreach ($dataAbsensi as $row) {
            $tanggal = $row->tanggal;
            $keterangan = $row->keterangan ?? '-';
            $status = strtolower(trim($row->status ?? '-'));
            $jumlah = intval($row->jumlah);

            $key = $tanggal . '|' . $keterangan;

            if (!isset($grafikArr[$key])) {
                $grafikArr[$key] = [
                    'tanggal' => $tanggal,
                    'keterangan' => ucfirst($keterangan),
                    'hadir' => 0,
                    'lainnya' => 0,
                    'tidak_hadir' => 0,
                ];
            }

            if ($status === 'hadir') {
                $grafikArr[$key]['hadir'] += $jumlah;
            }
            else {
                $grafikArr[$key]['lainnya'] += $jumlah;
            }
        }

        foreach ($grafikArr as $key => &$vals) {
            $hadir = $vals['hadir'];
            $lainnya = $vals['lainnya'];
            $vals['tidak_hadir'] = max(0, $totalPegawai - ($hadir + $lainnya));
        }

        $grafik = collect($grafikArr)
            ->sortByDesc(fn($item) => $item['tanggal'])
            ->values()
            ->take(6);

        return view('home.index', compact('totalPegawai', 'grafik'));
    }
}
