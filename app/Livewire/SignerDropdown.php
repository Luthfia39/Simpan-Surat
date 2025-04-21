<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Signer;

class SignerDropdown extends Component
{
    public $signers; // Collection of signers
    public $selectedSigner = ''; // Selected signer from dropdown
    public $additionalSigner = ''; // New signer name
    public $workerNumber = ''; // Worker number
    public $department = ''; // Department

    public function mount()
    {
        // Load signers from the database or any other source
        $this->signers = Signer::all();
    }

    public function saveSigners()
    {
        // Logic to save the selected signer or the new signer
        // You can check if $selectedSigner is 'new' and handle accordingly
        if ($this->selectedSigner === 'new') {
            // Save the new signer
            Signer::create([
                'name' => $this->additionalSigner,
                'worker_number' => $this->workerNumber,
                'department' => $this->department,
            ]);
        } else {
            // Save the selected signer
        }

        // Reset fields after saving
        $this->reset(['selectedSigner', 'additionalSigner', 'workerNumber', 'department']);
        session()->flash('message', 'Signers saved successfully!');
    }

    // public $signers;
    // public $selectedSigner;
    // public $additionalSigner;
    // public $workerNumber;
    // public $department;

    // public function mount()
    // {
    //     $this->signers = Signer::all();
    // }

    // public function saveSigners()
    // {
    //     $this->validate([
    //         'selectedSigner' => 'required_without:additionalSigner',
    //         'additionalSigner' => 'required_without:selectedSigner|string|max:255',
    //         'workerNumber' => 'required_if:additionalSigner,true|string|max:255',
    //         'department' => 'required_if:additionalSigner,true|string|max:255',
    //     ]);

    //     if ($this->additionalSigner) {
    //         Signer::create([
    //             'name' => $this->additionalSigner,
    //             'worker_number' => $this->workerNumber,
    //             'department' => $this->department,
    //         ]);
    //         session()->flash('message', 'Additional signer added successfully!');
    //     } else {
    //         session()->flash('message', 'Signer selected successfully!');
    //     }

    //     // Reset fields
    //     $this->reset(['selectedSigner', 'additionalSigner', 'workerNumber', 'department']);
    //     $this->signers = Signer::all(); // Refresh the signers list
    // }

/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Render the Livewire signer dropdown component view.
     *
     * @return \Illuminate\View\View
     */

/*******  7401b871-5040-4dec-8f42-740b16cbdfd8  *******/
    public function render()
    {
        return view('livewire.signer-dropdown');
    }
}
