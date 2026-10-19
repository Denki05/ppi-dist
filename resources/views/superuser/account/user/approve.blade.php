@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.user.index', ['tab' => 'pending']) }}">User</a>
  <span class="breadcrumb-item active">Approve Pendaftar</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <div class="alert alert-info d-flex align-items-center" style="border-radius:12px;font-size:13px">
      <i class="fa fa-google fa-lg mr-2"></i>
      <div>Pendaftar via Google: <b>{{ $result->email }}</b> ({{ $result->created_at }}) — pilih paket jabatan, password random dibuat otomatis bila dikosongkan dan <b>tampil sekali</b> untuk disampaikan ke kandidat.</div>
    </div>
    <form id="frmApprove" action="#">
    @csrf
    <input type="hidden" name="user_id" value="{{$result->id}}">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Nama<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" name="name" class="form-control" value="{{ $result->name }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Username<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" name="username" class="form-control" value="{{ $result->username }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Email</label>
          <div class="col-md-9">
            <input type="text" class="form-control" value="{{ $result->email }}" disabled>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Password awal</label>
          <div class="col-md-9">
            <div class="input-group">
              <input type="text" name="password" id="password" class="form-control" placeholder="Kosongkan = random 12 karakter" autocomplete="new-password">
              <div class="input-group-append">
                <button type="button" class="btn btn-alt-primary" id="btn-generate"><i class="fa fa-random"></i> Generate</button>
              </div>
            </div>
            <small class="form-text text-muted">Sampaikan ke kandidat via WA, minta diganti di Profile saat login pertama.</small>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Jabatan<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <select name="jabatan" id="jabatan" class="form-control">
              @foreach($packages as $key => $pkg)
                <option value="{{ $key }}">{{ $key }} — {{ $pkg['label'] }} ({{ count($pkg['menus']) }} menu)</option>
              @endforeach
            </select>
            <small class="form-text text-muted" id="package-preview"></small>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Divisi</label>
          <div class="col-md-9">
            <input type="text" name="division" class="form-control" value="Marketing">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Cabang</label>
          <div class="col-md-9">
            <input type="text" name="cabang" class="form-control" value="Jakarta">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right">Akses modul</label>
          <div class="col-md-9">
            @foreach($modules as $m)
              <label class="css-control css-control-sm css-checkbox css-checkbox-primary mr-3">
                <input type="checkbox" name="modules[]" value="{{ $m }}" class="css-control-input" {{ in_array($m, ['MIS','AO']) ? 'checked' : '' }}>
                <span class="css-control-indicator"></span> {{ $m }}
              </label>
            @endforeach
            <small class="form-text text-muted d-block">MIS = laporan tim, AO = agenda/order, APM, TRS = transaksi, DSG = design.</small>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.account.user.index', ['tab' => 'pending'])}}" class="btn btn-warning btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
        <button class="btn btn-success btn-md" type="submit"><i class="fa fa-check"></i> Aktifkan + Terapkan Paket</button>
      </div>
    </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  var PACKAGE_MENUS = {!! json_encode(array_map(function($p){ return array_keys($p['menus']); }, $packages)) !!};
  $(function(){
    function renderPreview(){
      var key = $('#jabatan').val();
      var menus = PACKAGE_MENUS[key] || [];
      $('#package-preview').text(menus.length + ' menu: ' + menus.join(', ').replace(/superuser\./g, ''));
    }
    $('#jabatan').on('change', renderPreview);
    renderPreview();

    $('#btn-generate').on('click', function(){
      var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
      var out = '';
      for (var i = 0; i < 12; i++) out += chars.charAt(Math.floor(Math.random() * chars.length));
      $('#password').val(out);
    });

    $(document).on('submit','#frmApprove',function(e){
      e.preventDefault();
      if(confirm("Aktifkan user ini dengan paket terpilih?")){
        let _form = $('#frmApprove');
        $.ajax({
          url : '{{route('superuser.account.user.approve')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').html('Loading...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              var cred = resp.Credentials || {};
              Swal.fire({
                title: 'Berhasil diaktifkan!',
                html: 'Sampaikan ke kandidat:<br><b>Username:</b> ' + (cred.username || '-') +
                      '<br><b>Password:</b> ' + (cred.password || '-') +
                      '<br><small>Minta diganti di Profile saat login pertama. Kredensial ini tidak ditampilkan lagi.</small>',
                icon: 'success',
                confirmButtonText: 'Kembali ke daftar'
              }).then((result) => {
                  document.location.href = '{{route('superuser.account.user.index', ['tab' => 'active'])}}';
              })
            }
          },
          error : function(){
            alert("Cek Koneksi Internet")
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-check"> Aktifkan + Terapkan Paket</i>');
          }
        })
      }
    })
  })
</script>
@endpush
