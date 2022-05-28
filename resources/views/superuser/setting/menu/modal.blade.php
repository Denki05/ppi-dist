<div class="modal" tabindex="-1" role="dialog" id="modalCreate">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Menu</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmCreate">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Name<span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control">
          </div>
          <div class="form-group">
            <label>Route Name<span class="text-danger">*</span></label>
            <input type="text" name="route_name" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="modalEdit">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Menu</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmEdit">
        @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
          <div class="form-group">
            <label>Name<span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control">
          </div>
          <div class="form-group">
            <label>Route Name<span class="text-danger">*</span></label>
            <input type="text" name="route_name" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>