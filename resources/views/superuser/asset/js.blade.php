<script src="{{ asset('superuser_assets/js/codebase.core.min.js') }}"></script>
<script src="{{ asset('superuser_assets/js/codebase.app.min.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.categories.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.fillbetween.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/jquery.cookie.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/misc.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/settings.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/todolist.js') }}"></script>
<script src="{{ asset('superuser_assets/plus-admin/assets/js/dashboard.js') }}"></script>
<script src="{{ asset('utility/system.js') }}"></script>
<script src="{{ asset('superuser_assets/summernote/summernote.js') }}"></script>
<script type="text/javascript">

	let csrfname = "_token";
	let csrfhash = "{{csrf_token()}}";

	function ajaxcsrfscript(){
		$.ajaxSetup({
			data : {[csrfname] : csrfhash }
		})
		return;
	}
	function getFormData($form) {
	    var unindexed_array = $form.serializeArray();
	    var indexed_array = {};
	    $.map(unindexed_array, function (n, i) {
	        indexed_array[n['name']] = n['value'];
	    });
	    return indexed_array;
	}
	function showToast($type,$message){
		$.notify({
		  message: '<strong>'+$message+'</strong>' 
		},{
		  type: $type,
		  z_index: 9999999,
		});
	}
</script>
@include('superuser.asset.plugin.notify')