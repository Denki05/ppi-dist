<div class="modal fade" id="modalSelectCustomer" tabindex="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Select Customer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmSubmit" method="get" action="{{route('superuser.finance.payable.create')}}">
      @csrf
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label id="select_do">Select Customer<span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="customer_id" id="select_customer" style="width: 100%;">
                @foreach($customer as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
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