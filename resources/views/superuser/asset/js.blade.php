<script src="{{ asset('superuser_assets/js/codebase.core.min.js') }}"></script>
<script src="{{ asset('superuser_assets/js/codebase.app.min.js') }}"></script>

<!-- new ui -->
<script src="{{ asset('superuser_assets/new_ui/vendors/base/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/js/template.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/vendors/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/vendors/progressbar.js/progressbar.min.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/vendors/chartjs-plugin-datalabels/chartjs-plugin-datalabels.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/vendors/justgage/raphael-2.1.4.min.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/vendors/justgage/justgage.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/js/jquery.cookie.js') }}"></script>
<script src="{{ asset('superuser_assets/new_ui/js/dashboard.js') }}"></script>
<script src="{{ asset('utility/system.js') }}"></script>
<script src="{{ asset('superuser_assets/summernote/summernote.js') }}"></script>

<!-- Multi Tab -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"></script>

<!-- Multi form -->
<script src="{{ asset('superuser_assets/js/multi.tab.js') }}"></script>
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