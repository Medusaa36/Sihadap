<?php

namespace App\Http\Controllers;

use App\Models\AbsensiModel;
use App\Models\AdminModel;
use App\Models\KegiatanModel;
use App\Models\PegawaiModel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 

class AbsensiController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
    }
    
    public function index()
    {
        $kegiatan = KegiatanModel::orderByDesc('tanggal')
            ->withCount([
                'absensi as jumlah_hadir' => function ($query) {
                    $query->whereIn('status', ['Hadir', 'Masuk']);
                },
                'absensi as total_absensi'
            ])
            ->get();

        return view('absensi.index', [
            'kegiatan' => $kegiatan,
            'tanggal' => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y'),
            'jam' => now('Asia/Jakarta')->format('H:i:s'),
        ]);
    }


    public function search(Request $request)
    {
        $keterangan = $request->get('keterangan', '');
        $query = AbsensiModel::orderByDesc('waktu_absen');

        if ($keterangan != '') {
            $query->where('keterangan', 'like', '%' . $keterangan . '%');
        }

        $absensi = $query->get()
            ->groupBy(function ($item) {
                return $item->keterangan . '|' . Carbon::parse($item->waktu_absen)->toDateString();
            })
            ->map(function ($group) {
                $latest = $group->first();
                $tanggal_raw = Carbon::parse($latest->waktu_absen)->toDateString();
                $tanggal = Carbon::parse($latest->waktu_absen)
                    ->locale('id')
                    ->translatedFormat('l, d F Y');
                return (object) [
                    'id' => $latest->id,
                    'keterangan' => $latest->keterangan,
                    'tanggal' => $tanggal,
                    'tanggal_raw' => $tanggal_raw
                ];
            })
            ->sortBy(function ($item) {
                if (stripos($item->keterangan, 'hadir') !== false) return 1;
                if (stripos($item->keterangan, 'tidak hadir') !== false) return 3;
                return 2;
            });

        return view('absensi.partials.table', ['absensi_models' => $absensi])->render();
    }

    public function kamera()
    {
        return view('absensi.kamera');
    }

    public function getDescriptors()
    {
        try {
            $pegawai = PegawaiModel::select('nip', 'nama', 'verifikasi_wajah')->get();

            $pegawaiData = $pegawai->map(function ($p) {
                return [
                    'nip' => $p->nip,
                    'nama' => $p->nama,
                    'descriptors' => json_decode($p->verifikasi_wajah, true) ?: []
                ];
            });

            if ($pegawaiData->isEmpty() || $pegawaiData->every(fn($p) => empty($p['descriptors']))) {
                return response()->json([
                    'pegawaiData' => [],
                    'error' => 'Belum ada data wajah pegawai yang tersimpan di sistem.'
                ]);
            }

            return response()->json(['pegawaiData' => $pegawaiData]);
        } catch (\Exception $e) {
            return response()->json(['pegawaiData' => [], 'error' => $e->getMessage()]);
        }
    }
    public function edit($id)
    {
        $absensi = AbsensiModel::findOrFail($id);
        $pegawai = PegawaiModel::all();

        return view('absensi.edit', compact('absensi', 'pegawai'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nip' => 'required',
            'status' => 'required|string',
            'waktu_absen' => 'required|date',
        ]);

        $absensi = AbsensiModel::findOrFail($id);

        $status = $request->status === 'Lainnya' && $request->filled('keterangan_manual')
            ? $request->keterangan_manual
            : $request->status;

        $jamAbsen = \Carbon\Carbon::parse($request->waktu_absen)->format('H:i:s');

        $absensi->update([
            'nip' => $request->nip,
            'status' => $status,
            'waktu_absen' => $jamAbsen, 
        ]);

        return redirect()
            ->route('absensi.detailKegiatan', $absensi->kegiatan_id)
            ->with('success', 'Data absensi berhasil diperbarui!');
    }

    public function proses(Request $request)
    {
        try {
            $request->validate([
                'nip' => 'required|string',
                'keterangan' => 'required|string',
            ]);

            $nip = $request->nip;
            $keterangan = $request->keterangan;

            $pegawai = PegawaiModel::where('nip', $nip)->first();
            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai dengan NIP tersebut tidak ditemukan.'
                ], 404);
            }

            $now = Carbon::now('Asia/Jakarta');
            $kegiatan = KegiatanModel::firstOrCreate(
                [
                    'keterangan' => $keterangan,
                    'tanggal' => $now->toDateString(),
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $sudahAbsen = AbsensiModel::where('nip', $nip)
                ->where('kegiatan_id', $kegiatan->kegiatan_id)
                ->whereDate('created_at', $now->toDateString())
                ->first();

            if ($sudahAbsen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai sudah melakukan absensi untuk kegiatan ini hari ini.'
                ], 409);
            }

            AbsensiModel::create([
                'nip' => $nip,
                'kegiatan_id' => $kegiatan->kegiatan_id, 
                'waktu_absen' => $now->toTimeString(),
                'status' => 'Hadir',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan!',
                'data' => [
                    'nip' => $nip,
                    'nama' => $pegawai->nama ?? '-',
                    'kegiatan' => $keterangan,
                    'tanggal' => $now->toDateString(),
                    'waktu' => $now->toTimeString(),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detailKegiatan($id)
    {
        $kegiatan = KegiatanModel::with('absensi')->find($id);

        if (!$kegiatan) {
            return redirect()
                ->route('absensi.index')
                ->with('error', 'Kegiatan tidak ditemukan.');
        }

        $absensi_models = AbsensiModel::where('kegiatan_id', $kegiatan->kegiatan_id)
            ->orderByDesc('waktu_absen')
            ->get();

        $pegawai = PegawaiModel::all();

        return view('absensi.detail', [
            'pegawai' => $pegawai,
            'absensi_models' => $absensi_models,
            'keterangan' => $kegiatan->keterangan,
            'tanggal' => \Carbon\Carbon::parse($kegiatan->tanggal)
                ->locale('id')
                ->translatedFormat('l, d F Y'),
            'id_kegiatan' => $kegiatan->kegiatan_id,
        ]);
    }

    public function manual(Request $request, $id = null)
    {
        $kegiatanId = $id ?? $request->get('kegiatan_id');
        $keterangan = $request->get('keterangan');

        if (!$kegiatanId) {
            return redirect()
                ->route('absensi.index')
                ->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $kegiatan = KegiatanModel::find($kegiatanId);
        if (!$kegiatan) {
            return redirect()
                ->route('absensi.index')
                ->with('error', 'Kegiatan tidak ditemukan.');
        }

        $tanggal = \Carbon\Carbon::parse($kegiatan->tanggal)->toDateString();

        $belumAbsen = \DB::table('pegawai_models')
            ->whereNotIn('nip', function ($q) use ($kegiatanId, $tanggal) {
                $q->select('nip')
                    ->from('absensi_models')
                    ->where('kegiatan_id', $kegiatanId)
                    ->whereDate('waktu_absen', $tanggal);
            })
            ->orderBy('nama', 'asc')
            ->get();

        return view('absensi.manual', [
            'keterangan' => $keterangan ?? $kegiatan->keterangan,
            'tanggal' => $tanggal,
            'belumAbsen' => $belumAbsen,
            'kegiatan_id' => $kegiatanId,
        ]);
    }

    public function simpanManual(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'status' => 'required|string',
            'tanggal' => 'required|date',
            'kegiatan_id' => 'required|integer|exists:kegiatan_models,kegiatan_id',
            'keterangan_manual' => 'nullable|string',
        ]);

        $status = $request->status;
        if ($status === 'Lainnya' && !empty($request->keterangan_manual)) {
            $status = $request->keterangan_manual;
        }

        $nip = $request->nip;
        $kegiatanId = $request->kegiatan_id;
        $tanggalKegiatan = $request->tanggal;

        $kegiatan = KegiatanModel::where('kegiatan_id', $kegiatanId)->first();
        if (!$kegiatan) {
            return redirect()
                ->route('absensi.index')
                ->with('error', 'Kegiatan tidak ditemukan.');
        }

        $cek = AbsensiModel::where('nip', $nip)
            ->where('kegiatan_id', $kegiatanId)
            ->first();

        if ($cek) {
            return redirect()
                ->back()
                ->with('error', 'Pegawai ini sudah melakukan absensi untuk kegiatan tersebut.');
        }

        AbsensiModel::create([
            'nip' => $nip,
            'status' => $status,
            'kegiatan_id' => $kegiatanId,
            'waktu_absen' => now('Asia/Jakarta')->format('H:i:s'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        return redirect()
            ->route('absensi.manual', ['id' => $kegiatanId])
            ->with('success', 'Absensi manual berhasil disimpan!');
    }

    public function destroyOne($id)
    {
        $currentAdmin = AdminModel::find(session('admin_id'));

        if (!$currentAdmin) {
            return redirect()->back()->with('error', 'Anda belum login sebagai admin.');
        }

        if (strtolower(trim($currentAdmin->tipe_admin)) !== 'admin master') {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus data absensi.');
        }

        $absensi = AbsensiModel::find($id);
        if (!$absensi) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan.');
        }

        $absensi->delete();

        return redirect()->back()->with('success', "Berhasil menghapus absensi pegawai dengan NIP {$absensi->nip} dari kegiatan ini.");
    }

    public function destroyKegiatan($kegiatan_id)
    {
        $currentAdmin = AdminModel::find(session('admin_id'));

        if (!$currentAdmin) {
            return redirect()->back()->with('error', 'Anda belum login sebagai admin.');
        }

        if (strtolower(trim($currentAdmin->tipe_admin)) !== 'admin master') {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus data kegiatan.');
        }

        $kegiatan = KegiatanModel::find($kegiatan_id);
        if (!$kegiatan) {
            return redirect()->back()->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $deletedAbsensi = AbsensiModel::where('kegiatan_id', $kegiatan_id)->delete();

        $kegiatan->delete();

        return redirect()->back()->with(
            'success',
            "Berhasil menghapus kegiatan beserta  data absensi terkait."
        );
    }


    public function cetakDetail($id)
    {
        $kegiatan = KegiatanModel::findOrFail($id);

        $absensi_models = AbsensiModel::where('kegiatan_id', $kegiatan->kegiatan_id)
            ->orderByDesc('waktu_absen')
            ->get(['id', 'nip', 'status', 'waktu_absen', 'kegiatan_id']);

        $pegawai = PegawaiModel::orderBy('nama', 'asc')->get();

        $tanggalAbsensi = Carbon::parse($kegiatan->tanggal)->locale('id');
        $tanggalAbsensiFormatted = $tanggalAbsensi->translatedFormat('d F Y');

        $absenKey = $absensi_models->keyBy('nip');

        $dataTampil = $pegawai->map(function ($p) use ($absenKey) {
            if ($absenKey->has($p->nip)) {
                $absen = $absenKey[$p->nip];
                $status = $absen->status ?? 'Hadir';

                $tipe = in_array($status, ['Hadir', 'Masuk']) ? 'otomatis' : 'manual';

                return [
                    'nip' => $p->nip,
                    'nama' => $p->nama,
                    'status' => $status,
                    'tipe' => $tipe,
                    'waktu_absen' => in_array($status, ['Hadir', 'Masuk'])
                        ? Carbon::parse($absen->waktu_absen)->format('H:i')
                        : '-',
                ];
            } else {
                return [
                    'nip' => $p->nip,
                    'nama' => $p->nama,
                    'status' => 'Tidak Hadir',
                    'tipe' => 'belum',
                    'waktu_absen' => '-',
                ];
            }
        });

        $dataTampil = $dataTampil->sortBy(function ($item) {
            return [
                'otomatis' => 1,
                'manual' => 2,
                'belum' => 3,
            ][$item['tipe']];
        })->values();

        $pdf = Pdf::loadView('absensi.cetak', [
            'dataTampil' => $dataTampil,
            'keterangan' => $kegiatan->keterangan,
            'tanggal' => $tanggalAbsensiFormatted,
        ])->setPaper('A4', 'portrait');

        $namaFile = 'Absensi_' . ucfirst($kegiatan->keterangan) . '_' . str_replace(' ', '_', $tanggalAbsensiFormatted) . '.pdf';

        return $pdf->download($namaFile);
    }
    public function kameraAksi(Request $request)
    {
        $keterangan = $request->get('keterangan');
        $id = $request->get('id');

        if ($id) {
            $kegiatan = KegiatanModel::find($id);
            if (!$kegiatan) {
                return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');
            }
            $keterangan = $kegiatan->keterangan;
        } 
        elseif ($keterangan) {
            $kegiatan = KegiatanModel::where('keterangan', $keterangan)->first();

            if (!$kegiatan) {
                $kegiatan = KegiatanModel::create([
                    'keterangan' => $keterangan,
                    'tanggal' => now()->toDateString(),
                ]);
            }

            $id = $kegiatan->kegiatan_id; 
            $keterangan = $kegiatan->keterangan;
        } 
        else {
            return redirect()->back()->with('error', 'Keterangan kegiatan tidak ditemukan.');
        }

        return view('absensi.kamera-aksi', [
            'id' => $id,
            'keterangan' => $keterangan ?? 'Tidak diketahui',
        ]);
    }
    public function prosesAbsensi(Request $request)
    {
        try {
            $request->validate([
                'nip' => 'required|string',
                'kegiatan_id' => 'required|integer|exists:kegiatan_models,kegiatan_id',
                'similarity' => 'nullable|numeric',
            ]);

            $nip = $request->nip;
            $kegiatanId = $request->kegiatan_id;

            $pegawai = PegawaiModel::where('nip', $nip)->first();
            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai tidak ditemukan.'
                ], 404);
            }

            $kegiatan = KegiatanModel::find($kegiatanId);
            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan.'
                ], 404);
            }

            $sudahAbsen = AbsensiModel::where('nip', $nip)
                ->where('kegiatan_id', $kegiatanId)
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'success' => false,
                    'already_absent' => true,
                    'message' => 'Pegawai sudah melakukan absensi untuk kegiatan ini.'
                ]);
            }

            AbsensiModel::create([
                'nip' => $nip,
                'status' => 'Hadir',
                'kegiatan_id' => $kegiatanId,
                'waktu_absen' => now('Asia/Jakarta')->format('H:i:s'),
                'created_at' => now('Asia/Jakarta'),
                'updated_at' => now('Asia/Jakarta'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat.',
                'data' => [
                    'nip' => $nip,
                    'nama' => $pegawai->nama ?? '-',
                    'kegiatan' => $kegiatan->keterangan,
                    'tanggal' => $kegiatan->tanggal,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

}

