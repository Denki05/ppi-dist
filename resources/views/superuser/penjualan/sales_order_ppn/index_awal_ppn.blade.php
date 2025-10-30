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

    <div class="block">
      <div class="block-content block-content-full">
        <form>
            <div class="row">
              <div class="col-lg-2 pt-2">
                <h5>#SO PPN {{ $step_txt }}</h5>
              </div>
              <div class="col-lg-3">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Customer</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="customer_other_address_id" data-placeholder="Cari Customer">
                      <option value="">All</option>
                      @foreach($other_address as $key)
                      <option value="{{ $key->id }}">{{ $key->name }} {{$key->text_kota}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>   
              </div>
              <div class="col-lg-3">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Status</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="status_so" data-placeholder="Cari Status">
                      <option value=""></option>
                      <option value="1">AWAL</option>
                      <option value="3">REVISI</option>
                      <option value="4">TUTUP</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-group row">
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                          <button type="submit" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50"><i class="fa fa-search ml-10"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </form>

        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
          <i class="fa fa-plus mr-10"></i> Add SO
        </button> -->
        @if(session('is_from_agenda', false))
          <button id="btn-back-agenda" class="btn btn-warning">
            ← Kembali ke Daftar SO
          </button>
        @endif

        <a class="btn btn-primary" href="{{ route('superuser.penjualan.sales_order_ppn.create') }}" role="button"><i class="fa fa-plus"></i></a>

        <!-- <button type="button" class="btn btn-outline-info"><i class="fa fa-print"></i> GET SO</button> -->

        <br>
        <br>
        <table class="table table-striped" id="so_awal">
          <thead>
            <th>#</th>
            <th>Code</th>
            <th>Nota</th>
            <th>Brand</th>
            <th>Customer</th>
            <th>Sales</th>
            <th>Tanggal Buat</th>
            <th>Status</th>
            <th>Action</th>
          </thead>
          <tbody>
            @foreach($table as $index => $row)
              
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{$row->so_code}}</td>
                  <td>{{ $row->code ?? '-' }}</td>
                  <td>{{ $row->brand_name ?? '-' }}</td>
                  <td>{{ $row->member->name }} {{ $row->member->text_kota }}</td>
                  <td>{{ $row->so_sales() }} | {{ $row->so_sales_senior() }}</td>
                  <td><?= date('d-m-Y',strtotime($row->created_at)); ?></td>
                  <td>{{ $row->so_status()->scalar }}</td>
                  <td>
                      @if($row->status == 1 OR $row->status == 3)
                        <a href="{{route('superuser.penjualan.sales_order_ppn.edit_ppn', ['id'=>$row->id, 'step'=>1])}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> {{ $row->status === 1 ? 'Edit' : 'Revisi' }}</a>
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.sales_order_ppn.lanjutkan', $row->id) }}')" class="btn btn-success btn-sm btn-flat btn-lanjutan" data-id="{{$row->id}}"><i class="fa fa-check"></i> Lanjutan</a>
                        <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                      @endif
                      @if($row->status == 2 OR $row->status == 4)
                        <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                      @endif
                      @if($row->status == 5)
                        <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                      @endif
                      <a href="{{route('superuser.penjualan.sales_order.print_so',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print SO</a>
                  </td>
                </tr>
            @endforeach
          </tbody>
        </table>
    </div>

    <!-- Modal view & detail -->
@foreach($table as $row)
<div class="modal fade bd-example-modal-xl" id="myModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">View SO #{{$row->code}}</h5>
            </div>
            <div class="modal-body">
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
                            <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y',strtotime($row->created_at)) }}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code">Code</label>
                            <input type="text" class="form-control" id="invoice_code" value="{{$row->so_code}} || {{$row->code}}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="invoice_date">Sales Senior</label>
                            <input type="text" name="sales_senior_id" class="form-control" value="{{ $row->so_sales_senior() }}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code">Sales</label>
                            <input type="text" class="form-control" id="sales_id"  value="{{ $row->so_sales() }}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Type Transaksi</label>
                            <input type="text" name="type_transaction" class="form-control" value="{{ $row->type_transaction }}"  readonly>
                          </div>
                          @if($row->status == 2 || $row->status == 4)
                          <div class="form-group col-md-6">
                            <label for="note">Note</label>
                            <input type="text" class="form-control" value="{{ $row->note }}"  readonly>
                          </div>
                          @endif
                          @if($row->status == 5)
                          <div class="form-group col-md-6">
                            <label for="catatan">Catatan</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="1" readonly>{{ $row->catatan }}</textarea>
                          </div>
                          @endif
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
                            <label for="type_transaction">Customer</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $row->member->name }} {{$row->member->text_kota}}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="note">Alamat Kirim</label>
                            <textarea class="form-control" rows="1" readonly>{{$row->member->address}}</textarea>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="customer_city">Kota</label>
                            <input type="text" name="customer_city" class="form-control" value="{{$row->member->text_kota}}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="customer_area">Provinsi</label>
                            <input type="text" name="customer_area" class="form-control" value="{{$row->member->text_provinsi}}"  readonly>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>

              <div class="row">
                <div class="col">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Kemasan</th>
                        <th>Qty</th>
                        <th>Harga</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($row->so_detail as $value => $key)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $key->product_pack->code ?? '-' }} - <b>{{$key->product_pack->name ?? '-'}}</b></td>
                          <td>{{ $key->product_pack->packaging->pack_name ?? '-' }}</td>
                          <td>{{ $key->qty }}</td>
                          <td>{{ $key->product_pack->price }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              @if($row->status == 5)
                <a href="" class="btn btn-success btn-sm btn-flat btn-lanjutan" data-id="{{$row->id}}"><i class="fa fa-check mr-10"></i>Lanjutan</a>
              @endif
              <button type="button" class="btn btn-danger btn-sm btn-flat" data-dismiss="modal"><i class="fa fa-close mr-10"></i>Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<form method="post" action="{{route('superuser.penjualan.sales_order_ppn.destroy_ppn')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(function(){
    $('#so_awal').DataTable( {
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

    $('.js-select2').select2();

    $(document).on('click','.btn-delete',function(){
        if(confirm("Apakah anda yakin ingin menghapus SO ini ? ")){
          let id = $(this).data('id');
          $('#frmDestroyItem').find('input[name="id"]').val(id);
          $('#frmDestroyItem').submit();
        }
    })

    @if(session('is_from_agenda', false))
          $('#btn-back-agenda').on('click', function() {
              window.location.href = "https://sys-af.lsfragrance.id/transaksi/sales_order/list";
          });

          // Cegah back browser
          window.history.replaceState(null, null, window.location.href);
          window.onpopstate = function(event) {
              alert("Gunakan tombol Back di halaman ini, tidak bisa pakai back browser.");
              history.pushState(null, null, window.location.href);
          };
        @endif
  });
</script>
@endpush