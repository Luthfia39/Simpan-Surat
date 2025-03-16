<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class FormUpload extends Component
{
    use WithFileUploads;

    public $files;
    public $fileName = null; // Simpan nama file yang dipilih

    public function updatedFiles()
    {
        // Simpan nama file setelah user memilih
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

        $filePath = $this->files->storeAs("pdfs", $this->files->getClientOriginalName(), "public");

        session()->flash('success', 'Berhasil menyimpan data!');
        $this->dispatch('showSweetAlert', ['type' => 'success', 'title' => 'Hasil Scan', 'text' => 'Berhasil menyimpan data!']);
        $this->reset(['files']);
    }

    public function render()
    {
        return view('livewire.form-upload');
    }
}
