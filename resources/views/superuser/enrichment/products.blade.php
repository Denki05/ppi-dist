@extends('superuser.app')

@section('content')
<div class="enrich-wrap">
  <div class="enrich-head">
    <h5>Enrichment Produk</h5>
    <small><strong>{{ $stats['ready'] }}</strong> produk punya brand + searah Fragrantica dan siap dijalankan.</small>
  </div>

  @include('superuser.enrichment._tabs', ['activeTab' => 'produk'])

  <div id="alert-block"></div>

  <div class="enrich-card">
    <div class="enrich-toolbar">
      <form method="GET" action="{{ route('superuser.enrichment.products') }}" class="enrich-search mb-0">
        <div class="input-group input-group-sm">
          <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode / brand / searah...">
          <div class="input-group-append">
            <button type="submit" class="btn btn-primary px-3"><i class="fa fa-search"></i></button>
            @if (request('q'))
            <a href="{{ route('superuser.enrichment.products') }}" class="btn btn-outline-secondary px-2" title="Hapus pencarian">&times;</a>
            @endif
          </div>
        </div>
      </form>
      <div class="enrich-meta">
        <span class="enrich-chip"><i class="fas fa-database"></i> {{ $stats['references'] }} referensi</span>
        <span class="enrich-chip"><i class="fas fa-exclamation-circle"></i> {{ $stats['needs_review'] }} perlu review</span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover enrich-table products-table">
        <colgroup>
          <col style="width:44px">
          <col style="width:120px">
          <col>
          <col style="width:34%">
          <col style="width:150px">
        </colgroup>
        <thead>
          <tr>
            <th class="col-mid"><input type="checkbox" id="check-all" title="Centang semua di halaman ini"></th>
            <th>Kode</th>
            <th>Produk</th>
            <th>Searah</th>
            <th class="col-mid">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
          <tr>
            <td class="col-mid"><input type="checkbox" class="check-entity" value="{{ $product->id }}"></td>
            <td><span class="enrich-mono">{{ $product->code }}</span></td>
            <td>
              <div class="enrich-name">{{ $product->name }}</div>
              <div class="enrich-sub">{{ $product->brand_name ?: '-' }}</div>
            </td>
            <td>{{ $product->sub_brand_reference ? $product->sub_brand_reference->name : '-' }}</td>
            <td class="col-mid">
              @php
                $map = $maps->get($product->id);
                $refStatus = $map && $map->reference ? $map->reference->status : null;
              @endphp
              @if ($refStatus === null)
                <span class="badge-soft gray">belum</span>
              @elseif ($refStatus === \App\Entities\Enrichment\FragranceReference::STATUS['VERIFIED'])
                <span class="badge-soft green">verified</span>
              @elseif ($refStatus === \App\Entities\Enrichment\FragranceReference::STATUS['REJECTED'])
                <span class="badge-soft red">ditolak</span>
              @else
                <span class="badge-soft amber">pending</span>
              @endif
              @if ($map && $map->reference && $map->reference->needs_review)
                <span class="badge-soft amber" title="Perlu review"><i class="fas fa-exclamation"></i> review</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5">
              <div class="enrich-empty">
                <i class="fa fa-inbox"></i>
                Tidak ada produk yang cocok. Hanya produk dengan brand + searah Fragrantica yang ditampilkan.
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($products->hasPages())
    <div class="px-15 py-10 border-top">
      {{ $products->links() }}
    </div>
    @endif

    <div class="enrich-actionbar">
      <span class="enrich-count" id="selected-count">0 dipilih</span>
      <a href="#" class="text-danger small d-none" id="selected-clear">kosongkan</a>
      <span class="enrich-note ml-auto d-none d-md-inline">Maks. 30 produk sekali jalan (1 request LLM per parfum acuan)</span>
      <button type="button" class="btn btn-primary min-width-125" id="btn-run-selected">
        <i class="fas fa-play mr-2"></i> Jalankan terpilih
      </button>
    </div>
  </div>

  <div class="enrich-card">
    <div class="enrich-card-head">
      <i class="fas fa-flask text-primary mr-2"></i> Uji sampel (tidak disimpan)
    </div>
    <div class="p-15">
      <p class="enrich-note mb-10">Uji satu parfum bebas &mdash; cocok untuk sampel negatif (parfum fiktif harus dijawab <em>not_found</em>).</p>
      <form id="form-sample">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="sample-brand" class="small mb-10">Brand</label>
            <input type="text" class="form-control form-control-sm" id="sample-brand" name="brand">
          </div>
          <div class="form-group col-md-6">
            <label for="sample-searah" class="small mb-10">Searah</label>
            <input type="text" class="form-control form-control-sm" id="sample-searah" name="searah">
          </div>
        </div>
        <div class="form-group">
          <label for="sample-notes" class="small mb-10">Notes internal</label>
          <textarea class="form-control form-control-sm" id="sample-notes" name="notes" rows="2" placeholder="Boleh kosong"></textarea>
        </div>
        <button type="submit" class="btn btn-sm btn-outline-primary" id="btn-sample">
          <i class="fas fa-flask mr-2"></i> Jalankan uji
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .products-table { table-layout: fixed; }
  .products-table td, .products-table th { overflow-wrap: anywhere; }
  @media (max-width: 991.98px) {
    .products-table { min-width: 760px; }
  }
</style>
@endpush

@push('scripts')
<script>
  var SELECT_KEY = 'enrichment.selected';
  var MAX_SELECT = 30;
  var selectedIds = loadSelected();

  function loadSelected() {
    try {
      var arr = JSON.parse(sessionStorage.getItem(SELECT_KEY) || '[]');
      return Array.isArray(arr) ? arr.map(String) : [];
    } catch (e) {
      return [];
    }
  }

  function persistSelected() {
    sessionStorage.setItem(SELECT_KEY, JSON.stringify(selectedIds));
  }

  function addSelected(id) {
    if (selectedIds.indexOf(id) !== -1) {
      return true;
    }
    if (selectedIds.length >= MAX_SELECT) {
      return false;
    }
    selectedIds.push(id);
    persistSelected();
    return true;
  }

  function removeSelected(id) {
    var index = selectedIds.indexOf(id);
    if (index !== -1) {
      selectedIds.splice(index, 1);
      persistSelected();
    }
  }

  function refreshSelectionUI() {
    $('input.check-entity').each(function () {
      $(this).prop('checked', selectedIds.indexOf(String($(this).val())) !== -1);
    });

    var visible = $('input.check-entity').length;
    var checkedHere = $('input.check-entity:checked').length;
    $('#check-all').prop('checked', visible > 0 && checkedHere === visible);

    var label = selectedIds.length + ' dipilih';
    if (selectedIds.length > checkedHere) {
      label += ' (termasuk halaman lain)';
    }
    $('#selected-count').text(label);
    $('#selected-clear').toggleClass('d-none', selectedIds.length === 0);
  }

  $('#check-all').on('change', function () {
    var check = this.checked;
    var overflow = false;
    $('input.check-entity').each(function () {
      var id = String($(this).val());
      if (check) {
        if (!addSelected(id)) {
          overflow = true;
        }
      } else {
        removeSelected(id);
      }
    });
    if (overflow) {
      alert('Maksimal ' + MAX_SELECT + ' produk sekali jalan');
    }
    refreshSelectionUI();
  });

  $(document).on('change', 'input.check-entity', function () {
    var id = String($(this).val());
    if (this.checked) {
      if (!addSelected(id)) {
        this.checked = false;
        alert('Maksimal ' + MAX_SELECT + ' produk sekali jalan');
      }
    } else {
      removeSelected(id);
    }
    refreshSelectionUI();
  });

  $('#selected-clear').on('click', function (e) {
    e.preventDefault();
    selectedIds = [];
    persistSelected();
    refreshSelectionUI();
  });

  $('#btn-run-selected').on('click', function () {
    var ids = selectedIds.slice();
    if (ids.length === 0) {
      alert('Centang minimal 1 produk dulu');
      return;
    }
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses ' + ids.length + ' produk...');
    $.ajax({
      url: '{{ route('superuser.enrichment.run_selected') }}',
      method: 'POST',
      data: { ids: ids },
      dataType: 'JSON'
    }).done(function (response) {
      renderBlock(response.data.notification);
      if (response.data.redirect_to) {
        setTimeout(function () { window.location.href = response.data.redirect_to; }, 4000);
      }
    }).fail(function (xhr) {
      if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.notification) {
        renderBlock(xhr.responseJSON.data.notification);
      } else {
        renderBlock({ alert: 'block', type: 'alert-danger', header: 'Error', content: 'Request gagal (HTTP ' + xhr.status + ')' });
      }
    }).always(function () {
      btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i> Jalankan terpilih');
    });
  });

  $('#form-sample').on('submit', function (e) {
    e.preventDefault();
    var btn = $('#btn-sample');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...');
    $.ajax({
      url: '{{ route('superuser.enrichment.sample_run') }}',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'JSON'
    }).done(function (response) {
      renderBlock(response.data.notification);
    }).fail(function (xhr) {
      if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.notification) {
        renderBlock(xhr.responseJSON.data.notification);
      } else {
        renderBlock({ alert: 'block', type: 'alert-danger', header: 'Error', content: 'Request gagal (HTTP ' + xhr.status + ')' });
      }
    }).always(function () {
      btn.prop('disabled', false).html('<i class="fas fa-flask mr-2"></i> Jalankan uji');
    });
  });

  function renderBlock(notification) {
    $('#alert-block').html(
      '<div class="alert ' + notification.type + ' alert-dismissable mt-10" role="alert">' +
      '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
      '<p class="font-weight-bold mb-10">' + notification.header + '</p>' +
      '<p class="mb-0">' + notification.content + '</p>' +
      '</div>'
    );
    window.scrollTo(0, 0);
  }

  refreshSelectionUI();
</script>
@endpush
