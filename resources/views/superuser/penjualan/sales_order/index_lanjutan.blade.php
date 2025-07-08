@extends('superuser.app')

@section('content')

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<h4 style="font-weight: bold;">#SALES ORDER LANJUTAN</h4>
@role('Developer', 'superuser')
  <a class="btn btn-primary" href="{{ route('superuser.penjualan.sales_order.updateBrandName') }}" role="button">Update</a>
@endrole
<br>
<br>
<main style="background:#fff">
  
  <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
  <label style="padding: 15px 25px;" for="tab1">SO {{ $step_txt }}</label>
    
  <input style="display: none;" id="tab2" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab2">Packing Order</label>
    
  <input style="display: none;" id="tab3" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab3">SO Progress</label>

  @if($superuser->division == "Management" OR $superuser->division == "Admin" OR $superuser->division == "Developer")
  <input style="display: none;" id="tab4" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab4">DO CANCEL</label>
  @endif  

    
  <!-- Sales Order Lanjutan -->
  <section id="content1">
    <div class="row mb-30">
            <div class="row">
              <div class="col-6">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Status</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="status_so" id="status_so">
                      <option value="">Pilih Status</option>
                      <option value="LANJUTAN">LANJUTAN</option>
                      <option value="TUTUP">TUTUP</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="form-group row">
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                          <button type="button" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50"><i class="fa fa-search ml-10"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
      <div class="col-12">
        <table class="table table-hover" id="so_lanjutan">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Nota</th>
              <th>Brand</th>
              <th>Customer</th>
              <th>Created By</th>
              <th>Type</th>
              <th>Created At</th>
              <th>Action</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      
    </div>
  </section>
    
  <!-- Packing Order -->
  <section id="content2">
    <div class="alert alert-warning" role="alert" align="left">
      Revisi hanya transaksi <strong>Tempo</strong>

      <a href="{{ route('superuser.penjualan.packing_order.update_header_do') }}" class="btn btn-sm btn-circle btn-alt-success" title="Update Header DO">
          <i class="fa fa-sync"></i>
      </a>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="packing_order">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Customer</th>
              <th>Tanggal Buat</th>
              <th>Refrensi SO</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($packing_order as $index => $row)
              @if($row->status == 2)
                <tr>
                  @if($row->so->payment_status == 1 OR $row->type_transaction == "TEMPO" OR $row->type_transaction == "COD" OR $row->type_transaction == "MARKETPLACE")
                    <td>{{ $index+1 }}</td>
                    <td>{{$row->code ?? '-'}}</td>
                    <td>{{ $row->member->name }} {{$row->member->text_kota}}</td>
                    <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                    <td>{{$row->so->code}} / {{$row->so->type_transaction}}</td>
                    <td>
                        <!-- <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span> -->
                      @if($row->status == 2)
                        <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span>
                      @endif
                      @if($row->status == 3)
                        <span class="badge badge-success"><b>Success</b></span>
                      @endif
                    </td>
                    <td>
                      @if($row->status == 2)
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.ready', $row->id) }}')" class="btn btn-success btn-sm btn-flat" data-id="{{$row->id}}"><i class="fa fa-send"></i> Naik Ke DO</a>
                        <a href="{{route('superuser.penjualan.delivery_order.print_manifest', $row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank">
                          <i class="fas fa-clipboard-list"></i> Print Manifest
                        </a>
                      @if($row->type_transaction == 'TEMPO' OR $row->type_transaction == "COD" OR $row->type_transaction == "MARKETPLACE")
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.revisi', $row->id) }}')" class="btn btn-danger btn-sm btn-flat" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                      @endif
                      @role('Developer')
                        @if($row->type_transaction == 'CASH')
                          <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.revisi', $row->id) }}')" class="btn btn-dark btn-sm btn-flat" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                        @endif
                      @endrole
                    @endif
                      </td>
                  @endif
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
      
    </div>
  </section>
    
  <section id="content3">
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="sales_order_progress">
          <thead>
            <tr>
              <th>#</th>
              <th>Refrensi SO</th>
              <th>DO Code</th>
              <th>Customer</th>
              <th>Tanggal Buat</th>
              <th>Transaction Type</th>
              <th>Status</th>
              <!-- <th>Action</th> -->
            </tr>
          </thead>
          <tbody>
          @foreach($so_progress as $index => $row)
            @if(isset($row->so) && $row->so->type_so == 'nonppn')
                  <tr>
                      <td>{{ $index+1 }}</td>
                      <td>{{$row->so->so_code}}</td>
                      <td>{{$row->do_code}}</td>
                      <td>{{$row->member->name}} {{$row->member->text_kota}}</td>
                      <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                      <td>{{$row->type_transaction}}</td>
                      <td>
                        @if($row->status == 2)
                          <span class="badge badge-pill badge-info"><b>Submit DO</bb></span>
                        @endif
                        @if($row->status == 3)
                          <span class="badge badge-pill badge-primary"><b>Dikemas</b></span>
                        @endif
                        @if($row->status == 4)
                          <span class="badge badge-pill badge-primary"><b>Cetak SJ / DO</b></span>
                        @endif
                        @if($row->status == 5)
                          <span class="badge badge-pill badge-warning"><b>Dikirim</b></span>
                        @endif
                        @if($row->status == 6)
                          <span class="badge badge-pill badge-success"><b>Terkirim</b></span>
                        @endif
                        @if($row->status == 7)
                          <span class="badge badge-pill badge-danger"><b>Revisi</b></span>
                        @endif
                      </td>
                  </tr>
                @endif
              @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>  

    <section id="content4">
      <div class="row mb-30">
        <div class="col-12">
        <table class="table table-hover" id="do_cancel">
    <thead>
        <tr>
            <th>#</th>
            <th>DO Code</th>
            <th>Refrensi SO</th>
            <th>Customer</th>
            <th>Tanggal Buat</th>
            <th>Transaction Type</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($packing_order as $index => $row)
            @if(in_array($row->status, [5, 6, 7]))
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->do_code }}</td>
                    <td>{{ $row->so->code ?? '-' }}</td>
                    <td>{{ $row->member->name ?? '-' }} {{ $row->member->text_kota }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y h:i:s') }}</td>
                    <td>{{ $row->so->type_transaction ?? '-' }}</td>
                    <td>
                        @if((in_array($row->status, [5, 6]) || ($superuser->division == "Management" && $superuser->division == "Developer")))
                            <a href="javascript:void(0)" type="button" class="btn btn-danger opneModalDoCancel" data-id="{{ $row->id }}">Cancel DO</a>
                        @endif
                        @if($row->status == 7)
                            <a href="#" class="btn btn-info btn-sm btn-flat btn-frmdoedit" data-id="{{ $row->id }}"><i class="fa fa-edit"></i> Form Revisi</a>
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

        </div>
      </div>
    </section>
</main>

<form method="post" action="{{route('superuser.penjualan.delivery_order.do_edit')}}" id="frmDoEdit">
    @csrf
    <input type="hidden" name="id">
</form>

<div class="modal fade" id="modalDoCancel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div role="alert" class="alert" id="alert-message" style="display: none;"></div>
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">verify auth token</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="myFormDoCancel" method="POST" role="form" enctype="multipart/form-data" novalidate>
          @csrf
          <div class="mb-3">
            <label>TOKEN :</label>
            <input type="password" class="form-control" name="secreatCode" id="secreatCode">
          </div>
          <input type="hidden" id="doID" />
          <button type="button" class="btn btn-info" id="submitDoCancel">Auth</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- view so -->
<div class="modal fade bd-example-modal-xl" id="modalViewSo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">#View SO <span id="so_code_display"></span></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="modal-data">
              <!-- Invoice and Customer Details -->
              <div class="row">
                  <div class="col">
                      <div class="block">
                          <div class="block-header block-header-default">
                              <h3 class="block-title">#Detail Nota</h3>
                          </div>
                          <div class="block-content">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="invoice_date">Tanggal Nota</label>
                                      <input type="text" id="invoice_date" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="invoice_code">Code</label>
                                      <input type="text" id="invoice_code" class="form-control" readonly>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="sales_senior_id">Sales Senior</label>
                                      <input type="text" id="sales_senior_id" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="sales_id">Sales</label>
                                      <input type="text" id="sales_id" class="form-control" readonly>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-4">
                                      <label for="type_transaction">Type Transaksi</label>
                                      <input type="text" id="type_transaction" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-8" id="note-container">
                                      <label for="note">Note</label>
                                      <textarea class="form-control" style="height:auto; min-height:50px; overflow:hidden;" id="note" rows="1" readonly></textarea>
                                  </div>
                                  <div class="form-group col-md-6" id="catatan-container">
                                      <label for="catatan">Catatan</label>
                                      <textarea class="form-control" style="height:auto; min-height:50px; overflow:hidden;" id="catatan" rows="1" readonly></textarea>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col">
                      <div class="block">
                          <div class="block-header block-header-default">
                              <h3 class="block-title">#Customer</h3>
                          </div>
                          <div class="block-content">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="customer_name">Customer</label>
                                      <input type="text" id="customer_name" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="customer_address">Alamat Kirim</label>
                                      <textarea class="form-control" id="customer_address" rows="1" readonly></textarea>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="customer_city">Kota</label>
                                      <input type="text" id="customer_city" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="customer_area">Provinsi</label>
                                      <input type="text" id="customer_area" class="form-control" readonly>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Product Details Table -->
              <div class="row">
                  <div class="col">
                      <table class="table">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Kode</th>
                                  <th>Product</th>
                                  <th>Kemasan</th>
                                  <th>Qty</th>
                                  <th>Harga</th>
                                  <th>Free</th>
                              </tr>
                          </thead>
                          <tbody id="product-details">
                              <!-- Product details will be injected here -->
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
          let datatableUrl = '{{ route('superuser.penjualan.sales_order.json_lanjutan') }}';
          let firstDatatableUrl = datatableUrl + '?status_so=all';

            $('.js-select2').select2();

            var datatable = $('#so_lanjutan').DataTable({
              language: {
                processing: "<span class='fa-stack fa-lg'>\n\
                                        <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                                </span>",
              },
              processing: true,
              serverSide: false,
              searching: true,
              paging: true,
              info: false,
              ajax: {
                "url": datatableUrl,
                "dataType": "json",
                "type": "GET",
                "data":{ _token: "{{csrf_token()}}"}
              },
              columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'so_code', name: 'penjualan_so.so_code'},
                {data: 'code', name: 'penjualan_so.code'},
                {data: 'nota_brand', name: 'penjualan_so.brand_name'},
                {data: 'customer'},
                {data: 'so_created_by'},
                {data: 'so_transaction', name: 'penjualan_so.type_transaction'},
                {
                    data: 'so_created_at',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }
                },
                {data: 'action'},
              ],
              order: [
                [2, 'asc'],
              ],
              pageLength: 10,
              lengthMenu: [
                [10, 30, 100, -1],
                [10, 30, 100, 'All']
              ], 
            });

            $('#btn-filter').on('click', function(e) {
                e.preventDefault();
                var status = $('#status_so').val();
                let newDatatableUrl = datatableUrl + '?status_so=' + status;
                datatable.ajax.url(newDatatableUrl).load();
            })

            $('#so_lanjutan tbody').on('click', 'button.btn-view', function() {
              var data = datatable.row($(this).parents('tr')).data();
              var id = $(this).data('id'); // Ensure this matches the button's data-id attribute

              var url = "{{ route('superuser.penjualan.sales_order.data_so', ':id') }}";
              url = url.replace(':id', id);

              $.ajax({
                  url: url,
                  type: 'GET',
                  success: function(response) {
                      $('#invoice_date').val(response.created_at);
                      $('#so_code').val(response.so_code);
                      $('#invoice_code').val(response.so_code);
                      $('#so_code_display').text(response.so_code);
                      // $('#sales_senior_id').val(response.sales_senior_id);
                      
                      // $('#sales_id').val(response.sales_id);
                      $('#type_transaction').val(response.type_transaction);
                      
                      if (response.status == 2 || response.status == 4) {
                          $('#note-container').show();
                          $('#note').val(response.note);
                      } else {
                          $('#note-container').hide();
                      }

                      if (response.status == 5) {
                          $('#catatan-container').show();
                          $('#catatan').val(response.catatan);
                      } else {
                          $('#catatan-container').hide();
                      }

                      $('#customer_name').val(response.customer_name);
                      $('#customer_address').val(response.customer_address);
                      $('#customer_city').val(response.customer_kota);
                      $('#customer_area').val(response.customer_provinsi);

                      // Populate product details table
                      var productDetails = '';
                      response.products.forEach(function(product, index) {
                          productDetails += '<tr>';
                          productDetails += '<td>' + (index + 1) + '</td>';
                          productDetails += '<td>' + product.code + '</td>';
                          productDetails += '<td>' + product.name + '</td>';
                          productDetails += '<td>' + product.kemasan + '</td>';
                          productDetails += '<td>' + product.qty + '</td>';
                          productDetails += '<td>' + product.price + '</td>';
                          productDetails += '<td>' + (product.free ? 'Yes' : 'No') + '</td>';
                          productDetails += '</tr>';
                      });
                      $('#product-details').html(productDetails);

                      $('#userModal').css("display", "block"); // Show the modal
                  },
                  error: function(xhr, status, error) {
                      console.error('Error:', error);
                      alert('An error occurred while fetching the data.');
                  }
              });
            });

            $('#packing_order').DataTable( {
              paging:   true,
                orderin: true,
                info:     false,
                searching : true,
                order: [
                  [1, 'asc'],
                ],
                pageLength: 10,
                lengthMenu: [
                  [10, 30, 100, -1],
                  [10, 30, 100, 'All']
                ], 
            });

            $('#sales_order_progress').DataTable( {
              paging:   true,
                orderin: true,
                info:     false,
                searching : true,
                order: [
                  [2, 'asc'],
                ],
                pageLength: 10,
                lengthMenu: [
                  [10, 30, 100, -1],
                  [10, 30, 100, 'All']
                ], 
            });
            
            $('#do_cancel').DataTable( {
              paging:   true,
                orderin: true,
                info:     false,
                searching : true,
                order: [
                  [2, 'asc'],
                ],
                pageLength: 10,
                lengthMenu: [
                  [10, 30, 100, -1],
                  [10, 30, 100, 'All']
                ], 
            });

            $(document).on('click','.btn-delete',function(){
                if(confirm("Apakah anda yakin ingin menghapus SO ini ? ")){
                let id = $(this).data('id');
                $('#frmDestroyItem').find('input[name="id"]').val(id);
                $('#frmDestroyItem').submit();
                }
            });

            $(document).on('click','.btn-kembali-ke-awal',function(){
                if(confirm("Apakah anda yakin ingin mengembalikan sales order ini?")){
                let id = $(this).data('id');
                $('#frmKembali').find('input[name="id"]').val(id);
                $('#frmKembali').submit();
                }
            });

            $(document).on('click','.btn-frmedit',function(){
                if(confirm("Apakah anda yakin melakukan Edit?")){
                let id = $(this).data('id');
                $('#frmRevisi').find('input[name="id"]').val(id);
                $('#frmRevisi').submit();
                }
            });

            $(document).on('click','.btn_cancel',function(){
                if(confirm("Apakah anda yakin melakukan Cancel DO?")){
                let id = $(this).data('id');
                $('#frmCancel').find('input[name="id"]').val(id);
                $('#frmCancel').submit();
                }
            });

            $(document).on('click','.btn-frmdoedit',function(){
                if(confirm("Apakah anda yakin melakukan Edit DO?")){
                let id = $(this).data('id');
                $('#frmDoEdit').find('input[name="id"]').val(id);
                $('#frmDoEdit').submit();
                }
            });

            $(document).on('click', '.opneModalDoCancel', function () {
                var id = $(this).data('id');
                $('#doID').val(id);
                $('#modalDoCancel').modal('show');
            });

            $('#submitDoCancel').on('click', function (e) {
              e.preventDefault();
              var code = $('#secreatCode').val();
              var do_id = $('#doID').val();

              $.ajax({
                  type: 'POST',
                  url: "{{ route('superuser.penjualan.delivery_order.cancel_proses') }}",
                  data: {
                      "_token": "{{ csrf_token() }}",
                      "pass": code,
                      "id": do_id
                  },
                  success: function (response) {
                      $('#alert-message') // Assuming this is the alert message div
                          .removeClass('alert-danger')
                          .addClass('alert-success')
                          .text(response.message)
                          .show();

                      setTimeout(function () {
                          $('#modalDoCancel').modal('hide');
                          setTimeout(function () {
                              window.location.reload();
                          }, 800);
                      }, 800);
                  },
                  error: function (xhr, status, error) {
                      $('#alert-message') // Assuming this is the alert message div
                          .removeClass('alert-success')
                          .addClass('alert-danger')
                          .text(xhr.responseJSON.message || 'Token tidak sah!')
                          .show();
                  }
              });
            });
        })
    </script>
@endpush