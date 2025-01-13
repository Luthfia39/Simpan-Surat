<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function scan(Request $request)
    // {
    //     // Validate the uploaded file
    //     $request->validate([
    //         'surat' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
    //     ]);

    //     $file = $request->file('surat');
    //     $extension = $file->getClientOriginalExtension();
    //     $filePath = $file->path();
    //     $results = [];

    //     if ($extension === 'pdf') {
    //         $imagePaths = $this->convertPdfToImages($filePath);

    //         //     dd($imagePaths);

    //         foreach ($imagePaths as $index => $imagePath) {
    //             $text = (new TesseractOCR($imagePath))->run();
    //             $results["Page " . ($index + 1)] = $text;

    //             // Optionally, delete the image after processing
    //             unlink($imagePath);
    //         }
    //     } else {
    //         $text = (new TesseractOCR($filePath))->run();
    //         $results['Single Page'] = $text;
    //     }

    //     // Return the results to the view or any other response
    //     return redirect('/')->with('result', $results);
    // }
    {
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('images', 'public');
                $paths[] = $path;
            }

            $results = $this->preprocessImages();
            return redirect()->back()->with('result', $results);
            // return redirect()->back()->with('success', 'Images uploaded successfully: ' . implode(', ', $paths));
        }
        return redirect()->back()->with('error', 'No images uploaded.');
    }

    /**
     * Convert PDF pages to images using Spatie PDF to Image library.
     */
    // private function convertPdfToImages($pdfPath)
    // {
    //     try {
    //         $pdf = new Pdf($pdfPath);
    //         $pageCount = $pdf->getNumberOfPages();
    //         $imagePaths = [];

    //         foreach (range(1, $pageCount) as $pageNumber) {
    //             $tempImagePath = storage_path('app/public/temp/' . Str::random(20) . "_page{$pageNumber}.jpg");

    //             // Ensure the temp directory exists
    //             if (!file_exists(dirname($tempImagePath))) {
    //                 mkdir(dirname($tempImagePath), 0777, true);
    //             }

    //             $pdf->setPage($pageNumber)->saveImage($tempImagePath);
    //             $imagePaths[] = $tempImagePath;
    //         }

    //         return $imagePaths;
    //     } catch (\Exception $e) {
    //         throw new \Exception("PDF to Image conversion failed: " . $e->getMessage());
    //     }
    // }

    public function preprocessImages()
    {
        // $request->validate(
        //     [
        //         'image' => 'required',
        //     ],
        //     [
        //         'image.required' => 'Please capture an image',
        //     ]
        // );
        // $img = $request->image;
        // $folderPath = "app/public/temp/";
        // $image_parts = explode(";base64,", $img);
        // $image_base64 = base64_decode($image_parts[1]);
        // $fileName = uniqid() . '.png';

        // $file = $folderPath . $fileName;
        // Storage::put($file, $image_base64);
        // $folderPath = public_path('images'); // Path to the folder containing images
        $files = Storage::disk('public')->files('images');
        $combinedText = '';

        foreach ($files as $file) {
            $filePath = storage_path('app/public/' . $file);
            $text = (new TesseractOCR($filePath))->run();
            $combinedText .= $text . "\n";
            // Storage::disk('public')->delete($file); // Delete the image after processing
        }

        return $combinedText;
    }
}


// class SuratController extends Controller
// {
//     protected $imageManager;

//     public function __construct()
//     {
//         // Initialize Intervention Image with GD driver
//         // $this->imageManager = new ImageManager(driver: 'imagick');
//         // Or use GD driver:
//         // $this->imageManager = new ImageManager('gd');
//         $this->imageManager = ImageManager::gd();
//     }

//     /**
//      * Display a listing of the resource.
//      */
//     public function index()
//     {
//         return view('welcome');
//     }

//     /**
//      * Show the form for creating a new resource.
//      */
//     public function create()
//     {
//         // return view('welcome');
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
//     {
//         // Validate the uploaded file
//         $request->validate([
//             'surat' => 'required|file|mimes:jpeg,jpg,png,pdf'
//         ]);

//         $pdfPath = $request->file('surat')->path();
//         $outputDir = storage_path('/public/ocr_output');

//         // Ensure the output directory exists
//         if (!file_exists($outputDir)) {
//             mkdir($outputDir, 0777, true);
//         }

//         $results = [];

//         if (pathinfo($pdfPath, PATHINFO_EXTENSION) === 'pdf') {
//             $imagePaths = $this->convertPdfToImage($pdfPath);

//             // Perform OCR on each image
//             foreach ($imagePaths as $index => $imagePath) {
//                 $text = (new TesseractOCR($imagePath))->run();
//                 $results["Page " . ($index + 1)] = $text;

//                 // Optionally, delete the image after processing
//                 unlink($imagePath);
//             }
//         } else {
//             // Handle non-PDF files (if needed)
//             $text = (new TesseractOCR($pdfPath))->run();
//             $results['Single Page'] = $text;
//         }

//         // Return results to the main page
//         return redirect('/')->with('result', $results);
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(string $id) {}

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit(string $id)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, string $id)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(string $id)
//     {
//         //
//     }

//     private function processPdf(Request $request)
//     {
//         // $pdfPath = $request->file('pdf')->path();
//         // $pdf = new Pdf($pdfPath);
//         // $results = [];

//         // // Convert each page of the PDF to an image and perform OCR
//         // for ($page = 1; $page <= $pdf->getNumberOfPages(); $page++) {
//         //     $imagePath = storage_path("app/public/page-{$page}.jpg");
//         //     $pdf->setPage($page)->saveImage($imagePath);

//         //     $text = (new TesseractOCR($imagePath))->run();
//         //     $results["Page {$page}"] = $text;
//         // }

//         // return view('ocr_results', ['results' => $results]);
//     }

//     public function scan(Request $request)
//     {
//         try {
//             $request->validate([
//                 'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
//             ]);

//             $file = $request->file('document');
//             $extension = $file->getClientOriginalExtension();
//             $fileName = Str::random(20) . '.' . $extension;
//             $path = Storage::disk('public')->putFileAs('uploads', $file, $fileName);
//             $fullPath = Storage::disk('public')->path($path);

//             if ($extension === 'pdf') {
//                 $imagePaths = $this->convertPdfToImage($fullPath);
//                 foreach ($imagePaths as $imagePath) {
//                     if (!file_exists($imagePath)) {
//                         throw new \Exception("PDF conversion failed - image not created");
//                     }
//                     if (filesize($imagePath) === 0) {
//                         throw new \Exception("PDF conversion created empty file");
//                     }
//                 }
//             } else {
//                 $imagePath = $fullPath;
//             }

//             try {
//                 $imageContent = file_get_contents($imagePath);
//                 if (!$imageContent) {
//                     throw new \Exception("Cannot read image content");
//                 }

//                 $this->preprocessImage($imagePath);
//                 $tesseract = new TesseractOCR($imagePath);
//                 $text = $tesseract->run();

//                 if (empty($text)) {
//                     throw new \Exception("OCR produced no text");
//                 }

//                 $data = $this->extractInformation($text);

//                 if ($extension === 'pdf') {
//                     unlink($imagePath);
//                 }
//                 Storage::disk('public')->delete($path);

//                 return redirect()->route('home')->with('result', $data);
//             } catch (\Exception $e) {
//                 throw new \Exception("Image processing failed: " . $e->getMessage());
//             }
//         } catch (\Exception $e) {
//             return redirect()->route('home')->with('error', $e->getMessage());
//         }
//     }

//     private function convertPdfToImage($pdfPath)
//     {
//         try {
//             $pdf = new \Spatie\PdfToImage\Pdf($pdfPath);
//             $numberOfPages = $pdf->getNumberOfPages();
//             $imagePaths = [];

//             // Convert each page of the PDF to an image
//             for ($pageNumber = 1; $pageNumber <= $numberOfPages; $pageNumber++) {
//                 $tempImagePath = storage_path('app/public/temp/' . Str::random(20) . "_page{$pageNumber}.jpg");

//                 // Ensure temp directory exists
//                 if (!file_exists(dirname($tempImagePath))) {
//                     mkdir(dirname($tempImagePath), 0777, true);
//                 }

//                 // Save the image
//                 $pdf->setPage($pageNumber)->saveImage($tempImagePath);
//                 $imagePaths[] = $tempImagePath;
//             }

//             return $imagePaths; // Return array of image paths
//         } catch (\Exception $e) {
//             throw new \Exception("PDF conversion failed: " . $e->getMessage());
//         }
//     }

//     private function preprocessImage($imagePath)
//     {
//         try {
//             // Read image directly without preprocessing for PDF-converted images
//             if (strpos($imagePath, '/temp/') !== false) {
//                 return;
//             }
//             $image = $this->imageManager->read(file_get_contents($imagePath));
//             $image->greyscale(); // Fixed method name from toGrayScale to greyscale
//             $image->brightness(10);
//             $image->contrast(10);
//             $image->save($imagePath);
//         } catch (\Exception $e) {
//             throw new \Exception("Image preprocessing failed: " . $e->getMessage());
//         }
//     }

//     private function extractInformation($text)
//     {
//         // Split text into lines
//         $lines = explode("\n", $text);
//         $lines = array_map('trim', $lines);
//         $lines = array_filter($lines);

//         // Initialize data structure
//         $data = [
//             'title' => '',
//             'date' => '',
//             'recipient' => '',
//             'sender' => '',
//             'body' => '',
//             'extracted_text' => $text // Store full text for reference
//         ];

//         // Extract title (assuming it's the first non-empty line)
//         if (!empty($lines)) {
//             $data['title'] = $lines[0];
//         }

//         // Look for date patterns
//         foreach ($lines as $line) {
//             if (preg_match('/\b\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\b/', $line)) {
//                 $data['date'] = $line;
//                 break;
//             }
//         }

//         // Look for common letter components
//         foreach ($lines as $index => $line) {
//             // Look for recipient (usually starts with "To:" or "Dear")
//             if (preg_match('/^(To:|Dear\s)/i', $line)) {
//                 $data['recipient'] = $line;
//             }

//             // Look for sender (usually starts with "From:" or ends with "Sincerely")
//             if (preg_match('/^From:/i', $line) || preg_match('/(Sincerely|Regards|Yours truly)/i', $line)) {
//                 $data['sender'] = $line;
//             }
//         }

//         // Everything else goes into body
//         $bodyLines = array_filter($lines, function ($line) use ($data) {
//             return $line !== $data['title']
//                 && $line !== $data['date']
//                 && $line !== $data['recipient']
//                 && $line !== $data['sender'];
//         });
//         $data['body'] = implode("\n", $bodyLines);

//         return $data;
//     }
// }
