<div class="modal fade" id="modalSelectWarehouse" tabindex="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Select Warehouse</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmSubmit" method="get" action="{{route('superuser.gudang.stock_adjustment.create')}}">
      @csrf
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label id="select_do">Select Warehouse<span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="warehouse_id" id="select_warehouse" style="width: 100%;">
                @foreach($warehouse as $index => $row)
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