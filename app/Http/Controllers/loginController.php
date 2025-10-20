<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminModel;
use App\Models\PegawaiModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        if ($request->session()->has('admin_id')) {
            $request->session()->flush();
        }
        return view('login.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip'      => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = AdminModel::with('pegawai')->where('nip', $request->nip)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            $namaPegawai = $admin->pegawai ? $admin->pegawai->nama : null;

            session([
                'admin_id'   => $admin->id,
                'nip'        => $admin->nip,
                'nama'       => $namaPegawai,
                'tipe_admin' => $admin->tipe_admin
            ]);

            return redirect()->route('home.index')->with('success', 'Login berhasil!');
        }

        return back()->withErrors(['login.index' => 'NIP atau Password salah!'])->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login.index')->with('success', 'Anda berhasil logout.');
    }

    public function lupaPassword()
    {
        return view('login.lupa-password');
    }
    public function checkAdmin(Request $request)
    {
        $nip = $request->input('nip');
        if (!$nip) {
            return response()->json(['success' => false, 'message' => 'NIP tidak disediakan']);
        }

        $admin = AdminModel::where('nip', $nip)->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'NIP tidak terdaftar sebagai admin!']);
        }

        $pegawai = PegawaiModel::where('nip', $nip)->first();

        return response()->json([
            'success' => true,
            'nip' => $nip,
            'nama' => $pegawai->nama ?? 'Tidak diketahui'
        ]);
    }

    public function getDescriptor(Request $request)
    {
        $nip = $request->input('nip');
        if (!$nip) {
            return response()->json(['success' => false, 'message' => 'NIP tidak disediakan']);
        }

        $pegawai = PegawaiModel::where('nip', $nip)->first();
        if (!$pegawai || !$pegawai->verifikasi_wajah) {
            return response()->json(['success' => false, 'message' => 'Data verifikasi wajah tidak ditemukan untuk NIP ini']);
        }

        $stored = json_decode($pegawai->verifikasi_wajah, true);
        $descriptors = [];
        if (isset($stored[0]) && is_array($stored[0])) {
            foreach ($stored as $d) {
                $descriptors[] = array_map('floatval', $d);
            }
        } else {
            $descriptors[] = array_map('floatval', $stored);
        }

        return response()->json([
            'success' => true,
            'descriptors' => $descriptors,
            'nama' => $pegawai->nama ?? null,
            'nip' => $nip
        ]);
    }

    public function verifikasiWajah(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string',
            'descriptor' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid']);
        }

        $nip = $request->input('nip');
        $clientDescriptor = $request->input('descriptor');

        $pegawai = PegawaiModel::where('nip', $nip)->first();
        if (!$pegawai || !$pegawai->verifikasi_wajah) {
            return response()->json(['success' => false, 'message' => 'Data wajah tidak ditemukan di database.']);
        }

        $storedRaw = json_decode($pegawai->verifikasi_wajah, true);
        $storedDescriptors = [];

        if (isset($storedRaw[0]) && is_array($storedRaw[0])) {
            foreach ($storedRaw as $d) {
                $storedDescriptors[] = array_map('floatval', $d);
            }
        } else {
            $storedDescriptors[] = array_map('floatval', $storedRaw);
        }

        $minDistance = INF;
        foreach ($storedDescriptors as $sd) {
            $count = min(count($sd), count($clientDescriptor));
            $sum = 0;
            for ($i = 0; $i < $count; $i++) {
                $sum += pow($sd[$i] - floatval($clientDescriptor[$i]), 2);
            }
            $dist = sqrt($sum);
            if ($dist < $minDistance) $minDistance = $dist;
        }
        $threshold = 0.55;

        if ($minDistance <= $threshold) {
            return response()->json(['success' => true, 'message' => 'Wajah cocok dengan NIP tersebut', 'distance' => $minDistance]);
        } else {
            return response()->json(['success' => false, 'message' => 'Wajah tidak cocok dengan NIP tersebut', 'distance' => $minDistance]);
        }
    }

    /**
     * resetPassword
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $admin = AdminModel::where('nip', $request->nip)->first();
        if (!$admin) {
            return back()->with('error', 'Akun tidak ditemukan!');
        }

        $admin->password = bcrypt($request->password);
        $admin->save();

        return redirect()->route('login.index')->with('success', 'Password berhasil diganti! Silakan login kembali.');
    }
}
