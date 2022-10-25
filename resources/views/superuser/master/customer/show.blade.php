@extends('superuser.app')

@section('content')

<div class="block">
  {{--<div class="block-header block-header-default">
    <h3 class="block-title">Show Store</h3>
    <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.destroy', $customer->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.customer.edit', $customer->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
    </div>
  </div>--}}
  <div class="container py-5">
  <div class="p-5 bg-white rounded shadow mb-5">
    <!-- Rounded tabs -->
    <ul id="myTab" role="tablist" class="nav nav-tabs nav-pills flex-column flex-sm-row text-center bg-light border-0 rounded-nav">
      <li class="nav-item flex-sm-fill">
        <a id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true" class="nav-link border-0 text-uppercase font-weight-bold active">Profile</a>
      </li>
      <li class="nav-item flex-sm-fill">
        <a id="address-tab" data-toggle="tab" href="#address" role="tab" aria-controls="address" aria-selected="false" class="nav-link border-0 text-uppercase font-weight-bold">Address</a>
      </li>
      <li class="nav-item flex-sm-fill">
        <a id="document-tab" data-toggle="tab" href="#document" role="tab" aria-controls="document" aria-selected="false" class="nav-link border-0 text-uppercase font-weight-bold">Document</a>
      </li>
    </ul>
    <div id="myTabContent" class="tab-content">
      <div id="profile" role="tabpanel" aria-labelledby="profile-tab" class="tab-pane fade px-4 py-5 show active">
        <div class="media align-items-center py-3 mb-3">
          <img src="{{ $customer->img_store }}" alt="" class="d-block ui-w-100 rounded-circle">
          <div class="media-body ml-4">
            <h5 class="font-weight-bold mb-0">{{ $customer->name }} <span class="text-muted font-weight-normal">@ {{ $customer->code }}</span></h4>
            <div class="text-muted mb-2">ID: {{ $customer->id }}</div>
              <a href="{{ route('superuser.master.customer.edit', $customer->id) }}" class="btn btn-primary btn-sm" target="_blank">Edit</a>&nbsp;
              <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.destroy', $customer->id) }}', true)" class="btn btn-danger btn-sm">Delete</a>&nbsp;
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-body">
            <table class="table user-view-table m-0" id="profile_table">
              <tbody>
                <?php
                  $total_outstanding = 0;
                  $total_outstanding_past_due_date = 0;
                  if(isset($customer_history) && sizeof($customer_history) > 0) {
                    foreach($customer_history as $index => $row) {
                      $total_outstanding += $row->grand_total_idr;
                    }
                  }
                ?>
                <tr>
                  <td>Registered On</td>
                  <td><b>:</b></td>
                  <td>{{ \Carbon\Carbon::parse($customer->created_at)->format('d-m-Y')}}</td>
                </tr>
                <tr>
                  <td>Saldo | Plafon Piutang</td>
                  <td><b>:</b></td>
                  <td>
                      {{ rupiah($customer->saldo ?? '-') }} |  {{ rupiah($customer->plafon_piutang) }}</td>
                </tr>
                <tr>
                  <td>Category Store</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ route('superuser.master.customer_category.show', $customer->category->id) }}" target="_blank" class="badge badge-info">
                      {{ $customer->category->name }}
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>Type Store</td>
                  <td><b>:</b></td>
                  <td>
                      @foreach($customer->types as $type)
                      <a href="{{ route('superuser.master.customer_type.show', $type->id) }}" target="_blank" class="badge badge-info">
                        {{ $type->name }}
                      </a>
                      @endforeach
                  </td>
                </tr>
                <tr>
                  <td>Status</td>
                  <td><b>:</b></td>
                  <td><span class="badge badge-outline-success">{{ $customer->status() }} </span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="address" role="tabpanel" aria-labelledby="address-tab" class="tab-pane fade px-4 py-5">
        <div class="card mb-4">
          <div class="card-body">
            <table class="table user-view-table m-0" id="address_table">
              <tbody>
                <tr>
                  <td>Address</td>
                  <td><b>:</b></td>
                  <td>{{ $customer->address }}</td>
                </tr>
                <tr>
                  <td>Phone</td>
                  <td><b>:</b></td>
                  <td>{{ $customer->phone }}</td>
                </tr>
                <tr>
                  <td>Email</td>
                  <td><b>:</b></td>
                  <td>{{ $customer->email }}</td>
                </tr>
                <tr>
                  <td>Area</td>
                  <td><b>:</b></td>
                  <td>{{ $customer->text_provinsi }} </td>
                </tr>
                <tr>
                  <td>Location Maps</td>
                  <td><b>:</b></td>
                  <td>
                    <iframe src="https://maps.google.com/maps?q={{ $customer->gps_latitude }},{{ $customer->gps_longitude }}&hl=es;z=14&amp;output=embed" ?? ></iframe>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="document" role="tabpanel" aria-labelledby="document-tab" class="tab-pane fade px-4 py-5">
        <div class="card mb-4">
          <div class="card-body">
            <table class="table user-view-table m-0" id="document_table">
              <tbody>
              <tr>
                  <td>NPWP</td>
                  <td><b>:</b></td>
                  <td>{{ $customer->npwp ?? '-' }}</td>
                </tr>
                <tr>
                  <td>KTP</td>
                  <td><b>:</b></td>
                  <td>{{ $customer->ktp ?? '-' }}</td>
                </tr>
                <tr>
                  <td>Image NPWP</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ $customer->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                      <img src="{{ $customer->img_npwp }}" class="img-fluid img-show-small">
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>Image KTP</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ $customer->img_ktp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                      <img src="{{ $customer->img_ktp }}" class="img-fluid img-show-small">
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.master.customer.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
      </div>
    </div>
    <!-- End rounded tabs -->
    </div>
  </div>
  </div>
</div>



@endsection

@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#profile_table').DataTable({
      columnDefs: [
        { orderable: false, targets: [2, 3] }
      ]
    })

    $('#address_table').DataTable({
      columnDefs: [
        { orderable: false, targets: [3] }
      ]
    })

    $('#document_table').DataTable({
      columnDefs: [
        { orderable: false, targets: [3] }
      ]
    })

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });

    Codebase.helpers('table-tools')
  })
</script>
@endpush
