@extends('superuser.app')

@section('content')

<nav class="breadcrumb bg-white push">
  <a href="{{ route('superuser.master.customer.index') }}" class="btn btn-outline-secondary ml-10">
    <i class="fa fa-arrow-left mr-5"></i> Kembali
  </a>
  <span class="ml-15 mt-10 text-muted">
    Editor latitude &amp; longitude member. Tempel koordinat dari Google Maps (format <b>lat, lng</b>), lalu simpan.
  </span>
</nav>

<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">GPS Member</h3>
    <div class="block-options">
      <button type="button" id="btn-save-all" class="btn btn-sm btn-success">
        <i class="fa fa-save mr-5"></i> Simpan Semua Perubahan
      </button>
    </div>
  </div>
  <div class="block-content block-content-full">
    <div class="row mb-15">
      <div class="col-md-4">
        <input type="text" class="form-control" id="search_q" placeholder="Cari nama member / store / kota / alamat">
      </div>
      <div class="col-md-3">
        <div class="form-check mt-10">
          <input type="checkbox" class="form-check-input" id="only_empty" value="1">
          <label class="form-check-label" for="only_empty">Hanya yang belum ada koordinat</label>
        </div>
      </div>
      <div class="col-md-5 text-right">
        <button type="button" id="btn-filter" class="btn btn-primary">
          Filter <i class="fa fa-search ml-10"></i>
        </button>
        <button type="button" id="btn-reset" class="btn btn-outline-secondary ml-5">
          Reset
        </button>
      </div>
    </div>

    <style>
      #gps-datatable .gps-input { width: 140px; }
      #gps-datatable td { vertical-align: middle; }
      #gps-datatable .gps-input.changed { border-color: #f0b400; background: #fffbe6; }
      #gps-datatable .gps-input.is-invalid { border-color: #dc3545; }
    </style>

    <table id="gps-datatable" class="table table-striped table-vcenter">
      <thead>
        <tr>
          <th style="width: 40px;">#</th>
          <th>Store</th>
          <th>Member</th>
          <th>Kota</th>
          <th>Alamat</th>
          <th>Latitude</th>
          <th>Longitude</th>
          <th>Maps</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  var gpsDatatable;

  $(document).ready(function () {
    var datatableUrl = '{{ route('superuser.master.customer.gps_maps.json') }}';

    gpsDatatable = $('#gps-datatable').DataTable({
      language: {
        processing: "<span class='fa-stack fa-lg'><i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i></span>",
      },
      processing: true,
      serverSide: true,
      ajax: {
        url: datatableUrl,
        dataType: 'json',
        type: 'GET',
        data: function (d) {
          d._token = '{{ csrf_token() }}';
          d.q = $('#search_q').val();
          d.only_empty = $('#only_empty').is(':checked') ? 1 : 0;
        }
      },
      columns: [
        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
        {data: 'store_name', name: 'master_customers.name'},
        {data: 'member_name', name: 'master_customer_other_addresses.name'},
        {data: 'member_kota', name: 'master_customer_other_addresses.text_kota'},
        {data: 'member_address', name: 'master_customer_other_addresses.address'},
        {data: 'gps_latitude', orderable: false, searchable: false},
        {data: 'gps_longitude', orderable: false, searchable: false},
        {data: 'map', orderable: false, searchable: false},
        {data: 'action', orderable: false, searchable: false}
      ],
      order: [[2, 'asc']],
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      dom: "<'row'<'col-sm-2'l><'col-sm-10 text-left'B>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });

    $('#btn-filter').on('click', function (e) {
      e.preventDefault();
      gpsDatatable.ajax.reload();
    });

    $('#btn-reset').on('click', function (e) {
      e.preventDefault();
      $('#search_q').val('');
      $('#only_empty').prop('checked', false);
      gpsDatatable.ajax.reload();
    });

    $('#search_q').on('keypress', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        gpsDatatable.ajax.reload();
      }
    });

    // Dukung tempel "lat, lng" ke salah satu kolom: otomatis isi pasangannya.
    $(document).on('paste', '.gps-input', function (e) {
      var text = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
      var parts = (text || '').split(/[,;]+/).map(function (v) { return v.trim(); }).filter(function (v) { return v !== ''; });

      if (parts.length === 2) {
        e.preventDefault();
        var $tr = $(this).closest('tr');
        $tr.find('.gps-lat').val(parts[0]).trigger('input');
        $tr.find('.gps-lng').val(parts[1]).trigger('input');
      }
    });

    $(document).on('input', '.gps-input', function () {
      var $tr = $(this).closest('tr');
      var changed = $.trim($tr.find('.gps-lat').val()) !== $.trim($tr.find('.gps-lat').attr('data-original') || '')
        || $.trim($tr.find('.gps-lng').val()) !== $.trim($tr.find('.gps-lng').attr('data-original') || '');
      $tr.find('.gps-input').toggleClass('changed', changed);
      $(this).removeClass('is-invalid');
    });

    $(document).on('keypress', '.gps-input', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        saveRow($(this).closest('tr'));
      }
    });

    $(document).on('click', '.btn-save-row', function () {
      saveRow($(this).closest('tr'));
    });

    $('#btn-save-all').on('click', function () {
      var rows = [];
      $('#gps-datatable tbody tr').each(function () {
        if (isRowChanged($(this))) {
          rows.push(readRow($(this)));
        }
      });

      if (!rows.length) {
        Swal.fire('Tidak ada perubahan', 'Ubah latitude/longitude terlebih dahulu.', 'info');
        return;
      }

      if (!validateRows(rows)) {
        return;
      }

      submitRows(rows);
    });
  });

  function readRow($tr) {
    return {
      id: $tr.find('.gps-lat').attr('data-member-id'),
      latitude: $.trim($tr.find('.gps-lat').val()),
      longitude: $.trim($tr.find('.gps-lng').val())
    };
  }

  function isRowChanged($tr) {
    var lat = $tr.find('.gps-lat');
    var lng = $tr.find('.gps-lng');
    return $.trim(lat.val()) !== $.trim(lat.attr('data-original') || '')
      || $.trim(lng.val()) !== $.trim(lng.attr('data-original') || '');
  }

  function validateRow(row) {
    var lat = row.latitude === '' ? null : parseFloat(String(row.latitude).replace(',', '.'));
    var lng = row.longitude === '' ? null : parseFloat(String(row.longitude).replace(',', '.'));

    if ((lat === null) !== (lng === null)) {
      return 'Latitude dan longitude harus diisi bersamaan.';
    }

    if (lat === null) {
      return null;
    }

    if (isNaN(lat) || lat < -90 || lat > 90) {
      return 'Latitude harus angka antara -90 sampai 90.';
    }

    if (isNaN(lng) || lng < -180 || lng > 180) {
      return 'Longitude harus angka antara -180 sampai 180.';
    }

    return null;
  }

  function validateRows(rows) {
    for (var i = 0; i < rows.length; i++) {
      var error = validateRow(rows[i]);
      if (error) {
        var $tr = $('#gps-datatable tbody tr').filter(function () {
          return $(this).find('.gps-lat').attr('data-member-id') === String(rows[i].id);
        });
        $tr.find('.gps-input').addClass('is-invalid');
        Swal.fire('Koordinat tidak valid', error, 'error');
        return false;
      }
    }

    return true;
  }

  function saveRow($tr) {
    if (!isRowChanged($tr)) {
      return;
    }

    var row = readRow($tr);
    var error = validateRow(row);

    if (error) {
      $tr.find('.gps-input').addClass('is-invalid');
      Swal.fire('Koordinat tidak valid', error, 'error');
      return;
    }

    submitRows([row]);
  }

  function submitRows(rows) {
    Swal.fire({
      title: 'Menyimpan...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      backdrop: false,
      onOpen: function () {
        Swal.showLoading();
      }
    });

    $.ajax({
      url: '{{ route('superuser.master.customer.gps_maps.update') }}',
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        rows: rows
      },
      dataType: 'json'
    }).then(function (response) {
      Swal.fire({
        title: 'Tersimpan!',
        text: 'Koordinat berhasil disimpan.',
        type: 'success',
        backdrop: false,
      }).then(function () {
        gpsDatatable.ajax.reload(null, false);
      });
    }).catch(function (xhr) {
      var message = 'Gagal menyimpan koordinat.';
      if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.notification) {
        var content = xhr.responseJSON.data.notification.content;
        message = Array.isArray(content) ? content.join('<br>') : content;
      }
      Swal.fire('Error!', message, 'error');
    });
  }
</script>
@endpush
