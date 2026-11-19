@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Zona Pengembang</span>
  <span class="breadcrumb-item active">Health Check</span>
</nav>

@if(session('success'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
  {{ session('success') }}
</div>
@endif

<div class="block">
  <div class="block-content block-content-full">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
      <div>
        <span class="badge badge-danger">Unresolved: {{ $errorStats['unresolved'] }}</span>
        <span class="badge badge-warning">Error 24 jam: {{ $errorStats['last24'] }}</span>
        @if($result)
          <span class="badge badge-danger">Critical: {{ $result['summary']['critical'] }}</span>
          <span class="badge badge-warning">Warning: {{ $result['summary']['warning'] }}</span>
          <span class="badge badge-info">Info: {{ $result['summary']['info'] }}</span>
        @endif
      </div>
      <div class="btn-group">
        <a href="{{ route('superuser.health_check.index', ['tab' => 'checks']) }}" class="btn btn-sm {{ $tab === 'checks' ? 'btn-primary' : 'btn-outline-secondary' }}">Anomali Data</a>
        <a href="{{ route('superuser.health_check.index', ['tab' => 'errors']) }}" class="btn btn-sm {{ $tab === 'errors' ? 'btn-primary' : 'btn-outline-secondary' }}">Error Live</a>
      </div>
    </div>

    @if($tab === 'checks')
      <div class="mb-3 d-flex flex-wrap align-items-center">
        <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => $sinceDays]) }}" class="btn btn-sm btn-primary mr-2">
          <i class="fa fa-stethoscope"></i> Jalankan Health Check
        </a>
        <div class="btn-group btn-group-sm mr-2" role="group" aria-label="Rentang transaksi">
          <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => 30]) }}" class="btn {{ $sinceDays === 30 ? 'btn-primary' : 'btn-outline-secondary' }}">30 hari</a>
          <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => 180]) }}" class="btn {{ $sinceDays === 180 ? 'btn-primary' : 'btn-outline-secondary' }}">180 hari</a>
          <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => 365]) }}" class="btn {{ $sinceDays === 365 ? 'btn-primary' : 'btn-outline-secondary' }}">1 tahun</a>
          <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => 0]) }}" class="btn {{ $sinceDays === 0 ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
        </div>
        @if($result)
          <small class="text-muted">Dijalankan {{ $result['run_at'] }}, transaksi {{ $result['since_days'] === 0 ? 'semua' : $result['since_days'] . ' hari terakhir' }} (read-only).</small>
        @endif
      </div>

      @if(!$result)
        <div class="alert alert-info">Belum dijalankan. Klik "Jalankan Health Check" untuk memindai anomali data.</div>
      @else
        @foreach($result['checks'] as $check)
          <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
              <strong>{{ $check['label'] }}</strong>
              <span>
                <span class="badge badge-danger">{{ $check['counts']['critical'] }}</span>
                <span class="badge badge-warning">{{ $check['counts']['warning'] }}</span>
                <span class="badge badge-info">{{ $check['counts']['info'] }}</span>
                <small class="text-muted ml-2">{{ $check['duration_ms'] }} ms</small>
              </span>
            </div>
            @if(!empty($check['findings']))
            <div class="table-responsive">
              <table class="table table-sm table-striped mb-0">
                <thead>
                  <tr><th style="width:90px">Level</th><th>Pesan</th><th style="width:280px">Referensi</th></tr>
                </thead>
                <tbody>
                  @foreach($check['findings'] as $finding)
                  <tr>
                    <td>
                      @if($finding['level'] === 'critical')
                        <span class="badge badge-danger">Critical</span>
                      @elseif($finding['level'] === 'warning')
                        <span class="badge badge-warning">Warning</span>
                      @else
                        <span class="badge badge-info">Info</span>
                      @endif
                    </td>
                    <td>{{ $finding['message'] }}</td>
                    <td class="text-break">{{ $finding['ref'] }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @if($check['truncated'])
              <div class="card-body py-2"><small class="text-muted">Temuan dipotong. Periksa langsung atau persempit lewat command.</small></div>
            @endif
            @else
              <div class="card-body py-2"><small class="text-success">Tidak ada temuan.</small></div>
            @endif
          </div>
        @endforeach
      @endif
    @else
      <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">Error 500/exception live yang tertangkap otomatis. Notifikasi dikirim ke Developer (maks. 1 per 10 menit per jenis error).</small>
        @if($errorStats['unresolved'] > 0)
        <form method="POST" action="{{ route('superuser.health_check.resolve_all') }}">
          @csrf
          <button type="submit" class="btn btn-sm btn-outline-secondary">Tandai semua selesai</button>
        </form>
        @endif
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th style="width:140px">Terakhir</th>
              <th>Error</th>
              <th style="width:260px">Lokasi</th>
              <th style="width:240px">URL</th>
              <th style="width:60px">Jumlah</th>
              <th style="width:110px">Status</th>
              <th style="width:80px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($errors as $error)
            <tr>
              <td>{{ $error->last_seen_at }}</td>
              <td>
                <div><strong>{{ $error->exception_class }}</strong></div>
                <div class="text-muted">{{ \Illuminate\Support\Str::limit($error->message, 160) }}</div>
              </td>
              <td class="text-break">{{ $error->file }}:{{ $error->line }}</td>
              <td class="text-break">{{ \Illuminate\Support\Str::limit($error->url, 100) }}</td>
              <td>{{ $error->occurrences }}x</td>
              <td>
                @if($error->is_resolved)
                  <span class="badge badge-success">Selesai</span>
                @else
                  <span class="badge badge-danger">Baru</span>
                @endif
              </td>
              <td>
                @if(!$error->is_resolved)
                <form method="POST" action="{{ route('superuser.health_check.resolve', $error->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-primary">Selesai</button>
                </form>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada error tercatat.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $errors->links() }}
    @endif
  </div>
</div>
@endsection
