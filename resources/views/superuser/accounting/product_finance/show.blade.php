@extends('superuser.app')

@section('content')

@if($product->count() <= 0)
<div class="block">  
  <div class="block-header block-header-default">
    <h2 class="block-title">#Mitra : {{ $mitra->name ?? '' }}</h2>
    <input type="hidden" id="mitra_id" name="mitra_id" value="{{ $mitra->id }}"> 
  </div>
  <div class="block-content">
    <div class="alert alert-warning alert-dismissible fade show">
        <strong>Warning!</strong> Tidak ada data Product, Silahkan input dahulu!
    </div>

    <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
    <label style="padding: 15px 25px;" for="tab1">Export / Import</label>

    <section id="content1">
          <div class="row">
            <div class="col-md-6">
              <span class="font-size-h5">Import</span>
              <p>
                Impor data Anda dengan template yang disediakan di bawah ini.<br>
                <span class="text-danger"><b>Jangan</b></span> menhapus / mengubah header (baris pertama).<br>
                Hanya mengisi kolom yang tersedia saja, kolom tambahan tidak akan diproses.
              </p>
              @if(isset($import_custom_message))
              <div class="mb-15">
                <b>Note :</b> <br>
                {!! $import_custom_message !!}
              </div>
              @endif
              <a href="{{route('superuser.accounting.product_finance.import_template')}}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-download mr-5"></i> Template
                </button>
              </a>
              <hr>
              <form action="{{ route('superuser.accounting.product_finance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="import_file" name="import_file" data-toggle="custom-file-input" required>
                  <label class="custom-file-label" for="import_file">Choose file</label>
                </div>
                
                <button type="submit" class="btn mt-10 w-100 btn-alt-primary">Import</button>
              </form>
            </div>
            <div class="col-md-6">
              <span class="font-size-h5">Export</span>
              <p>Ekspor data ini ke format seperti excel</p>
              <a href="{{ $export_url ?? '' }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-file-excel-o mr-5"></i> Export
                </button>
              </a>
            </div>
          </div>
    </section>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.accounting.product_finance.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>
@else
<div class="block">  
  <div class="block-header block-header-default">
    <h2 class="block-title">#Mitra : {{ $mitra->name ?? '' }}</h2>
    <input type="hidden" id="mitra_id" name="mitra_id" value="{{ $mitra->id }}"> 
  </div>
  <div class="block-content">      
      <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
      <label style="padding: 15px 25px;" for="tab1">DATA</label>

      <input style="display: none;" id="tab2" type="radio" name="tabs">
      <label style="padding: 15px 25px;" for="tab2">Export / Import</label>

      <section id="content1">
        <form>
        <div class="row">
          <div class="col-lg-3 pt-2">
            <h5>#Search Product Finance</h5>
          </div>
          <div class="col-lg-3">
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-right">Product</label>
              <div class="col-md-9">
                <select class="js-select2 form-control" name="product" id="product">
                  <option value="">Select Product</option>
                  @foreach($product as $row)
                  <option value="{{ $row->id }}">{{ $row->code_product }} - {{ $row->name_product }}</option>
                  @endforeach
                </select>
              </div>
            </div>   
          </div>
          <div class="col-lg-2">
            <div class="form-group row">
              <div class="col-md-2">
                <div class="input-group mb-3">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <hr>

        <div class="row mb-30">
          <div class="col-8">
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Kode produk</label>
              <div class="col-8">
                <input class="form-control" type="text" value="{{ $table_data->code_product ?? '' }}" id="example-text-input" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Nama produk</label>
              <div class="col-8">
                <input class="form-control" type="text" value="{{ $table_data->name_product ?? '' }}" id="example-text-input" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Mitra</label>
              <div class="col-8">
                <input class="form-control" type="text" value="{{ $table_data->mitra->name  ?? ''}}" id="example-text-input" readonly>
              </div>
            </div>
          </div>

          <div class="col-4">
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Beli Satuan(USD)</label>
              <div class="col-6">
                <input class="form-control" type="text" value="{{ $table_data->buying_price_usd_unit ?? '' }}" id="example-text-input" readonly> 
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Beli Drum(USD)</label>
              <div class="col-6">
                <input class="form-control" type="text" value="{{ $table_data->buying_price_usd_drum ?? '' }}" id="example-text-input" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Jual Satuan(USD)</label>
              <div class="col-6">
                <input class="form-control" type="text" value="{{ $table_data->selling_price_usd_unit ?? '' }}" id="example-text-input" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Jual Drum(USD)</label>
              <div class="col-6">
                <input class="form-control" type="text" value="{{ $table_data->selling_price_usd_drum ?? '' }}" id="example-text-input" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Status</label>
              <div class="col-6">
                <input class="form-control" type="text" value="{{ $table_data->status() ?? '' }}" id="example-text-input" readonly>
              </div>
            </div>
          </div>
          <div class="row pt-30 mb-15">
              <div class="col-md-6">
                  <a href="{{ route('superuser.accounting.product_finance.index') }}">
                  <button type="button" class="btn bg-gd-cherry border-0 text-white">
                      <i class="fa fa-arrow-left mr-10"></i> Back
                  </button>
                  </a>
              </div>
              
              <div class="col-md-6 text-right">
                  <a href="javascript:void(0)" type="button" class="btn bg-gd-leaf border-0 text-black openModal" data-id="{{ base64_encode($table_data->id) }}" title="Update price"><i class="fa fa-pencil ml-10"></i> Edit Price</a> 
                  <a href="javascript:void(0)" type="button" class="btn bg-gd-sea border-0 text-black openModalHistory" data-id="{{ base64_encode($table_data->id) }}" title="History Price"><i class="fa fa-clock-o ml-10"></i> History Price</a> 
              </div>
          </div>
        </div>
      </form>
      </section>
    
      <section id="content2">
          <div class="row">
            <div class="col-md-6">
              <span class="font-size-h5">Import</span>
              <p>
                Impor data Anda dengan template yang disediakan di bawah ini.<br>
                <span class="text-danger"><b>Jangan</b></span> menhapus / mengubah header (baris pertama).<br>
                Hanya mengisi kolom yang tersedia saja, kolom tambahan tidak akan diproses.
              </p>
              @if(isset($import_custom_message))
              <div class="mb-15">
                <b>Note :</b> <br>
                {!! $import_custom_message !!}
              </div>
              @endif
              <a href="{{route('superuser.accounting.product_finance.import_template')}}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-download mr-5"></i> Template
                </button>
              </a>
              <hr>
              <form action="{{ route('superuser.accounting.product_finance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="import_file" name="import_file" data-toggle="custom-file-input" required>
                  <label class="custom-file-label" for="import_file">Choose file</label>
                </div>
                
                <button type="submit" class="btn mt-10 w-100 btn-alt-primary">Import</button>
              </form>
            </div>
            <div class="col-md-6">
              <span class="font-size-h5">Export</span>
              <p>Ekspor data ini ke format seperti excel</p>
              <a href="{{ $export_url ?? '' }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-file-excel-o mr-5"></i> Export
                </button>
              </a>
            </div>
          </div>
          <div class="row pt-30 mb-15">
              <div class="col-md-6">
                  <a href="{{ route('superuser.accounting.product_finance.index') }}">
                  <button type="button" class="btn bg-gd-cherry border-0 text-white">
                      <i class="fa fa-arrow-left mr-10"></i> Back
                  </button>
                  </a>
              </div>
          </div>
      </section>
  </div>
</div>
@endif



<!-- Modal Update Price -->
<div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="display:none;">
        <strong>Success!</strong> Update price!.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Price</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="myForm" method="POST" role="form" enctype="multipart/form-data" novalidate>
                  @csrf
                    <div class="mb-3">
                        <label>Harga Beli USD (DRUM)<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="buying_price_usd_drum">
                    </div>
                    <div class="mb-3">
                        <label>Harga Jual USD (DRUM)<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="selling_price_usd_drum">
                    </div>
                    <div class="mb-3">
                        <label>Harga Beli USD (SATUAN)<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="buying_price_usd_unit">
                    </div>
                    <div class="mb-3">
                        <label>Harga Jual USD (SATUAN)<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="selling_price_usd_unit">
                    </div>
                    <input type="hidden" id="productPackID" />
                    <button type="submit" class="btn btn-info">Save</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal History Price -->
<div class="modal fade bd-example-modal-lg" id="appointmentModalHistory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">History Price - {{ $table_data->name_product ?? '' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="overflow-x:auto;">
              <table id="datatables" class="table table-striped ">
                  <thead>
                    <tr>
                      <th scope="col">Date</th>
                      <th scope="col">Harga beli satuan(USD)</th>
                      <th scope="col">Harga beli drum(USD)</th>
                      <th scope="col">Harga jual satuan(USD)</th>
                      <th scope="col">Harga jual drum(USD)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(empty($table_data->log_price))
                      Tidak ada data!
                    @else
                      @foreach($table_data->log_price as $row => $value)
                          <tr>
                            <td>{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                            <td>{{ $value->buying_price_usd_unit }}</td>
                            <td>{{ $value->buying_price_usd_drum }}</td>
                            <td>{{ $value->selling_price_usd_unit }}</td>
                            <td>{{ $value->selling_price_usd_drum }}</td>
                          </tr>
                        @endforeach
                    @endif
                  </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2();

    $('#datatables').DataTable( {
        paging : false,
        info  : false,
        searching : false,
        order: [
          [1, 'desc']
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 30, 100, -1],
          [10, 30, 100, 'All']
        ],
    });

    $(document).on('click', '.openModal', function () {
      var id = $(this).data('id');
      $('#productPackID').val(id);
      $('#appointmentModal').modal('show');
      // alert(id);
    })

    $(document).on('click', '.openModalHistory', function () {
      var id = $(this).data('id');
      $('#productPackID').val(id);
      $('#appointmentModalHistory').modal('show');
      // alert(id);
    })

    $('#myForm').on('submit', function (e) {
      e.preventDefault(); // prevent the form submit
      var id = $('#productPackID').val();
      var url = "{{ route('superuser.accounting.product_finance.update_cost', ":id") }}";
      url = url.replace(':id', id);
      var AlertMsg = $('div[role="alert"]');

      // alert(id);
    

      var formData = new FormData(this); 
      // build the ajax call
      $.ajax({
          url: url,
          type: 'POST',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            $(AlertMsg).show();
            setTimeout(function () {
                    $('#myModal').modal({ show: true });
                    setTimeout(function () {
                        window.location.reload(1);
                    }, 800);
            }, 800);
          }
      });
    });

    $('a[href^="#"]').on('click', function(event) {
      var target = $( $(this).attr('href') );
      target.fadeToggle(100);
    });

    

  })
</script>
@endpush