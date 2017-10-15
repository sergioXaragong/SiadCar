jQuery(document).ready(function($) {
	$(document).on('submit', '#clientes-form', function(event) {
		event.preventDefault();

		$form = $(this);

		$callback = function($data){
			if($data.status == 'success'){
				$.modalMessage($data.title, $data.message, $data.status, true);
				if($form.hasClass('success__clear-form'))
					$.clearForm($form);
			}
			else
				$.showNotify($data.title, $data.message, $data.status);
		}

		$.sendFormAjax($form, $callback);
	});

	$(document).on('click', '.link__reset__pass', function(event) {
		event.preventDefault();
		$link = $(this);
		$callback = function($data){
			if($data.status == 'success'){
				$.modalMessage($data.title, $data.message, $data.status, true);
			}
			else{
				$.showNotify($data.title, $data.message, $data.status);
			}
		}
		$.goLinkAjax($link, $callback);
	});
});