<div id="modalBuktiChat" aria-hidden="true"
     style="display:none;position:fixed;top:0;right:0;bottom:0;left:0;z-index:2000;background:rgba(0,0,0,.6);padding:16px;overflow:auto;">
    <div id="modalBuktiChatDialog" role="dialog" aria-modal="true" aria-labelledby="modalBuktiChatLabel"
         style="position:relative;margin:auto;width:100%;max-width:1100px;background:#fff;border-radius:8px;box-shadow:0 12px 40px rgba(0,0,0,.35);display:flex;flex-direction:column;max-height:calc(100vh - 32px);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e9ecef;">
            <h5 class="mb-0" id="modalBuktiChatLabel" style="font-size:18px;">
                Bukti Chat AO
                <small id="buktiChatCounter" class="text-muted ml-1" style="font-size:13px;"></small>
            </h5>
            <button type="button" id="buktiChatClose" aria-label="Tutup"
                    style="border:0;background:transparent;font-size:26px;line-height:1;color:#6c757d;cursor:pointer;padding:0 4px;">&times;</button>
        </div>
        <div style="padding:12px 16px;overflow:auto;">
            <div class="text-center" style="background:#f8f9fa;border-radius:8px;padding:8px;">
                <a id="buktiChatMainLink" href="#" target="_blank" rel="noopener" title="Buka ukuran penuh">
                    <img id="buktiChatMain" src="" alt="Bukti chat"
                         style="max-width:100%;max-height:65vh;object-fit:contain;border-radius:6px;background:#fff;cursor:zoom-in;">
                </a>
            </div>
            <div id="buktiChatThumbs" class="d-flex flex-wrap justify-content-center mt-3"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    if (window.showBuktiChat) return;

    var buktiUrlTpl = {!! json_encode(route('superuser.penjualan.so_proforma.bukti', ['id' => '__ID__', 'index' => '__IDX__'])) !!};
    var urls = [];
    var box = document.getElementById('modalBuktiChat');

    function openModal() {
        box.style.display = 'flex';
        box.style.alignItems = 'center';
        box.style.justifyContent = 'center';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        box.style.display = 'none';
        document.body.style.overflow = '';
    }

    function render(index) {
        var main = document.getElementById('buktiChatMain');
        var link = document.getElementById('buktiChatMainLink');
        var counter = document.getElementById('buktiChatCounter');
        var thumbs = document.getElementById('buktiChatThumbs');
        if (!main || !urls.length) return;

        main.src = urls[index];
        link.href = urls[index];
        counter.textContent = urls.length > 1 ? '(' + (index + 1) + ' / ' + urls.length + ')' : '';

        if (thumbs) {
            var items = thumbs.querySelectorAll('img[data-index]');
            for (var t = 0; t < items.length; t++) {
                var active = parseInt(items[t].getAttribute('data-index'), 10) === index;
                items[t].style.border = active ? '2px solid #39f' : '1px solid #dee2e6';
                items[t].style.opacity = active ? '1' : '.6';
            }
        }
    }

    window.showBuktiChat = function (id, count) {
        var thumbs = document.getElementById('buktiChatThumbs');
        urls = [];
        if (thumbs) thumbs.innerHTML = '';

        for (var i = 0; i < count; i++) {
            urls.push(buktiUrlTpl.replace('__ID__', id).replace('__IDX__', i));
        }

        if (thumbs && count > 1) {
            urls.forEach(function (url, i) {
                var img = document.createElement('img');
                img.src = url;
                img.setAttribute('data-index', i);
                img.alt = 'Bukti chat ' + (i + 1);
                img.style.cssText = 'width:78px;height:78px;object-fit:cover;border-radius:6px;margin:2px;cursor:pointer;background:#fff;';
                img.addEventListener('click', function () { render(i); });
                thumbs.appendChild(img);
            });
        }

        render(0);
        openModal();
    };

    document.getElementById('buktiChatClose').addEventListener('click', closeModal);

    box.addEventListener('click', function (e) {
        if (e.target === box) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && box.style.display === 'flex') closeModal();
    });
})();
</script>
@endpush
