<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminModel;

class PasswordController extends Controller
{
    public function index()
    {
        $admin = AdminModel::find(session('admin_id'));

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return view('password.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $messages = [
            'password_lama.required' => 'Kolom Password Lama wajib diisi.',
            'password_baru.required' => 'Kolom Password Baru wajib diisi.',
            'password_baru.min' => 'Password Baru harus minimal 8 karakter.',
            'password_baru.same' => 'Konfirmasi Password tidak sama dengan Password Baru.',
            'konfirmasi_password.required' => 'Kolom Konfirmasi Password wajib diisi.',
            'konfirmasi_password.min' => 'Konfirmasi Password minimal 8 karakter.',
        ];

        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|same:konfirmasi_password',
            'konfirmasi_password' => 'required|min:8',
        ], $messages);

        $admin = AdminModel::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!Hash::check($request->password_lama, $admin->password)) {
            return redirect()->back()->with('error', 'Password lama yang Anda masukkan salah.');
        }

        $admin->password = bcrypt($request->password_baru);
        $admin->save();

        return redirect()->route('home.index')->with('password_success', 'Password berhasil diganti!');
    }
    
}
