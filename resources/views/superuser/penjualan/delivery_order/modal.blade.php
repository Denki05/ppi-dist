<div class="modal fade" id="modalOtherCost" tabindex="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Adding Cost</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmSent" action="{{route('superuser.penjualan.delivery_order.sent')}}" method="post" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="do_id" value="">
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <!-- <button type="button" class="btn btn-info btn-add-cost mb-10"><i class="fa fa-plus"></i> Add</button> -->
            <div class="table-responsive">
              <div class="form-group row">
                <label class="col-md-2 col-form-label text-right" for="name">Delivery Cost(IDR)</label>
                <div class="col-md-4">
                  <input type="text" class="form-control" value="{{strlen($result->do_cost->delivery_cost_note) > 0 ? $result->do_cost->delivery_cost_note : ($result->ekspedisi ? $result->ekspedisi->name : '')}}" name="delivery_cost_note">
                </div>
                <div class="col-md-4">
                  <input type="number" class="form-control" value="{{$result->do_cost->delivery_cost_idr ?? 0}}" name="delivery_cost_idr" step="any">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-md-2 col-form-label text-right" for="name">Other Cost(IDR)</label>
                <div class="col-md-4">
                  <input type="text" class="form-control" value="{{$result->do_cost->other_cost_note ?? ''}}" name="other_cost_note">
                </div>
                <div class="col-md-4">
                  <input type="number" class="form-control" value="{{$result->do_cost->other_cost_idr ?? 0}}" name="other_cost_idr" step="any">
                </div>
              </div>
              <!-- <table class="table table-striped table-bordered">
                <thead>
                  <th>Note</th>
                  <th>Cost(IDR)</th>
                  <th>Action</th>
                </thead>
                <tbody>
                  <tr class="repeater0">
                    <td>
                      <input type="text" name="repeater[0][note]" class="form-control note">
                    </td>
                    <td>
                      <input type="text" name="repeater[0][cost_idr]" class="form-control cost_idr">
                    </td>
                    <td>
                      -
                    </td>
                  </tr>
                </tbody>
              </table> -->
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
      </div>
      </form>
    </div>
  </div>
</div>