$(function(){
    init_office_menu();



    function init_office_menu() {
        button().click(function(){
            var selector = $(this).attr('selector');
            $(selector).slideToggle();
        });
    }


    function button() {
        return $('.button[role="show-sub-menu"]');
    }
});