@extends('superuser.app')

@section('content')
<!-- <nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <span class="breadcrumb-item">Sales Kontrak</span>
  <span class="breadcrumb-item active">Create</span>
</nav> -->
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order_kontrak.index') }}">Sales Kontrak</a>
  <span class="breadcrumb-item active">Revisi</span>
</nav>

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order_kontrak.update_revisi', $kontrak->id) }}" data-type="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="row">
        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                    <h3 class="block-title">#Detail Sales Kontrak</h3>
                </div>
                <div class="block-content">
                    <div class="form-group row">
                        <div class="col">
                            <label class="col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $kontrak->code }}" readonly>
                        </div>
                        <div class="col">
                            <label class="col-form-label text-right" for="durasi_kontrak">Durasi <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" id="durasi_kontrak" name="durasi_kontrak" value="{{ $kontrak->contract_range }}">
                                </div>
                                <div class="col">
                                    <p class="h4">BULAN</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        
                            <div class="col">
                                <label class="col-form-label text-right" for="sales_senior">Sales  Senior <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="sales_senior" name="sales_senior" data-placeholder="Pilih Sales Senior" disabled>
                                    <option></option>
                                    @foreach(\App\Entities\Penjualan\SalesOrderKontrak::SALES_SENIOR as $sales => $value)
                                    <option value="{{ $value }}" {{ $kontrak->sales_senior == $value ? 'selected' : '' }}>{{ $sales }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="col-form-label text-right" for="sales_junior">Sales <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="sales_junior" name="sales_junior" data-placeholder="Pilih Sales" disabled>
                                    <option></option>
                                    @foreach(\App\Entities\Penjualan\SalesOrderKontrak::SALES as $sales => $value)
                                    <option value="{{ $value }}" {{ $kontrak->sales_junior == $value ? 'selected' : '' }}>{{ $sales }}</option>
                                    @endforeach
                                </select>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                    <h3 class="block-title">#Customer</h3>
                </div>
                <div class="block-content">
                    <div class="form-group row">
                        <div class="col">
                            <label class="col-form-label text-right" for="code">Customer <span class="text-danger">*</span></label>
                            <select class="js-select2 form-control" id="customer_other_address_id" name="customer_other_address_id" data-placeholder="Pilih Customer" disabled>
                                <option></option>
                                @foreach($customer as $key)
                                <option value="{{ $key->id }}" {{ $kontrak->customer_other_address_id == $key->id ? 'selected' : ''}}>{{ $key->name }} {{ $key->text_kota }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="col-form-label text-right" for="code">Address</label>
                            <input type="text" class="form-control" name="customer_address" id="customer_address" readonly value="{{ $kontrak->member->address }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">#Add Product</h3>
        </div>
        <div class="block-content">
            <table id="datatables" class="table table-striped">
                <thead>
                <tr>
                    <th class="text-center">Product</th>
                    <th class="text-center">Packaging</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty Before</th>
                    <th class="text-center">Qty Plus</th>
                </tr>
                </thead>
                <tbody>
                    <?php 
                        $item = DB::table('penjualan_so_kontrak_item')->where('so_kontrak_id', $kontrak->id)->first();
                    ?>
                    <td style="width: 40%;">
                        <select class="form-control js-select2" id="product_name" name="product_name" disabled>
                            <option>Pilih Product</option>
                            @foreach($product as $row)
                            <option value="{{ $row->id }}" {{ $item->product_packaging_id == $row->id ? 'selected' : '' }}>{{$row->code}} - {{ $row->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" class="form-control" value="{{ $item->id }}" name="kontrak_item">
                    </td>
                    <td style="width: 25%;">
                        <select class="js-select2 form-control" id="packaging_id" name="packaging_id" placeholder="Pilih Kemasan" disabled>
                            <option>Pilih Kemasan</option>
                            @foreach($packaging as $row)
                            <option value="{{ $row->id }}" {{ $item->packaging_id == $row->id ? 'selected' : '' }}>{{ $row->pack_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td style="width: auto;">
                        <input type="number" name="price" id="price" class="form-control" style="text-align: center;" value="{{ $item->price }}" readonly>
                    </td>
                    <td style="width: auto;">
                        <input type="number" name="qty" id="qty" class="form-control" style="text-align: center;" value="{{ $item->qty }}" readonly>
                    </td>
                    <td style="width: auto;">
                        <input type="number" name="qty_plus" id="qty_plus" class="form-control" style="text-align: center;">
                    </td>
                </tbody>
            </table>
            <div class="form-group row pt-30">
                <div class="col-md-6">
                    <a href="javascript:history.back()">
                        <button type="button" class="btn bg-gd-cherry border-0 text-white">
                        <i class="fa fa-arrow-left mr-10"></i> Back
                        </button>
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
                        Submit <i class="fa fa-arrow-right ml-10"></i>
                    </button>
                </div>
            </div>
        </div>
  </div>

  <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">#Add Note</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" name="note" id="editor" rows="4" col="10">{{ $kontrak->note }}</textarea>
            <br>
            <a class="btn btn-info" id="addNote" href="javascript:void(0);" title="">click</a>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
</form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.js-select2').select2();

        $('#datatables').DataTable( {
            "paging":   false,
            "ordering": true,
            "info":     false,
            "searching" : false,
            "columnDefs": [{
                "targets": 0,
                "orderable": false
            }]
        });

        $('#product_name').on('change', function(){
          let product_id = $('#product_name').val();

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.penjualan.sales_order_kontrak.get_packaging_edit')}}',
            data : {product_id:product_id},
            cache : true,

            success: function(msg){
              $('#packaging_id').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })

        $("#addNote").on("click",function(e){
            e.preventDefault();
            addListItem();
        });

        function addListItem() {
            var text = document.getElementById('editor').value;
            var listNumberRegex = /^[0-9]+(?=\.)/gm;
            var existingNums = [];
            var num;
            
            while ((num = listNumberRegex.exec(text)) !== null) {
                existingNums.push(num);
            }
            
            
            existingNums.sort();

            
            
            var addListItemNum;
            if (existingNums.length > 0) {
            
                addListItemNum = parseInt(existingNums[existingNums.length - 1], 10) + 1;
            } else {
            
                addListItemNum = 1;
            } 

            var exp = '\n' + addListItemNum + '.\xa0';
            text = text.concat(exp);
            document.getElementById('editor').value = text;
        }

        $(document).on('change','#customer_other_address_id',function(){
            let val = $(this).val();
            if(val != ""){
                customer_address(val);
            }else{
                $('$customer_address').val("");
            }
        })

        function customer_address(id){
            ajaxcsrfscript();
            $.ajax({
                url : '{{route('superuser.penjualan.sales_order_ppn.ajax_customer_detail')}}',
                method : "POST",
                data : {id:id},
                dataType : "JSON",
                success : function(resp){
                if(resp.IsError == true){
                    showToast('danger',resp.Message);
                }
                else{
                    // $('textarea[name="address"]').val(resp.Data.address);
                    $('#customer_address').val(resp.Data.address);
                    $('#customer_city').val(resp.Data.text_kota);
                    $('#customer_area').val(resp.Data.text_provinsi);
                }
                },
                error : function(){
                alert('Cek Koneksi Internet');
                },
            })
            }
    })
</script>
@endpush