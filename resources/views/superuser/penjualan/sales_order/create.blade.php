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
      <div class="row">
        <div class="col-4">
          <div class="card">
            <div class="card-body">
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Team Leader<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_senior_id">
                        <option value="">Pilih TL</option>
                        @foreach($sales as $index => $row)
                          <option value="{{$row->id}}">{{$row->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Salesman<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_id">
                        <option value="">Pilih Salesman</option>
                        @foreach($sales as $index => $row)
                          <option value="{{$row->id}}">{{$row->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
            </div>
          </div>
        </div>

        <div class="col-8">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
                      <div class="col-md-6">
                        <select class="form-control js-select2 select-transaksi" name="type_transaction">
                          <option value="">Jenis transaksi</option>
                          @foreach($type_transaction as $row)
                            <option value="{{$row}}">{{$row}}</option>
                          @endforeach
                        </select>
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
                        <select class="form-control js-select2 select-brand" name="select-brand" id="select-brand">
                          <option>Pilih Brand</option>
                          @foreach($brand as $row)
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

      <hr />
        <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">Add Product</h3>
            
          </div>
          <div class="block-content">
            <table class="table table-bordered" id="dynamicTable">  
              <tr>
                  <th>Name</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Action</th>
              </tr>
              <tr>  
                  <td><input type="text" name="addmore[0][name]" placeholder="Enter your Name" class="form-control" /></td>  
                  <td><input type="text" name="addmore[0][qty]" placeholder="Enter your Qty" class="form-control" /></td>  
                  <td><input type="text" name="addmore[0][price]" placeholder="Enter your Price" class="form-control" /></td>  
                  <td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  
              </tr>  
            </table> 
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
    var i = 0;
       
    $("#add").click(function(){
        ++i;
        $("#dynamicTable").append('<tr><td><input type="text" name="addmore['+i+'][name]" placeholder="Enter your Name" class="form-control" /></td><td><input type="text" name="addmore['+i+'][qty]" placeholder="Enter your Qty" class="form-control" /></td><td><input type="text" name="addmore['+i+'][price]" placeholder="Enter your Price" class="form-control" /></td><td><button type="button" class="btn btn-danger remove-tr">Remove</button></td></tr>');
    });
   
    $(document).on('click', '.remove-tr', function(){  
         $(this).parents('tr').remove();
    }); 
  })
</script>
@endpush