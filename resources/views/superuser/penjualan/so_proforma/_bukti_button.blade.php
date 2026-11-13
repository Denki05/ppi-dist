@php $jumlahBuktiChat = count(optional($row->salesOrder)->buktiList() ?? []); @endphp
@if($jumlahBuktiChat > 0)
<button type="button" class="btn btn-sm btn-circle btn-alt-info"
        onclick="showBuktiChat({{ $row->id }}, {{ $jumlahBuktiChat }})"
        title="Lihat bukti chat AO ({{ $jumlahBuktiChat }} foto)" aria-label="Lihat bukti chat {{ $row->code }}">
    <i class="fa fa-picture-o"></i>
</button>
@endif
