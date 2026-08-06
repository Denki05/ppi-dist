@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Create Sales Order</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

@if($step == 1)
  <form id="frmCreate" action="#" data-type="POST" enctype="multipart/form-data">
  @csrf
    <input type="hidden" name="ajukankelanjutan" value="0">
    <input type="hidden" name="need_proforma" value="{{ $is_proforma }}">
    <div class="row">
      <div class="col-4">
        <div class="block">
          <div class="block-content">
            <div class="form-row">
              <div class="form-group col-md">
                <label for="note">Type Transaksi</label>
                <input type="text" class="form-control" value="{{ $type_transaction }}" name="type_transaction" readonly>
              </div>

              <div class="form-group col-md">
                <label for="note">Indent</label>
                <?php 
                  $indent_type = $type_indent;
                  if($indent_type == 0){
                    $indent = "NO";
                  }else{
                    $indent = "YES";
                  }
                ?>
                <input type="text" class="form-control" value="{{ $indent }}" id="so_indnet" name="so_indent" readonly>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md">
                <label for="note">Brand</label>
                <input type="text" class="form-control" value="{{ $merek->brand_name }}" name="brand_name" id="brand_name" readonly>
              </div>
              <div class="form-group col-md">
                <label for="note">Kemasan</label>
                <input type="text" class="form-control bg-light" value="{{ $selected_packaging->pack_name ?? '-' }}" readonly>
                <input type="hidden" name="packaging_id" id="packaging_id" value="{{ $selected_packaging->id ?? '' }}">
                @if(empty($selected_packaging))
                  <small class="text-danger">Kemasan belum terdefinisi — kembali dan pilih kemasan terlebih dahulu.</small>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-8">
        <div class="block">
          <div class="block-content">
            <div class="row">
              <div class="col col-md-5">
                <div class="form-row">
                  <div class="form-group col-md-4">
                    <span class="form-label"><b>Kurs </b> <span class="text-danger">*</span></span>
                    <input class="form-control" type="text" id="kurs_display" value="{{ number_format((float) $idr_rate, 2, ',', '.') }}" readonly>
                    <input type="hidden" name="kurs" id="kurs" value="{{ number_format((float) $idr_rate, 2, ',', '') }}">
                  </div>

                  <div class="form-group col-md-4">
                    <span class="form-label"><b>Disc % </b>
                    @if($approval_mou == 0)
                    <input class="form-control" type="text" name="disc_percent" id="disc_percent" value="{{ $disc }}" readonly>
                    @else
                    <input class="form-control" type="text" name="disc_percent" id="disc_percent" required>
                    @endif
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-8">
                    <span class="form-label"><b>Approval </b> <span class="text-danger">*</span></span>
                    <?php 
                      if($approval_mou == 0){
                        $approval = "NO";
                      }else{
                        $approval = "YES";
                      }
                    ?>
                    <input class="form-control" type="text" name="approvalText" id="approvalText" value="{{ $approval }}" readonly>
                    <input type="hidden" name="approval" id="approval" value="{{ $approval_mou }}">
                  </div>
                </div>
              </div>
              <div class="col col-md-6">
                <div class="form-group">
                  <span class="form-label"><b>Note </b> <span class="text-danger">*</span></span>
                  <textarea class="form-control" name="note_so" id="editor" rows="4" col="10" readonly>{{ $note_so }}</textarea>
                </div>
              </div>
           </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
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
            <div id="produk-empty-alert" class="alert alert-warning" style="display:none;"></div>

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
              </tbody>
            </table>
          </div>
          <br>
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
            <button class="btn btn-primary btn-md btn-simpan" type="button"><i class="fa fa-save"></i> Simpan</button>
          @role('Developer')
            <button class="btn btn-primary btn-md btn-simpan-dan-ajukan-ke-lanjutan" type="button"><i class="fa fa-save"></i> Simpan dan ajukan ke Lanjutan</button>
          @endrole
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
            <!-- <input type="hidden" name="customer_name" value="{{ $other_address->id }}"> -->
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <!-- <button type="button" class="btn btn-primary" id="addKontrak">Add</button> -->
            <a href="#" class="row-add" data-id="1">
              <button type="button" class="btn bg-gd-sea border-0 text-white" id="addModalKontrak" data-dismiss="modal">
                Add
              </button>
            </a>

            <!-- <input type="hidden" class="form-control" id="valueKontrak" value="1"> -->
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
<script type="text/javascript">
  $(document).ready(function () {

    $('.js-select2').select2();

    $(document).on('click','.btn-simpan',function(){
      $('#frmCreate').find('input[name="ajukankelanjutan"]').val(0);
      $('#frmCreate').submit();
    })

    $(document).on('click','.btn-simpan-dan-ajukan-ke-lanjutan',function(){
      $('#frmCreate').find('input[name="ajukankelanjutan"]').val(1);
      $('#frmCreate').submit();
    })

    $(document).on('submit','#frmCreate',function(e){
      e.preventDefault();

      if (table.rows().count() === 0) {
        Swal.fire('Perhatian', 'Tambahkan minimal 1 produk sebelum menyimpan SO.', 'warning');
        return;
      }

      if(confirm("Apakah anda yakin ingin menambakan sales order ini ?")){
        let _form = $('#frmCreate');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.store', [$other_address->id])}}',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content'), '_method': 'patch'},
          method : "POST",
          data : $('#frmCreate').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('.btn-simpan, .btn-simpan-dan-ajukan-ke-lanjutan').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire(
                'Success!',
                resp.Message,
                'success'
              ).then((result) => {
                  document.location.href = '{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}';
              })
              
            }
          },
          error: function (jqXHR) {
            let errorMessage = "Cek Koneksi Internet";
            if (jqXHR.responseJSON && jqXHR.responseJSON.Notification) {
                errorMessage = jqXHR.responseJSON.Notification.content;
            }
            Swal.fire(
                'Error!',
                errorMessage,
                'error'
            );
          },
          complete : function(){
            $('.btn-simpan').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            $('.btn-simpan-dan-ajukan-ke-lanjutan').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan dan ajukan ke Lanjutan');
          }
        })
      }
    })

    $(".js-select2-kontrak").select2({
      
      ajax: {
        url: '{{ route('superuser.penjualan.sales_order.search_kontrak', [$other_address->id, $merek->brand_name]) }}',
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

    var product_data = new Object();
    var product_kontrak = new Object();
    var isProductLoading = false;

    var table = $('#datatables').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'checkbox', orderable: false, width: "5%"},
          {name: 'sku', orderable: false, width: "40%"},
          {name: 'kemasan', orderable: false, searcable: false, width: "15%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'disc', orderable: false, searcable: false, width: "10%"},
          {name: 'free', orderable: false, searcable: false, width: "5%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    function loadProductList() {
      isProductLoading = true;
      $('#loading-produk').show();
      $('#produk-empty-alert').hide();
      $('a.row-add, #addModalKontrak').prop('disabled', true);

      $.ajax({
        url: '{{ route('superuser.penjualan.sales_order.get_product_pack') }}',
        data: {
          id: $('#brand_name').val(),
          packaging_id: $('#packaging_id').val(),
          _token: "{{csrf_token()}}"
        },
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_data = json.data;
          } else {
            product_data = {};
            $('#produk-empty-alert')
              .text('Tidak ada produk yang cocok dengan Brand & Kemasan ini.')
              .show();
          }
        },
        error: function() {
          Swal.fire('Error', 'Gagal memuat daftar produk. Coba muat ulang halaman.', 'error');
        },
        complete: function() {
          isProductLoading = false;
          $('#loading-produk').hide();
          $('a.row-add, #addModalKontrak').prop('disabled', false);
        }
      });
    }

    loadProductList();

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      var typeAdd = $(this).data('id');

      if (isProductLoading) {
        Swal.fire('Tunggu', 'Daftar produk masih dimuat, coba lagi sebentar.', 'info');
        return;
      }
      if ($.isEmptyObject(product_data)) {
        Swal.fire('Perhatian', 'Tidak ada produk untuk Brand & Kemasan ini.', 'warning');
        return;
      }

      if($('#brand_name').val()) {
        if(typeAdd == 0){
          
          makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';
        
          $.map( product_data, function( val, i ) {
            if(val['typeName'] === null){
              makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-packname="'+ val['packName'] +'" data-price="'+ val['price'] +'" data-packid="'+ val['packID']+'">'+ val['code'] + ' - ' + val['name'] + '</option>';
            } else {
              makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-packname="'+ val['packName'] +'" data-price="'+ val['price'] +'" data-packid="'+ val['packID']+'">'+ val['code'] + ' - ' + val['name'] + '</option>';
            }
            
          });

          makeselect += '</select>';

                    table.row.add([
                      counter,
                      '<input class="form-check-input" type="checkbox" value="0" name="check_kontrak" id="check_kontrak" disabled><input type="hidden" class="form-control" value="0" name="value_kontrak[]">',
                      makeselect,
                      '<input type="text" class="form-control packaging-name-display" value="" disabled>',
                      '<input type="number" class="form-control" name="price[]" style="text-align: center;"><input type="hidden" class="form-control packaging" name="packaging[]">',
                      '<input type="number" class="form-control" name="qty[]" style="text-align: center;" required>',
                      '<input type="number" class="form-control" name="disc[]" style="text-align: center;">',
                      '<input type="checkbox" class="form-check-input input-gift" id="gift" name="gift"><input class="form-control input-free" type="hidden" id="free_product" value="0" name="free_product[]">',
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
                  '<input class="form-check-input" type="checkbox" value="1" name="check_kontrak" id="check_kontrak" disabled checked><input type="hidden" class="form-control" value="1" name="value_kontrak[]"><input type="hidden" class="form-control" name="kontrak_so_id[]">',
                  makeselect,
                  '<input type="text" class="form-control packaging-name-display" value="" disabled>',
                  '<input type="number" class="form-control" name="price[]" style="text-align: center;" readonly><input type="hidden" class="form-control packaging" name="packaging[]">',
                    '<input type="number" class="form-control noscroll" name="qty[]" style="text-align: center;" required>',
                    '<input type="number" class="form-control noscroll usd_disc" style="text-align: center;" name="disc[]">',
                    '<input type="checkbox" class="form-check-input input-gift" id="gift" name="gift" disabled><input class="form-control input-free" type="hidden" id="free_product" value="0" name="free_product[]">',
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
        $(this).parents('tr').find('input[name="price[]"]').val(price);

        var pack = $(this).find(':selected').data('packid');
        $(this).parents('tr').find('input[name="packaging[]"]').val(pack);

        var packName = $(this).find(':selected').data('packname');
        $(this).parents('tr').find('.packaging-name-display').val(packName);
      });

    };

    function initailizeSelectKontrak2(){
      $(".js-ajax-kontrak").select2();

      $('.js-ajax-kontrak').on('select2:select', function (e) {
        var kontrak = $(this).find(':selected').data('kontrak');
        $(this).parents('tr').find('input[name="kontrak_so_id[]"]').val(kontrak);

        var price = $(this).find(':selected').data('price');
        $(this).parents('tr').find('input[name="price[]"]').val(price);

        var disc = $(this).find(':selected').data('disc');
        $(this).parents('tr').find('.usd_disc').val(disc);

        var pack = $(this).find(':selected').data('packaging');
        $(this).parents('tr').find('input[name="packaging[]"]').val(pack);

        var packName = $(this).find(':selected').data('packname');
        $(this).parents('tr').find('.packaging-name-display').val(packName);
      });
    }

    $('#datatables tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      var $row = $(this).parents('tr');

      Swal.fire({
        title: 'Hapus baris ini?',
        text: 'Data produk pada baris ini akan dihapus dari SO.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          table.row($row).remove().draw();

          if(typeof $('input[name="id[]"]').val() == 'undefined') {
            $('#submit-table').prop('disabled', true);
          }
        }
      });
    });

    $('#datatables tbody').on( 'click', '.input-gift', function (e) {
      if($(this).is(':checked')){
        $(this).parents('tr').find('.input-free').val(1);
      }else{
        $(this).parents('tr').find('.input-free').val(0);
      }
    });
  })
</script>
@endpush