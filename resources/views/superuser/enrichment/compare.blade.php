@extends('superuser.app')

@section('content')
<div class="enrich-wrap">
  <div class="enrich-head">
    <h5>Bandingkan Driver</h5>
    <small>Hasil terakhir per parfum acuan untuk tiap driver, diambil dari riwayat proses.</small>
  </div>

  @include('superuser.enrichment._tabs', ['activeTab' => 'bandingkan'])

  <div class="enrich-card">
    <div class="enrich-toolbar">
      <div class="enrich-pills">
        <a class="enrich-pill {{ request('scope') !== 'all' ? 'active' : '' }}" href="{{ route('superuser.enrichment.compare') }}">
          Diisi &ge; 2 driver
        </a>
        <a class="enrich-pill {{ request('scope') === 'all' ? 'active' : '' }}" href="{{ route('superuser.enrichment.compare', ['scope' => 'all']) }}">
          Semua
        </a>
      </div>
      <span class="enrich-note ml-auto d-none d-md-inline">Maksimal 50 baris terakhir</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover enrich-table compare-table">
        <colgroup>
          <col style="width:26%">
          @foreach ($drivers as $driverName)
          <col style="width:{{ 74 / max(count($drivers), 1) }}%">
          @endforeach
        </colgroup>
        <thead>
          <tr>
            <th>Parfum acuan</th>
            @foreach ($drivers as $driverName)
            <th class="col-mid">{{ isset($driverLabels[$driverName]) ? $driverLabels[$driverName] : $driverName }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
          <tr>
            <td>
              <div class="enrich-name">{{ $row['reference']->brand }}</div>
              <div class="enrich-sub">{{ $row['reference']->searah }}</div>
            </td>
            @foreach ($drivers as $driverName)
              @php $cell = $row['cells'][$driverName]; @endphp
              <td class="col-mid">
                @if (!$cell)
                  <span class="text-muted">&ndash;</span>
                @elseif ($cell['status'] !== 'success')
                  <span class="badge-soft red">gagal</span>
                  <div class="compare-note" title="{{ $cell['error'] }}">{{ \Illuminate\Support\Str::limit($cell['error'], 45) }}</div>
                @else
                  @if ($cell['match'] === 'found')
                    <span class="badge-soft green">found</span>
                  @elseif ($cell['match'] === 'ambiguous')
                    <span class="badge-soft amber">ambiguous</span>
                  @elseif ($cell['match'] === 'not_found')
                    <span class="badge-soft gray">not_found</span>
                  @else
                    <span class="badge-soft gray">?</span>
                  @endif
                  <div class="compare-note">
                    {{ $cell['year'] ?: '-' }} &middot; {{ $cell['confidence'] ?: '-' }} &middot; {{ $cell['sources'] }} sumber &middot; {{ $cell['notes'] }} notes
                  </div>
                  <div class="compare-note text-muted">{{ $cell['model'] }}</div>
                @endif
              </td>
            @endforeach
          </tr>
          @empty
          <tr>
            <td colspan="{{ count($drivers) + 1 }}">
              <div class="enrich-empty">
                <i class="fa fa-code-compare"></i>
                Belum ada data pembanding. Jalankan parfum yang sama dengan driver berbeda.
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .compare-table { table-layout: fixed; }
  .compare-table td, .compare-table th { overflow-wrap: anywhere; }
  .compare-note { font-size: .72rem; line-height: 1.35; color: #6b7787; }
  @media (max-width: 991.98px) {
    .compare-table { min-width: 760px; }
  }
</style>
@endpush
