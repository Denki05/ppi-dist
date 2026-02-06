<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- STEP TITLE --}}
        <div class="alert alert-secondary py-2 mb-3">
            <strong>Step 1:</strong> Checklist produk
        </div>

        <form id="formStep1">
            @csrf
            <input type="hidden" name="mutasi_id" value="{{ $mutasi->id }}">

            <div class="table-responsive">
                <table class="table table-sm mb-3">
                    <thead>
                        <tr>
                            <th width="30"></th>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mutasi->details as $d)
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           name="items[]"
                                           value="{{ $d->id }}"
                                           {{ $d->is_checked ? 'checked' : '' }}>
                                </td>
                                <td>
                                    {{ $d->product_packaging->product->code }}
                                    - {{ $d->product_packaging->product->name }}
                                </td>
                                <td class="text-end">
                                    {{ $d->qty }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ACTION --}}
            <div class="d-flex justify-content-end gap-2">
                <!--<button type="button" class="btn btn-secondary" id="cancelStep1">-->
                <!--    Cancel-->
                <!--</button>-->
                <button type="submit" class="btn btn-primary">
                    Save & Lanjut
                </button>
            </div>
        </form>

    </div>
</div>