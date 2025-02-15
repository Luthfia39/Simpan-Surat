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
        return view('welcome');
    }

    public function scan(Request $request)
    {
        if (!$request->hasFile('files')) {
            return redirect()->back()->with('error', 'No files uploaded.');
        }

        $files = $request->file('files');
        $paths = [];
        $isPdf = false;
        $isImage = false;
        $latestPdfPath = null;

        foreach ($files as $file) {
            if ($file->getClientOriginalExtension() === 'pdf') {
                $isPdf = true;
            } elseif (str_starts_with($file->getMimeType(), 'image/')) {
                $isImage = true;
            }
        }

        // Prevent mixing PDFs and images
        if ($isPdf && $isImage) {
            return redirect()->back()->with('error', 'You cannot upload both images and PDFs at the same time.');
        }

        // Restrict to one PDF only
        if ($isPdf && count($files) > 1) {
            return redirect()->back()->with('error', 'Only one PDF file can be uploaded at a time.');
        }

        foreach ($files as $file) {
            $path = $file->store($isPdf ? 'pdfs' : 'images', 'public');
            $paths[] = $path;

            // Store the most recent PDF path
            if ($isPdf) {
                $latestPdfPath = storage_path("app/public/{$path}");
            }
        }

        if ($isImage) {
            $results = $this->preprocessImages();
            return redirect()->back()->with('result', $results);
        }

        if ($isPdf && $latestPdfPath) {
            // Convert the most recent PDF to images
            $this->convertPdfToImages($latestPdfPath);

            // Call preprocessImages() after conversion
            $results = $this->preprocessImages();

            $no_letter = $this->getData($results);

            return redirect()->back()->with([
                'results' => $results, 
                'no_letter' => $no_letter
            ]);
        }

        return redirect()->back()->with('success', 'Files uploaded successfully: ' . implode(', ', $paths));
    }


    /**
     * Convert PDF pages to images using Spatie PDF to Image library.
     */
    private function convertPdfToImages($pdfPath) {
        try {
            $pdf = new Pdf($pdfPath);

            Storage::disk('public')->makeDirectory('images');
            $imagePath = Storage::disk('public')->path("images");

            $pdf->saveAllPages($imagePath);

            // return "Successfully convert pdf to images!";
        } catch (\Exception $e) {
            throw new \Exception('PDF to Image conversion failed: ' . $e->getMessage());
        }
    }

    public function preprocessImages()
    {
        $files = Storage::disk('public')->files('images');
        $combinedText = '';

        foreach ($files as $file) {
            $filePath = storage_path('app/public/' . $file);
            $text = (new TesseractOCR($filePath))->lang('ind')->run();
            $combinedText .= $text . "\n";
            Storage::disk('public')->delete($file);
        }

        return $combinedText;
    }

    private function getData($letters) {
        $cleanedText = str_replace('\/', '/', $letters);

        // Updated regex pattern with debugging
        $reg_number = "/(\d+\/UNI1\/[A-Z0-9.-]+\/[A-Z]+\/[A-Z]+\/\d{4})/";
        preg_match_all($reg_number, $cleanedText, $no_letter);

        return $no_letter[0];
    }
}