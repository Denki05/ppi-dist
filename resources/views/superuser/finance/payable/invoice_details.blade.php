<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Data Pesanan {{ $invoice->do->do_code }}</h5>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Tanggal Invoice</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($invoice->do->so->so_date ?? '')->format('d-m-Y') }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Customer</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{$invoice->do->member->name ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Address</label>
          <div class="col-md-8">
            <textarea class="form-control" readonly name="address" rows="1">{{$invoice->do->member->address ?? ''}}</textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">IDR Rate</label>
          <div class="col-md-8">
            <input type="number" class="form-control" value="{{number_format($invoice->do->idr_rate,0,',','.')}}" readonly>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Transaction</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{$invoice->do->type_transaction ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Note</label>
          <div class="col-md-8">
            <textarea class="form-control summernote" name="note" readonly><?= htmlspecialchars_decode($invoice->do->note); ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Item</h5>
        <div class="table table-responsive">
          <table class="table table-striped" id="datatables">
            <thead>
              <th>#</th>
              <th>Code</th>
              <th>Product</th>
              <th>packaging</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Note</th>
            </thead>
            @if(count($invoice->do->do_detail) == 0)
              <tr><td colspan="8" align="center">Data tidak ditemukan</td></tr>
            @endif
            @foreach($invoice->do->do_detail as $row)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{$row->product_pack->code ?? ''}}</td>
                <td>{{$row->product_pack->name ?? ''}}</td>
                <td>{{$row->product_pack->packaging->pack_name ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->price ?? ''}}</td>
                <td>{{$row->note ?? ''}}</td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Cost</h5>
        <div class="row">
          <div class="col-lg-3"></div>
          <?php $subtotal_item = 0; ?> <!-- Initialize subtotal_item -->
          @foreach($invoice->do->do_detail as $row)
            <?php
              $subtotal_item += ceil((($row->price - $row->usd_disc) * $row->qty ) * $row->do->idr_rate);
            ?>
          @endforeach
          <div class="col-lg-9 float-right">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">IDR Subtotal</label>
              <div class="col-md-8">
                <input type="text" name="idr_sub_total" class="form-control" readonly value="{{number_format($subtotal_item ,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Disc %</label>
              <!-- <div class="col-md-8">
                <input type="text" name="discount_1" class="form-control count" value="{{$invoice->do->do_detail_cost[0]->discount_1 ?? 0}}" readonly>
              </div> -->
              <div class="col-2">
                <input type="text" name="discount_1" class="form-control count" value="{{$invoice->do->do_detail_cost[0]->discount_1 ?? 0}}" readonly>
              </div>
              <div class="col-4">
                <input type="text" name="discount_1_idr" class="form-control count" value="{{number_format($invoice->do->do_detail_cost[0]->discount_1_idr ,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Disc Kemasan %</label>
              <div class="col-2">
                <input type="text" name="discount_2" class="form-control count" value="{{$invoice->do->do_detail_cost[0]->discount_2 ?? 0}}" readonly>
              </div>
              <div class="col-4">
                <input type="text" name="discount_2_idr" class="form-control count" value="{{number_format($invoice->do->do_detail_cost[0]->discount_2_idr ,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount IDR</label>
              <div class="col-md-8">
                <input type="text" name="discount_idr" class="form-control count" value="{{number_format($invoice->do->do_detail_cost[0]->discount_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <!-- <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Total Discount (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="discount_total" class="form-control" readonly value="{{number_format($invoice->do->do_cost->total_discount_idr ?? 0,0,',','.')}}">
              </div>
            </div> -->
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">PPN</label>
              <div class="col-md-8">
                <input type="text" name="ppn" class="form-control" readonly value="{{number_format($invoice->do->do_detail_cost[0]->ppn ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Voucher (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="voucher_idr" class="form-control count" value="{{number_format($invoice->do->do_detail_cost[0]->voucher_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <!-- <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Cashback (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="cashback_idr" class="form-control count" value="{{number_format($invoice->do->do_cost->cashback_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div> -->
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Purchase Total (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="purchase_total_idr" class="form-control" readonly value="{{number_format($invoice->do->do_detail_cost[0]->purchase_total_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Delivery Cost (IDR)</label>
              <div class="col-md-4">
                <input type="text" name="delivery_cost_note" class="form-control"  value="{{$invoice->do->do_detail_cost[0]->delivery_cost_note ?? 0}}" readonly>
              </div>
              <div class="col-md-4">
                <input type="text" name="delivery_cost_idr" class="form-control"  value="{{number_format($invoice->do->do_detail_cost[0]->delivery_cost_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Other Cost (IDR)</label>
              <div class="col-md-4">
                <input type="text" name="other_cost_note" class="form-control" value="{{$invoice->do->do_detail_cost[0]->other_cost_note ?? 0}}" readonly>
              </div>
              <div class="col-md-4">
                <input type="text" name="other_cost_idr" class="form-control" value="{{number_format($invoice->do->do_detail_cost[0]->other_cost_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Grand Total (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="grand_total_idr" class="form-control" readonly value="{{number_format($invoice->do->do_detail_cost[0]->grand_total_idr ?? 0,0,',','.')}}">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>