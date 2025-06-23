@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.gudang.mutasi_out.index') }}">Mutation Display</a>
    <span class="breadcrumb-item active">Create</span>
  </nav>
  <div id="alert-block"></div>

  <form class="ajax" data-action="{{ route('superuser.gudang.mutasi_out.store') }}" data-type="POST"
    enctype="multipart/form-data">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Create Mutation Out</h3>
      </div>
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)"
              value="" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse_from">Warehouse From <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse_from" name="warehouse_from"
              data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouse_from as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
            <small class="form-text text-muted font-italic">Select warehouse first before choose product</small>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse_to">Warehouse To <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse_to" name="warehouse_to"
              data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouse_to as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="{{ route('superuser.gudang.mutasi_out.index') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-6 text-right">
            <button type="submit" class="btn bg-gd-corporate border-0 text-white">
              Submit <i class="fa fa-arrow-right ml-10"></i>
            </button>
          </div>
        </div>

      </div>
    </div>

    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Add Product</h3>
        <a href="#" class="row-add">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            <i class="fa fa-plus mr-10"></i> Row
          </button>
        </a>
      </div>
      <div class="block-content">
        <table id="datatable" class="table table-striped">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">Product</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Keterangan</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </form>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
  <script src="{{ asset('utility/superuser/js/form.js') }}"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2()

      var table = $('#datatable').DataTable({
        paging: false,
        bInfo: false,
        searching: false,
        columns: [{
            name: 'counter',
          },
          {
            name: 'sku',
            orderable: false,
          },
          {
            name: 'quantity',
            orderable: false,
            searcable: false,
          },
          {
            name: 'keterangan',
            orderable: false,
            searcable: false
          },
          {
            name: 'action',
            orderable: false,
            searcable: false,
          }
        ],
        order: [
          [1, 'desc']
        ]
      })

      var counter = 1;

      $('a.row-add').on('click', function(e) {
        e.preventDefault();

        table.row.add([
          counter,
          '<select class="js-select2 form-control js-ajax" id="sku[' + counter +
          ']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
          '<input type="number" class="form-control text-center" name="qty[]" required>',
          '<input type="text" class="form-control text-center" name="description[]">',
          '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
        ]).draw(false);

        initailizeSelect2();
        counter++;
      });

      function formatProduct (product) {
        if (!product.id) {      // item placeholder
            return product.text;
        }
        // Tampilkan kode – nama (kemasan) + stok
        return $(
          '<div>' +
            '<strong>' + product.code + '</strong> - ' +
            product.name + ' (' + product.pack + ')' +
            '<div class="text-muted medium">Stok: ' + product.stock + '</div>' +
          '</div>'
        );
      }

      function formatProductSelection (product) {
        if (!product.id) {
            return product.text;
        }
        return product.code + ' - ' + product.name + ' - ' + product.pack;  // lebih ringkas di input
      }

      function initailizeSelect2() {
      $(".js-ajax").select2({
        ajax: {
          url: '{{ route('superuser.gudang.mutasi_out.search_sku') }}',
          dataType: 'json',
          delay: 250,
          data: params => ({
            q: params.term,
            warehouse: $('#warehouse_from').val(),
            _token: "{{ csrf_token() }}"
          }),
          cache: true
        },
        templateResult: formatProduct,
        templateSelection: formatProductSelection,
        escapeMarkup: markup => markup      // biar HTML di template tidak di-escape
      });

      $('.js-ajax').on('select2:select', function (e) {
          $(this).closest('tr').find('input[name="qty[]"]').attr({
            "max": e.params.data.stock,
            "min": 0,
            "placeholder": e.params.data.stock
          });
        });
      }

      $('#warehouse_from').on('select2:select', function(e) {
        table.clear().draw();
      });

      $('#datatable tbody').on('click', '.row-delete', function(e) {
        e.preventDefault();
        table.row($(this).parents('tr')).remove().draw();

        if (typeof $('input[name="id[]"]').val() == 'undefined') {
          $('#submit-table').hide();
        }
      });


    });

  </script>
@endpush
