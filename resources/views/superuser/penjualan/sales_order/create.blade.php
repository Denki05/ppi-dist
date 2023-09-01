@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Create Sales Order</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmCreate" action="#" data-type="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="ajukankelanjutan" value="0">
      <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#SO Detail</h3>
          </div>
          <div class="block-content">
            <div class="row">
              <div class="col-6">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Sales Senior<span class="text-danger">*</span></label>
                            <div class="col-md-8">
                              <select class="form-control js-select2" name="sales_senior_id">
                                <option value="">Pilih Sales senior</option>
                                @foreach($sales as $index => $row)
                                  <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Sales<span class="text-danger">*</span></label>
                            <div class="col-md-8">
                              <select class="form-control js-select2" name="sales_id">
                                <option value="">Pilih Sales</option>
                                @foreach($sales as $index => $row)
                                  <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          @endif
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Order Date<span class="text-danger">*</span></label>
                            <div class="col-md-8">
                              <input type="date" class="form-control so_date" name="so_date">
                            </div>
                          </div>
                          @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
                            <div class="col-md-6">
                              @if ($customers->has_tempo == 0)
                                <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="CASH" readonly>
                                <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="CASH">
                              @elseif($customers->has_tempo == 1)
                                <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="TEMPO" readonly>
                                <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="TEMPO">
                              @endif
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Note</label>
                            <div class="col-8">
                              <textarea class="form-control" name="note" rows="1"></textarea>
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Kurs</label>
                            <div class="col-md-6">
                              <input type="text" name="idr_rate" class="form-control" value="1">
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Brand</label>
                            <div class="col-md-6">
                              <select class="form-control js-select2 select-brand" data-index="0">
                                <option value="">Pilih Merek</option>
                                @foreach($brand as $index => $row)
                                <option value="{{$row->id}}">{{$row->brand_name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      <hr />
      <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#Add Product</h3>
            <a href="#" class="row-add">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                <i class="fa fa-plus mr-10"></i> Row
              </button>
            </a>
          </div>
          <div class="block-content">
            <div class="container">
              <div class="row">
                <table id="datatable" class="table table-striped table-vcenter table-responsive">
                  <thead>
                    <tr>
                      <th class="text-center">Counter</th>
                      <th class="text-center">Category</th>
                      <th class="text-center">Packaging</th>
                      <th class="text-center">Product</th>
                      <th class="text-center">Qty</th>
                      <th class="text-center">Price</th>
                      <th class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <hr />
      
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
          
          <button class="btn btn-primary btn-md btn-simpan-dan-ajukan-ke-lanjutan" type="button"><i class="fa fa-save"></i> Simpan dan ajukan ke Lanjutan</button>
        </div>
      </div>
    </form>
  </div>
</div>


@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script>
  var productCount = 1;

  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

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
      if(confirm("Apakah anda yakin ingin menambakan sales order ini ?")){
        let _form = $('#frmCreate');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.store', [$other_address->id, $customers->id])}}',
          method : "POST",
          data : $('#frmCreate').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').html('Loading...');
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
          error : function(){
            alert("Cek Koneksi Internet")
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

    // add product
    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'category', orderable: false, width: "15%"},
          {name: 'packaging', orderable: false, searcable: false, width: "15%"},
          {name: 'product', orderable: false, searcable: false, width: "25%"},
          {name: 'qty', orderable: false, searcable: false, width: "5%"},
          {name: 'price', orderable: false, searcable: false},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })
    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="category['+counter+']" name="category[]" data-placeholder="Select Category" style="width:100%" required></select>',
                    '<select class="js-select2 form-control js-ajax" id="packaging[]" name="packaging[]" data-placeholder="Select Packaging" style="width:100%" required></select>',
                    '<select class="js-select2 form-control js-ajax" id="product[]" name="product[]" data-placeholder="Select Product" style="width:100%" required></select>',
                    '<input type="number" class="form-control" name="total[]" readonly>',
                    '<input type="number" class="form-control" name="quantity[]" readonly required>',
                    '<input type="number" class="form-control" name="price[]" readonly required>',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
                  // $('.js-select2').select2()
                  initailizeSelect2();
      counter++;
    });
 
    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      
      table.row( $(this).parents('tr') ).remove().draw();

    });
  })
</script>
@endpush