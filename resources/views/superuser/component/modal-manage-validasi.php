<div class="modal fade" id="modal-manage" tabindex="-1" role="dialog" aria-labelledby="modal-manage" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Manage</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content pb-20">
          <div class="row">
            <div class="col-md-6">
              <span class="font-size-h5">Import</span>
                
              
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
    </div>
  </div>
</div>