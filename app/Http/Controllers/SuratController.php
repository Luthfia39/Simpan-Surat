<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('screen.dashboard.index');
    }

    public function show(Request $request)
    {
        $type = $request->query('type'); // Ambil parameter dari URL
        if ($type) {
            $surats = Surat::where('type', $type)->get();
        } else {
            $surats = Surat::all();
        }
        // dd($surats);
        return view('screen.data.read.index', compact('surats'));
    }

    public function create()
    {
        return view('screen.data.create.index');
    }

    public function destroy($id)
    {
        $surat = Surat::find($id);

        if (!$surat) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $surat->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    public function detail($id)
    {
        // Ambil data surat berdasarkan ID
        $surat = Surat::findOrFail($id);

        // Tampilkan view detail dan kirimkan data surat
        return view('screen.data.detail.index', compact('surat'));
    }

    public function download($id)
    {
        $surat = Surat::findOrFail($id);
        $path = public_path('storage/pdfs/' . $surat->nama_file);

        if (!file_exists($path)) {
            return redirect()->back()->with('file_not_found', 'File tidak ditemukan atau sudah terhapus.');
        }

        return response()->download($path);
    }



    public function login()
    {
        return view('screen.login.index');
    }
}
