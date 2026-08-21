@php $no = 0; @endphp
@foreach($so_progress as $row)
  @if(isset($row->so) && $row->so->type_so == 'nonppn')
    @php $no++; @endphp
    <tr>
        <td class="sop-td-check">
          @if($row->is_kurs_hold && $row->status == 4)
            <input type="checkbox" class="check-kurs-row" value="{{ $row->id }}">
          @endif
        </td>
        <td class="sop-th-num text-muted" data-label="#">{{ $no }}</td>
        <td data-label="Referensi SO"><span class="sop-code">{{ $row->so->so_code }}</span></td>
        <td data-label="DO Code"><span class="sop-code">{{ $row->do_code }}</span></td>
        <td data-label="Customer">
            @if($row->member)
              {{ $row->member->name }} {{ $row->member->text_kota }}
            @else
                <span class="text-danger">-</span>
            @endif
        </td>
        <td data-label="Tanggal Buat" data-sort-value="{{ \Carbon\Carbon::parse($row->created_at)->timestamp }}" class="text-muted">
            {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, H:i') }}
        </td>
        <td data-label="Type"><span class="sop-type">{{ $row->type_transaction }}</span></td>
        <td data-label="Status DO">
            @if($row->void_status == 1)
              <span class="sop-badge sop-badge-danger">
                <i class="sop-dot"></i>Pengajuan Void
              </span>
            @else
              <span class="sop-badge sop-badge-{{ $row->do_status()->class }}">
                <i class="sop-dot"></i>{{ $row->do_status()->msg }}
              </span>
            @endif
        </td>
        <td data-label="Status Kurs">
            @if($row->status < 3)
              <span class="sop-badge sop-badge-secondary">
                <i class="sop-dot"></i>Draft
              </span>
            @elseif($row->is_kurs_hold)
              <span class="sop-badge sop-badge-warning">
                <i class="sop-dot"></i>Belum Valid
              </span>
            @else
              <span class="sop-badge sop-badge-success">
                <i class="sop-dot"></i>Valid
              </span>
            @endif
        </td>
        <td class="text-center sop-td-action">
          <div class="btn-group">
            <button type="button" class="sop-btn-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Aksi">
              <i class="fa fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right sop-dropdown">
              @if($row->void_status == 1)
                <span class="dropdown-item text-muted disabled" style="pointer-events:none;">
                  <i class="fa fa-clock text-warning"></i> Menunggu Approval Void
                </span>
              @else
                @if($row->is_kurs_hold && $row->status == 4)
                  <a href="javascript:void(0)" class="dropdown-item btn-update-kurs" data-id="{{ $row->id }}">
                    <i class="fa fa-money text-warning"></i> Update Kurs
                  </a>
                @endif
                @if(in_array($row->status, [3, 4]))
                  <a href="javascript:void(0)" class="dropdown-item btn-revisi-logistik" data-id="{{ $row->id }}"
                    data-url="{{ route('superuser.penjualan.packing_order.revisi_dari_logistik', $row->id) }}">
                    <i class="fa fa-undo text-danger"></i> Revisi (Tarik dari Logistik)
                  </a>
                @endif
                @if($row->status == 5)
                  <a href="javascript:void(0)" class="dropdown-item btn-ajukan-void" data-id="{{ $row->id }}"
                    data-url="{{ route('superuser.penjualan.packing_order.ajukan_void', $row->id) }}">
                    <i class="fa fa-ban text-danger"></i> Ajukan Void
                  </a>
                @endif
                @if(!($row->is_kurs_hold && $row->status == 4) && !in_array($row->status, [3, 4]) && $row->status != 5)
                  <span class="dropdown-item text-muted disabled" style="pointer-events:none;">
                    Tidak ada aksi tersedia
                  </span>
                @endif
              @endif
            </div>
          </div>
        </td>
    </tr>
  @endif
@endforeach