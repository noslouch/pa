;(function(global, $){
    //es5 strict mode
    "use strict";

    var Updater = global.Updater = global.Updater || {};

    // internal
    var _wrap = {};
    var _internal = {};

    // ----------------------------------------------------------------------

    Updater.settingsInit = function(){
        if (!document.getElementById('updater-settings')) return;

        _wrap = Updater.Wrap;

        // File Transfer Toggle
        _wrap.delegate('.js-togglefiletransfer', 'click', toggleFileTransferOptions);
        toggleFileTransferOptions();

        // Browse Path Map
        _wrap.delegate('.pathmap .browse', 'click', browsePathMap);

        // Login Check
        var login_check_timeout = 0;
        _wrap.delegate('.js-ftp, .js-sftp', 'keyup', function(){
            // Clear the timeout
            clearTimeout(login_check_timeout);

            // Trigger a new drawing
            login_check_timeout = setTimeout(function(){
                checkLogin();
            }, 500);
        });

        _wrap.delegate('.retest', 'click', checkLogin);

        getServerInfo();
    };

    // ----------------------------------------------------------------------

    function checkLogin(){

        // Serialize the Settings Form
        var inputs = _wrap.find('form').serializeArray();

        // Status indicator
        var td = _wrap.find('td.login');
        td.removeClass('login-failed login-success').addClass('login-testing');

        inputs.push({name:'XID', value:EE.XID});

        $.ajax({
            url: Updater.MCP_AJAX_URL+'&task=test_login',
            type: 'POST',
            dataType: 'json',
            data: inputs,
            success: function(data) {
                // Succes or not?
                if (data.success == 'yes') {
                    td.removeClass('login-testing').addClass('login-success');
                } else {
                    td.removeClass('login-testing').addClass('login-failed');
                }
            }
        });
    }

    // ----------------------------------------------------------------------

    function toggleFileTransferOptions(){
        var radio_buttons = _wrap.find('.js-togglefiletransfer');
        var current_value = radio_buttons.find('input:checked').val();
        var parent_div = radio_buttons.closest('.utable');

        parent_div.find('tbody').hide();
        parent_div.find('tbody.js-'+current_value).show();

        if (current_value == 'sftp' || current_value == 'ftp') {
            checkLogin();
        }
    }

    // ----------------------------------------------------------------------

    function browsePathMap(e){
        var modalelem = $('#updater_folder_browse');
        var save_btn = modalelem.find('.btn-primary');
        var mapper = $(e.target).data('map');

        modalelem.data('mapper', mapper);

        // Serialize the Settings Form (we might have changed the credentials mid form)
        _internal.inputs = _wrap.find('form').serializeArray();

        // Store it for quick access
        _internal.loading_elem = modalelem.find('.loading');
        _internal.error_elem = modalelem.find('.error');
        _internal.modal = modalelem;

        modalelem.modal({
            backdrop: 'static'
        });

        sendBrowseXhr('chdir', '');

        // Attach the click handler
        if (!save_btn.data('attached')) {
            save_btn.data('attached', true);

            save_btn.click(function(ee){
                ee.preventDefault();
                var value = modalelem.find('.path input').val();
                _wrap.find('.map-'+modalelem.data('mapper')+' input').attr('value', value);
                modalelem.modal('hide');
            });

            modalelem.delegate('.cdup', 'click', function(ee){
                sendBrowseXhr('cdup', modalelem.find('.path input').val());
            });

            modalelem.delegate('.chdir', 'click', function(ee){
                sendBrowseXhr('chdir', modalelem.find('.path input').val() + $(ee.target).html());
            });
        }
    }

    // ----------------------------------------------------------------------

    function sendBrowseXhr(action, path){

        _internal.error_elem.hide();
        _internal.loading_elem.show();

        $.ajax({
            url: Updater.MCP_AJAX_URL+'&task=browse_server',
            type: 'POST',
            dataType: 'json',
            data: {
                settings: _internal.inputs,
                path: path,
                action: action,
                XID: EE.XID
            },
            success: function(data) {
                _internal.loading_elem.hide();

                if (data.success == 'no') {
                    _internal.error_elem.show();
                    return;
                }

                if (data.path) {
                    _internal.modal.find('.path input').attr('value', data.path);
                }

                var html = Updater.Templates.browse_server(data.items);
                _internal.modal.find('.content').html(html);
            }
        });

    }

    // ----------------------------------------------------------------------

    function getServerInfo(){
        $.ajax({
            url: Updater.ACT_URL+'&task=get_server_info',
            dataType: 'json', type: 'POST',
            data: {
                XID: EE.XID
            },
            error: function(){

            },
            success: function(rData){
                Updater.Server = rData.server;

                for (var Path in Updater.Server) {
                    if (_wrap.find('.map-'+Path+' input').val() === '') {
                        _wrap.find('.map-'+Path+' input').attr('value', Updater.Server[Path]);
                    }
                }
            }
        });
    }

    // ----------------------------------------------------------------------

}(window, jQuery));
