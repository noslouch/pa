;(function(global, $){
    //es5 strict mode
    "use strict";

    var Updater = global.Updater = global.Updater || {};

    // internal
    var uwrap;

    // Actions
    var actionsQueueElement;
    var actionsQueue = {};
    var currentAction;

    // Backup
    var backupDate = (new Date()).getTime()/1000;
    var backupTables = [];
    var backupDirs = [];
    var backupDbDone = false;
    var backupFilesDone = false;

    // ExpressionEngine Update
    var eeUpdates = [];
    var eeServer;
    var eeCopyDirs = [];
    var eeInstallerUrl = window.location.protocol+'//'+window.location.host+window.location.pathname + '?/dupdater/';

    // ----------------------------------------------------------------------

    Updater.processInit = function(){
        if (!document.getElementById('actions_queue')) return;

        // Store internal references for quick access
        uwrap = Updater.Wrap;
        actionsQueueElement = $('#actions_queue');

        // Backup Toggles
        uwrap.delegate('.backup_db input, .backup_files input', 'click', toggleBackupActions);
        toggleBackupActions();

        uwrap.find('.start_action').click(startActions);

        uwrap.delegate('.queries_exec', 'click', showSqlQueries);
        uwrap.delegate('.js-retrybtn', 'click', retryAction);
    };

    // ----------------------------------------------------------------------

    function toggleBackupActions(force_db, force_files){
        var action, exists;
        var enabled = {
            backup_files: uwrap.find('.backup_files input:checked').val(),
            backup_db: uwrap.find('.backup_db input:checked').val()
        };
        var actions = ['backup_files', 'backup_db'];

        // Force DB Backup?
        if (force_db === true) {
            uwrap.find('.backup_db .js-yes').attr('checked', 'checked');
            enabled.backup_db = 'yes';
        }

        // Force Files Backup?
        if (force_files === true) {
            uwrap.find('.backup_files .js-yes').attr('checked', 'checked');
            enabled.backup_files = 'yes';
        }

        // Loop over our backup options
        for (var i = 0; i < actions.length; i++) {
            exists = false;

            // Does it exists?
            if (actionsQueueElement.find('.type-'+actions[i]).length > 0) exists = true;

            // Does it NOT exists AND is enabled
            if (exists === false && enabled[actions[i]] == 'yes') {
                action = {};
                action.type = actions[i];
                action.updaterAction = 'Backup';

                // Add the forced status if needed
                if (actions[i] == 'backup_db' && force_db === true) {
                    action.status = 'forced';
                }

                if (actions[i] == 'backup_files' && force_files === true) {
                    action.status = 'forced';
                }

                addSingleAction(action);
            }

            // Does it exists AND is disabled?
            else if (exists === true && enabled[actions[i]] == 'no') {
                // Remove it
                actionsQueueElement.find('.type-'+actions[i]).remove();
            }
        }
    }

    // ----------------------------------------------------------------------

    Updater.addProcessAction = function(data){
        var found, action, actions;

        // Loop over all actions found
        for (var i = 0; i < data.found.length; i++) {
            actions = [];
            found = data.found[i];

            // Some types can have multiple actions
            if (found.type == 'addon' || found.type == 'cp_theme' || found.type == 'forum_theme') {

                // Add all actions then
                for (var ii in found.info) {
                    actions.push({
                        type: found.type,
                        info: found.info[ii]
                    });
                }

            } else {
                // Single action then
                actions.push({
                    type: found.type,
                    info: found.info
                });
            }

            // Create the actions object
            for (var iii = 0; iii < actions.length; iii++) {
                action = {};
                action.temp_dir = data.temp_dir;
                action.temp_key = data.temp_key;
                action.type = actions[iii].type;
                action.info = actions[iii].info;
                action.updaterAction = actions[iii].info.updater_action ? actions[iii].info.updater_action : 'update';
                addSingleAction(action);
            }
        }
    };

    // ----------------------------------------------------------------------

    function addSingleAction(obj){
        var html;
        var location = 'after';


        // Standard properties
        obj.id = Updater.generateRandomString();

        // Add the queue status if not defined
        if (typeof(obj.status) == 'undefined') {
            obj.status = 'queued';
        }

        // If it's EE add backup options automatically
        if (obj.type == 'ee') {
            toggleBackupActions(true, true);
        }

        // Backup options are always first
        if (obj.type == 'backup_files' || obj.type == 'backup_db') {
            location = 'before';
        }

        // Generate the row HTML
        html = Updater.Templates.action_row(obj);

        // And hide the no-actions
        actionsQueueElement.find('.js-no_actions').hide();

        if (location == 'after') {

            // If an EE Update is in the list add it before that
            if (actionsQueueElement.find('.type-ee').length > 0) {
                actionsQueueElement.find('.type-ee').before(html);
            } else {
                actionsQueueElement.append(html);
            }

        } else {
            // If it's before, prepend it!
            actionsQueueElement.prepend(html);
        }

        // Remove the disabled state
        if (actionsQueueElement.find('tr.action:not(.type-backup_db,.type-backup_files)').length > 0) {
            uwrap.find('button.start_action').removeAttr('disabled').removeClass('disabled');
        }

        // Add it to our actions queue
        actionsQueue[obj.id] = obj;

        // Initialize sortable
        initSortable();
    }

    // ----------------------------------------------------------------------

    function updateCurrentAction() {
        var html = Updater.Templates.action_row(currentAction);
        $('#'+currentAction.id).replaceWith(html);
        actionsQueueElement.sortable('update');
    }

    // ----------------------------------------------------------------------

    function initSortable() {
        actionsQueueElement.sortable({
            items: '> .action:not(.type-ee)',
            handle: '.move'
        });
    }

    // ----------------------------------------------------------------------

    function startActions() {
        var current, current_id;
        var queued_trs = actionsQueueElement.find('tr.status-forced, tr.status-queued');

        if (queued_trs.length === 0) {
            //console.log('No queued actions found!');
            return false;
        }

        // Get the current action
        current = queued_trs.first();
        current_id = current.attr('id');

        // Does it exists? if not quit
        if (typeof actionsQueue[current_id] == 'undefined') {
            //console.log('Actions object does not exists');
            return false;
        }

        // Triple check the status
        if (actionsQueue[current_id].status != 'queued' && actionsQueue[current_id].status != 'forced') {
            return false;
        }

        // Execute the next step!
        switch (actionsQueue[current_id].type) {
            case 'backup_files':
                startBackupFiles(current_id);
                break;
            case 'backup_db':
                startBackupDb(current_id);
                break;
            case 'addon':
            case 'ee_forum':
            case 'ee_msm':
            case 'cp_theme':
            case 'forum_theme':
                startGeneralAction(current_id);
                break;
            case 'ee':
                eeUpdateInit(current_id);
                break;
        }
    }

    // ----------------------------------------------------------------------

    function startBackupFiles(id) {
        currentAction = actionsQueue[id];
        currentAction.status = 'processing';
        currentAction.loadingMsg = 'Preparing Files Backup';
        updateCurrentAction();

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=backup_files_prepare' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST', data: {
                XID:EE.XID,
                time: backupDate
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){

                // Store the backup dirs
                backupDirs = rData.dirs;

                // Start backup
                backupFilesDirs(0);
            }
        });
    }

    // ----------------------------------------------------------------------

    function backupFilesDirs(index) {
        var percent;

        // If it's the first one, we need to update the action
        if (index === 0) {
            currentAction.progressMsg = '&nbsp;';
            delete currentAction.loadingMsg;
            updateCurrentAction();
        }

        // If the next index is undefined, we are done
        if (typeof(backupDirs[index]) == 'undefined') {
            currentAction.status = 'done';
            delete currentAction.progressMsg;
            updateCurrentAction();

            // Continue with the other actions
            startActions();
            return;
        }

        // What percent is done
        percent = (100/backupDirs.length) * index;
        $('#single_action_progress').css('width', percent+'%');
        $('#single_action_progress').find('.inner').html(backupDirs[index]);

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=backup_files' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action: 'backup',
                dir: backupDirs[index],
                time: backupDate,
                XID:EE.XID
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                // Next on line
                backupFilesDirs((index+1));
            }
        });
    }

    // ----------------------------------------------------------------------

    function startBackupDb(id) {
        currentAction = actionsQueue[id];
        currentAction.status = 'processing';
        currentAction.loadingMsg = 'Preparing Files Backup';
        updateCurrentAction();

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=backup_database_prepare' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST', data: {XID: EE.XID},
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){

                // Store the tables
                backupTables = rData.tables;

                // Start backup!
                backupDbTables(0);
            }
        });
    }

    // ----------------------------------------------------------------------

    function backupDbTables(index) {
        var percent;

        // If it's the first one, we need to update the action
        if (index === 0) {
            currentAction.progressMsg = '&nbsp;';
            delete currentAction.loadingMsg;
            updateCurrentAction();
        }

        // If the next index is undefined, we are done
        if (typeof(backupTables[index]) == 'undefined') {
            currentAction.status = 'done';
            delete currentAction.progressMsg;
            updateCurrentAction();

            // Continue with other actions
            startActions();
            return;
        }

        // What percent is done
        percent = (100/backupTables.length) * index;
        $('#single_action_progress').css('width', percent+'%');
        $('#single_action_progress').find('.inner').html(backupTables[index]);

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=backup_database' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action: 'backup',
                table: backupTables[index],
                time: backupDate,
                XID: EE.XID
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                // Next in line
                backupDbTables((index+1));
            }
        });
    }

    // ----------------------------------------------------------------------

    function startGeneralAction(id) {
        currentAction = actionsQueue[id];
        currentAction.status = 'processing';
        currentAction.loadingMsg = 'Moving Files';
        updateCurrentAction();

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=addon_move_files' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                addon: JSON.stringify(currentAction),
                XID: EE.XID
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                /*
                if (Updater.backup_db_done == false && typeof(rData.force_db_backup) != 'undefined' && rData.force_db_backup == 'yes') {
                    Updater.addon_update = rData.update;
                    Updater.BackupDB(true);
                } else {
                    Updater.InstallAddon(rData.update);
                }
                */

                // Any queries executed?
                if (rData.queries) {
                    for (var i = 0; i < rData.queries.length; i++) {
                        Updater.queries.push(rData.queries[i]+';');
                    }
                }

                if (currentAction.type == 'addon' || currentAction.type == 'ee_forum') {
                    endGeneralAction(id);
                    return;
                }

                currentAction.status = 'done';
                delete currentAction.loadingMsg;
                updateCurrentAction();
                startActions();

                checkQueriesExecuted();
            }
        });
    }

    // ----------------------------------------------------------------------

    function endGeneralAction(id) {

        if (currentAction.updaterAction == 'install') {
            currentAction.loadingMsg = 'Installing...';
        } else {
            currentAction.loadingMsg = 'Updating...';
        }

        updateCurrentAction();

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=addon_install' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                addon: JSON.stringify(currentAction),
                XID: EE.XID
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                // Any queries executed?
                if (rData.queries) {
                    for (var i = 0; i < rData.queries.length; i++) {
                        Updater.queries.push(rData.queries[i]+';');
                    }
                }

                currentAction.status = 'done';
                delete currentAction.loadingMsg;
                updateCurrentAction();
                startActions();

                checkQueriesExecuted();
            }
        });
    }

    // ----------------------------------------------------------------------

    function eeUpdateInit(id) {
        currentAction = actionsQueue[id];
        currentAction.status = 'processing';
        currentAction.loadingMsg = 'Putting site offline...';
        updateCurrentAction();

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=ee_update_init' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action: 'site_offline',
                action_obj: JSON.stringify(currentAction),
                XID: EE.XID
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                eeUpdateCopyInstaller();
            }
        });
    }

    // ----------------------------------------------------------------------

    function eeUpdateCopyInstaller() {
        currentAction.loadingMsg = 'Copying installer files...';
        updateCurrentAction();

        $.ajax({url: Updater.MCP_AJAX_URL+'&task=ee_update_init' + '&cache=' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action: 'copy_installer',
                action_obj: JSON.stringify(currentAction),
                XID: EE.XID
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                eeUpdates = rData.updates;
                eeServer = rData.server;

                // Is this a build update?
                if (eeUpdates.length === 0) {
                    eeUpdateWaitForServer();
                    return;
                }

                eeUpdateWaitForServer();
            }
        });
    }

    // ----------------------------------------------------------------------

    function eeUpdateWaitForServer(times) {
        if (!times) times = 1;
        else times++;

        currentAction.loadingMsg = 'Waiting for server to respond. Attempts: ' + times;
        updateCurrentAction();

        $.ajax({url:eeInstallerUrl+'index/' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action_obj: JSON.stringify(currentAction),
                server: eeServer
            },
            timeout:3000,
            error: function(xhr){
                setTimeout(function(){
                    eeUpdateWaitForServer(times);
                }, 1000);
            },
            success: function(rData){
                if (!rData || !rData.success) {
                    setTimeout(function(){
                        eeUpdateWaitForServer(times);
                    }, 1000);
                }
                else {
                    eeCopyFilesPrepare();
                }
            }
        });
    }

    // ----------------------------------------------------------------------

    function eeCopyFilesPrepare(){
        currentAction.loadingMsg = 'Preparing Files Copy';
        updateCurrentAction();

        $.ajax({url: eeInstallerUrl+'copy_files_prepare' + '/' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action_obj: JSON.stringify(currentAction),
                server: eeServer
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no'){
                    triggerError(rData.body);
                    return;
                }

                eeCopyDirs = rData.dirs;
                eeCopyFiles(0);
            }
        });
    }

    // ----------------------------------------------------------------------

    function eeCopyFiles(index){
        var percent;

        //eeUpdate(0); return;

        if (index === 0) {
            currentAction.progressMsg = 'Preparing Files Copy';
            delete currentAction.loadingMsg;
            updateCurrentAction();
        }

        if (typeof(eeCopyDirs[index]) == 'undefined') {
            delete currentAction.progressMsg;
            eeUpdate(0);
            return;
        }

        percent = (100/eeCopyDirs.length) * index;
        $('#single_action_progress').css('width', percent+'%');
        $('#single_action_progress').find('.inner').html(eeCopyDirs[index]);

        $.ajax({url: eeInstallerUrl+'copy_files/'+encodeURIComponent(eeCopyDirs[index].replace(/\//g,'-')) + '/' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                dir: eeCopyDirs[index],
                action_obj: JSON.stringify(currentAction),
                server: eeServer
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                eeCopyFiles((index+1));
            }
        });
    }

    // ----------------------------------------------------------------------

    function eeUpdate(index) {

        if (typeof(eeUpdates[index]) == 'undefined') {
            delete currentAction.loadingMsg;
            eeUpdateModules();
            return;
        }

        currentAction.loadingMsg = 'Updating to: ' + eeUpdates[index].label;
        updateCurrentAction();


        $.ajax({url: eeInstallerUrl + 'update_ee/'+ eeUpdates[index].version + '/' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                version: eeUpdates[index].version,
                action_obj: JSON.stringify(currentAction),
                server: eeServer
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData, textStatus, xhr){
                if (rData === null){
                    triggerError('UNKNOWN ERROR');
                   return;
                }

                // Any queries executed?
                if (rData.queries) {
                    for (var i = 0; i < rData.queries.length; i++) {
                        Updater.queries.push(rData.queries[i]+';');
                    }
                }

                if (rData.success == 'no'){
                   triggerError(rData.body);
                   return;
                }

                eeUpdate((index+1));
            }
        });

    }

    // ----------------------------------------------------------------------

    function eeUpdateModules(){
        currentAction.loadingMsg = 'Executing module update routines...';
        updateCurrentAction();

        $.ajax({url: eeInstallerUrl+'update_modules' + '/' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action_obj: JSON.stringify(currentAction),
                server: eeServer
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                if (rData.success == 'no') {
                    triggerError(rData.body);
                    return;
                }

                if (rData.queries) {
                    for (var i = 0; i < rData.queries.length; i++) {
                        Updater.queries.push(rData.queries[i]+';');
                    }
                }

                eeUpdateCleanup();
            }
        });

    }

    // ----------------------------------------------------------------------

    function eeUpdateCleanup(){

        currentAction.loadingMsg = 'Removing installer files...';
        updateCurrentAction();

        checkQueriesExecuted();

        $.ajax({url: eeInstallerUrl+'cleanup' + '/' + Updater.generateRandomString(),
            dataType: 'json', type: 'POST',
            data: {
                action_obj: JSON.stringify(currentAction),
                server: eeServer
            },
            error: function(xhr){
                triggerError(xhr);
            },
            success: function(rData){
                currentAction.status = 'done';
                delete currentAction.loadingMsg;
                updateCurrentAction();
            }
        });

    }

    // ----------------------------------------------------------------------

    function triggerError(err){
        if (typeof(err.responseText) != 'undefined') {
            currentAction.errorMsg = 'Unexpected server response, probably a PHP error.';
            currentAction.errorDetail = global.btoa(err.responseText);
        } else {
            currentAction.errorMsg = err;
        }

        currentAction.status = 'error';
        delete currentAction.loadingMsg;
        delete currentAction.progressMsg;
        updateCurrentAction();
    }

    // ----------------------------------------------------------------------

    function retryAction(e){
        $('#error_log').hide();

        $(e.target).closest('.action').removeAttr('status-error').addClass('status-queued');

        delete currentAction.errorMsg;
        delete currentAction.errorDetail;

        if (currentAction.type == 'ee') {
            currentAction.status = 'processing';
            updateCurrentAction();
            eeUpdateWaitForServer();
        } else {
            currentAction.status = 'queued';
            updateCurrentAction();
            startActions();
        }
    }

    // ----------------------------------------------------------------------

    function checkQueriesExecuted(){
        if (Updater.queries.length > 0) {
            uwrap.find('a.queries_exec').show().find('.total').html(Updater.queries.length);
        }
    }

    // ----------------------------------------------------------------------

    function showSqlQueries(e){
        e.preventDefault();

        $('#queries_executed').slideDown();

        $('html, body').stop().animate({
            scrollTop: $('#queries_executed').offset().top
        }, 1000);

        $('#queries_executed').find('textarea').html(Updater.queries.join("\n"));
    }

    // ----------------------------------------------------------------------

}(window, jQuery));
