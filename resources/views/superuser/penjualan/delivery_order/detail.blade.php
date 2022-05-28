@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Detail Delivery Order</span>
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
      <div class="col-12">
        <div class="row mb-30">
          <div class="col-lg-6">
            <span class="badge badge-{{ $result->do_status()->class }}">{{ $result->do_status()->msg }}</span>
          </div>
          <div class="col-lg-6 text-right">
            {{date('d F Y',strtotime($result->created_at))}}
          </div>
        </div>
        <div class="row mb-30">
          <div class="col-12">
            @if(!empty($result->do_code))
            <div class="row">
              <div class="col-lg-2">
                <strong>PO Code</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->code ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>DO Code</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->do_code ?? ''}}
              </div>
            </div>
            @else
            <div class="row">
              <div class="col-lg-2">
                <strong>PO Code</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->code ?? ''}}
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-lg-2">
                <strong>Warehouse</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->warehouse->name ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Customer</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->customer->name ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Address</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->customer->address ?? ''}}
              </div>
            </div>
            @if(!empty($result->customer_other_address))
            <div class="row">
              <div class="col-lg-2">
                <strong>Contact</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->customer_other_address->label ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Delivery Address</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->customer_other_address->address ?? ''}}
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-lg-2">
                <strong>Ekspedisi</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->ekspedisi->name ?? null}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Ongkir</strong>
              </div>
              <div class="col-lg-10">
                : {{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-30">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <th>No</th>
                  <th>Code</th>
                  <th>Produt</th>
                  <th>Packaging</th>
                  <th>Qty</th>
                  <th>Total Packaging</th>
                  <th>Note</th>
                </thead>
                <tbody>
                  @if(count($result->do_detail) == 0)
                    <tr>
                      <td colspan="7">Data tidak ditemukan</td>
                    </tr>
                  @endif
                  @foreach($result->do_detail as $index => $row)
                    <tr>
                      <td>{{$index+1}}</td>
                      <td>{{$row->product->code ?? ''}}</td>
                      <td>{{$row->product->name ?? ''}}</td>
                      <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                      <td>{{$row->qty ?? ''}}</td>
                      <td>
                        @if($row->packaging == 7)
                        Free
                        @else
                        <?php
                          $total_packing = $row->qty / floatval($row->packaging_val()->scalar ?? 0);
                        ?>
                        {{$total_packing}}
                        @endif
                      </td>
                      <td>{{$row->note ?? ''}}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        @if($result->status != 1 && $result->status != 4)
        <div class="row mb-30">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control" name="status">
                <option value="">==Update status==</option>
                <option value="3">Sending</option>
                <option value="4">Sent</option>
              </select>
            </div>
          </div>
          <div class="col-lg-3">
            <button type="button" class="btn btn-primary btn-sending"><i class="fa fa-save"></i> Update Status</button>
          </div>
        </div>
        @endif

        @if($result->status == 4 && $result->image == null)
        <form id="frmUploadImage" action="{{route('superuser.penjualan.delivery_order.upload_image')}}" method="post" enctype="multipart/form-data">
        @csrf
          <input type="hidden" name="do_id" value="{{$result->id}}">
          <div class="row mb-30">
            <div class="col-lg-5">
              <div class="form-group row">
                <label class="col-md-2 col-form-label text-right" for="name">Upload Image</label>
                <div class="col-md-8">
                  <input type="file" name="image" class="form-control" accept="image/*">
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Upload</button>
            </div>
          </div>
        </form>
        @endif

        @if(!empty($result->image))
        <div class="row mb-30">
          <div class="col-lg-5">
            <a href="<?= asset($result->image) ?>" class=" mb-5" target="_blank"><img src="<?= asset($result->image) ?>" style="max-width: 300px; max-height: 300px" /><br>
          </div>
        </div>
        @endif

        <!-- @if($result->status == 4)
          <div class="row">
            <div class="col-12">
              <h5>#Cost</h5>
              <div class="table-responsive">
                <table class="table table-striped table-bordered">
                  <thead>
                    <th>Note</th>
                    <th>Cost IDR</th>
                  </thead>
                  <tbody>
                    @foreach($result->do_other_cost as $index => $row)
                      <tr>
                        <td>{{$row->note}}</td>
                        <td>{{number_format($row->cost_idr)}}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif -->

        <div class="row">
          <div class="col-12">
            <a href="{{route('superuser.penjualan.delivery_order.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<form method="post" action="{{route('superuser.penjualan.delivery_order.sending')}}" id="frmUpdateStatus">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
    <input type="hidden" name="status">
</form>
<!-- Modal -->
@include('superuser.penjualan.delivery_order.modal')
<!-- End Modal -->
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  let idx = 0;
  $(function(){
    $(document).on('click','.btn-sending',function(){
        let val = $('select[name="status"]').val();
        let do_id = "{{$result->id}}";
        if(val == 3){
          if(confirm("Apakah anda yakin ingin mengubah status orderan ini ? ")){
            $('#frmUpdateStatus').find('input[name="status"]').val(val);
            $('#frmUpdateStatus').submit();
          }
        }
        else if(val==4){
          $('#frmSent').find('input[name="do_id"]').val(do_id);
          $('#modalOtherCost').modal('show');
        }
        else{
          alert("Please select status")
        }
    })
    // $(document).on('click','.btn-delete-cost',function(){
    //   $(this).parent().parent().remove();
    //   reset_repeater();
    // })

    // $(document).on('click','.btn-add-cost',function(){
    //   template = '<tr class="repeater'+idx+'">'+
    //                 '<td>'+
    //                   '<input type="text" class="form-control note" name="repeater['+idx+'][note]">'+
    //                 '</td>'+
    //                 '<td>'+
    //                   '<input type="text" class="form-control cost_idr" name="repeater['+idx+'][cost_idr]">'+
    //                 '</td>'+
    //                 '<td>'+
    //                   '<button class="btn btn-danger btn-delete-cost" type="button"><i class="fa fa-trash"></i></button>'+
    //                 '</td>'+
    //               '</tr>';
    //   idx++;
    //   $('#modalOtherCost tbody').append(template);
    //   reset_repeater();
    // })

    $(document).on('submit','#frmSent',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah status do ini ?")){
        let _form = $('#frmSent');
        $.ajax({
          url : '{{route('superuser.penjualan.delivery_order.sent')}}',
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
                location.reload();
              })
             
            }
          },
          error : function(){
            alert('Cek Koneksi Internet');
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })
    
  })

  // function get_cost(do_id){
  //   ajaxcsrfscript();
  //   $.ajax({
  //     url : '{{route('superuser.penjualan.delivery_order.get_cost')}}',
  //     method : "POST",
  //     data : {do_id : do_id},
  //     dataType : "JSON",
  //     success : function(resp){
  //       if(resp.IsError == true){
  //         showToast('danger',resp.Message);
  //       }
  //       else{
  //         if(resp.Data.length > 0){
  //           let template = "";
  //           $.each(resp.Data,function(i,e){
  //             let button = '<button class="btn btn-danger btn-delete-cost" type="button"><i class="fa fa-trash"></i></button>';
  //             if(i == 0){
  //               button = "-";
  //             }
  //             template += '<tr class="repeater'+i+'">'+
  //                           '<td>'+
  //                             '<input type="text" class="form-control note" name="repeater['+i+'][note]" value="'+e.note+'">'+
  //                           '</td>'+
  //                           '<td>'+
  //                             '<input type="text" class="form-control cost_idr" name="repeater['+i+'][cost_idr]" value="'+e.cost_idr+'">'+
  //                           '</td>'+
  //                           '<td>'+
  //                             button
  //                           '</td>'+
  //                         '</tr>';
  //             idx += i;
  //           })

  //           $('#modalOtherCost tbody').html(template);

  //         }
  //         $('#modalOtherCost').modal('show');
  //       }
  //     },
  //     error : function(){
  //       alert('Cek Koneksi Internet');
  //     },
  //   })
  // }
  // function reset_repeater(){
  //   let sess_idx = 0
  //   $('#modalOtherCost tr').each(function(i,e){
  //     $('#modalOtherCost tbody tr').eq(i).find('.note').attr('name','repeater['+i+'][note]');
  //     $('#modalOtherCost tbody tr').eq(i).find('.cost_idr').attr('name','repeater['+i+'][cost_idr]');
  //     sess_idx += i;
  //   })
  //   idx = sess_idx;
  // }
</script>
@endpush
