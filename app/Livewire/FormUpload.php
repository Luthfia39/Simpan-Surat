<?php

namespace App\Livewire;

use Livewire\Component;

class FormUpload extends Component
{
    public $files;
    public $showModal = false; // Controls the modal visibility

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function scan(Request $request)
    {
        if (!$request->hasFile('files')) {
            return redirect()->back()->with('error', 'No files uploaded.');
        }

        $files = $request->file('files');
        $paths = [];
        $latestPdfPath = null;

        // Restrict to one PDF only
        if (count($files) > 1) {
            return redirect()->back()->with('error', 'Only one PDF file can be uploaded at a time.');
        }

        foreach ($files as $file) {
            $path = $file->store('pdfs', 'public');
            $paths[] = $path;
            $latestPdfPath = storage_path("app/public/{$path}");
        }
        // Convert the most recent PDF to images
        $this->convertPdfToImages($latestPdfPath);

        // Call preprocessImages() after conversion
        $results = $this->preprocessImages();

        $no_letter = $this->getData($results);

        session()->flash('success',
            $results, 
            // 'no_letter' => $no_letter
        );

        // return redirect()->back()->with('success', 'Files uploaded successfully: ' . implode(', ', $paths));
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

    public function render()
    {
        return view('livewire.form-upload');
    }
}
