<div class="modal fade" id="modalTidakLanjut" tabindex="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">SO tidak dilanjutkan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmTidakLanjutSO" action="#" data-type="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="id" value="{{ $result->id }}" />

          <div class="row">
            <div class="col-12">
              <div class="form-group row">
                <label class="col-md-2 col-form-label text-right" for="keterangan">Alasan tidak lanjut<span class="text-danger">*</span></label>
                <div class="col-md-8">
                  <textarea type="text" name="keterangan" class="form-control"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12 text-center">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a class="btn btn-warning btn-md text-white btn-close-tidak-lanjut-modal"><i class="fa fa-arrow-left"></i> Back</a>
          <button type="submit" class="btn btn-primary">Tidak dilanjutkan</button>
        </div>
      </form>
    </div>
  </div>
</div>