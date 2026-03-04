{{-- STEP 2: Print SJ --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="alert alert-info py-2 mb-3">
            <strong>Step 2:</strong> Cetak Surat Jalan
        </div>

        {{-- Hidden ID --}}
        <input type="hidden" name="mutasi_id" value="{{ $mutasi->id }}">

        {{-- ACTION PRINT --}}
        <div class="d-flex gap-2 mb-4">
            @if ($type === 'showroom')
                <a href="{{ route('superuser.gudang.mutasi_showroom.print_pdf', $mutasi->id) }}"
                target="_blank"
                class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer"></i> Print SJ
                </a>
            @else
                <a href="{{ route('superuser.gudang.mutasi_out.print_pdf', $mutasi->id) }}"
                target="_blank"
                class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer"></i> Print SJ
                </a>
            @endif
        </div>

        <hr>

        {{-- ACTION --}}
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-danger" id="cancelStep2">Cancel</button>
            <button type="button" class="btn btn-primary" id="nextStep2">Next Step</button>
        </div>
    </div>
</div>