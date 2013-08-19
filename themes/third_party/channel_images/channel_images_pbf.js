// ********************************************************************************* //
var ChannelImages = ChannelImages ? ChannelImages : {};
ChannelImages.SWF = {}; ChannelImages.HTML5 = {}; ChannelImages.SWFUPLOAD = {};
ChannelImages.CI_Images = {}; ChannelImages.Templates = {}; ChannelImages.Refreshing = false;
//********************************************************************************* //

// Add :Contains (case-insensitive)
$.expr[':'].Contains = function(a,i,m){
    return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
};

$(document).ready(function() {

	// If you have multiple saef fields we only need to do this once
	if (typeof(ChannelImages.initfields_done) == 'undefined'){
		ChannelImages.initfields_done = 'yes';
		ChannelImages.InitFields();
	}

	// Parse Hogan Templates
	ChannelImages.Templates['TableTR'] = Hogan.compile($('#ChannelImagesSingleField').html());

	// Loop over all fields and insert files (if any)
	for (var Field in ChannelImages.Fields){

		if (typeof(ChannelImages.Fields[Field]) == 'string') ChannelImages.Fields[Field] = jQuery.parseJSON($.base64Decode(ChannelImages.Fields[Field]));

		if (!ChannelImages.Fields[Field].images.length) continue;

		for (var File in ChannelImages.Fields[Field].images){

			// Is this the last one? So we can trigger sync..
			var Sync = ((ChannelImages.Fields[Field].images.length -1) == File) ? true : false;

			ChannelImages.AddNewFile(ChannelImages.Fields[Field].images[File], ChannelImages.Fields[Field].images[File].field_id, Sync);
		}
	}

	// Submit Entry Stop
	$('#submit_button').click(function(Event){
		if (ChannelImages.CFields.find('.ImageQueue div.Done').length > 0){
			$(Event.target).parent(':first').append('<div class="ChannelImagesSubmitWait">' + ChannelImages.LANG.submitwait + '</div>');
			setTimeout(function(){$(Event.target).attr('disabled', 'disabled').css('background', '#DDE2E5');}, 300);
		}
	});

	ChannelImages.CFields.closest('form').bind('submit', function(){
		ChannelImages.CFields.find(':input').not('.ImageData').trigger('blur').trigger('change');
	});

	ChannelImages.CFields.delegate('.Image select', 'change', ChannelImages.TriggerChangeFile);
	ChannelImages.CFields.delegate('.Image textarea, .Image input', 'blur', ChannelImages.TriggerChangeFile);
	ChannelImages.CFields.delegate('.Image input', 'keydown', function(event){ if (event.keyCode == 13) return false;  });


	if (typeof(Bwf) != 'undefined'){
		Bwf.bind('channel_images', 'previewClose', function(){
			ChannelImages.RefreshImages(Bwf._transitionInstance.draftExists);
		});
	}
});

//********************************************************************************* //

ChannelImages.InitFields = function(){

	// Grabb all fields
	ChannelImages.CFields = $('div.CIField');

	// Loop Over all fields
	ChannelImages.CFields.each(function(index, elem){
		var FIELDID = $(elem).data('fieldid');

		// Activate Upload Handlers
		ChannelImages.ActivateUploadHandlers(FIELDID);
	});

	// Open Error Handler
	ChannelImages.CFields.find('div.UploadProgress').delegate('a.OpenError', 'click', function(){
		$.colorbox({width:'90%', height:'90%', html:'<pre style="font-size:11px; font-family:helvetica,arial">'+ChannelImages.LastError+'</pre>'});
		return false;
	});

	// Activate Sortable
	ChannelImages.CFields.find('.AssignedImages.TableBased').sortable({axis:'y', cursor:'move', opacity:0.6, handle:'.ImageMove',
		helper:function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});

			return ui;
		},
		update:ChannelImages.SyncOrderNumbers
	});

	// Activate Sortable
	ChannelImages.CFields.find('.TileBased .AssignedImages').sortable({cursor:'move', opacity:0.6, update:ChannelImages.SyncOrderNumbers});

	// Add some handlers
	ChannelImages.CFields.find('.AssignedImages').delegate('.ImageCover', 'click', ChannelImages.TogglePrimaryFile);
	ChannelImages.CFields.find('.AssignedImages').delegate('.ImageProcessAction', 'click', ChannelImages.OpenPerImageAction);
	ChannelImages.CFields.find('.AssignedImages').delegate('.ImageEdit', 'click', ChannelImages.OpenImageEdit);
	ChannelImages.CFields.find('.AssignedImages').delegate('.ImageReplace', 'click', ChannelImages.OpenImageReplace);
	ChannelImages.CFields.find('.AssignedImages').delegate('.ImageDel', 'click', ChannelImages.TriggerDeleteImage);
	ChannelImages.CFields.find('.AssignedImages').delegate('.Image', 'del_image', ChannelImages.DeleteImage);

	ChannelImages.CFields.delegate('.StoredImages', 'click', ChannelImages.OpenStoredImages);
	ChannelImages.CFields.delegate('.ImportImages', 'click', ChannelImages.OpenImportFiles);
};

//********************************************************************************* //

ChannelImages.ActivateUploadHandlers = function(FIELD_ID){

	if (typeof(ChannelImages.Fields['Field_'+FIELD_ID]) == 'string') ChannelImages.Fields['Field_'+FIELD_ID] = jQuery.parseJSON($.base64Decode(ChannelImages.Fields['Field_'+FIELD_ID]));

	// Enable Hybrid Upload?
	if (ChannelImages.Fields['Field_'+FIELD_ID].settings.hybrid_upload == 'yes')
	{
		var input = document.createElement('input');
		input.type = 'file';

		if ('multiple' in input && typeof File != "undefined" && typeof (new XMLHttpRequest()).upload != "undefined" ) {
			ChannelImages.Debug('CHANNEL_IMAGES: We can use HTML5 File Upload!');
			ChannelImages.HTML5.Init(FIELD_ID);
		}
		else {
			ChannelImages.Debug('CHANNEL_IMAGES: HTML5 File Upload is not available, using flash now.');
			ChannelImages.SWFUPLOAD.Init(FIELD_ID);
		}
	}
	else {
		ChannelImages.Debug('CHANNEL_IMAGES: HTML5 File Upload is disabled, using flash now.');
		ChannelImages.SWFUPLOAD.Init(FIELD_ID);
	}
};

//********************************************************************************* //

ChannelImages.AddFileToQueue = function(FIELD_ID, Filename, IDstr, RELstr){

	// Can we add more files?
	var Remaining = ChannelImages.FilesRemaining(FIELD_ID);
	Remaining = (Remaining - $('#ChannelImages_'+FIELD_ID).find('.ImageQueue .Queued').length);

	if (Remaining > 0) {
		var File = $('<div class="File Queued" id="' + IDstr + '" rel="'+RELstr+'">' + Filename + '</div>');
		$('#ChannelImages_'+FIELD_ID).find('.ImageQueue').css('display', 'table-row').children('th').append(File);
		return true;
	}
	else return false;
};


//********************************************************************************* //

ChannelImages.UploadProgress = function(FIELD_ID, loaded, total, speed){
	var ProgressBox = $('#ChannelImages_' + FIELD_ID).find('div.UploadProgress').show();
	var PercentUploaded = loaded / (total / 100);

	ProgressBox.children('.progress').css('width', PercentUploaded.toFixed(2) + '%');

	ProgressBox.find('.percent').html(PercentUploaded.toFixed(2) + '%');
	if (speed) ProgressBox.find('.speed').html(SWFUpload.speed.formatBPS(speed / 10));
	ProgressBox.find('.bytes .uploadedBytes').html(SWFUpload.speed.formatBytes(loaded));
	ProgressBox.find('.bytes .totalBytes').html('/ ' + SWFUpload.speed.formatBytes(total));
};

//********************************************************************************* //

ChannelImages.AddNewFile = function(JSONOBJ, FIELD_ID, Sync){
	if (!FIELD_ID) return;

	// Lets add some keys!
	if (!JSONOBJ.image_id) JSONOBJ.image_id = '0';
	if (!JSONOBJ.description) JSONOBJ.description = '';
	if (!JSONOBJ.category) JSONOBJ.category = '';
	if (!JSONOBJ.cifield_1) JSONOBJ.cifield_1 = '';
	if (!JSONOBJ.cifield_2) JSONOBJ.cifield_2 = '';
	if (!JSONOBJ.cifield_3) JSONOBJ.cifield_3 = '';
	if (!JSONOBJ.cifield_4) JSONOBJ.cifield_4 = '';
	if (!JSONOBJ.cifield_5) JSONOBJ.cifield_5 = '';
	if (!JSONOBJ.cover) JSONOBJ.cover = '0';
	if (!JSONOBJ.link_image_id) JSONOBJ.link_image_id = '0';

	// Lets store it for POST
	JSONOBJ.json_data = JSON.stringify(JSONOBJ);

	// Add field_name
	JSONOBJ.field_name = ChannelImages.Fields['Field_'+FIELD_ID].field_name;

	// Is it primary?
	if (JSONOBJ.cover == 1) JSONOBJ.is_cover = true;

	// Is it linked?
	if (JSONOBJ.link_image_id > 0) JSONOBJ.is_linked = true;

	// Loop through all columns
	for (var column in ChannelImages.Fields['Field_'+FIELD_ID].settings.columns){
		if (ChannelImages.Fields['Field_'+FIELD_ID].settings.columns[column] != false){
			// It's not empty, so lets add it
			JSONOBJ['show_'+column] = true;
		}
	}

	// Disable Cover
	if (ChannelImages.Fields['Field_'+FIELD_ID].settings.disable_cover == 'no') {
		JSONOBJ['show_cover'] = true;
	}

	if (typeof(ChannelImages.Fields['Field_'+FIELD_ID].settings.allow_per_image_action) != 'undefined'){
		if (ChannelImages.Fields['Field_'+FIELD_ID].settings.allow_per_image_action == 'yes') JSONOBJ.show_image_action = true;
	}

	if (typeof(ChannelImages.Fields['Field_'+FIELD_ID].settings.show_image_edit) != 'undefined'){
		if (ChannelImages.Fields['Field_'+FIELD_ID].settings.show_image_edit == 'yes') JSONOBJ.show_image_edit = true;
	}

	if (typeof(ChannelImages.Fields['Field_'+FIELD_ID].settings.show_image_replace) != 'undefined'){
		if (ChannelImages.Fields['Field_'+FIELD_ID].settings.show_image_replace == 'yes') JSONOBJ.show_image_replace = true;
	}

	if (JSONOBJ.is_linked == true) {
		JSONOBJ.show_image_replace = false;
	}

	// Kill Titles and url_titles!
	JSONOBJ.image_title = JSONOBJ.title;
	JSONOBJ.image_url_title = JSONOBJ.url_title;

	delete JSONOBJ.title; delete JSONOBJ.url_title;

	// Category
	var Cat = '<select>';
	for (var CatLabel in ChannelImages.Fields['Field_'+FIELD_ID].categories) {

		Cat += '<option value="'+CatLabel+'" '+((JSONOBJ.category == CatLabel) ? 'selected' : '')+' >'+CatLabel+'</option>';
	}
	Cat += '</select>';
	JSONOBJ.category = Cat;

	//console.log(JSONOBJ);

	// View Mode
	JSONOBJ['tile_view'] = (ChannelImages.Fields['Field_'+FIELD_ID].settings.view_mode == 'tiles') ? true : false;
	JSONOBJ['table_view'] = (ChannelImages.Fields['Field_'+FIELD_ID].settings.view_mode == 'table') ? true : false;

	// Render the new row
	var HTML = ChannelImages.Templates['TableTR'].render(JSONOBJ);

	// Add it
	$('#ChannelImages_'+FIELD_ID).find('.AssignedImages').append(HTML);
	$('#ChannelImages_'+FIELD_ID).find('.NoImages').hide();

	var JustAppended = $('#ChannelImages_'+FIELD_ID).find('.AssignedImages').find('.Image:last');

	// Activate jEditable
	//ChannelImages.ActivateEditable(JustAppended, FIELD_ID);

	JustAppended.find('.ImgUrl').colorbox({photo:true});

	if (Sync === false) return;

	// Sync those numbers
	ChannelImages.SyncOrderNumbers(FIELD_ID);
	ChannelImages.FilesRemaining(FIELD_ID);
};

//********************************************************************************* //

ChannelImages.TriggerChangeFile = function(e){
	var Parent = $(e.target).parent();
	if (!Parent.data('field')) return;
	ChannelImages.ChangeFile(Parent.data('field'), Parent.find(':input').val(), Parent.closest('.Image'));
};

//********************************************************************************* //

ChannelImages.ChangeFile = function(attr, value, file){

	// Double check!
	if (typeof(file) != 'object') return;
	if (file.length === 0) return;

	// Grab the json
	var jsondata = file.find('textarea.ImageData').html();
	jsondata = JSON.parse(jsondata);

	// Set the attribute
	jsondata[attr] = value;

	// Put it back!
	file.find('textarea.ImageData').html(JSON.stringify(jsondata));
};

//********************************************************************************* //

ChannelImages.FilesRemaining = function(FIELD_ID){
	var TotalFiles = $('#ChannelImages_'+FIELD_ID).find('.AssignedImages .Image').not('.deleted').length;
	var FileLimit = ChannelImages.Fields['Field_'+FIELD_ID].settings.image_limit;
	var FilesRemaining = (FileLimit - TotalFiles);

	var RemainingColor = (FilesRemaining > 0) ? 'green' : 'red';

	$('#ChannelImages_'+FIELD_ID).find('.ImageLimit .remain').css('color', RemainingColor).text(FilesRemaining);

	return FilesRemaining;
};

//********************************************************************************* //

ChannelImages.SyncOrderNumbers = function(FIELD_ID){

	// Is it an event object? Get the Field_id
	if (typeof(FIELD_ID) == 'object'){
		FIELD_ID = $(FIELD_ID.target).closest('.CIField').data('fieldid');
	}

	var Count = 0;

	ChannelImages.Fields['Field_'+FIELD_ID].wimages = [];

	// Loop over all Files
	$('#ChannelImages_'+FIELD_ID).find('.AssignedImages').find('.Image').each(function(Fileindex, Elem){
		var FILETD = $(Elem);

		if (FILETD.hasClass('deleted') === false) {
			Count++;
			ChannelImages.Fields['Field_'+FIELD_ID].wimages.push( JSON.parse(FILETD.find('textarea.ImageData').html()) );
		}

		// Insert the row number (most of the time it's the first column)
		FILETD.find('td.num').html(Count);

		// Find all form inputs
		$(FILETD).find('input, textarea, select').each(function(findex,felem){
			if (!felem.getAttribute('name')) return false;

			// Get it's attribute and change it
			var attr = $(this).attr('name').replace(/\[images\]\[.*?\]/, '[images][' + (Fileindex+1) + ']');
			$(this).attr('name', attr);
		});
	});

	// Odd/Even
	$('#ChannelImages_'+FIELD_ID).find('.Image').removeClass('odd');
	$('#ChannelImages_'+FIELD_ID).find('.Image:odd').addClass('odd');
};

//********************************************************************************* //

ChannelImages.TogglePrimaryFile = function(e){

	// Store!
	var TableParent = $(e.target).closest('.CIField');
	var Parent = $(e.target).closest('.Image');
	var FIELD_ID = TableParent.data('fieldid');

	var cover_first = true;
	if (ChannelImages.Fields['Field_'+FIELD_ID].settings.cover_first == 'no') {
		cover_first = false;
	}

	// Are we unchecking?
	var Uncheck = false;
	if ( $(e.target).hasClass('StarIcon') ) Uncheck = true;

	// Find all files and remove the StarClass & Cover Value
	TableParent.find('.AssignedImages').find('.Image').each(function(i, elem){
		$(elem).removeClass('PrimaryImage').find('.ImageCover').removeClass('StarIcon');
		ChannelImages.ChangeFile('cover', '0', $(elem));
	});


	if (Uncheck == true) return false;

	ChannelImages.ChangeFile('cover', '1', Parent);

	// Add the star status to the clicked file
	Parent.addClass('PrimaryImage').find('.ImageCover').addClass('StarIcon');

	if (cover_first) TableParent.find('.AssignedImages').prepend(Parent);

	ChannelImages.SyncOrderNumbers(TableParent.closest('.CIField').data('fieldid'));
	return false;
};

//********************************************************************************* //


ChannelImages.TriggerDeleteImage = function(e){
	e.preventDefault();

	if (!e.shiftKey) {

		if ( $(e.target).hasClass('ImageLinked') === true){
			confirm_delete = confirm(ChannelImages.LANG.unlink_file);
			if (confirm_delete == false) return false;
		}
		else {
			confirm_delete = confirm(ChannelImages.LANG.del_file);
			if (confirm_delete == false) return false;
		}

		$(e.target).closest('.Image').trigger('del_image');

	} else {

		confirm_delete = confirm(ChannelImages.LANG.del_file_all);
		if (confirm_delete == false) return false;

		$(e.target).closest('.AssignedImages').find('.Image').trigger('del_image');
	}

};

//********************************************************************************* //

ChannelImages.DeleteImage = function(e){

	// Store!
	var Parent = $(e.target);
	var FIELD_ID = $(e.target).closest('div.CIField').data('fieldid');

	var jsondata = Parent.find('textarea.ImageData').html();
	jsondata = JSON.parse(jsondata);

	if (typeof(jsondata.image_id) == 'undefined') jsondata.image_id = 0;

	if (jsondata.image_id > 0) ChannelImages.ChangeFile('delete', '1', Parent);

	// Add the star status to the clicked file
	Parent.addClass('deleted').fadeOut('slow', function(){
		if (jsondata.image_id < 1) Parent.remove();
		ChannelImages.SyncOrderNumbers(FIELD_ID);
		ChannelImages.FilesRemaining(FIELD_ID);
	});

	return false;
};

//********************************************************************************* //

ChannelImages.EditFileDetails = function(value, settings){
	var Field = $(this).data('field');

	ChannelImages.ChangeFile(Field, value, $(this).closest('.Image'));

	return value;
};

//********************************************************************************* //

ChannelImages.ActivateLiveUrlTitle = function(options, parentTD){
	var parentTR = $(parentTD).closest('tr.File');
	setTimeout(function(){
		$(parentTD).find('input[name=value]').liveUrlTitle(parentTR.find('td[rel=url_title]'), {separator: EE.publish.word_separator});
		$(parentTD).find('input[name=value]').liveUrlTitle(parentTR.find('.inputs .url_title'), {separator: EE.publish.word_separator});
	}, 500);
};

//********************************************************************************* //

ChannelImages.OpenImportFiles = function(Event){
	Event.preventDefault();

	var FIELD_ID = jQuery(Event.target).closest('div.CIField').data('fieldid');
	var Remaining = ChannelImages.FilesRemaining(FIELD_ID);

	jQuery.colorbox({
		href: ChannelImages.AJAX_URL + '&ajax_method=import_files_ui&field_id='+ FIELD_ID + '&remaining='+Remaining,
		onComplete: function(){

			// Store it
			var Elem = jQuery('#cboxContent');

			Elem.find('.ImportImagesBtn').click(function(){
				// Show the indicator
				Elem.find('.ImportImagesBtn span').css('display', 'inline-block');

				// Prepare Params
				var Params = {};
				Params.ajax_method = 'import_images';
				Params.field_id = Elem.find('.CITable').attr('rel');
				Params.key = ChannelImages.Fields['Field_'+FIELD_ID].key;
				Params.files = [];

				// Loop over all checkboxes
				jQuery('#cboxContent').find('input[type=checkbox]:checked').each(function(i, el){
					Params.files.push(el.value);
				});

				jQuery.post(ChannelImages.AJAX_URL, Params, function(rData){

					for (var File in rData.files){
						ChannelImages.AddNewFile(rData.files[File], FIELD_ID);
					}

					// Lets fake it so we get the submit wait message
					jQuery('#ChannelImages_'+FIELD_ID).find('.ImagesQueue').append('<div class="Done"></div>');

					jQuery.colorbox.close();
				}, 'json');
			});
		}
	});

};

//********************************************************************************* //








//********************************************************************************* //
ChannelImages.HTML5.Init = function(FIELD_ID) {

	// Create an Input
	// opacity: 0; filter:alpha(opacity: 0); IS REQUIRED!
	// So we can click the placeholder and still trigger the dialog
	var input = document.createElement('input');
	input.setAttribute('multiple', 'multiple');
	input.setAttribute('type', 'file');
    input.setAttribute('name', 'channel_images_file');
    input.setAttribute('id', 'ci_upload_btn_'+FIELD_ID);
    input.setAttribute('accept', 'image/*');
    input.setAttribute('style', 'position:absolute; cursor:pointer; top:0; left:0; opacity: 0; filter:alpha(opacity: 0);');

    // Replace the placeholder with the input
    $('#ChannelImagesSelect_'+FIELD_ID).replaceWith(input);

    // Add the Change event (FileDialogClosed)
    $('#ci_upload_btn_'+FIELD_ID).change(ChannelImages.HTML5.FileDialogClosed);

    // Cancel all drop of files, so browser doesn't redirect!
    $(document.body).bind('dragover', function(e) {e.preventDefault(); return false;});
    $(document.body).bind('drop', function(e) {e.preventDefault(); return false;});

	var FIELD = $('#ChannelImages_' + FIELD_ID);

	// STEP 1: Bind DragOver to the Main Field
	FIELD.bind('dragover', function(Event){
		Event.preventDefault(); Event.stopPropagation();
		$('#CIDragDrop_'+FIELD_ID).css({width:FIELD.width(), height:FIELD.height()}).show();
	});

	// STEP 2: Bind DragLeave To the DragDrop Wrapper that shows up
	$('#CIDragDrop_'+FIELD_ID).bind('dragleave', function(Event){
		Event.preventDefault(); Event.stopPropagation();
		$('#CIDragDrop_'+FIELD_ID).hide();
	});

	// STEP 3: Bind the DROP to the Main Field
	FIELD.bind('drop', function(Event){
		Event.stopPropagation(); Event.preventDefault();

		// Remove all queued items!
		ChannelImages.HTML5.CleanQueue(FIELD_ID);

		// Get the files and store them in our main field object
		ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped = Event.originalEvent.dataTransfer.files;

		// Hide the drop wrapper
		$('#CIDragDrop_'+FIELD_ID).hide();

		// Loop through all files
		for (var i=0; i<ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped.length; i++) {

			// Add it to the queue
			ChannelImages.AddFileToQueue(FIELD_ID, ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped[i].name, 'File_'+i, i);
		}

		// Trigger Start Upload!
		ChannelImages.HTML5.UploadStart(FIELD_ID);

	});

};

//********************************************************************************* //

ChannelImages.HTML5.FileDialogClosed = function(Event) {

	// We need the Fiel ID
	var FIELD_ID = $(Event.target).closest('.CIField').data('fieldid');
	var Extensions = ['jpg','png','gif','jpeg'];

	// Loop through all files
	for (var i=0; i<Event.target.files.length; i++) {

		var Ext = Event.target.files[i].name.toLowerCase().split('.').pop();

		if (Extensions.indexOf(Ext) < 0) continue;

		// Add it to the queue
		ChannelImages.AddFileToQueue(FIELD_ID, Event.target.files[i].name, 'File_'+i, i);
    }

	ChannelImages.HTML5.UploadStart(FIELD_ID);
};

//********************************************************************************* //

ChannelImages.HTML5.UploadStart = function(FIELD_ID) {
	ChannelImages.UploadError = false;

	var UploadURL = ChannelImages.AJAX_URL + '&ajax_method=upload_file&field_id='+ FIELD_ID + '&key=' + ChannelImages.Fields['Field_'+FIELD_ID].key;

	// Find the next on the line!
	var FileQueue = $('#ChannelImages_'+FIELD_ID).find('.ImageQueue').find('.Queued:first');

	// Nothing found? Quit!
	if (FileQueue.length == 0) {

		// Hide the progress box
		$('#ChannelImages_' + FIELD_ID).find('div.UploadProgress').hide();

		// Empty the input field
		$(document.getElementById('ci_upload_btn_' + FIELD_ID)).val('');
		ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped = null;

		return false;
	}

	// Show StopUpload
	$('#ChannelImages_' + FIELD_ID).find('.StopUpload').show();

	// Mark it as uploading..
	FileQueue.removeClass('Queued').addClass('Uploading');

	// What key was it?
	var Index = FileQueue.attr('rel');

	// Grab the file object (check if it's a dropped file one before)
	if (ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped) var File = ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped[Index];
	else var File = document.getElementById('ci_upload_btn_' + FIELD_ID).files[Index];

	var xhr = new XMLHttpRequest();

	// Log Progress Events!
	xhr.upload['onprogress'] = function(rpe) {
		ChannelImages.UploadProgress(FIELD_ID, rpe.loaded, rpe.total);
	};

	// When done!
	xhr.onload = function(load){ ChannelImages.HTML5.UploadReponse(load, xhr, File, FIELD_ID, FileQueue); };

	xhr.open('post', UploadURL, true);
	xhr.setRequestHeader('Cache-Control', 'no-cache');
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	xhr.setRequestHeader('X-File-Name', File.name);
	xhr.setRequestHeader('X-File-Size', File.fileSize);

	//xhr.setRequestHeader("Content-Type", "multipart/form-data");
	//xhr.send(File);


	if (window.FormData) {
		var f = new FormData();
		f.append('channel_images_file', File);
		xhr.send(f);
	}
	else if (File.getAsBinary || window.FileReader) {
		var boundary = '------multipartformboundary' + (new Date).getTime();
		var dashdash = '--';
		var crlf = '\r\n';

		/* Build RFC2388 string. */
		var builder = '';

		builder += dashdash;
		builder += boundary;
		builder += crlf;

		builder += 'Content-Disposition: form-data; name="channel_images_file"';
		builder += '; filename="' + File.name + '"';
		builder += crlf;

		builder += 'Content-Type: application/octet-stream';
		builder += crlf;
		builder += crlf;

		/* Append binary data. */
		if (window.FileReader) {
			reader = new FileReader();
			reader.onload = function(evt) {
				builder += evt.target.result;
				builder += crlf;

				/* Write boundary. */
				builder += dashdash;
				builder += boundary;
				builder += crlf;

				builder += dashdash;
				builder += boundary;
				builder += dashdash;
				builder += crlf;

				xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
				xhr.sendAsBinary(builder);
			};
			reader.readAsBinaryString(File);
		}
		else if (typeof(File.getAsBinary) != 'undefined') {
			builder += File.getAsBinary();
			builder += crlf;

			/* Write boundary. */
			builder += dashdash;
			builder += boundary;
			builder += crlf;

			builder += dashdash;
			builder += boundary;
			builder += dashdash;
			builder += crlf;

			xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
			xhr.sendAsBinary(builder);
		}
		else {
			alert('HTML5 Upload Failed! (FILEREADER & GetAsBinary are not supported)');
			return false;
		}
	}
	else {
		alert('HTML5 Upload Failed! (FEATURES_NOT_SUPPORTED)');
		return false;
	}

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

};

//********************************************************************************* //

ChannelImages.HTML5.UploadReponse = function(load, xhr, File, FIELD_ID, FileQueue){

	// Sometimes we get the progressbar to 90%, so lets finish it here
	ChannelImages.UploadProgress(FIELD_ID, File.size, File.size);

	// Show StopUpload
	$('#ChannelImages_' + FIELD_ID).find('.StopUpload').hide();

	// Was the request succesfull?
	if (xhr.status == 200){
		try {
			ServerData = JSON.parse(xhr.responseText);
		}
		// JSON ERROR!
		catch(errorThrown) {
			ChannelImages.LastError = xhr.responseText;
			ChannelImages.ErrorMSG(ChannelImages.LANG.xhr_reponse_error, FIELD_ID);
			ChannelImages.Debug("Server response was not as expected, probably a PHP error. \n RETURNED RESPONSE: \n" + xhr.responseText);
			FileQueue.removeClass('Uploading').addClass('Error');
			ChannelImages.HTML5.CleanQueue(FIELD_ID);
			return false;
		}

		// Was the upload a success?
		if (ServerData.success == 'yes') {

			// Mark it as done
			FileQueue.removeClass('Uploading').addClass('Done');

			// Hide it?
			var TempCurrentFile = FileQueue;
			setTimeout(function(){
				if (TempCurrentFile.hasClass('Done') === false) return;
				TempCurrentFile.slideUp('slow');
			}, 2000);

			// Add the new file to the table
			ChannelImages.AddNewFile(ServerData, FIELD_ID);

			// Start a new upload!
			ChannelImages.HTML5.UploadStart(FIELD_ID);
		}
		else {
			FileQueue.removeClass('Uploading').addClass('Error');
			ChannelImages.ErrorMSG(ServerData.body, FIELD_ID);
			ChannelImages.Debug('ERROR: ' + ServerData.body);
			ChannelImages.HTML5.CleanQueue(FIELD_ID);
		}

	}

	// Request was bad..do something about it
	else {
		ChannelImages.LastError = xhr.responseText;
		ChannelImages.ErrorMSG(ChannelImages.LANG.xhr_status_error, FIELD_ID);
		ChannelImages.Debug("Upload request failed, no HTTP 200 Return Code! \n RETURNED RESPONSE: \n" + xhr.responseText);
		FileQueue.removeClass('Uploading').addClass('Error');
		ChannelImages.HTML5.CleanQueue(FIELD_ID);
	}
};

//********************************************************************************* //

ChannelImages.HTML5.CleanQueue = function(FIELD_ID) {

	// Empty the input field
	$(document.getElementById('ci_upload_btn_' + FIELD_ID)).val('');

	// Remove all queue files
	$('#ChannelImages_'+FIELD_ID).find('.ImagesQueue').find('.Queued').slideUp('slow', function(){ $(this).remove(); });

	// Also here
	ChannelImages.Fields['Field_'+FIELD_ID].FilesDropped = null;
};

//********************************************************************************* //












//********************************************************************************* //

ChannelImages.SWFUPLOAD.Init = function(FIELD_ID) {

	// When the field is hidden by default, the Flash object's width is 0 so you cannot do anything with it
	// Here we force the width, by getting the width of the parent
	var ButtonWith = 120;
	if ($('#ChannelImagesSelect_'+FIELD_ID).is(':visible') !== false){
		ButtonWith = ($('#ChannelImagesSelect_'+FIELD_ID).parent().width() + 10);
	}

	ChannelImages.SWF[FIELD_ID] = new SWFUpload({

		// Backend Settings
		flash_url : ChannelImages.ThemeURL + 'swfupload.swf',
		upload_url: ChannelImages.AJAX_URL,
		post_params: {
			ajax_method: 'upload_file',
			field_id: FIELD_ID,
			key: ChannelImages.Fields['Field_'+FIELD_ID].key
		},
		file_post_name: 'channel_images_file',
		prevent_swf_caching: true,
		assume_success_timeout: 0,

		// File Upload Settings
		file_size_limit : 0,
		file_types : '*.jpg;*.jpeg;*.png;*.gif',
		file_types_description : 'Images',
		file_upload_limit : 0,
		file_queue_limit : 0,

		// Event Handler Settings
		swfupload_preload_handler : function(){},
		swfupload_load_failed_handler : function(){},
		file_dialog_start_handler : function(){},
		file_queued_handler : ChannelImages.SWFUPLOAD.QueuedHandler,
		file_queue_error_handler : function(){},
		file_dialog_complete_handler : ChannelImages.SWFUPLOAD.DialogCompleteHandler,
		upload_resize_start_handler : function(){},
		upload_start_handler : ChannelImages.SWFUPLOAD.StartHandler,
		upload_progress_handler : ChannelImages.SWFUPLOAD.ProgressHandler,
		upload_error_handler : function(file, error, message){
			// Sometimes we cancel the upload because of an error, no need to display "Cancelled error"
			if (error == '-270') return;
			if (error == '-280') return;

			$('#ChannelImages_' + FIELD_ID).find('.ImagesQueue .Uploading:first').removeClass('Uploading').addClass('Error');
			ChannelImages.ErrorMSG('Upload Failed:' + error + ' MSG:' + message, FIELD_ID);
			ChannelImages.Debug('Upload Failed:' + error + ' MSG:' + message);
		},
		upload_success_handler : ChannelImages.SWFUPLOAD.SuccessHandler,
		upload_complete_handler : function(){},

		// Button Settings
		button_image_url : '', // Relative to the SWF file
		button_placeholder_id : 'ChannelImagesSelect_'+FIELD_ID,
		button_width: ButtonWith,
		button_height: 16,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
		button_action: SWFUpload.BUTTON_ACTION.SELECT_FILES,

		// Custom Settings
		custom_settings : {
			field_id : FIELD_ID
		},

		// Debug Settings
		debug: false
	});

	// Cancel Upload
	$('#ChannelImages_' + FIELD_ID).find('a.StopUpload').click(function(){

		$('#ChannelImages_' + FIELD_ID).find('.ImageQueue div.File').not('div.Done').each(function(index,elem){
			var Elem = $(elem);
			ChannelImages.SWF[FIELD_ID].cancelUpload(Elem.attr('id'), true);
			Elem.fadeOut(1400, function(){
				Elem.remove();
				$('#ChannelImages_' + FIELD_ID).find('div.UploadProgress').hide();
			});
			ChannelImages.SWFUPLOAD.CleanQueue(FIELD_ID);
			$('#ChannelImages_' + FIELD_ID).find('a.StopUpload').hide();
		});

		$(this).hide();

		return false;
	});
};

//********************************************************************************* //

ChannelImages.SWFUPLOAD.QueuedHandler = function(File) {

	// Attempt to add file to Queue
	if (ChannelImages.AddFileToQueue(this.customSettings.field_id, File.name, File.id, '') == false){

		// If fails, cancel this upload
		this.cancelUpload(File.id, false);
		return false;
	}
};

//********************************************************************************* //

ChannelImages.SWFUPLOAD.DialogCompleteHandler = function(FilesSelected, ImagesQueued, TotalImagesQueued) {
	// Reset Errors
	ChannelImages.LastError = '';
	ChannelImages.UploadError = false;

	// Start Upload!
	this.startUpload();
};

//********************************************************************************* //

ChannelImages.SWFUPLOAD.StartHandler = function(File) {

	// Was there an error? Stop! And cancel all
	if (ChannelImages.UploadError == true) {
		ChannelImages.SWFUPLOAD.CleanQueue(this.customSettings.field_id);
		return false;
	}

	ChannelImages.LastError = '';

	// Add the UploadingClass
	$('#' + File.id).removeClass('Queued').addClass('Uploading');

	// Show StopUpload
	$('#ChannelImages_' + this.customSettings.field_id).find('.StopUpload').show();
};

//********************************************************************************* //

ChannelImages.SWFUPLOAD.ProgressHandler = function(file, bytesLoaded, bytesTotal) {
	ChannelImages.UploadProgress(this.customSettings.field_id, file.sizeUploaded, file.size, file.averageSpeed);
};

//********************************************************************************* //

ChannelImages.SWFUPLOAD.SuccessHandler = function(File, serverData, response) {

	// Store the current file queue
	var FileQueue = $('#' + File.id);
	var FIELD_ID = this.customSettings.field_id;

	try {
		// Parse the JSON, if it failed we have error
		var rData = JSON.parse(serverData);
	}
	catch(errorThrown) {
		ChannelImages.LastError = serverData;
		ChannelImages.ErrorMSG(ChannelImages.LANG.xhr_reponse_error, FIELD_ID);
		ChannelImages.Debug("Server response was not as expected, probably a PHP error. \n RETURNED RESPONSE: \n" + serverData);
		FileQueue.removeClass('Uploading').addClass('Error');
		ChannelImages.SWFUPLOAD.CleanQueue(FIELD_ID);
		return false;
	}


	// Was it an success?
	if (rData.success == 'yes') {

		// Mark it as done
		FileQueue.removeClass('Uploading').addClass('Done');

		// Hide it?
		var TempCurrentFile = FileQueue;
		setTimeout(function(){
			if (TempCurrentFile.hasClass('Done') === false) return;
			TempCurrentFile.slideUp('slow');
		}, 2000);

		// Add the new file to the table
		ChannelImages.AddNewFile(rData, FIELD_ID);

		// Hide the progressbox! when done
		if ($('#ChannelImages_' + FIELD_ID).find('.ImagesQueue .Queued:first').length < 1) $('#ChannelImages_' + FIELD_ID).find('.UploadProgress').css('display', 'none');
	}

	// Upload uploaded but returned success=no
	else {
		FileQueue.removeClass('Uploading').addClass('Error');
		ChannelImages.ErrorMSG(rData.body, FIELD_ID);
		ChannelImages.Debug('ERROR: ' + rData.body);
		ChannelImages.SWFUPLOAD.CleanQueue(FIELD_ID);
	}

	// Hide StopUpload
	$('#ChannelImages_' + FIELD_ID).find('.StopUpload').hide();

};

//********************************************************************************* //

ChannelImages.SWFUPLOAD.CleanQueue = function(FIELD_ID) {

	$('#ChannelImages_'+FIELD_ID).find('.ImagesQueue').find('.Queued').each(function(i, Element){
		// Cancel the file
		this.cancelUpload(Element.id, false);

		// Kill it
		$(this).remove();
	});
};

//********************************************************************************* //

ChannelImages.OpenPerImageAction = function(Event){
	Event.preventDefault();

	var FIELD_ID = $(Event.target).closest('div.CIField').data('fieldid');

	var PostParams = {XID: EE.XID, ajax_method:'apply_action'};
	PostParams.entry_id = $('input[name=entry_id]').val();
	PostParams.site_id = ChannelImages.site_id;
	PostParams.field_id = FIELD_ID;
	PostParams.key = ChannelImages.Fields['Field_'+FIELD_ID].key;

	// Grab the json
	var ItemObj = $(Event.target).closest('.Image');
	var jsondata = ItemObj.find('textarea.ImageData').html();
	jsondata = JSON.parse(jsondata);

	PostParams.image_id = jsondata.image_id;
	PostParams.filename = jsondata.filename;

	$.colorbox({
		innerWidth:550,
		innerHeight:400,
		html:$.base64Decode($(Event.target).closest('div.CIField').find('.PerImageActionHolder:first').text()),
		onComplete: function(){
			var Wrapper = $('#cboxContent');

			Wrapper.find('.SelectAction').change(function(e){
				if (!$(e.target).val()) return;

				var Content = $.base64Decode( Wrapper.find('.ActionSettings .'+$(e.target).val()).text() ); // Decode Text
				Wrapper.find('.ActionHolder').html(Content).find('.ChannelImagesTable').css('width', '100%');
				setTimeout(function(){
					Wrapper.find('.ActionHolder').find('input,select,textarea').each(function(index,elem){
						attr = $(elem).attr('name').replace(/\[action_groups\]\[.*?\]\[actions\]/, '');
						$(elem).attr('name', attr);
					});
				}, 200);
			});

			Wrapper.find('.PreviewImage').click(function(e){
				e.preventDefault();

				if (!Wrapper.find('.SelectAction').val()) return;
				Wrapper.find('.ApplyingAction').show();
				Wrapper.find('.PreviewHolder').empty();

				$.colorbox.resize({height:'80%', width:'60%'});
				PostParams.stage = 'preview';
				PostParams.size = Wrapper.find('.ImageSizes input:checked').val();
				PostParams.action = Wrapper.find('.SelectAction').val();

				Wrapper.find('.ActionHolder').find('input,select,textarea').each(function(index, elem){
					PostParams[$(elem).attr('name')] = $(elem).val();
				});

				$.post(ChannelImages.AJAX_URL, PostParams, function(rData){
					Wrapper.find('.ApplyingAction').hide();
					Wrapper.find('.PreviewHolder').html(rData);
				});
			});

			Wrapper.find('.SaveImage').click(function(e){
				e.preventDefault();

				if (!Wrapper.find('.SelectAction').val()) return;
				Wrapper.find('.ApplyingAction').show();

				PostParams.stage = 'save';
				PostParams.size = Wrapper.find('.ImageSizes input:checked').val();
				PostParams.action = Wrapper.find('.SelectAction').val();

				Wrapper.find('.ActionHolder').find('input,select,textarea').each(function(index, elem){
					PostParams[$(elem).attr('name')] = $(elem).val();
				});

				$.post(ChannelImages.AJAX_URL, PostParams, function(rData){

					Wrapper.find('.ApplyingAction').hide();
					$.colorbox.close();
				});
			});
		}
	});


};

//********************************************************************************* //

ChannelImages.OpenStoredImages = function(Event){

	var Target = $(Event.target).closest('div.CIField');
	var Parent = Target.find('.SearchImages');
	var FIELD_ID = Target.data('fieldid');

	// Is it hidden already?
	if (Parent.css('display') == 'none'){
		Parent.css('display', '');
	}
	else {
		Parent.css('display', 'none');
		return false;
	}

	// Entry Based?
	if ( ChannelImages.Fields['Field_'+FIELD_ID].settings.stored_images_search_type == 'entry' ){

		if (!Parent.data('event_binded')){
			Parent.find('.entryfilter .filter select').bind('change', {field_id:FIELD_ID}, ChannelImages.StoredImagesLoadEntries);

			// Activate Filter
			Parent.find('.entryfilter .filter input').bind('keyup', {field_id:FIELD_ID}, ChannelImages.StoredImagesLoadEntries);

			// Disable Enter!
			Parent.find('.entryfilter .filter input').keydown(function(event){ if (event.keyCode == 13) return false;  });

			Parent.data('event_binded', true);
		}

		Parent.find('.entryfilter .filter select').trigger('change');
	}
	else {
		if (!Parent.data('event_binded')){
			Parent.find('.imagefilter .filter select').bind('change', {field_id:FIELD_ID}, ChannelImages.StoredImagesLoadImages);
			Parent.find('.imagefilter .filter input').bind('keyup', {field_id:FIELD_ID}, ChannelImages.StoredImagesLoadImages);

			// Disable Enter!
			Parent.find('.imagefilter .filter input').keydown(function(event){ if (event.keyCode == 13) return false;  });

			Parent.data('event_binded', true);
		}
		Parent.find('.imagefilter .filter select').trigger('change');
	}


	return false;
};

//********************************************************************************* //

ChannelImages.StoredImagesLoadEntries = function(Event){
	var FIELD_ID = Event.data.field_id;
	var Parent = ChannelImages.CFields.filter('#ChannelImages_'+FIELD_ID);

	Parent.find('.SearchImages .entryfilter .entries a').slideUp('fast', function(){
		$(this).remove();
	});

	Parent.find('.SearchImages .Loading').show();

	var Params = {};
	Params.ajax_method = 'load_entries';
	Params.field_id = FIELD_ID;
	Params.limit = Parent.find('.SearchImages .entryfilter .filter select').val();
	Params.filter = Parent.find('.SearchImages .entryfilter .filter input').val();
	Params.entry_id = $('input[name=entry_id]').val();

	$.get(ChannelImages.AJAX_URL, Params, function(rData){
		Parent.find('.SearchImages .Loading').hide();
		Parent.find('.SearchImages .entryfilter .entries').prepend(rData).find('a').bind('click', {field_id:FIELD_ID}, ChannelImages.StoredImagesLoadEntryImages);
	});

};

//********************************************************************************* //

ChannelImages.StoredImagesLoadEntryImages = function(Event){
	Event.preventDefault();

	var FIELD_ID = Event.data.field_id;
	var Parent = ChannelImages.CFields.filter('#ChannelImages_'+FIELD_ID);

	Parent.find('.SearchImages .entryimages .NoEntrySelect').hide();
	Parent.find('.SearchImages .SearchingForImages').show();
	Parent.find('.SearchImages .entryimages .images div').remove();

	var Params = {};
	Params.ajax_method = 'load_images';
	Params.field_id = FIELD_ID;
	Params.entry_id = $(Event.target).attr('rel');

	$.get(ChannelImages.AJAX_URL, Params, function(rData){
		Parent.find('.SearchImages .SearchingForImages').hide();
		Parent.find('.SearchImages .entryimages .images').append(rData);
		Parent.find('.SearchImages .entryimages .images a').colorbox({photo:true});
		Parent.find('.SearchImages .entryimages .images a span.add').bind('click', {field_id:FIELD_ID}, ChannelImages.AddImage);
	});

};

//********************************************************************************* //

ChannelImages.StoredImagesLoadImages = function(Event){
	var FIELD_ID = Event.data.field_id;
	var Parent = ChannelImages.CFields.filter('#ChannelImages_'+FIELD_ID);

	var ImgFilter = Parent.find('.SearchImages .imagefilter');
	ImgFilter.find('.Loading').show();
	ImgFilter.find('.images div').remove();

	var Params = {};
	Params.ajax_method = 'load_images';
	Params.field_id = FIELD_ID;
	ImgFilter.find('.filter').find('input, select').each(function(){
		Params[$(this).attr('rel')] = $(this).val();
	});

	$.get(ChannelImages.AJAX_URL, Params, function(rData){
		ImgFilter.find('.Loading').hide();
		ImgFilter.find('.images').html(rData);
		ImgFilter.find('.images a').colorbox({photo:true});
		ImgFilter.find('.images a span.add').bind('click', {field_id:FIELD_ID}, ChannelImages.AddImage);
	});

};

//********************************************************************************* //

ChannelImages.AddImage = function(Event){

	// Stop Defailt Event Stuff
	Event.preventDefault();
	Event.stopPropagation();

	var FIELD_ID = Event.data.field_id;
	var Parent = ChannelImages.CFields.filter('#ChannelImages_'+FIELD_ID);

	// How Many Images Remaining?
	if (ChannelImages.FilesRemaining(FIELD_ID) < 1){
		alert(ChannelImages.LANG.file_limit_reached);
		return false;
	}

	// Mark it as Added!
	$(Event.target).addClass('Loading');

	var Params = {};
	Params.ajax_method = 'add_linked_image';
	Params.field_id = FIELD_ID;
	Params.image_id = $(this).closest('a').attr('rel');

	// Get Image Details
	$.get(ChannelImages.AJAX_URL, Params, function(rData){

		ChannelImages.AddNewFile(rData, FIELD_ID);
		$(Event.target).closest('div.img').slideUp('slow', function(){$(this).remove();});
	}, 'json');

};


//********************************************************************************* //

ChannelImages.ErrorMSG = function (Msg, FIELD_ID){
	ChannelImages.UploadError = true;

	var CIFIELD = $('#ChannelImages_' + FIELD_ID).find('.UploadProgress').show();
	CIFIELD.find('.percent').html('<span style="color:brown; font-weight:bold;">' + Msg + '</span>');
	CIFIELD.find('.speed, .uploadedBytes, .totalBytes').empty();
};

//********************************************************************************* //

ChannelImages.Debug = function(msg){
	try {
		console.log(msg);
	}
	catch (e) {	}
};

//********************************************************************************* //

ChannelImages.RefreshImages = function(draft){

	if (ChannelImages.Refreshing === true) return;

	ChannelImages.Refreshing = true;

	var Params = {};
	Params.draft = (!draft) ? 'no' : 'yes';
	Params.ajax_method = 'refresh_images';
	Params.entry_id = $('input[name=entry_id]').val();

	var Called = false;

	// Loop over all fields
	ChannelImages.CFields.each(function(i, e){
		Params.field_id = $(e).data('fieldid');

		// Grab the new field_id
		$('#ChannelImages_'+Params.field_id).find('.temp_key').attr('value', ChannelImages.Fields['Field_'+Params.field_id].key);

		$(e).find('.AssignedImages').empty();

		$.post(ChannelImages.AJAX_URL, Params, function(rData){

			if (!Called) ChannelImages.InitFields();
			Called = true;

			for (var File in rData.images){

				// Is this the last one? So we can trigger sync..
				var Sync = ((rData.images.length -1) == File) ? true : false;

				ChannelImages.AddNewFile(rData.images[File], rData.images[File].field_id, Sync);
			}

			ChannelImages.Refreshing = false;

		}, 'json');
	});
};

//********************************************************************************* //

ChannelImages.OpenImageEdit = function(e){
	e.preventDefault();

	var FIELD_ID = $(e.target).closest('div.CIField').data('fieldid');

	var PostParams = {XID: EE.XID};
	PostParams.entry_id = $('input[name=entry_id]').val();
	PostParams.site_id = ChannelImages.site_id;
	PostParams.field_id = FIELD_ID;
	PostParams.key = ChannelImages.Fields['Field_'+FIELD_ID].key;

	$('.EditImageWrapper').remove();

	// Grab the json
	var ItemObj = $(e.target).closest('.Image');
	var jsondata = ItemObj.find('textarea.ImageData').html();
	jsondata = JSON.parse(jsondata);

	PostParams.image_id = jsondata.image_id;
	PostParams.filename = jsondata.filename;

	var Cloned = $('<tr class="EditImageWrapper"><td colspan="99" style="padding:0"><div style="padding:20px">loading....</div></td></tr>');
	Cloned.data(jsondata);
	Cloned.data('post_params', PostParams);

	if (ItemObj.hasClass('image-tile')) {
		$(e.target).closest('tbody').append(Cloned);
	} else {
		$(e.target).closest('tr').after(Cloned);
	}

	$.post(ChannelImages.AJAX_URL+'&ajax_method=edit_image_ui', PostParams, function(data){
		Cloned.find('td').html(data);

		var Elem = Cloned.parent();
		Elem.find('ul.sizes').delegate('a', 'click', ChannelImages.EditImage_ChangeSize);
		Elem.find('ul.actions').delegate('a', 'click', ChannelImages.EditImage_TriggerAction);
		Elem.find('.bottombar').delegate('.apply_crop', 'click', ChannelImages.ApplyAction);
		Elem.find('.bottombar').delegate('.set_sel', 'click', ChannelImages.jCropSetSelection);
		Elem.find('.bottombar').delegate('.cancel_crop', 'click', function(e){
			var Parent = $(e.target).closest('tr');
			Parent.find('ul.actions li.current').removeClass('current');
			Parent.find('.crop_holder').css('display', 'none');
			if (ChannelImages.jcrop) {
				ChannelImages.jcrop.destroy();
			}
		});
		Elem.find('.bottombar').delegate('.save_image', 'click', ChannelImages.EditImageSave);
		Elem.find('.bottombar').delegate('.cancel_image', 'click', function(e){
			$(e.target).closest('.EditImageWrapper').remove();
		});

		Elem.data('post_params', PostParams);
	});
};

//********************************************************************************* //

ChannelImages.EditImage_ChangeSize = function(Event){
	Event.preventDefault();
	var Parent = $(Event.target).closest('tr');
	var Elem = $(Event.target);
	var SizesElem = Parent.find('ul.sizes');

	SizesElem.find('li.current').removeClass('current');
	Elem.closest('li').addClass('current');

	if (Elem[0].getAttribute('data-name') != 'ORIGINAL') {
		if (Elem[0].getAttribute('data-width') != 'FALSE') Parent.find('ul.actions .rotate').addClass('disabled');
		else Parent.find('ul.actions .rotate').removeClass('disabled');
		Parent.find('.regen_sizes').css('display', 'none');
	} else {
		Parent.find('ul.actions .rotate').removeClass('disabled');
		Parent.find('.regen_sizes').css('display', 'inline');
	}

	if (ChannelImages.jcrop) {
		var Clone = $('#jcrop_target').clone().removeAttr('style');
		ChannelImages.jcrop.destroy();
		$('#jcrop_target').remove();
		Parent.find('.imgholder').prepend(Clone);
	}

	Parent.find('.crop_holder').css('display', 'none');
	Parent.find('.save_image_holder').css('display', 'inline');
	Parent.find('ul.actions').find('li.current').removeClass('current');

	var Params = Parent.data('post_params');
	Params.ajax_method = 'edit_image_ui';
	Params.refresh_images = 'yes';

	Parent.css('opacity', 0.2);
	$.post(ChannelImages.AJAX_URL, Params, function(rData){

		if (rData.img_url){
			var IMG = $('<img id="jcrop_target"/>');
			IMG.attr('src', rData.img_url);
			IMG.attr('data-alturl', rData.img_url_alt);
			IMG.attr('data-realwidth', rData.img_info[0]);
			IMG.attr('data-realheight', rData.img_info[1]);

			if (ChannelImages.jcrop) {
				ChannelImages.jcrop.destroy();
			}

			$('#jcrop_target').replaceWith(IMG);
			//Parent.find('.imgholder').prepend(IMG);
		}

		Parent.css('opacity', 1);
	}, 'json');
};

//********************************************************************************* //

ChannelImages.EditImage_TriggerAction = function(Event){
	Event.preventDefault();
	var Parent = $(Event.target).closest('tr');
	var Elem = $(Event.target);
	var Action = Event.target.getAttribute('data-action');
	var Size = Parent.find('ul.sizes .current a');

	if ($(Event.target).parent().hasClass('current') === true) return false;

	// Add Active!
	var ActionsElem = Parent.find('ul.actions');
	ActionsElem.find('li.current').removeClass('current');
	Elem.closest('li').addClass('current');

	if (Elem.hasClass('disabled') === true) return;

	if (Action == 'crop') {

		Parent.find('.crop_holder').css('display', 'inline');
		Parent.find('.save_image_holder').css('display', 'none');

		var Target = $('#jcrop_target');
		var Options = {};
		Options.outerImage = Target[0].getAttribute('data-alturl');
		Options.bgOpacity = 1;
		Options.trueSize = [ Target[0].getAttribute('data-realwidth'), Target[0].getAttribute('data-realheight') ];
		Options.onChange = ChannelImages.jCropChangeCoord;

		if (Size[0].getAttribute('data-name') != 'ORIGINAL') {
			var Width = Size[0].getAttribute('data-width');
			var Height = Size[0].getAttribute('data-height');

			if (Width == 'FALSE') {
				Options.setSelect = [ 250, 250, 100, 100 ];
			} else {
				Options.setSelect = [ Size[0].getAttribute('data-width'), Size[0].getAttribute('data-height'), 50, 50 ];
				Options.aspectRatio = Size[0].getAttribute('data-width') / Size[0].getAttribute('data-height');
			}
		} else {
			Options.setSelect = [ 250, 250, 100, 100 ];
		}

		ChannelImages.jcrop = $.Jcrop('#jcrop_target', Options);

	} else {
		Parent.find('.crop_holder').css('display', 'none');
		ChannelImages.ApplyAction(Event);
	}

};

//********************************************************************************* //

ChannelImages.jCropChangeCoord = function(c){
	$('.crop_holder .jcrop_x').attr('value', c.x);
	$('.crop_holder .jcrop_y').attr('value', c.y);
	$('.crop_holder .jcrop_x2').attr('value', c.x2);
	$('.crop_holder .jcrop_y2').attr('value', c.y2);
};

//********************************************************************************* //

ChannelImages.jCropSetSelection = function(Event){
	var Parent = $(Event.target).closest('tr');
	if (ChannelImages.jcrop){
		ChannelImages.jcrop.animateTo([Parent.find('.jcrop_x').val(), Parent.find('.jcrop_y').val(), Parent.find('.jcrop_x2').val(), Parent.find('.jcrop_y2').val()]);
	}
};

//********************************************************************************* //

ChannelImages.ApplyAction = function(e){

	var Parent = $(e.target).closest('tr');

	var Params = Parent.data('post_params');
	Params.ajax_method = 'apply_edit_image_action';
	Params.size = Parent.find('ul.sizes .current a')[0].getAttribute('data-name');
	Params.action = Parent.find('ul.actions .current a')[0].getAttribute('data-action');

	if (Params.action == 'crop') {
		Params.selection = ChannelImages.jcrop.tellSelect();
	}

	Parent.find('p.loading').show();

	$.post(ChannelImages.AJAX_URL, Params, function(rData){

		if (rData.img_url){
			var IMG = $('<img id="jcrop_target"/>');
			IMG.attr('src', rData.img_url);
			IMG.attr('data-alturl', rData.img_url_alt);
			IMG.attr('data-realwidth', rData.img_info[0]);
			IMG.attr('data-realheight', rData.img_info[1]);

			if (ChannelImages.jcrop) {
				ChannelImages.jcrop.destroy();
				Parent.find('.crop_holder').css('display', 'none');
				Parent.find('.save_image_holder').css('display', 'inline');
			}

			$('#jcrop_target').remove();
			Parent.find('.imgholder').prepend(IMG);
		}

		Parent.find('p.loading').hide();
		Parent.find('ul.actions li.current').removeClass('current');

	}, 'json');
};

//********************************************************************************* //

ChannelImages.EditImageSave = function(e){

	var Parent = $(e.target).closest('tr');
	var Params = Parent.data('post_params');
	Params.ajax_method = 'edit_image_save';
	Params.size = Parent.find('ul.sizes .current a')[0].getAttribute('data-name');
	Params.regen_sizes = Parent.find('.regen_sizes input:checked').val();

	Parent.find('p.loading').show();

	var d = new Date();

	$.post(ChannelImages.AJAX_URL, Params, function(rData){

		$(e.target).closest('table').find('.ImgUrl img').each(function(i, elem){
			$(elem).attr('src', $(elem).attr('src')+'&'+d.getTime() );
		});

		Parent.remove();

		//if (typeof(Bwf) == 'object') ChannelImages.RefreshImages(Bwf._transitionInstance.draftExists);
		//else ChannelImages.RefreshImages(false);

	}, 'json');
};

//********************************************************************************* //

ChannelImages.OpenImageReplace = function(e){
	e.preventDefault();

	// Grab the json
	var ItemObj = $(e.target).closest('.Image');
	var jsondata = ItemObj.find('textarea.ImageData').html();
	jsondata = JSON.parse(jsondata);

	$.colorbox({title: 'Replace Image', iframe: true, width:300, height:300, href:ChannelImages.AJAX_URL+'&ajax_method=display_replace_image_ui&image_id='+jsondata.image_id});
};

//********************************************************************************* //

ChannelImages.EditorOpenModal = function(obj, event, key){

	var handler = $.proxy(function(){
		var Modal = $('#redactor_modal');

		Modal.find('.WCI_Images').tabs().find('.CImage').click($.proxy(function(e){ ChannelImages.EditorSelectImage(obj, e); }, obj));
		Modal.find('.redactor_btn_modal_insert').click($.proxy(function(e){ ChannelImages.EditorInsertImage(obj, e); }, obj));

	}, obj);

	var endCallback = function(url){

	};

	var HTML = [];
	HTML.push('<div class="WCI_Images">');

	HTML.push('<ul class="tabs">');
	for (var FIELD in ChannelImages.Fields){
		HTML.push('<li><a href="#'+FIELD+'">'+ChannelImages.Fields[FIELD].field_label+'</a></li>');
	}

	HTML.push('</ul>');

	for (FIELD in ChannelImages.Fields){
		HTML.push('<div id="' + FIELD + '" class="tabcontent">');


		if (typeof(ChannelImages.Fields[FIELD].wimages) == 'undefined' || ChannelImages.Fields[FIELD].wimages.length === 0){
			HTML.push('<p>No images have yet been uploaded.</p>');
		} else {

			HTML.push('<div class="imageholder">');
			for (var i = 0; i < ChannelImages.Fields[FIELD].wimages.length; i++) {
				var IMG = ChannelImages.Fields[FIELD].wimages[i];
				HTML.push('<div class="CImage"><img src="'+IMG.big_img_url+'" title="'+IMG.title+'" alt="'+IMG.description+'" data-filename="'+IMG.filename+'"></div>');
			}

			HTML.push('</div>');

			HTML.push('<br clear="all">');

			HTML.push('<div class="sizeholder">');
			HTML.push('<ul>');

			var Checked = false;

			if (ChannelImages.Fields[FIELD].settings.wysiwyg_original == 'yes') {
				Checked = true;
				HTML.push('<li><input name="size_'+FIELD+'" type="radio" value="original" checked> ORIGINAL</li>');
			}

			if (typeof(ChannelImages.Fields[FIELD].settings.action_groups) != 'undefined'){

				for (i in ChannelImages.Fields[FIELD].settings.action_groups) {
					if (ChannelImages.Fields[FIELD].settings.action_groups[i].wysiwyg != 'yes') continue;
					var CheckText = (Checked === false) ? 'checked' : '';
					HTML.push('<li><input name="size_'+FIELD+'" type="radio" value="'+ChannelImages.Fields[FIELD].settings.action_groups[i].group_name+'"  '+CheckText+'> '+ChannelImages.Fields[FIELD].settings.action_groups[i].group_name+'</li>');
					Checked = true;
				}

			}

			HTML.push('</ul>');
			HTML.push('<br clear="all">');
			HTML.push('</div>');

		}

		HTML.push('</div>');
	}

	HTML.push('</div>'); // WCI Images
	HTML.push('<br>');

	var ModalContent = '<div id="redactor_modal_content">' + HTML.join('') +
				'<div id="redactor_modal_footer">' +
					'<span class="redactor_btns_box">' +
						'<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
						'<input type="button" class="redactor_modal_btn redactor_btn_modal_insert" value="' + RLANG.insert + '" />' +
					'</span>' +
				'</div></div>';

	obj.modalInit('Channel Images', ModalContent, 600, handler, endCallback);
};

//********************************************************************************* //

ChannelImages.EditorSelectImage = function(obj, e){

	if (typeof(e.target) == 'undefined') return;

	var Target = jQuery(e.target);

	// Remove all other
	Target.closest('.tabcontent').find('.CImage').removeClass('Selected');

	Target.closest('.CImage').addClass('Selected');
};

//********************************************************************************* //

ChannelImages.EditorInsertImage = function(obj, e){
	var Wrapper = $('#redactor_modal').find('.tabcontent:visible');

	if ( Wrapper.find('.Selected').length === 0) return;

	var Selected = Wrapper.find('.Selected img');

	var IMGSRC = Selected.attr('src');

	var filename = Selected.data('filename');
	var dot = filename.lastIndexOf('.');
	var extension = filename.substr(dot,filename.length);

	var Size = Wrapper.find('.sizeholder input[type=radio]:checked').val();
	var OLDFILENAME = Selected.data('filename');

	if (Size != 'original'){
		var NewName = filename.replace(extension, '__'+Size+extension);
		IMGSRC = IMGSRC.replace(/f\=(.*?)\&/, 'f='+NewName+'&');

	}
	else {
		IMGSRC = IMGSRC.replace(/f\=(.*?)\&/, 'f='+filename+'&');
	}

	var img = '<img src="'+IMGSRC+'" alt="'+Selected.attr('alt')+'" class="ci-image ci-'+Size+'">';

	Selected.parent().removeClass('Selected');

	obj.insertHtml(img);
	obj.syncCode();
	obj.modalClose();
	obj.observeImages();
};

//********************************************************************************* //
