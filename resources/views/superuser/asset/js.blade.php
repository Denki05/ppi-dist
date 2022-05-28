<script src="{{ asset('superuser_assets/js/codebase.core.min.js') }}"></script>
<script src="{{ asset('superuser_assets/js/codebase.app.min.js') }}"></script>
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