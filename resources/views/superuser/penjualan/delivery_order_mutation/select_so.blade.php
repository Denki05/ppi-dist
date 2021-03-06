@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item">Delivery Order Mutation</span>
  <span class="breadcrumb-item active">Select SO</span>
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
<div class="block">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-lg-3">
        DOM CODE
      </div>
      <div class="col-lg-9">
        : {{$detail_po->code}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3">
        Original Warehouse
      </div>
      <div class="col-lg-9">
        : {{$detail_po->origin_warehouse->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3">
        Destination Warehouse
      </div>
      <div class="col-lg-9">
        : {{$detail_po->destination_warehouse->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3">
        Address
      </div>
      <div class="col-lg-9">
        : {{$detail_po->address}}
      </div>
    </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <form id="frmInsert" autocomplete="off">
    @csrf
    <input type="hidden" name="do_mutation_id" value="{{$detail_po->id}}">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table striped table-bordered">
            <thead>
              <th><input type="checkbox" width="50" height="50" class="select-all"></th>
              <th>SO Number</th>
              <th>Product</th>
              <th>Packaging</th>
              <th>SO Qty</th>
              <th>DO Qty</th>
              <th>REJ Qty</th>
              <th>Price</th>
              <th>Total</th>
              <th>Note</th>
            </thead>
            <tbody>
              @if(count($result) <= 0)
                <tr>
                  <td colspan="10" align="center">Data tidak ditemukan</td>
                </tr>
              @endif
              @foreach($result as $index => $row)
              <input type="hidden" name="repeater[{{$index}}][product_id]" value="{{$row->product_id}}">
              <input type="hidden" name="repeater[{{$index}}][so_qty]" value="{{$row->qty}}">
              <input type="hidden" name="repeater[{{$index}}][so_item_id]" value="{{$row->id}}">
              <tr class="index{{$index}}" data-index="{{$index}}">
                <td>
                  <input type="checkbox" class="select_per_item" width="50" height="50" name="repeater[{{$index}}][checkbox]" data-index="{{$index}}">
                </td>
                <td>{{$row->so->code ?? ''}}</td>
                <td>{{$row->product->name ?? ''}}</td>
                <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                <td>{{$row->qty}}</td>
                
                <td>
                  <input type="text" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" step="any">
                </td>
                <td>
                  <input type="text" name="repeater[{{$index}}][rej_qty]" class="form-control" step="any">
                </td>
                <td>
                  <input type="text" name="repeater[{{$index}}][price]" class="form-control" readonly value="{{$row->product->selling_price ?? 0}}">
                </td>
                <td>
                  <input type="text" name="repeater[{{$index}}][total]" class="form-control" readonly>
                </td>
                <td>
                  <input type="text" name="repeater[{{$index}}][note]" class="form-control">
                </td>
              </tr>
                
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-9">
        
      </div>
      <div class="col-lg-3 float-right">
        Total<input type="text" name="total" class="form-control" readonly>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.delivery_order_mutation.edit',$detail_po->id)}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="submit" class="btn btn-primary btn-md" disabled><i class="fa fa-Insert Into Packing"></i> Insert Into DO</button>
      </div>
    </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
    let global_total = 0 ;

    $(function(){

      $('button[type="submit"]').attr('disabled',false);

      $(document).on('click','.select-all',function(){
        if($(this).is(':checked')){
          isSelected();
        }
        else{
          removeSelected();
        }
        total();
      })

      $(document).on('click','.select_per_item',function(){
        total();
      })

      $(document).on('keyup','.count',function(){
        let index = $(this).attr('data-index');
        count_per_item(index);
        total();
      })

      $(document).on('submit','#frmInsert',function(e){
        e.preventDefault();
        if(confirm("Yakin ?")){
          length = $('#frmInsert tbody').find('input:checkbox:checked').length;
          if(length == 0){
            alert("No Item SO Checked")  
          }
          else{
            let _form = $('#frmInsert');
            $.ajax({
              url : '{{route('superuser.penjualan.delivery_order_mutation.store_so')}}',
              method : "POST",
              data : getFormData(_form),
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
                      let token = getToken('{{route('superuser.penjualan.delivery_order_mutation.edit',$detail_po->id)}}');
                      location.href =  '{{route('superuser.penjualan.delivery_order_mutation.edit',$detail_po->id)}}' + '?token=' + token;
                  })
                  
                }
              },
              error : function(){
                  alert("Cek Koneksi Internet");
              },
              complete : function(){
                $('button[type="submit"]').html('<i class="fa fa-save"> Insert Into DO</i>');
              }
            })
          }
          
        }
      })
    })

    function isSelected(){
      $('tbody').find('input[type="checkbox"]').attr('checked','checked');
      $('tbody').find('input[type="checkbox"]').prop('checked', true);
    }
    function removeSelected(){
      $('tbody').find('input[type="checkbox"]').removeAttr('checked');
     $('tbody').find('input[type="checkbox"]').prop('checked', false);
    }
    
    function count_per_item(indx){
      let index = indx;
      let price = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][price]"]').val()); 
      let do_qty = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][do_qty]"]').val()); 
   
      let sub_total  = parseFloat(do_qty * price);

      if(isNaN(sub_total)){
        sub_total = 0;
      }

  
      $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val(sub_total);
      
      total();
    }
    function total(){
      let total = 0;
      $('tbody tr').each(function(index,e){
        let checkbox = $('tr.index'+index+'').find('input[name="repeater['+index+'][checkbox]"]').is(":checked");
        if(checkbox == true){
          let sub_total = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val());
          if(isNaN(sub_total)){
            sub_total = 0;
          }
          total += sub_total;
        }
      }) 

      $('input[name="total"]').val(total);
    }

  </script>
@endpush
