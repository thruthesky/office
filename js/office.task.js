$(function(){
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