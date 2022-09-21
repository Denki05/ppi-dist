<div class="modal fade" id="modalCustomerInvoice" tabindex="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Customer Invoice</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      @csrf
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <table class="customer-detail">
              <tr>
                <td class="text-left">Nama</td>
                <td>:</td>
                <td class="text-left">{{ $result->member->name }}</td>
              </tr>
              <tr>
                <td class="text-left">Alamat</td>
                <td>:</td>
                <td class="text-left">{{ $result->member->address }}</td>
              </tr>
            </table>
            <table class="customer-outstanding mt-3 mb-3">
              <?php
                $total_outstanding = 0;
                $total_outstanding_past_due_date = 0;
                if(isset($customer_history) && sizeof($customer_history) > 0) {
                  foreach($customer_history as $index => $row) {
                    $total_outstanding += $row->grand_total_idr;
                  }
                }
              ?>
              <tr>
                <td class="text-left">Total Outstanding</td>
                <td>:</td>
                <td class="text-left">{{ number_format($total_outstanding,0,',','.') }}</td>
              </tr>
              <tr>
                <td class="text-left">Total Outstanding Lewat Jatuh Tempo</td>
                <td>:</td>
                <td class="text-left">{{ number_format($total_outstanding,0,',','.') }}</td>
              </tr>
            </table>
          </div>
        </div>

        <hr />

        <div class="row">
          <div class="col-12">
            @if(isset($customer_history) && sizeof($customer_history) > 0)
            <table class="table table-striped table-vcenter table-responsive dataTable no-footer">
              <tr>
                <th class="text-center">No</th>
                <th class="text-center">Invoice No</th>
                <th class="text-center">Product</th>
                <th class="text-right">Total Invoice</th>
                <th class="text-right">Total Payment</th>
                <th class="text-right">Outstanding</th>
              </tr>
              @foreach($customer_history as $index => $row)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->code }}</td>
                <td>
                  <?php
                    if (sizeof($row->do->do_detail) > 0) {
                      foreach($row->do->do_detail as $indexdodetail => $row_do_detail) {
                        echo $row_do_detail->product->name . ' - ' . $packaging_dictionary[$row_do_detail->packaging] . '<br />';
                      }
                    }
                  ?>
                </td>
                <td class="text-right">{{ number_format($row->grand_total_idr,0,',','.') }}</td>
                <td class="text-right">
                  <?php
                    $total_paid = 0;
                    if (sizeof($row->payable_detail) > 0) {
                      foreach($row->payable_detail as $indexpayable => $row_payable) {
                        $total_paid += $row_payable->total;
                      }
                    }
                  ?>{{ number_format($total_paid,0,',','.') }}</td>
                <td class="text-right">{{ number_format(($row->grand_total_idr - $total_paid),0,',','.') }}</td>
              </tr>
              @endforeach
            </table>
            @endif
            @if(isset($customer_history) && sizeof($customer_history) == 0)
            <div class="text-center">Tidak ada data outstanding</div>
            @endif
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>