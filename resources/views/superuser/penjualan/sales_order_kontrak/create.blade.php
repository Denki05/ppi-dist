@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <span class="breadcrumb-item">Sales Kontrak</span>
  <span class="breadcrumb-item active">Create</span>
</nav>

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order_kontrak.store') }}" data-type="POST" enctype="multipart/form-data">
    @csrf
    <div class="block">
        <div class="block-header block-header-default">
        <h3 class="block-title">Create Sales Kontrak</h3>
        </div>
        <div class="block-content">
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-right" for="durasi_kontrak">Durasi <span class="text-danger">*</span></label>
                <div class="col-2">
                    <!-- <input type="number" class="form-control" id="durasi_kontrak" name="durasi_kontrak" min="1" max="12"> -->
                    <select class="form-control js-select2" name="durasi_kontrak">
                        <option value="">Pilih Bulan</option>
                        <option value="1">1 Bulan</option>
                        <option value="2">2 Bulan</option>
                        <option value="3">3 Bulan</option>
                        <option value="4">4 Bulan</option>
                        <option value="5">5 Bulan</option>
                        <option value="6">6 Bulan</option>
                        <option value="7">7 Bulan</option>
                        <option value="8">8 Bulan</option>
                        <option value="9">9 Bulan</option>
                        <option value="10">10 Bulan</option>
                        <option value="11">11 Bulan</option>
                        <option value="12">12 Bulan</option>
                    </select>
                </div>
                <div class="col">
                    <label class="col-md-3 col-form-label text-left" >Bulan</label>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-right" for="customer_other_address_id">Customer <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select class="js-select2 form-control" id="customer_other_address_id" name="customer_other_address_id" data-placeholder="Pilih Customer">
                        <option></option>
                        @foreach($customer as $key)
                        <option value="{{ $key->id }}">{{ $key->name }} {{ $key->text_kota }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-right" for="sales_senior">Sales Senior</label>
                <div class="col-md-7">
                    <select class="js-select2 form-control" id="sales_senior" name="sales_senior" data-placeholder="Pilih Sales Senior">
                        <option></option>
                        @foreach(\App\Entities\Penjualan\SalesOrderKontrak::SALES_SENIOR as $sales => $value)
                        <option value="{{ $value }}">{{ $sales }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-right" for="sales_junior">Sales</label>
                <div class="col-md-7">
                    <select class="js-select2 form-control" id="sales_junior" name="sales_junior" data-placeholder="Pilih Sales">
                        <option></option>
                        @foreach(\App\Entities\Penjualan\SalesOrderKontrak::SALES as $sales => $value)
                        <option value="{{ $value }}">{{ $sales }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-right" for="sales_junior">Brand</label>
                <div class="col-md-7">
                    <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name" data-placeholder="Brand">
                        <option></option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-right" for="sales_junior">Note</label>
                <div class="col-md-7">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">
                        <i class="fa fa-plus"></i> Note
                    </button>
                </div>
            </div>
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

    <div class="block">
        <div class="block-header">
        <h3 class="block-title">Add Product</h3>
        <!-- <a href="#" class="row-add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
            <i class="fa fa-plus mr-10"></i> Row
            </button>
        </a> -->
        </div>
        <div class="block-content">
        <table id="datatables" class="table table-striped">
            <thead>
            <tr>
                <th class="text-center">Product</th>
                <th class="text-center">Packaging</th>
                <th class="text-center">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Disc</th>
            </tr>
            </thead>
            <tbody>
                <td style="width: 40%;">
                    <!-- <select class="form-control js-select2" id="product_name" name="product_name">
                        <option>Pilih Product</option>
                        @foreach($product as $row)
                        <option value="{{ $row->id }}">{{ $row->code }} - {{ $row->name }}</option>
                        @endforeach
                    </select> -->
                    <select class="form-control js-select2 js-select2-product" id="product_name" name="product_name">
                        <option>Pilih Product</option>
                    </select>
                </td>
                <td style="width: 25%;">
                    <select class="js-select2 form-control" id="packaging_id" name="packaging_id" placeholder="Pilih Kemasan">
                        <option>Pilih Kemasan</option>
                    </select>
                </td>
                <td style="width: auto;">
                    <input type="number" name="price" id="price" class="form-control">
                </td>
                <td style="width: auto;">
                    <input type="number" name="qty" id="qty" class="form-control">
                </td>
                <td style="width: auto;">
                    <input type="number" name="disc_usd" id="disc_usd" class="form-control">
                </td>
            </tbody>
        </table>
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
            <textarea class="form-control" name="note" id="editor" rows="4" col="10"></textarea>
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

        $(".js-select2-brand").select2({
            ajax: {
                url: '{{ route('superuser.penjualan.sales_order_kontrak.get_brand') }}',
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

        $('#brand_name').on('change', function(){
          let brand_name = $('#brand_name').val();

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.penjualan.sales_order_kontrak.get_product')}}',
            data : {brand_name:brand_name},
            cache : true,

            success: function(msg){
              $('#product_name').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })

        $('#product_name').on('change', function(){
          let product_id = $('#product_name').val();

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.penjualan.sales_order_kontrak.get_packaging')}}',
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
    })
</script>
@endpush