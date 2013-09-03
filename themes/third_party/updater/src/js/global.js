;(function(global, $){
    //es5 strict mode
    "use strict";

    var Updater = global.Updater = global.Updater || {};
    Updater.queries = [];

    // ----------------------------------------------------------------------

    Updater.init = function(){

        Updater.Wrap = $('#updater');

        Updater.uploadInit();
        Updater.processInit();
        Updater.settingsInit();

        cleanTempDirs();

        Updater.Wrap.delegate('.js-test_settings', 'click', testSettings);
        Updater.Wrap.delegate('.js-show_error', 'click', showError);

        // Send a test AJAX request
        Updater.TestAJAX(Updater.MCP_AJAX_URL);
        Updater.TestAJAX(Updater.ACT_URL);
    };

    // ----------------------------------------------------------------------

    Updater.generateRandomString = function() {
        return Math.random().toString(36).substring(2);
    };

    // ----------------------------------------------------------------------

    Updater.TestAJAX = function(action_url){
        var test_ajax = $('#test_ajax_error');

        $.ajax({url:action_url+'&task=test_ajax_call',
            dataType: 'json', type: 'POST',
            error: function(xhr, a, b){
                test_ajax.show();
                test_ajax.find('a.url').attr('href', action_url).html(action_url);
                test_ajax.find('.error textarea').html( global.btoa(xhr.responseText) );

                if (xhr.status === 0) {
                    test_ajax.find('.error .inner').html('<strong>Response Code:</strong> ' + xhr.status + '&nbsp;&nbsp;&nbsp;<strong>Response Text</strong>: (Probably Cross-Domain AJAX Error)');
                }
                else if (xhr.status >= 200) {
                    test_ajax.find('.error .inner').html('<strong>Response Code:</strong> ' + xhr.status + '&nbsp;&nbsp;&nbsp;<strong>Response Text</strong>: ' + xhr.statusText);
                }
            },
            success: function(rData){
                if (!rData) test_ajax.show();
            }
        });

    };

    //********************************************************************************* //

    function cleanTempDirs(){
        $.ajax({
            url: Updater.MCP_AJAX_URL+'&task=clean_temp_dirs',
            type: 'POST',
            data: {XID: EE.XID}
        });
    }

    // ----------------------------------------------------------------------

    function testSettings(e){
        e.preventDefault();
        var modal = $('#test_transfer_method');

        modal.css({width:'800px', 'margin-left': function () {
                return -($(this).width() / 2);
        }}).modal().find('.loading').show();
        modal.find('.wrapper').empty();

        $.post(Updater.MCP_AJAX_URL+'&task=test_transfer_method', {}, function(rData){
            modal.find('.loading').hide();
            modal.find('.wrapper').html(rData);
        });
    }

    // ----------------------------------------------------------------------

    function showError(e){
        e.preventDefault();
        var error_log = $('#error_log');
        var html = global.atob( $(e.target).closest('.error').find('textarea').val() ) ;

        error_log.slideDown();
        error_log.find('body').empty();

        $('html, body').stop().animate({
            scrollTop: error_log.offset().top
        }, 1000);

        $('<iframe id="updater_error_iframe" style="width:100%;height:300px"/>').load(function(){
            $('#updater_error_iframe').contents().find('body').append(html);
        }).appendTo(error_log.find('.body'));
    }

    // ----------------------------------------------------------------------

}(window, jQuery));

// ----------------------------------------------------------------------

$(document).ready(function() {
    Updater.init();
});
