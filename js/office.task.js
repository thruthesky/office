$(function(){
    $(".status-all").click(function(){
        $("[name^='status']").prop('checked',true);
    });
});