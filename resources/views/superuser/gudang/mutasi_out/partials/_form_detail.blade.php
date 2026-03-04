<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-semibold mb-0">Detail Barang</h6>
            <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    id="addRow">
                + Tambah Item
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 detail-table"
                   id="detailTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:65%">Produk</th>
                        <th style="width:20%" class="text-center">Qty</th>
                        <th style="width:15%" class="text-center"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

@include('superuser.gudang.mutasi_out.partials._row_template')