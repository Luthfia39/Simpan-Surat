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

//    public function getData() {
//     $letters = "UNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI\n\nDEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA\nSekip Unit III, Catur Tunggal, Depok, Sleman, Yogyakarta, Indonesia. 55281\nTelp.: (0274) 561111, 505633 I Fax. (0274) 542908 I Email: tedi.sv@ugm.ac.id\n\nSURAT TUGAS\nNO. 4196\\\/UNI1\\\/SV.2-TEDI\\\/AKM\\\/PJ\\\/2024\n\nYang bertanda tangan dibawah ini :\n\nNama : Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng.\n\nNIP :111197510201206101\n\nJabatan : Ketua Departemen Teknik Elektro dan Informatika\nSekolah Vokasi UGM\n\nDengan ini menugaskan mahasiswa tersebut di bawah ini :\n\nNama : Ilham Muhammad Ismaya\nNIM 1 22\\\/499207\\\/SV\\\/21288\nProdi : Sarjana Terapan Teknologi Rekayasa Elektro\n\nDosen Pembimbing : Muhammad Rifgi Al Fauzan, S.Si., M.Sc.\n\nUntuk melaksanakan magang di PT. Pertamina Geothermal Energy - Area Kamojang, yang\ndimulai pada tanggal 7 Oktober 2024 s.d. 18 Januari 2025.\n\nDemikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.\n\nYogyakarta, 7 Oktober 2024\nKetua,\n\nIr. Nur Rohman Rosyid, S.T., M.T., D.Eng.\nNIP. 111197510201206101 \\u00bb\n\nDokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSIE\nUNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI\n\nDEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA\nSekip Unit III, Catur Tunggal, Depok, Sleman, Yogyakarta, Indonesia. 55281\nTelp.: (0274) 561111, 505633 I Fax. (0274) 542908 I Email: tedi.sv@ugm.ac.id\n\nSURAT TUGAS\nNO. 4197\\\/UNI1\\\/SV.2-TEDI\\\/AKM\\\/PJ\\\/2024\n\nYang bertanda tangan dibawah ini :\n\nNama : Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng.\n\nNIP :111197510201206101\n\nJabatan : Ketua Departemen Teknik Elektro dan Informatika\nSekolah Vokasi UGM\n\nDengan ini menugaskan mahasiswa tersebut di bawah ini :\n\nNama : Rosus Pangaribowo\nNIM 1 22\\\/504381\\\/SV\\\/21632\nProdi : Sarjana Terapan Teknologi Rekayasa Elektro\n\nDosen Pembimbing : Dr. Eng. Tika Erna Putri, S.Si., M.Sc.\n\nUntuk melaksanakan magang di PT. Pertamina Geothermal Energy - Area Kamojang, yang\ndimulai pada tanggal 7 Oktober 2024 s.d. 18 Januari 2025.\n\nDemikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.\n\nYogyakarta, 7 Oktober 2024\nKetua,\n\nIr. Nur Rohman Rosyid, S.T., M.T., D.Eng.\nNIP. 111197510201206101 -\n\nDokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSIE\n";

//     // Bersihkan format karakter escape
//     $cleanedText = str_replace('\\\/', '/', $letters);

//     // Regex untuk mendeteksi seluruh surat tugas (termasuk nomor dan isi)
//     $pattern = '/(\d+\/UNI1\/[A-Z0-9.-]+\/[A-Z]+\/[A-Z]+\/\d{4})\n\n(Yang bertanda tangan .*? dipergunakan sebagaimana mestinya\.)\n\n(([A-Z][a-z]+), \d{1,2} [A-Za-z]+ \d{4})/s';

//     preg_match_all($pattern, $cleanedText, $matches, PREG_SET_ORDER);

//     $results = [];

//     foreach ($matches as $match) {
//         $results[] = [
//             'number' => $match[1], // Nomor surat
//             'text' => $match[2],   // Isi surat
//             'date' => $match[3]
//         ];
//     }

//     return $results;
// }

    /**
     * Parse assignment letters and return structured data
     *
     * @return JsonResponse
     */
    public function getData()
    {
        $patterns = [
            'letter_number' => [
                'pattern' => '/NO\.\s*(\d+\/UNI1\/[A-Z0-9.-]+\/[A-Z]+\/[A-Z]+\/\d{4})/',
                'key' => 'nomor_surat'
            ],
            'signer' => [
                'pattern' => '/Nama\s*:\s*([^\n]+)\nNIP\s*:\s*(\d+)\nJabatan\s*:\s*([^\n]+)/',
                'key' => 'penandatangan'
            ],
            'student' => [
                'pattern' => '/Nama\s*:\s*([^\n]+)\nNIM\s*1\s*([^\n]+)\nProdi\s*:\s*([^\n]+)/',
                'key' => 'mahasiswa'
            ],
            'supervisor' => [
                'pattern' => '/Dosen Pembimbing\s*:\s*([^\n]+)/',
                'key' => 'pembimbing'
            ],
            'internship' => [
                'pattern' => '/melaksanakan magang di\s*([^,]+),\s*yang\s*dimulai pada tanggal\s*([^\s]+)\s*s\.d\.\s*([^\n\.]+)/',
                'key' => 'magang'
            ]
        ];

        // Your input text (you might want to get this from a request or file)
         $letters = "UNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI\n\nDEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA\nSekip Unit III, Catur Tunggal, Depok, Sleman, Yogyakarta, Indonesia. 55281\nTelp.: (0274) 561111, 505633 I Fax. (0274) 542908 I Email: tedi.sv@ugm.ac.id\n\nSURAT TUGAS\nNO. 4196\\\/UNI1\\\/SV.2-TEDI\\\/AKM\\\/PJ\\\/2024\n\nYang bertanda tangan dibawah ini :\n\nNama : Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng.\n\nNIP :111197510201206101\n\nJabatan : Ketua Departemen Teknik Elektro dan Informatika\nSekolah Vokasi UGM\n\nDengan ini menugaskan mahasiswa tersebut di bawah ini :\n\nNama : Ilham Muhammad Ismaya\nNIM 1 22\\\/499207\\\/SV\\\/21288\nProdi : Sarjana Terapan Teknologi Rekayasa Elektro\n\nDosen Pembimbing : Muhammad Rifgi Al Fauzan, S.Si., M.Sc.\n\nUntuk melaksanakan magang di PT. Pertamina Geothermal Energy - Area Kamojang, yang\ndimulai pada tanggal 7 Oktober 2024 s.d. 18 Januari 2025.\n\nDemikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.\n\nYogyakarta, 7 Oktober 2024\nKetua,\n\nIr. Nur Rohman Rosyid, S.T., M.T., D.Eng.\nNIP. 111197510201206101 \\u00bb\n\nDokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSIE\nUNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI\n\nDEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA\nSekip Unit III, Catur Tunggal, Depok, Sleman, Yogyakarta, Indonesia. 55281\nTelp.: (0274) 561111, 505633 I Fax. (0274) 542908 I Email: tedi.sv@ugm.ac.id\n\nSURAT TUGAS\nNO. 4197\\\/UNI1\\\/SV.2-TEDI\\\/AKM\\\/PJ\\\/2024\n\nYang bertanda tangan dibawah ini :\n\nNama : Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng.\n\nNIP :111197510201206101\n\nJabatan : Ketua Departemen Teknik Elektro dan Informatika\nSekolah Vokasi UGM\n\nDengan ini menugaskan mahasiswa tersebut di bawah ini :\n\nNama : Rosus Pangaribowo\nNIM 1 22\\\/504381\\\/SV\\\/21632\nProdi : Sarjana Terapan Teknologi Rekayasa Elektro\n\nDosen Pembimbing : Dr. Eng. Tika Erna Putri, S.Si., M.Sc.\n\nUntuk melaksanakan magang di PT. Pertamina Geothermal Energy - Area Kamojang, yang\ndimulai pada tanggal 7 Oktober 2024 s.d. 18 Januari 2025.\n\nDemikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.\n\nYogyakarta, 7 Oktober 2024\nKetua,\n\nIr. Nur Rohman Rosyid, S.T., M.T., D.Eng.\nNIP. 111197510201206101 -\n\nDokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSIE\n";
        
        // Clean escape characters
        $cleanedText = str_replace('\\\/', '/', $letters);
        
        // Find all complete letters
        $letterPattern = '/NO\.\s*(\d+\/UNI1\/[A-Z0-9.-]+\/[A-Z]+\/[A-Z]+\/\d{4})\n\n(Yang bertanda tangan .*? dipergunakan sebagaimana mestinya\.)/s';
        preg_match_all($letterPattern, $cleanedText, $letterMatches, PREG_SET_ORDER);
        
        $results = [];
        
        foreach ($letterMatches as $letterMatch) {
            $letterText = $letterMatch[0];
            $letterData = ['nomor_surat' => $letterMatch[1]];
            
            // Apply each pattern to the letter
            foreach ($patterns as $key => $patternInfo) {
                if (preg_match($patternInfo['pattern'], $letterText, $matches)) {
                    switch ($key) {
                        case 'signer':
                            $letterData[$patternInfo['key']] = [
                                'nama' => $matches[1],
                                'nip' => $matches[2],
                                'jabatan' => $matches[3]
                            ];
                            break;
                            
                        case 'student':
                            $letterData[$patternInfo['key']] = [
                                'nama' => $matches[1],
                                'nim' => $matches[2],
                                'prodi' => $matches[3]
                            ];
                            break;
                            
                        case 'internship':
                            $letterData[$patternInfo['key']] = [
                                'lokasi' => $matches[1],
                                'tanggal_mulai' => $matches[2],
                                'tanggal_selesai' => $matches[3]
                            ];
                            break;
                            
                        case 'supervisor':
                            $letterData[$patternInfo['key']] = $matches[1];
                            break;
                    }
                }
            }
            
            $results[] = $letterData;
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }

}