jQuery(document).ready(function($) {
	$(document).on('submit', '#clientes-form', function(event) {
		event.preventDefault();

		$form = $(this);

		$callback = function($data){
			//$.showNotify($data.title, $data.message, $data.status);
			$.modalMessage($data.title, $data.message, $data.status, true);
			if($data.status == 'success'){
				if($form.hasClass('success__clear-form'))
					$.clearForm($form);
			}
		}

		$.sendFormAjax($form, $callback);
	});
});