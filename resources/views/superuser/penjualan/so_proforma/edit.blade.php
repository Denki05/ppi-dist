@extends('superuser.app')

@section('content')
<style>
/* ── Padatkan tampilan edit proforma ── */
.pf-compact { max-width: 1240px; margin: 0 auto; }
.pf-compact .breadcrumb { padding: 4px 10px; font-size: .78rem; margin-bottom: 8px; }
.pf-compact .alert { padding: 5px 10px; font-size: .78rem; margin-bottom: 8px; }
.pf-compact .block { margin-bottom: 10px; }
.pf-compact .block-header { padding: 8px 12px; min-height: 0; }
.pf-compact .block-title { font-size: .85rem; }
.pf-compact .block-content { padding: 10px 12px; }
.pf-compact .form-group { margin-bottom: 8px; }
.pf-compact label { font-size: .72rem; color: #64748b; font-weight: 600; margin-bottom: 2px; }
.pf-compact .form-control { height: 32px; font-size: .85rem; padding: 4px 8px; }
.pf-compact select.form-control { height: 32px; }
.pf-compact #datatable th { font-size: .68rem; text-transform: uppercase; letter-spacing: .4px; color: #64748b; padding: 6px 6px; }
.pf-compact #datatable td { padding: 4px 6px; vertical-align: middle; }
.pf-compact #datatable .form-control { height: 30px; font-size: .82rem; }
/* Ringkasan: rapat kanan, tanpa whitespace kiri */
.pf-summary { max-width: 460px; margin-left: auto; display: grid; gap: 6px; padding: 10px 12px; }
.pf-sum-row { display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
.pf-sum-row label { width: 110px; text-align: right; margin: 0; }
.pf-sum-row .form-control { width: 132px; text-align: right; }
.pf-sum-row .form-control.wide { width: 272px; }
.pf-sum-total input { font-weight: 700; background: #f0fdf4; border-color: #bbf7d0; }
/* Status hitungan */
#calcBadge { font-size: .75rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
#calcBadge.ok { background: #dcfce7; color: #166534; }
#calcBadge.need { background: #fef3c7; color: #92400e; animation: pfPulse 1.2s infinite; }
@keyframes pfPulse { 0%,100% { opacity: 1; } 50% { opacity: .6; } }
#btn_call.need { box-shadow: 0 0 0 3px rgba(245,158,11,.35); }
/* Footer sticky agar Save selalu terjangkau */
.pf-footer { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #e8ecf0; padding: 8px 4px; z-index: 5; }
</style>
<div class="pf-compact">
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.so_proforma.index') }}">Sales Order Proforma</a>
  <span class="breadcrumb-item active">Edit {{ $results->code ?? '' }}</span>
</nav>

<div id="alert-block"></div>

@php $isRevisi = request('mode') === 'revisi'; @endphp
@if($isRevisi)
<div class="alert alert-warning d-flex align-items-center" role="alert">
  <i class="fa fa-exclamation-triangle mr-2"></i>
  <div><strong>Mode Revisi:</strong> boleh tambah varian produk baru (tombol <strong>+ Row</strong> aktif). Perubahan akan diteruskan ke AO otomatis.</div>
</div>
@else
<div class="alert alert-info d-flex align-items-center" role="alert">
  <i class="fa fa-info-circle mr-2"></i>
  <div><strong>Mode Edit:</strong> kalkulasi saja (ubah qty / diskon / kurs). Untuk tambah varian, gunakan tombol <strong>Revisi</strong> dari daftar proforma.</div>
</div>
@endif

<form class="ajax" data-action="{{ route('superuser.penjualan.so_proforma.update', $results->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="ids_delete" value="">
  {{-- Mode revisi (tambah varian) hanya via tombol Revisi; edit biasa = kalkulasi saja --}}
  <input type="hidden" name="revision_mode" value="{{ request('mode') === 'revisi' ? 'revisi' : '' }}">
    <div class="row">
        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                  <h3 class="block-title">#Detail Nota</h3>
                </div>
                <div class="block-content">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="so_date">Tanggal Nota</label>
                      <input type="date" name="so_date" class="form-control"
                      value="{{ $results->so_date ? \Carbon\Carbon::parse($results->so_date)->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="type_transaction">Type Transaksi</label>
                      <input type="text" name="type_transaction" class="form-control" 
                        value="{{ $results->getStatusTypeAttribute() }}" readonly>
                    </div>
                  </div>

                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="so_date">Warehouse</label>
                        <select class="form-control js-select2" name="warehouse" required>
                            <option value="">Pilih Gudang</option>    
                            @foreach($warehouse AS $row)
                            <option value="{{$row->id}}" {{ $row->id == $results->warehouse_id ? 'selected' : '' }}>
                                {{ $row->name }}
                            </option>
                            @endforeach
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="so_date">Ekspedisi</label>
                        <select class="form-control js-select2" name="vendor" required>
                            <option value="">Pilih vendor</option>    
                            @foreach($vendor AS $row)
                            <option value="{{$row->id}}" {{ $row->id == $results->vendor_id ? 'selected' : '' }}>
                                {{ $row->name }}
                            </option>
                            @endforeach
                        </select>
                      </div>
                  </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                          <label for="note">Note</label>
                          <input type="text" name="note" class="form-control" value="{{ $results->note }}">
                        </div>
                      </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                  <div class="block">
                    <div class="block-content">
                    <div class="form-row">
                       
                        <div class="form-group col-md-4">
                        <label for="note">Brand <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="so_brand_name" id="so_brand_name">
                            <option value="">Pilih Brand</option>
                            @foreach($brand AS $row)
                            <option value="{{ $row->brand_name }}" 
                                {{ $row->brand_name == $results->so_brand_name ? 'selected' : '' }}>
                                {{ $row->brand_name }}
                            </option>
                            @endforeach
                        </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="note">Rekening <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="rekening" required>
                              <option value="">Pilih Rekening</option>
                              @foreach($rekening as $key)
                              <option value="{{$key->id}}" 
                                  {{ $key->id == $results->rekening_id ? 'selected' : '' }}>
                                  {{$key->name}} - {{$key->number_card}}
                              </option>
                              @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="idr_rate_display">Kurs <span class="text-danger">*</span></label>
                          <input type="text" id="idr_rate_display" class="form-control"
                            value="{{ number_format((float) $results->so_idr_rate, 2, ',', '.') }}" placeholder="cth: 18.050">
                          <input type="hidden" name="idr_rate" id="idr_rate" value="{{ $results->so_idr_rate }}">
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                    <h3 class="block-title">#Detail Customer</h3>
                </div>
                <div class="block-content">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                          <label for="inputField">Customer</label>
                          @if($results->exsisting_customer == 0)
                                <input type="text" class="form-control" name="customer" value="{{ $results->customer_name ?? '-' }}">
                          @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->name }} {{ optional($results->member)->text_kota }}" readonly>
                              <input type="hidden" class="form-control" name="customer" value="{{ $results->customer_other_address_id }}">
                          @endif
                      </div>


                        <div class="form-group col-md-6">
                            <label for="note">Alamat Kirim</label>
                            @if($results->exsisting_customer == 0)
                              <input type="text" class="form-control" name="customer_address" value="{{ $results->customer_address }}" readonly>
                            @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->address }}" readonly>
                            @endif
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="customer_region">Provinsi</label>
                            @if($results->exsisting_customer == 0)
                              <select class="form-control js-select2" name="customer_region" id="customer_region">
                                  <option value="">Pilih Provinsi</option>
                                  @foreach($provinsi AS $row)
                                  <option value="{{ $row->prov_id }}" {{ ($row->prov_id == $results->customer_region ) ? 'selected' : '' }}>{{ $row->prov_name }}</option>
                                  @endforeach
                              </select>
                              @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->text_provinsi }}" readonly>
                              @endif
                        </div>
                        <div class="form-group col-md-6">
                            <label for="customer_city">Kota</label>
                            @php
                                  $city = !empty($results->customer_city) ? DB::table('kabupaten')->where('city_id', $results->customer_city)->first() : null;
                              @endphp
                              @if($results->exsisting_customer == 0)
                              <select class="form-control js-select2" name="customer_city" id="customer_city">
                                  @if($city)
                                  <option value="{{ $results->customer_city }}">{{ $city->city_name }}</option>
                                  @else
                                  <option value="">Pilih Kota — pilih Provinsi dulu</option>
                                  @endif
                              </select>
                              @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->text_kota }}" readonly tabindex="-1" title="Data dari member existing — tidak dapat diubah di sini">
                              @endif
                        </div>
                    </div>

                    <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="customer_phone">Phone</label>
                              <input type="text" class="form-control" value="{{ optional($results->member)->phone ?: $results->customer_phone }}" readonly tabindex="-1" title="Data dari member — tidak dapat diubah di sini">
                          </div>
                          <div class="form-group col-md-6">
                              <label for="customer_owner">Contact Person</label>
                              <input type="text" class="form-control" value="{{ optional($results->member)->contact_person ?: $results->customer_owner }}" readonly tabindex="-1" title="Data dari member — tidak dapat diubah di sini">
                          </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                <div class="block">
                    <div class="block-content">
                    <div class="form-row">
                       
                        <div class="form-group col-md-6">
                          <label for="note">Sales Senior <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="sales_senior_id" required>
                                <option value="">Pilih Sales Senior</option>
                                @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $senior_value)
                                <option value="{{ $senior_value }}" {{ ($senior_value == $results->sales_senior_id ) ? 'selected' : '' }}>{{ $sales_senior }}</option>
                                @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="note">Sales <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="sales_id" required>
                                <option value="">Pilih Sales</option>
                                @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                                <option value="{{ $sales_value }}" {{ ($sales_value == $results->sales_id ) ? 'selected' : '' }}>{{ $sales }}</option>
                                @endforeach
                          </select>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
        </div>
    </div>

    <div class="row">
      <div class="col-12">
      <div class="block">
                @if(request('mode') === 'revisi')
                <div class="block-header block-header-default" style="padding:6px 12px;">
                  <span class="small text-muted">Mode Revisi — tambah varian bila perlu</span>
                  <a href="#" class="row-add ml-auto">
                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold">
                      <i class="fa fa-plus mr-1"></i> Row
                    </button>
                  </a>
                </div>
                @endif
        <div class="block-content">
          <table id="datatable" class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">Select Product</th>
                <th class="text-center">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Disc</th>
                <th class="text-center">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($results->items as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" data-placeholder="Select SKU" style="width:100%" disabled tabindex="-1">
                      <option value="{{ $item->product_packaging_id }}">{{ $item->productPack->code }} - {{ $item->productPack->name }} / {{ $item->packaging->pack_name }}</option>
                    </select>
                    <input type="hidden" name="sku[]" value="{{ $item->product_packaging_id }}">
                    {{-- Free dihapus dari UI baku: status dipertahankan via hidden, ubah via Kembalikan (pengajuan / existing) --}}
                    <input type="hidden" name="free_product[]" value="{{ $item->free_product ? 1 : 0 }}">
                  </td>
                  <td><input type="number" class="form-control text-center" name="price[]" value="{{ $item->free_product ? 0 : $item->price }}" readonly required tabindex="-1"></td>
                  <td><input type="number" class="form-control text-center" name="qty[]" value="{{ $item->qty }}" required step="0.01" min="0"><input type="hidden" name="packaging[]" value="{{ $item->packaging_id }}"><input type="hidden" class="form-control" name="edit[]" value="{{ $item->id }}"></td>
                  <td><input type="number" class="form-control text-center" name="disc_usd[]" value="{{ $item->disc_usd }}" required></td>
                  <td><input type="text" class="form-control text-center" name="total[]" readonly tabindex="-1" value="{{ number_format((float) $item->total_item, 2, ',', '.') }}"></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="block-content" style="border-top:1px solid #e8ecf0; background:#f8fafc;">
          <div class="pf-summary">
            <div class="pf-sum-row">
              <label for="subtotal">IDR Sub Total</label>
              <input type="text" class="form-control wide" id="subtotal" name="subtotal" readonly value="{{ number_format((float) ($detailsCost->purchase_total_idr ?? 0), 2, ',', '.') }}">
            </div>
            <div class="pf-sum-row">
              <label>Disc %</label>
              @php $discAgenPct = $detailsCost->discount_1_percent ?? (is_numeric(optional($results->salesOrder)->catatan) ? $results->salesOrder->catatan : 0); @endphp
              <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" value="{{ $discAgenPct }}" inputmode="decimal">
              <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr" value="{{ number_format((float) ($detailsCost->discount_1 ?? 0), 2, ',', '.') }}">
            </div>
            <div class="pf-sum-row">
              <label>Disc Kemasan</label>
              <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent" value="{{ $detailsCost->discount_2_percent ?? 0 }}" inputmode="decimal">
              <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr" value="{{ number_format((float) ($detailsCost->discount_2 ?? 0), 2, ',', '.') }}">
            </div>
            <div class="pf-sum-row">
              <label for="disc_tambahan_idr">Disc IDR</label>
              <input type="text" class="form-control wide" id="disc_tambahan_idr" name="disc_tambahan_idr" value="{{ number_format((float) ($detailsCost->discount_idr ?? 0), 2, ',', '.') }}" inputmode="numeric">
            </div>
            <div class="pf-sum-row">
              <label for="voucher_idr">Voucher</label>
              <input type="text" class="form-control wide" id="voucher_idr" name="voucher_idr" value="{{ number_format((float) ($detailsCost->voucher_idr ?? 0), 2, ',', '.') }}" inputmode="numeric">
            </div>
            <div class="pf-sum-row">
              <label for="delivery_cost_idr">Ongkir</label>
              <input type="text" class="form-control wide" id="delivery_cost_idr" name="delivery_cost_idr" value="{{ number_format((float) ($detailsCost->delivery_cost_idr ?? 0), 2, ',', '.') }}" inputmode="numeric">
            </div>
            <div class="pf-sum-row pf-sum-total">
              <label for="grand_total">IDR Total</label>
              <input type="text" class="form-control wide" id="grand_total" name="grand_total" readonly value="{{ number_format((float) ($detailsCost->grand_total_idr ?? 0), 2, ',', '.') }}">
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
      <div class="block">
        <div class="block-content pf-footer">
          <div class="row align-items-center">
          <div class="col-md-6 d-flex align-items-center gap-2">
            <a href="{{ route('superuser.penjualan.so_proforma.index') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fa fa-arrow-left mr-1"></i> Back
            </a>
            <span id="calcBadge" class="ok ml-2"><i class="fa fa-check mr-1"></i>Sudah dihitung</span>
            <small id="calcCount" class="text-muted ml-1"></small>
          </div>
          <div class="col-md-6 text-right">
            <button type="button" class="btn btn-sm btn-warning font-weight-bold" id="btn_call">
              <i class="fas fa-calculator pr-1" aria-hidden="true"></i> Hitung Ulang
            </button>
            <button type="submit" class="btn btn-sm btn-primary font-weight-bold" id="btn_save">
                <i class="fa fa-save pr-1" aria-hidden="true"></i> Save
            </button>
          </div>
          </div>
        </div>
      </div>
      </div>
    </div>
</form>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2()

    // ── Dirty tracking + localStorage counter: wajib Hitung Ulang sebelum Save ──
    var PF_ID = '{{ $results->id }}';
    var LS_KEY = 'pfcalc_' + PF_ID;
    var suppressDirty = true; // true selama auto-kalkulasi awal
    function pfGet() {
      try {
        var raw = localStorage.getItem(LS_KEY);
        if (raw) { var o = JSON.parse(raw); return { dirty: o.dirty|0, calc: o.calc|0 }; }
      } catch (e) {}
      return { dirty: 0, calc: 0 };
    }
    function pfSet(s) { try { localStorage.setItem(LS_KEY, JSON.stringify(s)); } catch (e) {} }
    function refreshBadge() {
      var s = pfGet();
      var need = s.dirty > s.calc;
      var badge = $('#calcBadge');
      if (need) {
        badge.removeClass('ok').addClass('need')
          .html('<i class="fa fa-exclamation-triangle mr-1"></i>Belum dihitung (' + (s.dirty - s.calc) + ' perubahan)');
        $('#btn_call').addClass('need');
      } else {
        badge.removeClass('need').addClass('ok')
          .html('<i class="fa fa-check mr-1"></i>Sudah dihitung');
        $('#btn_call').removeClass('need');
      }
      $('#calcCount').text(s.dirty + ' perubahan • ' + s.calc + 'x dihitung');
    }
    function markDirty() {
      if (suppressDirty) return;
      var s = pfGet(); s.dirty++; pfSet(s); refreshBadge();
    }
    function markCalculated() {
      var s = pfGet(); s.calc = s.dirty; pfSet(s); refreshBadge();
    }

    $('#customer_region').on('change', function(){
        let prov_id = $('#customer_region').val();
          
        $.ajax({
            type : 'POST',
            url : '{{route('superuser.master.customer.getkabupaten')}}',
            data : {prov_id:prov_id},
            cache : false,

            success: function(msg){
              $('#customer_city').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
        })
    })

    $('#customer_city').on('change', function(){
      let city_id = $('#customer_city').val();
        
      $.ajax({
        type : 'POST',
        url : '{{route('superuser.master.customer.getkecamatan')}}',
        data : {city_id:city_id},
        cache : false,

        success: function(msg){
          $('#kecamatan').html(msg);
        },
        error : function(data){
          console.log('error:',data)
        },
      });
    });

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'sku', orderable: false, width: "40%"},
          {name: 'price', orderable: false, searcable: false, width: "12%"},
          {name: 'qty', orderable: false, searcable: false, width: "12%"},
          {name: 'disc_usd', orderable: false, searcable: false, width: "12%"},
          {name: 'total', orderable: false, searcable: false, width: "24%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1000;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#so_brand_name').val()) {
        table.row.add([
                      counter,
                      '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select><input type="hidden" name="free_product[]" value="0">',
                      '<input type="number" style="text-align: center;" class="form-control" name="price[]" readonly required>',
                      '<input type="number" style="text-align: center;" class="form-control" name="qty[]" required step="0.01" min="0"><input type="hidden" class="form-control packaging" name="packaging[]"><input type="hidden" class="form-control" name="edit[]" value="">',
                      '<input type="number" style="text-align: center;" class="form-control" name="disc_usd[]" value="0" required>',
                      '<input type="text" style="text-align: center;" class="form-control" name="total[]" readonly>'
                    ]).draw( false );
                    initailizeSelect2();
        counter++;
        markDirty();
      }
    });

    function initailizeSelect2(){
      $(".js-ajax").select2({
        ajax: {
          url: '{{ route('superuser.penjualan.so_proforma.search_sku') }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
                q: params.term,
                id: $('#so_brand_name').val(),
                _token: "{{csrf_token()}}"
            };
          },
          cache: true
        },
      });

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.name;
        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('input[name="qty[]"]').removeAttr('readonly');

        var $row = $(this).parents('tr');
        // Baku: baris baru selalu non-free (free hanya via Kembalikan)
        $row.find('input[name="price[]"]').val(e.params.data.product_price);

        var kemasan = e.params.data.IdKemasan;
        $row.find('input[name="packaging[]"]').val(kemasan);
        markDirty();
      });

    };

    // ============================================================
    // Helper format - dicontek langsung dari create_lanjutan.blade.php
    // supaya kelakuannya sama persis di seluruh app.
    // ============================================================
    function formatNumber(angka) {
      var num = parseFloat(String(angka));
      if (isNaN(num)) return '';
      return num.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function formatInputKurs(inputValue) {
      // pisahkan bagian desimal (setelah koma terakhir) dari bagian bulat
      var parts = String(inputValue).split(',');
      var integerPart = parts[0].replace(/[^\d]/g, '');
      var decimalPart = parts.length > 1 ? parts[1].replace(/[^\d]/g, '').substring(0, 2) : '';

      if (!integerPart) return '';

      integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

      return parts.length > 1 ? integerPart + ',' + decimalPart : integerPart;
    }

    // Ambil angka bersih dari field yang sudah diformat titik ribuan
    function clean(val) {
      if (val === null || val === undefined || val === '') return 0;
      var s = String(val).replace(/\./g, '').replace(',', '.');
      var n = parseFloat(s);
      return isNaN(n) ? 0 : n;
    }

    $(document).on('input', '#idr_rate_display', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      this.setSelectionRange(newPos, newPos);

      // Sinkron ke hidden field (angka bersih tanpa titik)
      $('#idr_rate').val(this.value.replace(/\./g, ''));

      // Trigger ulang kalkulasi semua baris supaya total ikut update pakai kurs baru
      $('input[name="qty[]"]').each(function () {
        var $row = $(this).parents('tr');
        recalcRow($row);
      });
    });

    function recalcRow($row) {
      var price = parseFloat($row.find('input[name="price[]"]').val()) || 0;
      var qty = parseFloat($row.find('input[name="qty[]"]').val()) || 0;
      var discUsd = parseFloat($row.find('input[name="disc_usd[]"]').val()) || 0;
      var kurs = clean($('#idr_rate').val());

      var total = ((price - discUsd) * qty) * kurs;

      $row.find('input[name="total[]"]').val(formatNumber(total));
      $row.find('input[name="total[]"]').change();
    }

    $('#datatable tbody').on('keyup', 'input[name="qty[]"]', function (e) {
      recalcRow($(this).parents('tr'));
    });

    $('#datatable tbody').on('keyup', 'input[name="disc_usd[]"]', function (e) {
      recalcRow($(this).parents('tr'));
    });

    $('#datatable tbody').on('change', 'input[name="total[]"]', function (e) {
      var subtotal = 0;
      $('input[name="total[]"]').each(function () {
        subtotal += clean($(this).val());
      });
      $('#subtotal').val(formatNumber(subtotal));

      grandtotal();
    });

    // Baku: tanpa hapus baris & tanpa free di layar edit.
    // Hapus/ubah free hanya via Kembalikan (pengajuan proforma / customer existing).
    // Handler .row-delete & .input-gift sengaja dihapus.

    // ==========================================
    // STEP 1: HITUNG DISC AGEN
    // ==========================================
    function hitungDiscAgen() {
      var discPercent = parseFloat($('#disc_agen_percent').val()) || 0;
      var subtotal = clean($('#subtotal').val());
      var result = (subtotal * discPercent) / 100;
      $('#disc_agen_idr').val(formatNumber(result));
      // Chain: setelah disc agen, hitung disc kemasan
      hitungDiscKemasan();
    }

    // ==========================================
    // STEP 2: HITUNG DISC KEMASAN
    // ==========================================
    function hitungDiscKemasan() {
      var percentVal = $('#disc_kemasan_percent').val();
      if (percentVal !== '' && percentVal !== '0') {
        var subtotal = clean($('#subtotal').val());
        var discAgen = clean($('#disc_agen_idr').val());
        var subAfterDiscAgen = subtotal - discAgen;
        var amount = (subAfterDiscAgen * parseFloat(percentVal)) / 100;
        $('#disc_kemasan_idr').val(formatNumber(amount));
      } else {
        $('#disc_kemasan_idr').val(formatNumber(0));
      }
      // Chain: setelah disc kemasan, hitung grand total
      hitungGrandTotal();
    }

    // ==========================================
    // STEP 3: HITUNG GRAND TOTAL
    // ==========================================
    function hitungGrandTotal() {
      var subtotal = clean($('#subtotal').val());
      var discAgen = clean($('#disc_agen_idr').val());
      var discKemasan = clean($('#disc_kemasan_idr').val());
      var discIdr = clean($('#disc_tambahan_idr').val());
      var voucher = clean($('#voucher_idr').val());
      var ongkir = clean($('#delivery_cost_idr').val());
      var grandTotal = subtotal - discAgen - discKemasan - discIdr - voucher + ongkir;
      $('#grand_total').val(formatNumber(grandTotal));
    }

    // ==========================================
    // EVENT LISTENERS - Live Update + tandai kotor
    // ==========================================
    $('#disc_agen_percent').on('keyup change', function() {
      hitungDiscAgen(); markDirty();
    });

    $('#disc_kemasan_percent').on('keyup change input', function() {
      hitungDiscKemasan(); markDirty();
    });

    $('#disc_tambahan_idr').on('keyup', function() {
      hitungGrandTotal(); markDirty();
    });

    $('#voucher_idr').on('keyup', function() {
      hitungGrandTotal(); markDirty();
    });

    $('#delivery_cost_idr').on('keyup', function() {
      hitungGrandTotal(); markDirty();
    });

    // Format input currency otomatis
    $(document).on('input', '#disc_tambahan_idr, #voucher_idr, #delivery_cost_idr', function() {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      if (this.selectionStart) {
        this.setSelectionRange(newPos, newPos);
      }
      hitungGrandTotal(); markDirty();
    });

    // Perubahan qty/disc/kurs/qty-row = kotor (live hitung tetap jalan agar angka tidak basi)
    $(document).on('input change', 'input[name="qty[]"], input[name="disc_usd[]"], #idr_rate_display', function() {
      markDirty();
    });
    $('#datatable').on('change', 'select[name="sku[]"], input[name="price[]"]', function() { markDirty(); });

    // ==========================================
    // TOMBOL HITUNG ULANG (wajib sebelum Save)
    // ==========================================
    $(document).on('click', '#btn_call', function(e) {
      e.preventDefault();
      // Hitung ulang semua baris dulu (mandiri), lalu rantai diskon
      $('input[name="qty[]"]').each(function () { recalcRow($(this).parents('tr')); });
      hitungDiscAgen();
      markCalculated();
      Swal.fire({ icon: 'success', title: 'Sudah dihitung ulang', timer: 1200, showConfirmButton: false });
    });

    // Legacy function name - panggil hitungDiscAgen
    function grandtotal() {
      hitungDiscAgen();
    }

    // Wajib Hitung Ulang sebelum Save: cegat klik Save bila masih kotor
    $(document).on('click', '#btn_save', function(e) {
      var s = pfGet();
      if (s.dirty > s.calc) {
        e.preventDefault(); e.stopImmediatePropagation();
        Swal.fire({
          icon: 'warning',
          title: 'Wajib Hitung Ulang dulu',
          html: 'Ada <b>' + (s.dirty - s.calc) + ' perubahan</b> (qty / diskon / kurs / ongkir) yang belum dihitung.<br>Klik <b>Hitung Ulang</b> sebelum Save.',
          confirmButtonText: 'Mengerti',
          confirmButtonColor: '#f59e0b'
        });
        $('#btn_call').focus();
        return false;
      }
      // Bersihkan counter setelah save sukses (form.js akan ajax; reset di sini agar tidak menumpuk)
      // (tidak reset dirty di sini — biarkan sampai response sukses; form.js me-reload/redirect)
    });
    // Pengaman ganda bila submit via Enter: cegat di level form (capture)
    document.querySelector('form.ajax').addEventListener('submit', function(e) {
      var s = pfGet();
      if (s.dirty > s.calc) {
        e.preventDefault(); e.stopImmediatePropagation();
        Swal.fire({
          icon: 'warning', title: 'Wajib Hitung Ulang dulu',
          html: 'Ada <b>' + (s.dirty - s.calc) + ' perubahan</b> belum dihitung.',
          confirmButtonText: 'Mengerti', confirmButtonColor: '#f59e0b'
        });
        return false;
      }
      try { localStorage.removeItem(LS_KEY); } catch (err) {}
    }, true);

    // Auto-hitung mandiri saat halaman dibuka: hitung semua baris + rantai diskon, lalu tandai bersih
    suppressDirty = true;
    $('input[name="qty[]"]').each(function () { recalcRow($(this).parents('tr')); });
    hitungDiscAgen();
    (function(){ var s = pfGet(); s.calc = s.dirty; pfSet(s); refreshBadge(); suppressDirty = false; })();
  });
</script>
@endpush