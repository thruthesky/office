$(function(){
    $(".jbutton.setting").click(function(){
        var text = $(this).text();
        if ( text == 'Show less filters' ) $(this).prev().val('less');
        else $(this).prev().val('more');
        $('.task-search').submit();
    });
    $(".status-all").click(function(){
        $("[name^='status']").prop('checked',true);
    });
    $(".priority-all").click(function(){
        $("[name='priority[]']").prop('checked',true);
    });
    $( ".task-edit #deadline" ).datepicker({
        showOn: "button",
        buttonImage: "/modules/office/img/calendar.png",
        buttonImageOnly: true,
        buttonText: "Select date",
        dateFormat: "yy-mm-dd"
    });
});