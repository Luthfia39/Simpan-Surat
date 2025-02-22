<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Http\Requests\FileRequest;

class FormUpload extends Component
{
    use WithFileUploads;
    
    public $files;

    public function scan(FileRequest $request)
    {

        if (!$request->hasFile('files')) {
            session()->flash('error', 'No files uploaded.');
            return;
        }

        $files = $request->file('files');

        // Restrict to one PDF only
        if (count($this->files) > 1) {
            session()->flash('error', 'Only one PDF file can be uploaded at a time.');
            return;
        }

        session()->flash('success', 'bisa');
        
    }

    public function render()
    {
        return view('livewire.form-upload');
    }
}
