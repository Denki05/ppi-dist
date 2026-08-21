@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Edit Sales Order</span>
</nav>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if($step == 1)
<form id="frmEditSOMaster" method="post" action="{{ route('superuser.penjualan.sales_order.update') }}" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="id" value="{{$result->id}}">
  <input type="hidden" name="step" value="{{$step}}">
  <input type="hidden" id="brand_ppi" value="{{ $result->brand_name }}">
    
  <div class="row">
    <!-- KOLOM KIRI (4) -->
    <div class="col-4">
      <div class="block">
        <div class="block-content">
          <div class="form-row">
            <!-- TYPE TRANSAKSI -->
            <div class="form-group col-md">
              <label for="type_transaction">Type Transaksi</label>
              <!-- Ditampilkan sebagai teks readonly agar persis seperti Create -->
              <input type="text" class="form-control bg-light" value="{{ $result->type_transaction }}" readonly>
              <!-- Hidden input agar datanya terkirim ke Controller -->
              <input type="hidden" name="type_transaction" value="{{ $result->type_transaction }}">
            </div>

            <!-- INDENT -->
            <div class="form-group col-md">
              <label for="so_indent">Indent</label>
              <!-- Ditampilkan sebagai teks readonly -->
              <input type="text" class="form-control bg-light" value="{{ $result->so_indent == 1 ? 'YES' : 'NO' }}" readonly>
              <!-- Hidden input agar datanya terkirim ke Controller -->
              <input type="hidden" name="so_indent" value="{{ $result->so_indent }}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md">
              <label for="brand_name">Brand</label>
              <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name" data-placeholder="Pilih Brand/Merek">
                @foreach($brand as $value)
                <option value="{{$value->brand_name}}" {{ ($result->brand_name == $value->brand_name) ? 'selected' : '' }}>{{ $value->brand_name }}</option>
                @endforeach
              </select>
            </div>
            
            <div class="form-group col-md">
              <label for="customer">Customer</label>
              <input type="text" name="customer_name" class="form-control bg-light" value="{{ $result->member->name }} {{$result->member->text_kota}}" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KOLOM KANAN (8) -->
    <div class="col-8">
      <div class="block">
        <div class="block-content">
          <div class="row">
            <div class="col col-md-7">
              <div class="form-row">
                
                <!-- Baris 1: Kurs, Approval, Disc USD -->
                <div class="form-group col-md-4 mb-2">
                  <span class="form-label"><b>Kurs </b></span>
                  <!-- Input type text agar bisa menampilkan titik ribuan -->
                  <input class="form-control" type="text" name="idr_rate" id="idr_rate" value="{{ number_format((float) $result->idr_rate, 2, ',', '.') }}">
                </div>

                <div class="form-group col-md-4 mb-2">
                  <span class="form-label"><b>Approval </b></span>
                  <input class="form-control bg-light" type="text" value="{{ $result->approval_mou == 1 ? 'YES' : 'NO' }}" readonly>
                </div>

                <div class="form-group col-md-4 mb-2">
                  <span class="form-label"><b>Disc USD </b></span>
                  <select class="form-control js-select2 global-disc" name="global_disc_usd" id="global_disc_usd" style="width: 100%;" {{ $result->approval_mou == 1 ? 'disabled' : '' }}>
                      <option value="0" {{ $result->disc_usd == 0 ? 'selected' : '' }}>0</option>
                      <option value="2" {{ $result->disc_usd == 2 ? 'selected' : '' }}>2</option>
                      <option value="4" {{ $result->disc_usd == 4 ? 'selected' : '' }}>4</option>
                  </select>
                </div>

                <!-- Baris 2: Disc (%), Disc Kemasan, Disc IDR -->
                <div class="form-group col-md-4 mb-2">
                  <span class="form-label"><b>Disc (%) </b></span>
                  @if($result->approval_mou == 1)
                    <input type="hidden" name="catatan" value="{{ $result->disc_percent }}">
                    <input class="form-control bg-light" type="number" value="{{ $result->disc_percent }}" readonly>
                  @else
                    <!-- Sengaja name-nya tetap 'catatan' agar Controller update bawaan Anda tidak error -->
                    <input class="form-control" type="number" name="catatan" id="disc_percent" value="{{ $result->disc_percent }}" step="any" min="0" max="100" placeholder="0">
                  @endif
                </div>

                <div class="form-group col-md-4 mb-2">
                  <span class="form-label"><b>Disc Kemasan (%) </b></span>
                  <input class="form-control global-disc {{ $result->approval_mou == 1 ? 'bg-light' : '' }}" type="number" step="any" min="0" max="100" name="global_disc_kemasan" id="global_disc_kemasan" value="{{ $result->disc_kemasan }}" placeholder="0" {{ $result->approval_mou == 1 ? 'readonly' : '' }}>
                </div>

                <div class="form-group col-md-4 mb-2">
                  <span class="form-label"><b>Disc IDR </b></span>
                  <input class="form-control global-disc {{ $result->approval_mou == 1 ? 'bg-light' : '' }}" type="number" step="any" name="global_disc_idr" id="global_disc_idr" value="{{ $result->disc_idr }}" {{ $result->approval_mou == 1 ? 'readonly' : '' }}>
                </div>
              </div>
            </div>
            
            <div class="col col-md-5"> 
              <div class="form-group">
                <span class="form-label"><b>Note </b></span>
                <textarea class="form-control" name="note" id="editor" rows="5">{{ $result->note }}</textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="block">
        <div class="block-header d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
          <h2 class="block-title mb-0">#Add Product</h2>
          <div class="d-flex flex-wrap" style="gap:8px;">
            <a href="#" class="row-add" data-id="0">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                <i class="fa fa-plus mr-10"></i> Row
              </button>
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSoKontrak">
              <i class="fa fa-search mr-10" aria-hidden="true"></i> Kontrak
            </button>
          </div>
        </div>
        <div class="block-content">
          <div id="loading-produk" class="text-center py-15" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-10 mb-0 text-muted">Memuat daftar produk...</p>
          </div>

          <table id="datatables" class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">#</th>
                <th class="text-center">Produk</th>
                <th class="text-center">Kemasan</th>
                <th class="text-center">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Disc</th>
                <th class="text-center">Free</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($result->so_detail as $detail)
                <tr class="list-body {{ $detail->free_product == 1 ? 'table-success' : '' }}" data-row="{{ $loop->index }}">
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <input class="form-check-input" type="checkbox" value="{{ $detail->kontrak }}" name="check_kontrak" disabled {{ $detail->kontrak == 1 ? 'checked' : '' }}>
                    <input type="hidden" name="value_kontrak[]" value="{{ $detail->kontrak }}">
                    <input type="hidden" name="so_kontrak_value[]" value="{{ $detail->kontrak }}"> <!-- INI TAMBAHANNYA -->
                    <input type="hidden" name="kontrak_id[]" value="{{ $detail->kontrak_id }}">
                    <input type="hidden" name="kontrak_new[]" value="0">
                    <input type="hidden" name="item_id[]" value="{{ $detail->id }}">
                  </td>
                  <td>
                      <input type="hidden" name="sku[]" value="{{ $detail->product_packaging_id }}">
                      <span class="name font-w600">{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }}</span>
                  </td>
                  <td>
                      <input type="text" class="form-control packaging-name-display text-center bg-light" value="{{ $detail->product_pack->packaging->pack_name }}" disabled>
                      <input type="hidden" class="form-control packaging" name="packaging[]" value="{{ $detail->packaging_id }}">
                  </td>
                  <td><input type="number" style="text-align: center;" class="form-control bg-light" name="price[]" required value="{{ $detail->price }}" readonly></td>
                  <td>
                    <input type="number" style="text-align: center;" class="form-control input-qty" name="qty[]" value="{{ $detail->qty }}" step="any" required>
                  </td>
                  <td>
                    <input type="text" style="text-align: center;" class="form-control" name="disc[]" value="{{ $detail->disc_usd }}" {{ $detail->free_product == 1 ? 'readonly' : '' }}>
                  </td>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input input-gift mt-2" name="gift" {{ $detail->free_product == 1 ? 'checked' : '' }}>
                    <input class="form-control input-free" type="hidden" value="{{ $detail->free_product }}" name="free_product[]">
                  </td>
                  <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="row pt-30 mb-15">
        <div class="col-md-6">
          <a href="{{route('superuser.penjualan.sales_order.index_' . strtolower($step_txt))}}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button class="btn btn-primary btn-md btn-simpan" type="submit"><i class="fa fa-save"></i> Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addSoKontrak" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add So Kontrak</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form>
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-right" for="so_kontrak">SO Kontrak <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <select class="js-select2 form-control js-select2-kontrak" id="so_kontrak" name="so_kontrak" data-placeholder="Search" style="width: 100%;">
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <a href="#" class="row-add" data-id="1">
            <button type="button" class="btn bg-gd-sea border-0 text-white" id="addModalKontrak" data-dismiss="modal">
              Add
            </button>
          </a>
        </div>
      </div>
    </div>
  </div>
</form>
@endif

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2();

    $(".js-select2-brand").select2({
      ajax: {
        url: '{{ route('superuser.penjualan.sales_order.get_brand') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}"
          };
        },
        cache: true
      },
    });

    $(".js-select2-kontrak").select2({
      ajax: {
        url: '{{ route('superuser.penjualan.sales_order.search_kontrak', [$result->customer_other_address_id, $result->brand_name]) }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}"
          };
        },
        cache: true,
      },
    });

    $('.js-select2-kontrak').on('select2:select', function (e) {
      $.ajax({
        url: '{{ route('superuser.penjualan.sales_order.get_product_kontrak') }}',
        data: {so_kontrak:$(this).val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        allowClear: true,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_kontrak = json.data;
          }
        }
      });
    });

    var table = $('#datatables').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'checkbox', orderable: false, width: "5%"},
          {name: 'sku', orderable: false, width: "35%"},
          {name: 'kemasan', orderable: false, searcable: false, width: "15%"},
          {name: 'price', orderable: false, searcable: false, width: "12%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'disc', orderable: false, searcable: false, width: "10%"},
          {name: 'free', orderable: false, searcable: false, width: "5%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    });

    var counter = {{ count($result->so_detail) + 1 }};
    var product_data = new Object();
    var product_kontrak = new Object();
    var isProductLoading = false;

    function loadProductList() {
      isProductLoading = true;
      $('#loading-produk').show();
      $('a.row-add, #addModalKontrak').prop('disabled', true);

      $.ajax({
        url: '{{ route('superuser.penjualan.sales_order.get_product_pack') }}',
        data: {
          id: $('#brand_ppi').val(), // Panggil default saat load
          _token: "{{csrf_token()}}"
        },
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_data = json.data;
          }
        },
        complete: function() {
          isProductLoading = false;
          $('#loading-produk').hide();
          $('a.row-add, #addModalKontrak').prop('disabled', false);
        }
      });
    }

    // Load data product onload
    loadProductList();

    // Trigger load data jika ganti brand
    $('#brand_name').on('select2:select', function (e) {
      $('#brand_ppi').val($(this).val());
      loadProductList();
    });

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      var typeAdd = $(this).data('id');

      if (isProductLoading) {
        Swal.fire('Tunggu', 'Daftar produk masih dimuat, coba lagi sebentar.', 'info');
        return;
      }

      if($('#brand_name').val()) {
        if(typeAdd == 0){
          makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';
          
          $.map( product_data, function( val, i ) {
            makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-packname="'+ val['packName'] +'" data-price="'+ val['price'] +'" data-packid="'+ val['packID']+'">'+ val['code'] + ' - ' + val['name'] + '</option>';
          });

          makeselect += '</select>';

          table.row.add([
            counter,
            '<input class="form-check-input" type="checkbox" value="0" name="check_kontrak" disabled><input type="hidden" class="form-control" value="0" name="value_kontrak[]"><input type="hidden" name="so_kontrak_value[]" value="0">',
            makeselect,
            '<input type="text" class="form-control packaging-name-display text-center bg-light" value="" disabled>',
            '<input type="number" class="form-control bg-light" name="price[]" style="text-align: center;" readonly><input type="hidden" class="form-control packaging" name="packaging[]"><input type="hidden" class="form-control" name="kontrak_id[]">',
            '<input type="number" class="form-control" name="qty[]" style="text-align: center;" required step="any">',
            '<input type="number" class="form-control" name="disc[]" style="text-align: center;" value="0">',
            '<input type="checkbox" class="form-check-input input-gift mt-2" name="gift"><input class="form-control input-free" type="hidden" value="0" name="free_product[]">',
            '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
          ]).draw( false );
                    
          initailizeSelect2();
          counter++;
        }else{
          makeselect = '<select class="js-select2 form-control js-ajax-kontrak" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';

          $.map( product_kontrak, function( val, i ) {
            makeselect += '<option value="'+ val['product_id'] +'" data-kontrak="'+ val['kontrak_id'] +'" data-name="'+ val['product_name'] +'" data-code="'+ val['product_code'] +'" data-price="'+ val['product_price'] +'" data-packaging="'+ val['packaging_id'] +'" data-packname="'+ val['packaging_name'] +'" data-disc="'+ val['product_disc'] + '">'+ val['product_code'] + ' - ' + val['product_name'] + ' - ' + val['packaging_name']  +'</option>';
          });

          makeselect += '</select>';

          table.row.add([
            counter,
            '<input class="form-check-input" type="checkbox" value="1" name="check_kontrak" disabled checked><input type="hidden" class="form-control" value="1" name="value_kontrak[]"><input type="hidden" class="form-control" name="so_kontrak_value[]" value="1"><input type="hidden" class="form-control" name="kontrak_so_id[]">',
            makeselect,
            '<input type="text" class="form-control packaging-name-display text-center bg-light" value="" disabled>',
            '<input type="number" class="form-control bg-light" name="price[]" style="text-align: center;" readonly><input type="hidden" class="form-control packaging" name="packaging[]"><input type="hidden" class="form-control" value="1" name="kontrak_new[]"><input type="hidden" class="form-control" name="kontrak_id[]">',
            '<input type="number" class="form-control noscroll" name="qty[]" style="text-align: center;" required>',
            '<input type="number" class="form-control noscroll usd_disc bg-light" style="text-align: center;" name="disc[]" readonly>',
            '<input type="checkbox" class="form-check-input input-gift mt-2" name="gift" disabled><input class="form-control input-free" type="hidden" value="0" name="free_product[]">',
            '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
          ]).draw( false );
                  
          initailizeSelectKontrak2();
          counter++;
        }
      }
    });

    function initailizeSelect2(){
      $(".js-ajax").select2();

      $('.js-ajax').on('select2:select', function (e) {
        var price = $(this).find(':selected').data('price');
        $(this).closest('tr').find('input[name="price[]"]').val(price);

        var pack = $(this).find(':selected').data('packid');
        $(this).closest('tr').find('input[name="packaging[]"]').val(pack);

        var packName = $(this).find(':selected').data('packname');
        $(this).parents('tr').find('.packaging-name-display').val(packName);
      });
    };

    function initailizeSelectKontrak2(){
      $(".js-ajax-kontrak").select2();

      $('.js-ajax-kontrak').on('select2:select', function (e) {
        var kontrak = $(this).find(':selected').data('kontrak');
        $(this).closest('tr').find('input[name="kontrak_so_id[]"]').val(kontrak);

        var price = $(this).find(':selected').data('price');
        $(this).closest('tr').find('input[name="price[]"]').val(price);

        var disc = $(this).find(':selected').data('disc');
        $(this).closest('tr').find('.usd_disc').val(disc);

        var pack = $(this).find(':selected').data('packaging');
        $(this).closest('tr').find('input[name="packaging[]"]').val(pack);

        var packName = $(this).find(':selected').data('packname');
        $(this).parents('tr').find('.packaging-name-display').val(packName);
      });
    }

    $('#datatables tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      var $row = $(this).parents('tr');

      Swal.fire({
        title: 'Hapus baris ini?',
        text: 'Data produk akan dihapus.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.value) {
          table.row($row).remove().draw();
        }
      });
    });
$('#datatables tbody').on('change', '.input-gift', function (e) {
      var $row = $(this).closest('tr');
      // Tangkap ulang nilai diskon global USD saat ini
      var globalDiscUsd = $('#global_disc_usd').val() || 0;

      if($(this).is(':checked')){
        $row.find('.input-free').val(1);
        $row.find('input[name="disc[]"]').val(0).prop('readonly', true);
        $row.addClass('table-success');
      }else{
        $row.find('.input-free').val(0);
        $row.find('input[name="disc[]"]').val(globalDiscUsd).prop('readonly', false); // Kembalikan ke nilai global
        $row.removeClass('table-success');
      }
    });

    // DETEKSI PERUBAHAN DISKON GLOBAL
    $('#global_disc_usd').on('change', function() {
        var nilaiBaru = $(this).val();

        // Looping ke semua baris di dalam tabel
        $('#datatables tbody tr').each(function() {
            var $row = $(this);
            var isFree = $row.find('.input-gift').is(':checked'); // Cek apakah barang ini gratis
            
            // Jika BUKAN barang gratis, update nilai diskonnya
            if (!isFree) {
                $row.find('input[name="disc[]"]').val(nilaiBaru);
            }
        });
    });

   $(document).on('submit','#frmEditSOMaster',function(e){
      e.preventDefault();
      let _form = $(this);

      if (table.rows().count() === 0) {
        Swal.fire('Perhatian', 'Tambahkan minimal 1 produk sebelum menyimpan perubahan.', 'warning');
        return;
      }

      Swal.fire({
        title: 'Konfirmasi',
        text: "Apakah anda yakin ingin menyimpan perubahan sales order ini?",
        type: 'warning', 
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.value || result.isConfirmed) { 
          $.ajax({
            url : '{{route("superuser.penjualan.sales_order.update")}}',
            method : "POST",
            data : new FormData(_form[0]), 
            processData: false,
            contentType: false,
            dataType : "JSON",
            beforeSend : function(){
              _form.find('.btn-simpan').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            },
            success : function(resp){
              if(resp.IsError == true){
                Swal.fire('Error!', resp.Message, 'error'); 
              }
              else{
                Swal.fire(
                    'Success!',
                    resp.Message,
                    'success'
                ).then(() => {
                    window.location.href = "{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}";
                });
              }
            },
            error: function(xhr) {
               Swal.fire('Error!', 'Terjadi kesalahan pada sistem/server.', 'error');
            },
            complete : function(){
              _form.find('.btn-simpan').html('<i class="fa fa-save"></i> Save Changes').prop('disabled', false);
            }
          });
        }
      });
    });

    // FUNGSI AUTO FORMAT RUPIAH PADA INPUT KURS
    $('#idr_rate').on('keyup', function() {
      var val = $(this).val().replace(/[^0-9,]/g, ''); // Hanya boleh angka dan koma
      var parts = val.split(',');
      var bulat = parts[0];
      var desimal = parts[1];
      
      var sisa = bulat.length % 3;
      var rupiah = bulat.substr(0, sisa);
      var ribuan = bulat.substr(sisa).match(/\d{3}/g);
      
      if (ribuan) {
          var separator = sisa ? '.' : '';
          rupiah += separator + ribuan.join('.');
      }
      
      var result = typeof desimal !== 'undefined' ? rupiah + ',' + desimal : rupiah;
      $(this).val(result);
    });

  })
</script>
@endpush