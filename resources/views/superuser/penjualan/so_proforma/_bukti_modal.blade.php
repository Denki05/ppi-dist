<div class="modal fade" id="modalBuktiChat" tabindex="-1" role="dialog" aria-labelledby="modalBuktiChatLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBuktiChatLabel">Bukti Chat AO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="buktiChatList" class="d-flex flex-wrap"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var buktiUrlTpl = {!! json_encode(route('superuser.penjualan.so_proforma.bukti', ['id' => '__ID__', 'index' => '__IDX__'])) !!};

    window.showBuktiChat = function (id, count) {
        var wrap = document.getElementById('buktiChatList');
        if (!wrap) return;
        wrap.innerHTML = '';
        for (var i = 0; i < count; i++) {
            var url = buktiUrlTpl.replace('__ID__', id).replace('__IDX__', i);
            var link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.className = 'd-inline-block mr-2 mb-2';
            var img = document.createElement('img');
            img.src = url;
            img.alt = 'Bukti chat ' + (i + 1);
            img.style.cssText = 'width:200px;height:200px;object-fit:cover;border:1px solid #dee2e6;border-radius:6px;background:#fff;';
            link.appendChild(img);
            wrap.appendChild(link);
        }
        if (window.jQuery && window.jQuery('#modalBuktiChat').modal) {
            window.jQuery('#modalBuktiChat').modal('show');
        }
    };
})();
</script>
@endpush
