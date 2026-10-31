@extends('superuser.app')

@section('content')
<div class="enrich-wrap">
  <div class="enrich-head">
    <h5>Pengaturan Enrichment</h5>
    <small>Driver aktif: <strong>{{ $driverOptions[$driver] ?? $driver }}</strong> &mdash; ganti driver, model, dan cadangan otomatis saat kuota habis.</small>
  </div>

  @include('superuser.enrichment._tabs', ['activeTab' => 'pengaturan'])

  <div id="alert-block"></div>

  <div class="enrich-card">
    <div class="enrich-card-head"><i class="fas fa-sliders-h text-primary mr-2"></i> Driver LLM</div>
    <form class="ajax p-15" data-action="{{ route('superuser.enrichment.settings.save') }}" data-type="POST">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right" for="driver">Driver LLM</label>
        <div class="col-lg-7">
          <select class="js-select2 form-control form-control-sm" id="driver" name="driver">
            @foreach ($driverOptions as $name => $label)
            <option value="{{ $name }}" {{ $driver === $name ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right" for="api_key">API Key</label>
        <div class="col-lg-7">
          <input type="password" class="form-control form-control-sm" id="api_key" name="api_key" autocomplete="new-password"
                 placeholder="{{ $hasApiKey ? 'tersimpan - isi untuk mengganti' : 'belum diisi' }}">
          <small class="text-muted">Disimpan per driver. Kosongkan bila tidak ingin mengganti key yang tersimpan.</small>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right" for="model">Model</label>
        <div class="col-lg-7">
          <input type="text" class="form-control form-control-sm" id="model" name="model" value="{{ $model }}">
          <small class="text-muted">Kosongkan untuk memakai default driver terpilih. Nama model bisa berubah &mdash; cek dashboard provider.</small>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right" for="use_search">Pencarian web</label>
        <div class="col-lg-7">
          <label class="css-control css-control-primary css-checkbox mb-0">
            <input type="checkbox" class="css-control-input" id="use_search" name="use_search" value="1" {{ $useSearch ? 'checked' : '' }}>
            <span class="css-control-indicator"></span>
            Wajibkan sumber (anti-halusinasi)
          </label>
          <small class="d-block text-muted mt-10">Grounding Gemini butuh project dengan billing aktif; di free tier ditolak (HTTP 429).</small>
          <small id="search-note" class="text-warning d-none d-block mt-10">Driver ini belum punya pencarian web &mdash; opsi ini diabaikan dan hasil otomatis masuk "Perlu Review".</small>
        </div>
      </div>

      <div class="form-group row mb-0">
        <label class="col-lg-3 col-form-label text-right">Driver cadangan</label>
        <div class="col-lg-7">
          <div class="d-flex flex-wrap">
            @foreach ($driverOptions as $name => $label)
            @if ($name !== $driver)
            <label class="css-control css-control-primary css-checkbox mr-20 mb-10">
              <input type="checkbox" class="css-control-input" name="fallback[]" value="{{ $name }}" {{ in_array($name, $fallback, true) ? 'checked' : '' }}>
              <span class="css-control-indicator"></span>
              {{ $label }}
            </label>
            @endif
            @endforeach
          </div>
          <small class="d-block text-muted mt-10">Dipakai otomatis saat driver aktif kena kuota (HTTP 429). Urutan mengikuti urutan di dropdown atas.</small>
        </div>
      </div>

      <div class="enrich-actionbar border-top mt-15 px-0 pb-0">
        <span class="enrich-note">Perubahan berlaku untuk proses berikutnya.</span>
        <button type="submit" class="btn btn-primary min-width-125 ml-auto">
          <i class="fas fa-save mr-2"></i> Simpan
        </button>
      </div>
    </form>
  </div>

  <div class="enrich-card">
    <div class="enrich-card-head"><i class="fas fa-plug text-primary mr-2"></i> Provider free untuk uji coba &amp; pembanding</div>
    <div class="p-15">
      <p class="enrich-note mb-10">Sudah terdaftar sebagai driver &mdash; cukup isi API key, tidak perlu ubah kode.</p>
      <ul class="pl-20 mb-10">
        <li class="mb-10"><strong>Groq</strong> &mdash; model <code>openai/gpt-oss-120b</code>, ~30 RPM / ~1.000 request per hari. Key: <a href="https://console.groq.com/keys" target="_blank" rel="noopener">console.groq.com/keys</a></li>
        <li class="mb-10"><strong>OpenRouter</strong> &mdash; model <code>nvidia/nemotron-3-ultra-550b-a55b:free</code>, ~50 request per hari. Key: <a href="https://openrouter.ai/keys" target="_blank" rel="noopener">openrouter.ai/keys</a> &mdash; aktifkan dulu free endpoints di Settings &gt; Privacy</li>
        <li class="mb-10"><strong>NVIDIA NIM</strong> &mdash; model <code>meta/llama-3.2-11b-vision-instruct</code>, ~40 RPM. Key: <a href="https://build.nvidia.com/settings/api-keys" target="_blank" rel="noopener">build.nvidia.com</a></li>
        <li class="text-muted"><strong>Mistral</strong> &mdash; belum bisa dipakai: API key hanya tersedia untuk plan berbayar (per Okt 2026).</li>
      </ul>
      <p class="enrich-note mb-10">Driver non-Gemini belum punya pencarian web, jadi <code>sources</code> kosong dan hasil masuk "Perlu Review". Pakai untuk membandingkan kualitas data, bukan untuk verifikasi.</p>
      <p class="enrich-note mb-0"><strong>Menambah provider lain:</strong> jika API-nya OpenAI-compatible, cukup tambah entri di <code>config/enrichment.php</code>. Jika butuh pencarian web bawaan, buat class baru yang implements <code>App\Enrichment\Contracts\Driver</code>.</p>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('styles')
<style>
  #driver + .select2-container .select2-selection--single { height: 36px; }
  #driver + .select2-container .select2-selection__rendered { line-height: 34px; }
  #driver + .select2-container .select2-selection__arrow { height: 34px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  var driverMeta = @json($driverMeta);

  function syncDriverFields() {
    var meta = driverMeta[$('#driver').val()] || {};
    $('#model').val(meta.model || meta.default_model || '');
    $('#api_key').val('').attr('placeholder', meta.has_key ? 'tersimpan - isi untuk mengganti' : 'belum diisi');
    $('#search-note').toggleClass('d-none', !meta.supports_search);
  }

  $(document).ready(function () {
    $('.js-select2').select2({ width: '100%' });
    $('#driver').on('change', syncDriverFields);
    $('#search-note').toggleClass('d-none', !(driverMeta[$('#driver').val()] || {}).supports_search);
  });
</script>
@endpush
