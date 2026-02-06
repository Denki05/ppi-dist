<div class="d-flex justify-content-between mb-3" style="font-size:12px;">
    @foreach ([1=>'Checklist',2=>'Print SJ',3=>'Update Barang'] as $step => $label)
        <div class="text-center flex-fill">
            <div
                class="rounded-circle mx-auto mb-1"
                style="
                    width:28px;
                    height:28px;
                    line-height:28px;
                    background: {{ $currentStep >= $step ? '#0d6efd' : '#dee2e6' }};
                    color:#fff;
                    font-size:12px;
                ">
                {{ $step }}
            </div>
            <div class="{{ $currentStep >= $step ? '' : 'text-muted' }}">
                {{ $label }}
            </div>
        </div>
    @endforeach
</div>