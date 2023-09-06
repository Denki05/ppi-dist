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

<h4 style="font-weight: bold;">#DELIVERY ORDER</h4>
<main style="background:#fff">
  
  <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
  <label style="padding: 15px 25px;" for="tab1">DO Proses</label>
    
  <input style="display: none;" id="tab2" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab2">DO Siap Kirim</label>
    
  <input style="display: none;" id="tab3" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab3">DO Update Resi</label>
    
  <!-- <input id="tab4" type="radio" name="tabs">
  <label for="tab4">Drupal</label> -->
    
  <!-- DO Proses -->
  <section id="content1">
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="do_proses">
          <thead>
            <tr>
              <th>#</th>
              <th>DO Code</th>
              <th>Referensi SO</th>
              <th>Customer</th>
              <th>Print Count</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          @foreach($table as $index => $row)
            @if($row->status == 3)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $row->do_code }}</td>
              <td>{{ $row->so->code }}</td>
              <td>{{ $row->member->name }}</td>
              <td>{{ $row->print_count }}</td>
              <td>
                @if($row->status == 3)
                <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span>
                @elseif($row->status > 4)
                <span class="badge badge-info"><b>Packed</b></span>
                @endif
              </td>
              <td>
                @if($row->status == 3)
                <a href="{{route('superuser.penjualan.delivery_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat">
                  <i class="fas fa-box"></i> Kerjakan
                </a>
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
    
  <section id="content2">
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="do_kirim">
          <thead>
            <tr>
              <th>#</th>
              <th>DO Code</th>
              <th>Customer</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          @foreach($table as $index => $row)
            @if($row->status == 4)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $row->do_code }}</td>
              <td>{{ $row->member->name }}</td>
              <td>
                <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span>
              </td>
              <td>
                @if($row->status == 4)
                <a href="{{route('superuser.penjualan.delivery_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat">
                <i class="fas fa-shipping-timed"></i> Surat Jalan
                </a>
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
    
  <section id="content3">
  <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="update_resi">
          <thead>
            <tr>
              <th>#</th>
              <th>DO Code</th>
              <th>Customer</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          @foreach($table as $index => $row)
            @if($row->status == 5 OR $row->status == 6)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $row->do_code }}</td>
              <td>{{ $row->member->name }}</td>
              <td>
                <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span>
              </td>
              <td>
                @if($row->status == 5)
                <a href="{{route('superuser.penjualan.delivery_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat">
                <i class="fa fa-money"></i> Update Resi
                </a>
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
    
  <!-- <section id="content4">
    <p>
      Bacon ipsum dolor sit amet landjaeger sausage brisket, jerky drumstick fatback boudin ball tip turducken. Pork belly meatball t-bone bresaola tail filet mignon kevin turkey ribeye shank flank doner cow kielbasa shankle. Pig swine chicken hamburger, tenderloin turkey rump ball tip sirloin frankfurter meatloaf boudin brisket ham hock. Hamburger venison brisket tri-tip andouille pork belly ball tip short ribs biltong meatball chuck. Pork chop ribeye tail short ribs, beef hamburger meatball kielbasa rump corned beef porchetta landjaeger flank. Doner rump frankfurter meatball meatloaf, cow kevin pork pork loin venison fatback spare ribs salami beef ribs.
    </p>
    <p>
      Jerky jowl pork chop tongue, kielbasa shank venison. Capicola shank pig ribeye leberkas filet mignon brisket beef kevin tenderloin porchetta. Capicola fatback venison shank kielbasa, drumstick ribeye landjaeger beef kevin tail meatball pastrami prosciutto pancetta. Tail kevin spare ribs ground round ham ham hock brisket shoulder. Corned beef tri-tip leberkas flank sausage ham hock filet mignon beef ribs pancetta turkey.
    </p>
  </section> -->
    
</main>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(function(){
    $('#do_proses').DataTable( {
          "paging":   false,
          "ordering": true,
          "info":     false,
          "searching" : false,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
        });

        $('#do_kirim').DataTable( {
          "paging":   false,
          "ordering": true,
          "info":     false,
          "searching" : false,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
        });

        $('#update_resi').DataTable( {
          "paging":   false,
          "ordering": true,
          "info":     false,
          "searching" : false,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
        });
  })
</script>
@endpush