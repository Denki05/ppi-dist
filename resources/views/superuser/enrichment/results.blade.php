@extends('superuser.app')

@section('content')
<div class="enrich-wrap">
  <div class="enrich-head">
    <h5>Hasil &amp; Verifikasi</h5>
    <small>Periksa hasil enrichment, buka detail, lalu verifikasi atau tolak.</small>
  </div>

  @include('superuser.enrichment._tabs', ['activeTab' => 'hasil'])

  <div class="enrich-card">
    <div class="enrich-toolbar">
      <div class="enrich-pills">
        <a class="enrich-pill {{ !request('filter') ? 'active' : '' }}" href="{{ route('superuser.enrichment.results') }}">
          Semua <span class="count">{{ $counts['all'] ?? 0 }}</span>
        </a>
        <a class="enrich-pill {{ request('filter') == 'needs_review' ? 'active' : '' }}" href="{{ route('superuser.enrichment.results', ['filter' => 'needs_review']) }}">
          Perlu Review <span class="count">{{ $counts['needs_review'] ?? 0 }}</span>
        </a>
        <a class="enrich-pill {{ request('filter') == 'pending' ? 'active' : '' }}" href="{{ route('superuser.enrichment.results', ['filter' => 'pending']) }}">
          Pending <span class="count">{{ $counts['pending'] ?? 0 }}</span>
        </a>
        <a class="enrich-pill {{ request('filter') == 'verified' ? 'active' : '' }}" href="{{ route('superuser.enrichment.results', ['filter' => 'verified']) }}">
          Verified <span class="count">{{ $counts['verified'] ?? 0 }}</span>
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover enrich-table results-table">
        <colgroup>
          <col style="width:30%">
          <col style="width:9%">
          <col style="width:7%">
          <col style="width:9%">
          <col style="width:11%">
          <col style="width:10%">
          <col style="width:16%">
        </colgroup>
        <thead>
          <tr>
            <th>Parfum Acuan</th>
            <th class="col-mid">Match</th>
            <th class="col-mid">Tahun</th>
            <th class="col-mid">Confidence</th>
            <th class="col-mid">Cross-check</th>
            <th class="col-mid">Status</th>
            <th class="col-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($references as $reference)
          <tr>
            <td>
              <div class="enrich-name">{{ $reference->brand }}</div>
              <div class="enrich-sub">{{ $reference->searah }}</div>
            </td>
            <td class="col-mid">
              @if ($reference->match_status === 'found')
                <span class="badge-soft green">found</span>
              @elseif ($reference->match_status === 'ambiguous')
                <span class="badge-soft amber">ambiguous</span>
              @elseif ($reference->match_status === 'not_found')
                <span class="badge-soft gray">not_found</span>
              @else
                <span class="badge-soft red">?</span>
              @endif
            </td>
            <td class="col-mid">{{ $reference->release_year ?: '-' }}</td>
            <td class="col-mid">{{ $reference->confidence ?: '-' }}</td>
            <td class="col-mid">{{ $reference->reference_notes_status ?: '-' }}</td>
            <td class="col-mid">
              @if ($reference->status === \App\Entities\Enrichment\FragranceReference::STATUS['VERIFIED'])
                <span class="badge-soft green">verified</span>
              @elseif ($reference->status === \App\Entities\Enrichment\FragranceReference::STATUS['REJECTED'])
                <span class="badge-soft red">ditolak</span>
              @else
                <span class="badge-soft amber">pending</span>
              @endif
              @if ($reference->needs_review)
                <span class="badge-soft amber" title="Perlu review"><i class="fas fa-exclamation"></i></span>
              @endif
            </td>
            <td class="col-end text-nowrap">
              <button type="button" class="btn btn-sm btn-outline-success btn-action" data-id="{{ $reference->id }}" data-url="{{ route('superuser.enrichment.verify') }}" title="Verifikasi" aria-label="Verifikasi {{ $reference->brand }} {{ $reference->searah }}">
                <i class="fas fa-check"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger btn-action" data-id="{{ $reference->id }}" data-url="{{ route('superuser.enrichment.reject') }}" title="Tolak" aria-label="Tolak {{ $reference->brand }} {{ $reference->searah }}">
                <i class="fas fa-times"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary btn-detail" data-target="#detail-{{ $reference->id }}" title="Detail" aria-label="Detail {{ $reference->brand }} {{ $reference->searah }}">
                <i class="fas fa-eye"></i>
              </button>
            </td>
          </tr>
          <tr class="detail-row d-none" id="detail-{{ $reference->id }}">
            <td colspan="7" class="bg-light">
              <div class="detail-wrap">
                <div>
                  @if (!empty($provenance[$reference->id]))
                  @php $prov = $provenance[$reference->id]; @endphp
                  <p class="mb-10"><strong>Sumber data:</strong> {{ isset($driverLabels[$prov->driver]) ? $driverLabels[$prov->driver] : $prov->driver }} &middot; <span class="text-muted">{{ $prov->model }}</span> &middot; {{ $prov->created_at->format('d/m/Y H:i') }}</p>
                  @endif
                  <p class="mb-10"><strong>Matched:</strong> {{ $reference->matched_brand }} &mdash; {{ $reference->matched_name }} ({{ $reference->concentration }})</p>
                  @if ($reference->match_note)
                  <p class="mb-10"><strong>Match note:</strong> {{ $reference->match_note }}</p>
                  @endif
                  <p class="mb-10"><strong>Gender:</strong> {{ $reference->gender_target ?: '-' }} &nbsp; <strong>Family:</strong> {{ $reference->scent_family ?: '-' }}</p>
                  @if ($reference->perfumers && count($reference->perfumers))
                  <p class="mb-10"><strong>Perfumers:</strong> {{ implode(', ', $reference->perfumers) }}</p>
                  @endif
                  <p class="mb-10"><strong>Deskripsi (ID):</strong> {{ $reference->description_id ?: '-' }}</p>
                  <p class="mb-10"><strong>Tags:</strong><br>
                    <span class="text-muted">mood:</span> {{ $reference->tags_mood ? implode(', ', $reference->tags_mood) : '-' }}<br>
                    <span class="text-muted">season:</span> {{ $reference->tags_season ? implode(', ', $reference->tags_season) : '-' }}<br>
                    <span class="text-muted">time:</span> {{ $reference->tags_time_of_day ? implode(', ', $reference->tags_time_of_day) : '-' }}
                  </p>
                </div>
                <div>
                  <p class="mb-10"><strong>Accords:</strong>
                    @if ($reference->accords && count($reference->accords))
                      {{ $reference->accords->sortBy('pivot.prominence')->pluck('name')->implode(', ') }}
                    @else
                      -
                    @endif
                  </p>
                  <p class="mb-10"><strong>Notes top:</strong> {{ $reference->notes_top ? implode(', ', $reference->notes_top) : '-' }}</p>
                  <p class="mb-10"><strong>Notes middle:</strong> {{ $reference->notes_middle ? implode(', ', $reference->notes_middle) : '-' }}</p>
                  <p class="mb-10"><strong>Notes base:</strong> {{ $reference->notes_base ? implode(', ', $reference->notes_base) : '-' }}</p>
                  <p class="mb-10"><strong>Sources:</strong>
                    @if ($reference->sources && count($reference->sources))
                    <ul class="pl-20 mb-0">
                      @foreach ($reference->sources as $source)
                      <li><a href="{{ $source['url'] ?? '#' }}" target="_blank" rel="noopener">{{ isset($source['title']) ? \Illuminate\Support\Str::limit($source['title'], 60) : ($source['url'] ?? '-') }}</a></li>
                      @endforeach
                    </ul>
                    @else
                      -
                    @endif
                  </p>
                  @if ($reference->notes_differences && count($reference->notes_differences))
                  <p class="mb-10"><strong>Perbedaan dengan notes internal:</strong><br>{{ implode('<br>', array_map('e', $reference->notes_differences)) }}</p>
                  @endif
                  @if ($reference->review_reasons && count($reference->review_reasons))
                  <p class="mb-10 text-warning"><strong>Alasan review:</strong><br>{{ implode('<br>', array_map('e', $reference->review_reasons)) }}</p>
                  @endif
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7">
              <div class="enrich-empty">
                <i class="fa fa-clipboard"></i>
                Belum ada hasil. Jalankan produk dari tab <strong>Jalankan</strong>.
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($references->hasPages())
    <div class="px-15 py-10 border-top">
      {{ $references->links() }}
    </div>
    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
  .results-table { table-layout: fixed; }
  .results-table th, .results-table td { overflow-wrap: anywhere; }
  .detail-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 28px; font-size: .82rem; }
  .detail-wrap p { margin-bottom: 4px; }
  .detail-wrap ul { margin-bottom: 0; }
  .btn-action, .btn-detail { min-height: 30px; min-width: 34px; }
  @media (max-width: 991.98px) {
    .detail-wrap { grid-template-columns: 1fr; }
    .results-table { min-width: 880px; }
  }
</style>
@endpush

@push('scripts')
<script>
  $('.btn-detail').on('click', function () {
    $($(this).data('target')).toggleClass('d-none');
  });

  $('.btn-action').on('click', function () {
    var btn = $(this);
    btn.prop('disabled', true);
    $.ajax({
      url: btn.data('url'),
      method: 'POST',
      data: { id: btn.data('id') },
      dataType: 'JSON'
    }).done(function () {
      window.location.reload();
    }).fail(function (xhr) {
      btn.prop('disabled', false);
      var message = 'Request gagal (HTTP ' + xhr.status + ')';
      if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.notification) {
        message = xhr.responseJSON.data.notification.content;
      }
      alert(message);
    });
  });
</script>
@endpush
