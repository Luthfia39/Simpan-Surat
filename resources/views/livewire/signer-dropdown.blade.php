<div class="container mt-5">
    <!-- <h2 class="text-center mb-4">Signer Management</h2> -->

    <div class="form-group">
        <label for="signer">Select Signer:</label>
        <select wire:model="selectedSigner" id="signer" class="form-control">
            <option value="">-- Select a signer --</option>
            @foreach($signers as $signer)
                <option value="{{ $signer->id }}">{{ $signer->name }}</option>
            @endforeach
            <option value="new">Add New Signer</option> <!-- Option to add new signer -->
        </select>
    </div>

    @if($selectedSigner === 'new') <!-- Show input only if "Add New Signer" is selected -->
        <div class="form-group">
            <label for="additionalSigner">Enter New Signer Name:</label>
            <input type="text" wire:model="additionalSigner" id="additionalSigner" class="form-control" placeholder="Enter name" />
        </div>
    @endif

    <div class="form-group">
        <label for="workerNumber">Worker Number:</label>
        <input type="text" wire:model="workerNumber" id="workerNumber" class="form-control" placeholder="Enter worker number"/>
    </div>

    <div class="form-group">
        <label for="department">Department:</label>
        <input type="text" wire:model="department" id="department" class="form-control" placeholder="Enter department" />
    </div>

    <button wire:click="saveSigners" class="btn btn-primary btn-block">Save Signers</button>

    @if(session()->has('message'))
        <div class="alert alert-success mt-3">{{ session('message') }}</div>
    @endif
</div>