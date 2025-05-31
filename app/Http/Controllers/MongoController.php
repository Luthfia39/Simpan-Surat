<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;

class MongoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->query('type'); // Ambil parameter dari URL
        if ($type) {
            $surats = Surat::where('type', $type)->get();
        } else {
            $surats = Surat::all();
        }
        return response()->json($surats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'nomor_surat' => 'required|string',
            'tanggal' => 'required|date',
            'pengirim' => 'required|string',
            'penerima' => 'required|string',
            'alamat' => 'required|string',
            'isi_surat' => 'required|string',
        ]);

        // Simpan ke MongoDB
        $surat = Surat::create([
            'type' => $request->type,
            'nomor_surat' => $request->nomor_surat,
            'tanggal' => $request->tanggal,
            'pengirim' => $request->pengirim,
            'penerima' => $request->penerima,
            'alamat' => $request->alamat,
            'isi_surat' => $request->isi_surat,
        ]);

        return response()->json([
            'message' => 'Surat berhasil disimpan',
            'data' => $surat
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $surat = Surat::find($id);
        if (!$surat) {
            return response()->json(['message' => 'Surat tidak ditemukan'], 404);
        }
        return response()->json($surat);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $surat = Surat::find($id);
        if (!$surat) {
            return response()->json(['message' => 'Surat tidak ditemukan'], 404);
        }

        $surat->delete();
        return response()->json(['message' => 'Surat berhasil dihapus']);
    }
}
