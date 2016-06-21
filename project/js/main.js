jQuery(document).ready(function($) {
    $('.show__collapse').hover(function() {
        $($(this).attr('data-collapse')).addClass('active');
    }, function() {
        $($(this).attr('data-collapse')).removeClass('active');
    });
    $('.show__collapse').on('click', function(event) {
        //event.preventDefault();
        $(this).hover();
    });

    $('.has__submenu .item').on('click', function(event) {
        event.preventDefault();

        $menuItem = $(this).parent();
        if($menuItem.hasClass('submenuact')){
            $menuItem.removeClass('submenuact');
            $menuItem.find('.submenu').slideUp('slow');
        }
        else{
            $('.has__submenu.submenuact')
                .removeClass('submenuact')
                .find('.submenu').slideUp('slow');

            $menuItem.addClass('submenuact');
            $menuItem.find('.submenu').slideDown('slow');
        }
    });



    $(document).on('click', '*[data-modal]', function(event) {
        event.preventDefault();
        $link = $(this);
        if($(this).attr('data-modal') == 'logout-modal'){
            $.modalConfirm(
                'Logout Confirmation',
                '¿Seguro que desea cerrar la sesión de su sistema SIADCAR?',
                'warning',
                function(){
                    window.location = $link.attr('href');
                }
            );
        }
    });
});



$.sendFormAjax = function($form, $callback){
    $.showLoading();

    $form.find('.form-ajax__control__disable').prop("disabled", false);

    $formHasImage = ($form.is('[enctype]') && $form.attr('enctype') == 'multipart/form-data');
    $data = ($formHasImage)?(new FormData($form[0])):$form.serialize();
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        dataType: 'json',
        data: $data,
        processData: ($formHasImage)?false:true,
        contentType: ($formHasImage)?false:'application/x-www-form-urlencoded; charset=UTF-8',
    })
    .done($callback)
    .fail(function() {
        $.showNotify('Error', 'Ocurrio un error durante la conexión con el servidor. Intente mas tarde!!!', 'error');
    })
    .always(function() {
        $form.find('.form-ajax__control__disable').prop("disabled", true);
        $.hiddenLoading();
    });
}
$.clearForm = function($form){
    $form[0].reset();
    $form.find('select').each(function(index, el) {
        $option = $(this).find('option:first-child').val();
        $(this).val($option);
        $(this).change();
    });
}