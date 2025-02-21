<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use Imagick;

class SuratController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function scan(Request $request)
    {
        if (!$request->hasFile('files')) {
            return redirect()->back()->with('error', 'No files uploaded.');
        }

        $file = $request->file('files');

        if ($file->getClientOriginalExtension() === 'pdf') {
            $path = $file->store('pdfs', 'public');
            $pdfPath = storage_path("app/public/{$path}");

            // Convert PDF to Images
            $this->convertPdfToImages($pdfPath);

            // Preprocess Images
            $results = $this->preprocessImages();

            // Extract Data
            $no_letter = $this->getData($results);

            return redirect()->back()->with([
                'results' => $results,
                'no_letter' => $no_letter
            ]);
        }

        return redirect()->back()->with('error', 'Invalid file type.');
    }

    private function convertPdfToImages($pdfPath)
    {
        try {
            $pdf = new Pdf($pdfPath);
            Storage::disk('public')->makeDirectory('images');
            $imagePath = Storage::disk('public')->path("images");
            $pdf->saveAllPages($imagePath);
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
            
            // Preprocess image
            // $processedImagePath = $this->preprocessImageWithImagick($filePath);

            // Extract text using Tesseract
            $text = (new TesseractOCR($filePath))
            // $text = (new TesseractOCR($processedImagePath))
                ->lang('ind')
                ->psm(6) // Page segmentation mode for uniform text blocks
                ->oem(1)
                ->run();

            $combinedText .= $text . "\n";

            // Cleanup processed images
            // Storage::disk('public')->delete($file);
            // unlink($processedImagePath);
        }

        return $combinedText;
    }

    // public function preprocessImageWithImagick()
    private function preprocessImageWithImagick($imagePath)
    {
        try {
            $image = new Imagick($imagePath);

            // $imagick->adaptiveResizeImage(1200, 0);

            // $imagick->setImageDepth(8);

            // Konversi ke grayscale
            // $imagick->modulateImage(100, 0, 100);
            // $imagick->transformImageColorspace(\Imagick::COLORSPACE_GRAY);

            // Hilangkan noise dengan Despeckle
            // $imagick->despeckleImage();

            // Gunakan blur ringan untuk mengurangi noise (opsional)
            // $imagick->blurImage(1, 0.5);

            // $imagick->statisticImage(\Imagick::STATISTIC_MEDIAN, 3, 3);

            
            // $imagick->adaptiveSharpenImage(2, 1.5);
            // $imagick->adaptiveThresholdImage(150, 150, 5);
            // $imagick->statisticImage(\Imagick::STATISTIC_MEDIAN, 3, 3);

            // Rotasi jika miring (opsional)
            // $imagick->deskewImage(40);

            // // ✅ Resize for faster processing
            // $imagick->adaptiveResizeImage(1200, 0);

            // // ✅ Convert to grayscale
            // $imagick->setImageType(\Imagick::IMGTYPE_GRAYSCALE);

            // // ✅ Apply thresholding to make it black & white
            // $imagick->adaptiveThresholdImage(150, 150, 5);

            // -------
            $image->setImageColorspace(Imagick::COLORSPACE_GRAY); // Convert to grayscale
            $image->contrastImage(1); // Increase contrast
            $image->adaptiveThresholdImage(1000, 1000, 10); // Apply adaptive thresholding
            $image->resizeImage(0, 2000, Imagick::FILTER_LANCZOS, 1); // Resize to 2000px height
            $image->blurImage(1, 0.5); // Apply a slight blur
            $image->sharpenImage(0, 1); // Sharpen the image
            $image->deskewImage(0.5); // Deskew the image
            $image->normalizeImage(); // Normalize brightness and contrast

            // Save preprocessed image
            $processedPath = storage_path('app/public/images/'.basename($imagePath));
            $image->writeImage($processedPath);
            $image->clear();
            $image->destroy();

            return $processedPath;
        } catch (\Exception $e) {
            throw new \Exception('Image preprocessing failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse assignment letters and return structured data
     *
     * @return JsonResponse
     */
    public function getData()
    {
        $letters = "UNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI\n\nDEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA\nSekip Unit III, Catur Tunggal, Depok, Sleman, Yogyakarta, Indonesia. 55281\nTelp.: (0274) 561111, 505633 I Fax. (0274) 542908 I Email: tedi.sv@ugm.ac.id\n\nSURAT TUGAS\nNO. 4196\\\/UNI1\\\/SV.2-TEDI\\\/AKM\\\/PJ\\\/2024\n\nYang bertanda tangan dibawah ini :\n\nNama : Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng.\n\nNIP :111197510201206101\n\nJabatan : Ketua Departemen Teknik Elektro dan Informatika\nSekolah Vokasi UGM\n\nDengan ini menugaskan mahasiswa tersebut di bawah ini :\n\nNama : Ilham Muhammad Ismaya\nNIM 1 22\\\/499207\\\/SV\\\/21288\nProdi : Sarjana Terapan Teknologi Rekayasa Elektro\n\nDosen Pembimbing : Muhammad Rifgi Al Fauzan, S.Si., M.Sc.\n\nUntuk melaksanakan magang di PT. Pertamina Geothermal Energy - Area Kamojang, yang\ndimulai pada tanggal 7 Oktober 2024 s.d. 18 Januari 2025.\n\nDemikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.\n\nYogyakarta, 7 Oktober 2024\nKetua,\n\nIr. Nur Rohman Rosyid, S.T., M.T., D.Eng.\nNIP. 111197510201206101 \\u00bb\n\nDokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSIE\nUNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI\n\nDEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA\nSekip Unit III, Catur Tunggal, Depok, Sleman, Yogyakarta, Indonesia. 55281\nTelp.: (0274) 561111, 505633 I Fax. (0274) 542908 I Email: tedi.sv@ugm.ac.id\n\nSURAT TUGAS\nNO. 4197\\\/UNI1\\\/SV.2-TEDI\\\/AKM\\\/PJ\\\/2024\n\nYang bertanda tangan dibawah ini :\n\nNama : Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng.\n\nNIP :111197510201206101\n\nJabatan : Ketua Departemen Teknik Elektro dan Informatika\nSekolah Vokasi UGM\n\nDengan ini menugaskan mahasiswa tersebut di bawah ini :\n\nNama : Rosus Pangaribowo\nNIM 1 22\\\/504381\\\/SV\\\/21632\nProdi : Sarjana Terapan Teknologi Rekayasa Elektro\n\nDosen Pembimbing : Dr. Eng. Tika Erna Putri, S.Si., M.Sc.\n\nUntuk melaksanakan magang di PT. Pertamina Geothermal Energy - Area Kamojang, yang\ndimulai pada tanggal 7 Oktober 2024 s.d. 18 Januari 2025.\n\nDemikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.\n\nYogyakarta, 7 Oktober 2024\nKetua,\n\nIr. Nur Rohman Rosyid, S.T., M.T., D.Eng.\nNIP. 111197510201206101 -\n\nDokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSIE\n";
        
        // Clean escape characters
        $cleanedText = str_replace('\\\/', '/', $letters);
        
        // Define patterns in an array for easy management
        $patterns = [
            'assignment_letter' => [
                'pattern' => '/(\d+\/UNI1\/[A-Z0-9.-]+\/[A-Z]+\/[A-Z]+\/\d{4})\n\n(Yang bertanda tangan.*?Demikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya\.)\n\n(([A-Z][a-z]+), \d{1,2} [A-Za-z]+ \d{4})\n((Ketua|Dekan|Rektor|Direktur)[\s,]*\n+([A-Za-z .,-]+)\nNIP\. (\d+))/s',                
                'keys' => [
                    'number' => 1,
                    'text' => 2,
                    'date' => 3,
                    'signer' => 7,
                    'nip' => 8,
                ]
            ],
            // 'certificate_letter' => [
            //     'pattern' => '/your_pattern_here/',
            //     'keys' => [
            //         'number' => 1,
            //         'text' => 2,
            //         // ... other keys
            //     ]
            // ],
            // 'recommendation_letter' => [
            //     'pattern' => '/another_pattern_here/',
            //     'keys' => [
            //         'number' => 1,
            //         'text' => 2,
            //         // ... other keys
            //     ]
            // ]
        ];
        
        $results = [];
        
        // Process each pattern
        foreach ($patterns as $type => $patternData) {
            preg_match_all($patternData['pattern'], $cleanedText, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $letterData = [
                    'type' => $type
                ];
                
                // Map the matches to their corresponding keys
                foreach ($patternData['keys'] as $key => $index) {
                    $letterData[$key] = trim($match[$index] ?? '');
                }
                
                $results[] = $letterData;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }
}
