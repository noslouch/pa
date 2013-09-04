;(function(global, $){
    //es5 strict mode
    "use strict";

    var Updater = global.Updater = global.Updater || {};

    // Base 64
    if (!global.btoa) global.btoa = global.base64.encode;
    if (!global.atob) global.atob = global.base64.decode;

    // internal
    var uwrap;
    var dropregion;
    var file_queue = {};
    var file_queue_elem;
    var upload_queue = {};
    var upload_url;


    var input = document.createElement('input');
    input.type = 'file';

    var html5_support = ('multiple' in input && typeof global.FormData != 'undefined');
    //html5_support = false;

    var swfobj;

    // ----------------------------------------------------------------------

    Updater.uploadInit = function(){
        if (!document.getElementById('updater_upload')) return;

        uwrap = Updater.Wrap;
        dropregion = uwrap.find('.dropregion');
        file_queue_elem = $('#upload_queue');
        upload_url = Updater.ACT_URL + '&task=upload_file';

        // Cancel all drop of files, so browser doesn't redirect!
        $(document.body).bind('dragover', function(e) {
            e.preventDefault();
            return false;
        });

        $(document.body).bind('drop', function(e) {
            e.preventDefault();
            return false;
        });

        if (html5_support) {
            html5Init();
        } else {
            swfInit();
        }
    };

    // ----------------------------------------------------------------------

    function addFileQueue(file, idstr){

        var obj = {
            id: idstr,
            filename: file.name,
            filesize: formatBytes(file.size, 2),
            status: 'queued'
        };

        upload_queue[idstr] = obj;

        var html = Updater.Templates.upload_filerow(obj);
        file_queue_elem.find('.js-no_files').hide();
        file_queue_elem.append(html);
    }

    // ----------------------------------------------------------------------

    function uploadProgress(file_id, loaded, total, speed){
        var percent_loaded = (loaded / (total / 100)).toFixed(2) + '%';
        $('#'+file_id).find('.progress').css('width', percent_loaded);
    }

    // ----------------------------------------------------------------------

    function html5Init(){

        // Create an Input
        var input = document.createElement('input');
        input.setAttribute('multiple', 'multiple');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', '.zip');

        // Replace the placeholder with the input
        $('#update_upload_placeholder').replaceWith(input);

        // Add the Change event (FileDialogClosed)
        dropregion.find('input').change(html5DialogClosed);

        // STEP 1: Bind DragOver to the Main Field
        dropregion.bind('dragover', function(e){
            e.preventDefault(); e.stopPropagation();
        });

        // STEP 2: Bind DragLeave To the DragDrop Wrapper that shows up
        dropregion.bind('dragleave', function(e){
            e.preventDefault(); e.stopPropagation();
        });

        // STEP 3: Bind the DROP to the Main Field
        dropregion.bind('drop', function(e){
            e.stopPropagation(); e.preventDefault();

            // Get the files and store them in our main field object
            var dropped_files = e.originalEvent.dataTransfer.files;

            // Loop through all files
            for (var i=0; i<dropped_files.length; i++) {

                // Add it to the queue
                var id = Math.random().toString(36).substring(2);
                file_queue[id] = dropped_files[i];
                addFileQueue(dropped_files[i], id);
            }

            // Trigger Start Upload!
            html5UploadStart();
        });
    }

    // ----------------------------------------------------------------------

    function html5DialogClosed(e){
        var extensions = ['zip'];

        // Loop through all files
        for (var i=0; i<e.target.files.length; i++) {

            var Ext = e.target.files[i].name.toLowerCase().split('.').pop();

            if (extensions.indexOf(Ext) < 0) continue;

            // Add it to the queue
            var id = Math.random().toString(36).substring(2);
            file_queue[id] = e.target.files[i];
            addFileQueue(e.target.files[i], id);
        }

        // Trigger Start Upload!
        html5UploadStart();
    }

    // ----------------------------------------------------------------------

    function html5UploadStart(){

        // Find the next on the line!
        var currentFile = file_queue_elem.find('.file').filter('.status-queued:first');
        var fileId = currentFile.attr('id');
        var fileObj = upload_queue[fileId];

        // Nothing found? Quit!
        if (currentFile.length === 0) {

            // Empty the input field
            //$(document.getElementById('ci_upload_btn_' + FIELD_ID)).val('');
            delete file_queue[fileId];
            return false;
        }

        // Mark it as uploading..
        fileObj.status = 'uploading';
        updateFileRow(fileId, fileObj);

        var xhr = new XMLHttpRequest();

        // Log Progress Events!
        xhr.upload['onprogress'] = function(rpe) {
            uploadProgress(fileId, rpe.loaded, rpe.total);
        };

        // When done!
        xhr.onload = function(event){
            html5UploadResponse(event, xhr, fileId);
        };

        xhr.open('post', upload_url, true);
        xhr.setRequestHeader('Cache-Control', 'no-cache');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-File-Name', fileObj.filename);
        xhr.setRequestHeader('X-File-Size', fileObj.fileSize);

        //xhr.setRequestHeader("Content-Type", "multipart/form-data");
        //xhr.send(File);

        var f = new FormData();
        f.append('updater_file', file_queue[fileId]);
        xhr.send(f);

/*
        // Cancel Upload
        $('#ChannelImages_' + FIELD_ID).find('a.StopUpload').click(function(){
            $('#ChannelImages_' + FIELD_ID).find('.ImageQueue div.File').not('div.Done').each(function(index,elem){
                var Elem = $(elem);
                xhr.abort();
                Elem.fadeOut(1400, function(){ Elem.remove(); });
                ChannelImages.HTML5.CleanQueue(FIELD_ID);
                $('#ChannelImages_' + FIELD_ID).find('div.UploadProgress').hide();
                $('#ChannelImages_' + FIELD_ID).find('a.StopUpload').hide();
            });
            return false;
        });
*/
}


    // ----------------------------------------------------------------------

    function html5UploadResponse(event, xhr, fileId){
        uploadProgress(fileId, 100, 100);

        var serverData;
        var fileObj = upload_queue[fileId];
        fileObj.status = 'error';

        // Was the request succesfull?
        if (xhr.status == 200){

            try {
                serverData = JSON.parse(xhr.responseText);
            }
            // JSON ERROR!
            catch(errorThrown) {
                fileObj.errorMsg = 'Unexpected server response, probably a PHP error.';
                fileObj.errorDetail = global.btoa(xhr.responseText);
                updateFileRow(fileId, fileObj);
                return;
            }

            if (serverData.error_msg) {
                fileObj.errorMsg = serverData.error_msg;
                updateFileRow(fileId, fileObj);
                return;
            }

            // Was the upload a success?
            if (serverData.success == 'no') {
                fileObj.errorMsg = serverData.error_msg;
                updateFileRow(fileId, fileObj);
                return;
            }

            fileObj.status = 'done';
            updateFileRow(fileId, fileObj);

            Updater.addProcessAction(serverData);
        }

        // Request was bad..do something about it
        else {
            fileObj.errorMsg = 'Server responded with a ' + xhr.status + ' status';
            fileObj.errorDetail = global.btoa(xhr.responseText);
            updateFileRow(fileId, fileObj);
        }

        // Continue
        html5UploadStart();
    }

    //********************************************************************************* //

    function swfInit(){

        // When the field is hidden by default, the Flash object's width is 0 so you cannot do anything with it
        // Here we force the width, by getting the width of the parent
        var ButtonWith = 120;
        if ($('#updater_upload').is(':visible') !== false){
            ButtonWith = ($('#updater_upload').width() + 10);
        }

        swfobj = new SWFUpload({

            // Backend Settings
            flash_url : Updater.THEME_URL + 'img/swfupload.swf',
            upload_url: global.location.protocol + upload_url,
            post_params: {
                task: 'upload_file'
            },
            file_post_name: 'updater_file',
            prevent_swf_caching: true,
            assume_success_timeout: 0,

            // File Upload Settings
            file_size_limit : 0,
            file_types : '*.zip',
            file_types_description : '.zip Files',
            file_upload_limit : 0,
            file_queue_limit : 0,

            // Event Handler Settings
            swfupload_preload_handler : function(){},
            swfupload_load_failed_handler : function(){},
            file_dialog_start_handler : function(){},
            file_queued_handler : swfQueuedHandler,
            file_queue_error_handler : function(){},
            file_dialog_complete_handler : swfDialogCompleteHandler,
            upload_resize_start_handler : function(){},
            upload_start_handler : swfStartHandler,
            upload_progress_handler : swfProgressHandler,
            upload_error_handler : swfErrorHandler,
            upload_success_handler : swfSuccessHandler,
            upload_complete_handler : function(){},

            // Button Settings
            button_image_url : '', // Relative to the SWF file
            button_placeholder_id : 'update_upload_placeholder',
            button_width: ButtonWith,
            button_height: 28,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_cursor: SWFUpload.CURSOR.HAND,
            button_action: SWFUpload.BUTTON_ACTION.SELECT_FILES,

            // Debug Settings
            debug: true
        });

/*
        // Cancel Upload
        $('#ChannelImages_' + FIELD_ID).find('a.StopUpload').click(function(){

            $('#ChannelImages_' + FIELD_ID).find('.ImageQueue div.File').not('div.Done').each(function(index,elem){
                var Elem = $(elem);
                ChannelImages.SWF[FIELD_ID].cancelUpload(Elem.attr('id'), true);
                Elem.fadeOut(1400, function(){
                    Elem.remove();
                    $('#ChannelImages_' + FIELD_ID).find('div.UploadProgress').hide();
                });
                swfCleanQueue(FIELD_ID);
                $('#ChannelImages_' + FIELD_ID).find('a.StopUpload').hide();
            });

            $(this).hide();

            return false;
        });
*/
    }

    // ----------------------------------------------------------------------

    function swfQueuedHandler(file){

        // Attempt to add file to Queue
        if (addFileQueue(file, file.id, '') === false){

            // If fails, cancel this upload
            this.cancelUpload(file.id, false);

            return false;
        }
    }

    // ----------------------------------------------------------------------

    function swfDialogCompleteHandler(totalFilesSelected, totalFilesQueued, grandTotalFilesQueued) {
        // Start Upload!
        this.startUpload();
    }

    // ----------------------------------------------------------------------

    function swfStartHandler(file) {
        var fileObj = upload_queue[file.id];
        fileObj.status = 'uploading';
        updateFileRow(file.id, fileObj);
    }

    // ----------------------------------------------------------------------

    function swfProgressHandler(file, bytesLoaded, bytesTotal) {
        uploadProgress(file.id, bytesLoaded, bytesTotal);
    }

    // ----------------------------------------------------------------------

    function swfErrorHandler(file, error, message){

        // Sometimes we cancel the upload because of an error, no need to display "Cancelled error"
        if (error == '-270') return;
        if (error == '-280') return;

        var fileObj = upload_queue[file.id];
        fileObj.status = 'error';

        fileObj.errorMsg = 'Upload Failed: ' + error + ' MSG:' + message;
        fileObj.errorDetail = global.btoa(serverData);
        updateFileRow(file.id, fileObj);
    }

    // ----------------------------------------------------------------------

    function swfSuccessHandler(file, serverResponse, response) {
        uploadProgress(file.id, 100, 100);

        var fileObj = upload_queue[file.id];
        fileObj.status = 'error';

        try {
            // Parse the JSON, if it failed we have error
            serverData = JSON.parse(serverResponse);
        }
        catch(errorThrown) {
            fileObj.errorMsg = 'Unexpected server response, probably a PHP error.';
            fileObj.errorDetail = global.btoa(serverResponse);
            updateFileRow(file.id, fileObj);
            return;
        }

        // Sometimes went wrong?
        if (serverData.success == 'no') {
            fileObj.errorMsg = serverData.error_msg;
            updateFileRow(file.id, fileObj);
            return;
        }

        fileObj.status = 'done';
        updateFileRow(file.id, fileObj);

        Updater.addProcessAction(serverData);
    }

    // ----------------------------------------------------------------------

    function updateFileRow(id, obj) {
        var html = Updater.Templates.upload_filerow(obj);
        $('#'+id).replaceWith(html);
    }

    // ----------------------------------------------------------------------

    function formatBytes(bytes, precision)
    {
        var units = ['b', 'KB', 'MB', 'GB', 'TB'];
        bytes = Math.max(bytes, 0);
        var pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
        pow = Math.min(pow, units.length - 1);
        bytes = bytes / Math.pow(1024, pow);
        precision = (typeof(precision) == 'number' ? precision : 0);
        return (Math.round(bytes * Math.pow(10, precision)) / Math.pow(10, precision)) + ' ' + units[pow];
    }

    // ----------------------------------------------------------------------

}(window, jQuery));
