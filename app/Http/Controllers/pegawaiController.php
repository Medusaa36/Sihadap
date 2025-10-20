<?php

namespace App\Http\Controllers;

use App\Models\PegawaiModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Milon\Barcode\DNS1D;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawai_models = PegawaiModel::all();
        return view('pegawai.index', compact('pegawai_models'));
    }

    public function print()
    {
        $pegawai = PegawaiModel::all();
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $namaFile = 'Data Pegawai ' . Carbon::now()->format('d F Y') . '.pdf';

        $pdf = Pdf::loadView('pegawai.print', compact('pegawai', 'tanggal'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($namaFile);
    }

    public function create()
    {
        return view('pegawai.create');
    }
    
    public function simpanWajah(Request $request, $nip)
    {
        $pegawai = PegawaiModel::where('nip', $nip)->firstOrFail();
        $deskriptor = $request->input('deskriptor');

        if (!$deskriptor) {
            return response()->json(['success' => false, 'message' => 'Data wajah tidak diterima.']);
        }

        $pegawai->update(['verifikasi_wajah' => json_encode($deskriptor)]);

        return response()->json([
            'success' => true,
            'message' => ' Data wajah berhasil disimpan untuk ' . $pegawai->nama,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:pegawai_models,nip',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        ]);

        PegawaiModel::create([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'verifikasi_wajah' => null, // 
        ]);

        return redirect()->route('pegawai.index')
            ->with('success', ' Data pegawai berhasil ditambahkan. Silakan rekam wajah di halaman daftar pegawai.');
    }


    public function edit(string $nip)
    {
        $pegawai_models = PegawaiModel::findOrFail($nip);
        return view('pegawai.edit', compact('pegawai_models'));
    }

    public function update(Request $request, string $nip)
    {
        $pegawai = PegawaiModel::findOrFail($nip);

        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:pegawai_models,nip,' . $nip . ',nip',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
        ]);

        $pegawai->update($request->only(['nama', 'nip', 'jenis_kelamin']));

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }
    
    public function datawajah(string $nip)
    {
        $pegawai = PegawaiModel::where('nip', $nip)->firstOrFail();

        return view('pegawai.data-wajah', compact('pegawai'));
    }

    public function createDataWajah(Request $request, string $nip)
    {
        $pegawai = PegawaiModel::where('nip', $nip)->firstOrFail();

        $deskriptor = $request->input('deskriptor');

        if (is_null($deskriptor)) {
            return redirect()->back()->with('error', 'Data wajah (deskriptor) tidak ditemukan pada request.');
        }

        if (is_string($deskriptor)) {
            $decoded = json_decode($deskriptor, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $deskriptorArray = $decoded;
            } else {
                return redirect()->back()->with('error', 'Format deskriptor tidak valid. Kirim sebagai JSON array.');
            }
        } elseif (is_array($deskriptor)) {
            $deskriptorArray = $deskriptor;
        } else {
            return redirect()->back()->with('error', 'Format deskriptor tidak valid.');
        }

        $updateData = [];

        if ($request->filled('nama')) {
            $updateData['nama'] = $request->input('nama');
        }
        if ($request->filled('jenis_kelamin')) {
            $updateData['jenis_kelamin'] = $request->input('jenis_kelamin');
        }

        $updateData['verifikasi_wajah'] = json_encode($deskriptorArray);

        $pegawai->update($updateData);

        return redirect()->route('pegawai.index')->with('success', '✅ Data wajah berhasil disimpan untuk ' . $pegawai->nama);
    }


    public function searchByName(Request $request)
    {
        $term = $request->get('term');
        $pegawai = PegawaiModel::when($term, function ($query, $term) {
            return $query->where('nama', 'like', '%' . $term . '%');
        })
            ->limit(20)
            ->get(['nip', 'nama']);

        return response()->json($pegawai);
    }

    public function searchByNip(Request $request)
    {
        $term = $request->get('term');
        $pegawai = PegawaiModel::when($term, function ($query, $term) {
            return $query->where('nip', 'like', '%' . $term . '%');
        })
            ->limit(20)
            ->get(['nip', 'nama']);

        return response()->json($pegawai);
    }

    public function destroy(string $nip)
    {
        $pegawai = PegawaiModel::where('nip', $nip)->first();

        if (!$pegawai) {
            return redirect()
                ->route('pegawai.index')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        if (method_exists($pegawai, 'admin')) {
            $pegawai->admin()->delete();
        }

        if (method_exists($pegawai, 'absensi')) {
            $pegawai->absensi()->delete();
        }

        $pegawai->delete();

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai beserta seluruh data terkait berhasil dihapus.');
    }

    public function checkNip(Request $request)
    {
        $exists = PegawaiModel::where('nip', $request->nip)->exists();
        return response()->json(['exists' => $exists]);
    }
    
    public function downloadBarcode($nip)
    {
        $pegawai = PegawaiModel::where('nip', $nip)->firstOrFail();

        $dns1d = new \Milon\Barcode\DNS1D();
        $barcode = $dns1d->getBarcodeHTML($pegawai->nip, 'C128', 2, 70, 'black', true);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pegawai.barcode-pdf', compact('pegawai', 'barcode'))
            ->setPaper('a7', 'landscape');

        return $pdf->download('Barcode_' . $pegawai->nama . '.pdf');
    }

    public function hapusWajah($nip)
    {
        $pegawai = PegawaiModel::where('nip', $nip)->firstOrFail();
        $pegawai->verifikasi_wajah = null;
        $pegawai->save();

        return redirect()->route('pegawai.index')->with('success', 'Data wajah berhasil dihapus.');
    }


}
