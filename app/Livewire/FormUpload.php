<?php

namespace App\Livewire;

use App\Models\Surat;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FormUpload extends Component
{
    use WithFileUploads;

    public $files;
    public $fileName = null;
    public $ocrData = [];
    protected $listeners = ['saveData' => 'saveData'];

    public function updatedFiles()
    {
        if ($this->files) {
            $this->fileName = $this->files->getClientOriginalName();
        }
    }

    public function scan()
    {
        if (!$this->files) {
            session()->flash('error', 'No files uploaded.');
            $this->dispatch('showSweetAlert', ['type' => 'error', 'message' => 'File tidak berhasil diunggah. Silahkan ulangi!']);
            return;
        }

        $newFileName = Str::random(20) . '.' . $this->files->getClientOriginalExtension();

        // Simpan file ke local storage
        $filePath = $this->files->storeAs("pdfs", $newFileName, "public");

        // Kirim file ke Python OCR
        $response = Http::attach('file', 'http://127.0.0.1:8000/storage/pdfs/' . $newFileName, $newFileName)
            ->post('http://127.0.0.1:5000/process-ocr'); // URL Python

        if ($response->successful()) {
            $this->ocrData = $response->json();
            $this->dispatch('showSweetAlert', [
                'type' => 'success_ocr',
                'title' => 'Hasil Scan',
                // 'text' => 'text',
                'text' => $this->ocrData['isi_surat'],
                'confirm' => true
            ]);
        } else {
            session()->flash('error', 'Gagal memproses OCR');
            $this->dispatch('showSweetAlert', ['type' => 'error', 'message' => 'Gagal memproses OCR.']);
        }
    }

    public function saveData()
    {
        try {
            Surat::create([
                "type" => $this->ocrData["type"],
                "nomor_surat" => $this->ocrData["nomor_surat"],
                "tanggal" => $this->ocrData["tanggal"],
                "pengirim" => $this->ocrData["pengirim"],
                "penerima" => $this->ocrData["penerima"],
                "alamat" => $this->ocrData["alamat"],
                "isi_surat" => $this->ocrData["isi_surat"],
                "penanda_tangan" => "Ir. Nur Rohman Rosyid",
            ]);

            $this->dispatch("showResultAlert", [
                "type" => "success",
                "title" => "Berhasil!",
                "text" => "Data surat berhasil disimpan ke database.",
            ]);
        } catch (\Exception $e) {
            $this->dispatch("showResultAlert", [
                "type" => "error",
                "title" => "Gagal!",
                "text" => "Terjadi kesalahan saat menyimpan data.",
            ]);
        }
    }

    public function render()
    {
        return view('livewire.form-upload');
    }
}
