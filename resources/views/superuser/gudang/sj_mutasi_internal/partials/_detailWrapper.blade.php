@php
    if ($mutasi->status_checked == 0 || $mutasi->status_checked == 2) {
        $currentStep = 1;
    } elseif ($mutasi->status_checked == 1 && $mutasi->print_count == 0) {
        $currentStep = 2;
    } elseif ($mutasi->status_checked == 1 && $mutasi->print_count > 0) {
        // Masuk step 3 hanya jika sudah sampai tahap barang
        $currentStep = 3;
    } else {
        $currentStep = 1;
    }
@endphp


<div class="mutasi-detail">

    {{-- ================= STEPPER ================= --}}
    <div class="mb-4">
        @include('superuser.gudang.sj_mutasi_internal.partials._stepper')
    </div>

    {{-- ================= HEADER ================= --}}
    {{--<div class="mb-3">
        <h6 class="mb-1">
            Mutasi: {{ $mutasi->kode }}
        </h6>

        <div class="text-muted" style="font-size:12px; line-height:1.6;">
            Tanggal: {{ $mutasi->tanggal->format('d/m/Y') }} <br>
            Status Checked: {{ $mutasi->statusChecked() }} <br>
            Status Barang: {{ $mutasi->statusBarang() }}
        </div>
    </div>--}}

    {{-- ================= CONTENT ================= --}}
    <div>
        @if ($currentStep == 1)
            @include('superuser.gudang.sj_mutasi_internal.partials.steps.step1_checklist')

        @elseif ($currentStep == 2)
            @include('superuser.gudang.sj_mutasi_internal.partials.steps.step2_print')

        @else
            @include('superuser.gudang.sj_mutasi_internal.partials.steps.step3_barang')
        @endif
    </div>
</div>