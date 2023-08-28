<script src="{{ asset('public/superuser_assets/js/codebase.core.min.js') }}"></script>
<script src="{{ asset('public/superuser_assets/js/codebase.app.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/bootstrap-slider.min.js" integrity="sha512-f0VlzJbcEB6KiW8ZVtL+5HWPDyW1+nJEjguZ5IVnSQkvZbwBt2RfCBY0CBO1PsMAqxxrG4Di6TfsCPP3ZRwKpA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>


<!-- Plus Admin -->
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.categories.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.fillbetween.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/vendors/flot/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/jquery.cookie.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/misc.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/settings.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/todolist.js') }}"></script>
<script src="{{ asset('public/superuser_assets/plus-admin/assets/js/dashboard.js') }}"></script>

<script src="{{ asset('public/utility/system.js') }}"></script>
<script src="{{ asset('public/superuser_assets/summernote/summernote.js') }}"></script>

<!-- Multi Tab -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"></script>

<!-- Multi form -->
<script src="{{ asset('public/superuser_assets/js/multi.tab.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>



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