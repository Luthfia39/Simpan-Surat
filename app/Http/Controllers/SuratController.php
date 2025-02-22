<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Spatie\PdfToImage\Pdf;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('screen.dashboard.index');
    }

    public function show()
    {
        return view('screen.data.read.index');
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