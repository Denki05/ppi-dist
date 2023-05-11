@extends('superuser.app')

@section('content')
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
    <div class="form-group row">
    <form>
            <div class="row">
              <div class="col-lg-3">
                <div class="form-group row">
                  {{--<label class="col-md-3 col-form-label text-right">Customer</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="customer_name">
                      <option value="">==All Customer==</option>
                      @foreach($customers as $index => $row)
                      <option value="{{$row->name}}">{{$row->name}}</option>
                      @endforeach
                    </select>
                  </div>--}}
                </div>   
              </div>
              <div class="col-lg-3">
                  <div class="form-group row">
                    {{--<label class="col-md-3 col-form-label text-right">Area</label>
                    <div class="col-md-9">
                      <select class="form-control js-select2" name="province">
                        <option value="">==All Provinsi==</option>
                        @foreach($provinsi as $index => $row)
                        <option value="{{$row->prov_id}}">{{$row->prov_name}}</option>
                        @endforeach
                      </select>
                    </div>--}}
                  </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group row">
                  <div class="col-md-3">
                    <!-- <label class="col-md-3 col-form-label text-right">Search</label> -->
                  </div>
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search" name="search">
                        <div class="input-group-append">
                          <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
    <table id="store_table" class="table ">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>Store</th>
          <th>Category</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($customer as $row)
          <tr class="clickable js-tabularinfo-toggle" data-toggle="collapse" id="row2" data-target=".a{{ $row->id }}">
              <td>
                <div class="col-sm-6">
                  <div class="row mb-2">
                    <a href="#" class="link">
                      <!-- <button type="button" name='edit' id='{{ $row->id }}'>#</button> -->
                      <button type="button" class="btn btn-secondary btn-sm" name="edit" id="{{ $row->id }}">-</button>
                    </a>
                  </div>
                </div>
              </td>
              <td style="font-size: 12pt; font-weight:bold;">{{ $row->name }}</td>
              <td style="font-size: 10pt;">{{ $row->category->name ?? '-' }}</td>
          </tr>

          <tr class="tabularinfo__subblock collapse a{{ $row->id }}">
                  <td colspan="8">
                    <table class="table-active table table-bordered">
                            <!-- <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Action</th>
                            </tr> -->

                            <tbody>
                                @foreach ($member as $index)
                                    @if ($row->id == $index->customer_id)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td style="font-size: 10pt; font-weight:bold;">{{ $index->name }}</td>
                                            <td>
                                              <a class="btn btn-primary" href="{{ route('superuser.finance.payable.create', [$row->id]) }}" role="button"><i class="fa fa-credit-card-alt" aria-hidden="true"></i></a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
      </tbody>
    </table>
    </div>
      <div class="d-flex justify-content-center">
        {!! $customer->links() !!}
      </div>
  </div>
</div>

@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    $(function(){
      $(function(){
        $('#store_table').DataTable( {
          "paging":   false,
          "ordering": true,
          "info":     false,
          "searching" : false,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
        });


        $('.js-select2').select2();

        $(document).on('click','.btn-add',function(){
          $('#modalSelectCustomer').modal('show');
        })

      });
    })
  </script>
@endpush
