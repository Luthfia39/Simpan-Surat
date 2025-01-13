<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;

class PdfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
        //
    }

    public function convertPdfToImage(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240'
        ]);

        try {
            // Store PDF
            $pdfPath = $request->file('pdf_file')->store('temp');
            $fullPdfPath = Storage::path($pdfPath);

            // Create output directory
            $outputPath = storage_path('app/public/converted_images');
            if (!file_exists($outputPath)) {
                mkdir($outputPath, 0777, true);
            }

            // Output image path
            $outputFile = $outputPath . '/' . time() . '_%d.jpg';

            // Convert using GhostScript
            $command = "gswin64c -dNOPAUSE -sDEVICE=jpeg -r300 -dJPEGQ=100 -dBATCH -sOutputFile=\"$outputFile\" \"$fullPdfPath\"";

            exec($command, $output, $returnVal);

            if ($returnVal !== 0) {
                throw new \Exception("PDF conversion failed");
            }

            // Get converted images
            $images = glob($outputPath . '/' . time() . '_*.jpg');

            // Format paths for response
            $imagePaths = array_map(function ($image) {
                return 'converted_images/' . basename($image);
            }, $images);

            // Cleanup
            Storage::delete($pdfPath);

            return response()->json([
                'success' => true,
                'images' => $imagePaths
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
