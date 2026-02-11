<div>

    {{-- TABS --}}
    <div class="mutasi-tabs mb-2">
        <div class="tab-btn active" data-tab="aktif">
            Aktif (<span id="count-aktif">{{ $mutasiAktif->total() }}</span>)
        </div>

        <div class="tab-btn" data-tab="belum-diambil">
            Belum Diambil (<span id="count-belum">{{ $mutasiBelumDiambil->total() }}</span>)
        </div>

        <div class="tab-btn" data-tab="selesai">
            Selesai (<span id="count-selesai">{{ $mutasiSelesai->total() }}</span>)
        </div>
    </div>

    {{-- TAB AKTIF --}}
    <div id="tab-aktif" class="mutasi-tab-content">
        @include('superuser.gudang.sj_mutasi_internal.partials._table_gudang_rows', [
            'rows' => $mutasiAktif,
            'muted' => false
        ])
        {{ $mutasiAktif->links() }}
    </div>

    {{-- TAB BELUM DIAMBIL --}}
    <div id="tab-belum-diambil" class="mutasi-tab-content d-none">
        @include('superuser.gudang.sj_mutasi_internal.partials._table_gudang_rows', [
            'rows' => $mutasiBelumDiambil,
            'muted' => false
        ])
        {{ $mutasiBelumDiambil->links() }}
    </div>

    {{-- TAB SELESAI --}}
    <div id="tab-selesai" class="mutasi-tab-content d-none">
        @include('superuser.gudang.sj_mutasi_internal.partials._table_gudang_rows', [
            'rows' => $mutasiSelesai,
            'muted' => true
        ])
        {{ $mutasiSelesai->links() }}
    </div>

</div>