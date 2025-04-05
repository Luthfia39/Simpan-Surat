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

    public function login()
    {
        return view('screen.login.index');
    }
}
