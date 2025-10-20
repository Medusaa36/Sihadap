<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SidebarController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required',
        ]);

        $admin = AdminModel::where('nip', $request->nip)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            session([
                'nip' => $admin->nip,
                'tipe_admin' => $admin->tipe_admin,
            ]);

            return redirect()->route('home')->with('success', 'Login berhasil');
        }

        return back()->with('error', 'NIP atau Password salah!');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login.index')->with('success', 'Anda berhasil logout.');
    }
}
