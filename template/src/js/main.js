jQuery(document).ready(function($) {
    $('.show__collapse').hover(function() {
        $($(this).attr('data-collapse')).addClass('active');
    }, function() {
        $($(this).attr('data-collapse')).removeClass('active');
    });
    $('.show__collapse').on('click', function(event) {
        event.preventDefault();
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
});