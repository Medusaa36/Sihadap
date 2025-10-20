<?php

namespace App\Http\Controllers;

use App\Models\adminModel;
use App\Models\pegawaiModel; // tambahkan model pegawai
use Illuminate\Http\Request;

class adminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admin_models = adminModel::with('pegawai')->get();
        return view('admin.index', compact('admin_models'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:50|unique:admin_models,nip',
            'password' => 'required|string|min:8',
            'tipe_admin' => 'required|string',
        ]);

        $admin = new adminModel();
        $admin->nip = $request->nip;
        $admin->password = bcrypt($request->password);
        $admin->tipe_admin = $request->tipe_admin;
        $admin->save();

        return redirect()->route('admin.index')->with('success', 'Data Admin berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $admin = adminModel::with('pegawai')->findOrFail($id);
        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = adminModel::findOrFail($id);

        $request->validate([
            'nip' => 'required|string|max:50|unique:admin_models,nip,' . $admin->id,
            'password' => 'nullable|string|min:8',
            'tipe_admin' => 'required|string',
        ]);

        $admin->nip = $request->nip;
        if ($request->password) {
            $admin->password = bcrypt($request->password);
        }
        $admin->tipe_admin = $request->tipe_admin;
        $admin->save();

        return redirect()->route('admin.index')->with('success', 'Data Admin berhasil diperbarui!');
    }

    
    public function searchByName(Request $request)
    {
        $term = $request->get('term');
        $result = pegawaiModel::where('nama', 'LIKE', "%$term%")->get(['id','nama','nip','jk']);
        return response()->json($result);
    }

    
    public function searchByNip(Request $request)
    {
        $term = $request->get('term');
        $result = pegawaiModel::where('nip', 'LIKE', "%$term%")->get(['id','nama','nip','jk']);
        return response()->json($result);
    }
    public function destroy($id)
    {
        $admin = adminModel::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.index')->with('success', 'Data Admin berhasil dihapus!');
    }


}
