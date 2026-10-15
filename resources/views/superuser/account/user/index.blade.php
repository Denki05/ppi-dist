@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <span class="breadcrumb-item active">User</span>
</nav>
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
      <div class="row align-items-center">
        <div class="col-md-6 mb-2 mb-md-0">
          <a href="{{ route('superuser.account.user.index', ['tab' => 'pending']) }}" class="btn btn-sm {{ $tab==='pending' ? 'btn-warning' : 'btn-alt-warning' }} mr-1">Perlu Approval <span class="badge badge-light">{{ $counts['pending'] }}</span></a>
          <a href="{{ route('superuser.account.user.index', ['tab' => 'active']) }}" class="btn btn-sm {{ $tab==='active' ? 'btn-success' : 'btn-alt-success' }} mr-1">Aktif <span class="badge badge-light">{{ $counts['active'] }}</span></a>
          <a href="{{ route('superuser.account.user.index', ['tab' => 'inactive']) }}" class="btn btn-sm {{ $tab==='inactive' ? 'btn-danger' : 'btn-alt-danger' }} mr-1">Nonaktif <span class="badge badge-light">{{ $counts['inactive'] }}</span></a>
          <a href="{{ route('superuser.account.user.index', ['tab' => 'all']) }}" class="btn btn-sm {{ $tab==='all' ? 'btn-primary' : 'btn-alt-primary' }}">Semua</a>
        </div>
        <div class="col-md-6 text-md-right">
          <a href="{{route('superuser.account.user.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add User</a>
        </div>
      </div>
  </div>
</div>
<div class="block">
  <div class="block-content block-content-full">
      <form>
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="row">
          <div class="col-lg-6"></div>
          <div class="col-lg-6">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Keyword" name="search" value="{{ request('search') }}">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <div class="row mb-30">
        <div class="col-12">
          <div class="table-responsive">
          <table class="table table-striped table-vcenter" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama / Username</th>
                <th>Email</th>
                <th>Divisi / Jabatan</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($table as $index => $row)
                @php
                  $em = strtolower(trim($row->email ?? ''));
                  $dom = substr(strrchr($em, '@') ?: '', 1);
                  $emailOk = $em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL) && in_array($dom, ['gmail.com','googlemail.com','lssoft88.xyz','lsfragrance.id'], true);
                  $roles = $rolesByUser[$row->id] ?? [];
                  $mods = $row->modul_akses ? json_decode($row->modul_akses, true) : [];
                @endphp
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>
                    <div class="font-w600">{{ $row->name ?: '-' }}</div>
                    <small class="text-muted">@ {{ $row->username }}</small>
                  </td>
                  <td>
                    <small>{{ $row->email }}</small><br>
                    @if(!empty($row->google_id))
                      <span class="badge badge-success"><i class="fa fa-google mr-1"></i>Terhubung</span>
                    @else
                      <span class="badge badge-secondary">Belum link</span>
                    @endif
                    @if(!$emailOk)
                      <span class="badge badge-danger">Email random</span>
                    @endif
                  </td>
                  <td>
                    <small>{{ $row->division ?: '-' }}@if(!empty($row->cabang)) &middot; {{ $row->cabang }}@endif</small><br>
                    @forelse($roles as $r)
                      <span class="badge badge-primary">{{ $r }}</span>
                    @empty
                      <span class="badge badge-secondary">Tanpa role</span>
                    @endforelse
                    @if(!empty($mods))
                      <br><small class="text-muted">{{ implode(' · ', $mods) }}</small>
                    @endif
                  </td>
                  <td>
                    @if($tab === 'pending' || $row->is_active == 0)
                      <span class="badge badge-warning">Menunggu approval</span>
                    @else
                      <span class="badge badge-success">Aktif</span>
                    @endif
                  </td>
                  <td><small>{{ $row->created_at }}</small></td>
                  <td style="white-space:nowrap">
                    @if($tab === 'pending')
                      <a href="{{route('superuser.account.user.approve.form',$row->id)}}" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Approve</a>
                    @endif
                    <a href="{{route('superuser.account.user.edit',$row->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                    @if($row->is_active == 1)
                    <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i></a>
                   	@else
                   	<a href="#" class="btn btn-warning btn-sm btn-restore" data-id="{{$row->id}}"><i class="fa fa-reload"></i></a>
                   	@endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center text-muted">
                  @if($tab === 'pending') Tidak ada pendaftar menunggu approval. @else Tidak ada data. @endif
                </td></tr>
              @endforelse
            </tbody>
          </table>
          </div>
        </div>
      </div>

      <div class="row mb-30">
        <div class="col-12">
          {{$table->links()}}
        </div>
      </div>
  </div>
</div>
<form method="post" action="{{route('superuser.account.user.destroy')}}" id="frmDestroy">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.account.user.restore')}}" id="frmRestore">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    $(function(){
      $(function(){
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


        $('.js-select2').select2();

        $(document).on('click','.btn-delete',function(){
          if(confirm("Nonaktifkan user ini? (login password + Google ditolak)")){
            let id = $(this).data('id');
            $('#frmDestroy').find('input[name="id"]').val(id);
            $('#frmDestroy').submit();
          }
        })

        $(document).on('click','.btn-restore',function(){
          if(confirm("Apakah anda yakin ingin mengaktifkan ini ? ")){
            let id = $(this).data('id');
            $('#frmRestore').find('input[name="id"]').val(id);
            $('#frmRestore').submit();
          }
        })
      });
    })
  </script>
@endpush
