<div class="modal fade" id="modal-manage" tabindex="-1" role="dialog" aria-labelledby="modal-manage" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Manage</h3>
        </div>
        <div class="block-content pb-20">
          <div class="row">
            <div class="col-md-6">
              <span class="font-size-h5">Import</span>
              <p>
                Import your data with the template provided below.<br>
                <span class="text-danger"><b>Don't</b></span> remove / change the header (first row).<br>
                Only fill in the column provided, the additional columns will not be processed.
              </p>
              @if(isset($import_custom_message))
              <div class="mb-15">
                <b>Note :</b> <br>
                {!! $import_custom_message !!}
              </div>
              @endif
              <a href="{{ $import_template_url ?? '' }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-download mr-5"></i> Template
                </button>
              </a>
              <hr>
              <form action="{{ $import_url ?? '' }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="import_file" name="import_file" data-toggle="custom-file-input" required>
                  <label class="custom-file-label" for="import_file">Choose file</label>
                </div>
                
                <button type="submit" class="btn mt-10 w-100 btn-alt-primary">Import</button>
              </form>
            </div>
            <div class="col-md-6">
              <span class="font-size-h5">Export</span>
              <p>Export this data to excel-like format</p>
              <a href="{{ $export_url ?? '' }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-file-excel-o mr-5"></i> Export
                </button>
              </a>
            </div>
          </div>
        </div>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-alt-secondary" data-dismiss="modal">
          <i class="fa fa-close"></i>
        </button>
      </div>
    </div>
  </div>
</div>