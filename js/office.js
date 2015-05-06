$(function(){
    init_office_menu();
    init_auto_complete();
    init_datepicker();
    init_dayoff();


    function init_dayoff() {
        var $group_form= $("form.group");
        if ( $group_form.length ) {
            $group_form.submit(function(){
                if ( $("[name='dayoff']").val() ) {
                    //alert("WARNING: Day-off has a date. Please add or remove it.")
                    //return false;
                    $(".add-dayoff").click();
                    return false;
                }
                return true;
            });
            $(".add-dayoff").click(function(){
                var group_id = $group_form.find("[name='group_id']").val();
                var qs = {"call": "dayoff", "group_id": group_id, "dayoff": $("[name='dayoff']").val(), "reason": $("[name='reason']").val()};
                office_api(qs, function(re){
                    if ( re.error ) {

                    }
                    else {
                        var html = '<div class="row">';
                        html += '<span class="date">' + re.date + '</span>';
                        html += '<span class="reason">'+ re.reason +'</span>';
                        html += '<span class="date">[ Delete ]</span>';
                        html += '</div>';
                        $group_form.find('.dayoffs .rows').prepend(html);
                        $("[name='dayoff']").val('');
                        $("[name='reason']").val('');
                    }
                    $group_form.find('.dayoff-message').html(re.message).show();
                });
            });
            $('body').on('click', '.row .delete', function(){
                var group_id = $group_form.find("[name='group_id']").val();
                var dayoff = $(this).parent().children('.date').text();
                var qs = {"call": "dayoff_delete", "group_id": group_id, "dayoff": dayoff};
                office_api(qs, function(re){
                    console.log(re);
                    $group_form.find('.date:contains("'+re.date+'")').parent().remove();
                    $group_form.find('.dayoff-message').html(re.message).show();
                });
            });
        }

    }



    function init_office_menu() {
        button().click(function(){
            var selector = $(this).attr('selector');
            $(selector).slideToggle();
        });
    }


    function button() {
        return $('.button[role="show-sub-menu"]');
    }

    function init_auto_complete() {
        var $auto = $("input[data-autocomplete-url]");
        if ( $auto.length ) {
            $auto.each(function(){
                var $this = $(this);
                $this.autocomplete({
                    source: $this.attr('data-autocomplete-url'),
                    minLength: 2,
                    select: function(event, ui) {
                        trace("name:" + $this.prop('name'));
                    }
                });
            });
        }
    }

    function init_datepicker() {
        $( "#datepicker" ).datepicker({
            dateFormat: "yy-mm-dd",
            showOtherMonths: true,
            selectOtherMonths: true,
            changeMonth: true,
            changeYear: true
        });
    }
});




/**
 * ajax api call for portal
 *
 * @param qs - query string or POST data
 * @param callback_function
 *
 * @code
 var qs = {};
 qs.method = 'scheduleTable';
 qs.teacher = teacher;
 ajax_api ( qs, function( re ) { } );

 * @Attention This method saved the returned-data from server into Web Storage IF qs.cache is set to true.
 *
 *  - and it uses the stored data to display on the web browser,
 *  - after that, it continues loading data from server
 *  - when it got new data from server, it display onto web browser and update the storage.
 *
 */
function office_api( qs, callback_function )
{
    var method = qs.method;
    var debug = true;
    trace("office_api for : " + method + " > begins ...");



    var o = {};
    o.data = qs;
    o.url = "/office/api";
    o.type = "POST";

    console.log( o );

    /**
     * TEST - set sys.debug to true if you want to test on ajax_load
     */
    if ( debug ) {
        o.type = 'GET';
        if ( o.data ) {
            qs = $.param( o.data );
            o.url += '?' + qs;
            delete( o.data );
            console.log("url : " + o.url );
        }
        else {
            console.log(" no data :");
            console.log(o);
        }
    }
    /* EO TEST */

    var promise = $.ajax( o );
    promise.done( function( o ) {
        if ( typeof o == 'string' ) {
            console.log( o );
        }
        else {
            trace(method + ' > promise done with no error');
            callback_function( o );
        }
    });

    promise.fail( function( re ) {
        console.log("office_api() : promise failed...");
        console.log(re);
    });
}