@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.mutation_from_display.index') }}">Mutation From Display</a>
    <span class="breadcrumb-item active">Edit</span>
  </nav>
  <div id="alert-block"></div>

  <form class="ajax" data-action="{{ route('superuser.inventory.mutation_from_display.update', $mutation_from_display->id) }}"
    data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="ids_delete" value="">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Edit Mutation Display</h3>
      </div>
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)"
              value="{{ $mutation_from_display->code }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse_from">Warehouse From <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse_from" name="warehouse_from"
              data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouse_from as $warehouse)
                <option value="{{ $warehouse->id }}"
                  {{ $warehouse->id == $mutation_from_display->warehouse_from ? 'selected' : '' }}>{{ $warehouse->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse_to">Warehouse To <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse_to" name="warehouse_to"
              data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouse_to as $warehouse)
                <option value="{{ $warehouse->id }}"
                  {{ $warehouse->id == $mutation_from_display->warehouse_to ? 'selected' : '' }}>{{ $warehouse->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="{{ route('superuser.inventory.mutation_from_display.index') }}">
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
        <table id="datatable" class="table table-striped table-vcenter table-responsive">
          <thead>
            <tr>
              <th class="text-center">Counter</th>
              <th class="text-center">Select SKU</th>
              <th class="text-center">Product</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Keterangan</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($mutation_from_display->sales_order_attribute->sales_order_details as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <input type="hidden" class="form-control" name="edit[]" value="{{ $item->id }}">
                  <select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" name="sku[]"
                    data-placeholder="Select SKU" style="width:100%" required>
                    <option value="{{ $item->product_id }}" selected>{{ $item->product->code }}</option>
                  </select>
                </td>
                <td>
                  <span class="name">{{ $item->product->name }}</span>
                </td>
                <td>
                  <input type="number" class="form-control" name="qty[]" value="{{ $item->quantity }}" required>
                </td>
                <td>
                  <input type="text" class="form-control" name="description[]" value="{{ $item->description }}">
                </td>
                <td>
                  <a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger"
                      title="Delete"><i class="fa fa-trash"></i></button></a>
                </td>
              </tr>
            @endforeach
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

      initailizeSelect2();

      var table = $('#datatable').DataTable({
        paging: false,
        bInfo: false,
        searching: false,
        columns: [{
            name: 'counter',
            "visible": true
          },
          {
            name: 'sku',
            orderable: false,
            width: "25%"
          },
          {
            name: 'name',
            orderable: false,
            searcable: false
          },
          {
            name: 'quantity',
            orderable: false,
            searcable: false,
            width: "5%"
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
            width: "5%"
          }
        ],
        'order': [
          [0, 'desc']
        ]
      })

      var counter = 1000;

      $('a.row-add').on('click', function(e) {
        e.preventDefault();

        table.row.add([
          counter,
          '<input type="hidden" class="form-control" name="edit[]" value=""><select class="js-select2 form-control js-ajax" id="sku[' +
          counter +
          ']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
          '<span class="name"></span>',
          '<input type="number" class="form-control" name="qty[]" required>',
          '<input type="text" class="form-control" name="description[]">',
          '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
        ]).draw(false);

        initailizeSelect2();
        counter++;
      });

      function initailizeSelect2() {
        $(".js-ajax").select2({
          ajax: {
            url: '{{ route('superuser.inventory.mutation_from_display.search_sku') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                q: params.term,
                warehouse: $('#warehouse_from').val(),
                _token: "{{ csrf_token() }}"
              };
            },
            cache: true
          },
          minimumInputLength: 3,
        });

        $('.js-ajax').on('select2:select', function(e) {
          var name = e.params.data.name;
          $(this).parents('tr').find('.name').text(name);

          $(this).parents('tr').find('input[name="qty[]"]').attr({
            "max": e.params.data.stock,
            "min": 0,
            "placeholder": e.params.data.stock
          });
        });

      };

      $('#warehouse_from').on('select2:select', function(e) {
        table.clear().draw();
      });

      $('#datatable tbody').on('click', '.row-delete', function(e) {
        e.preventDefault();

        parent = $(this).parents('tr');
        edit = parent.find('input[name="edit[]"]').val();
        if (edit) {
          ids_delete = $('input[name="ids_delete"]').val();
          $('input[name="ids_delete"]').val(edit + ',' + ids_delete);
        }

        table.row($(this).parents('tr')).remove().draw();
      });


    });

  </script>
@endpush
