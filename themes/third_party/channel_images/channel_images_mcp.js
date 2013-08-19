// ********************************************************************************* //
var ChannelImages = ChannelImages ? ChannelImages : {};
//********************************************************************************* //

$(document).ready(function() {

	$('#regen_fields').delegate('.label', 'click', ChannelImages.GrabImages);
	$('#start_regen').bind('click', ChannelImages.StartRegen);
	$('#regen_images').delegate('.show_ajax_error', 'click', ChannelImages.ShowAjaxError);

	$('.ImportMatrixImages .submit').click(ChannelImages.ImportMatrixImages);
});

//********************************************************************************* //

ChannelImages.GrabImages = function(Event){

	$('#regen_images').find('tbody').empty().append('<tr><td colspan="99"><p class="loading">loading......</p></td></tr>');
	$.post(ChannelImages.AJAX_URL, {ajax_method:'grab_image_ids', field_id:Event.target.getAttribute('data-field')}, function(rData){

		var HTML = '';
		HTML += '<tbody>';

		for (var i = 0; i < rData.images.length; i++) {
			HTML += '<tr data-id="'+rData.images[i].image_id+'">';
			HTML += '<td>'+ rData.images[i].image_id +'</td>';
			HTML += '<td>'+ rData.images[i].filename +'</td>';
			HTML += '<td>'+ rData.images[i].title +'</td>';
			HTML += '<td><a href="'+ChannelImages.EntryFormURL+'&channel_id='+rData.images[i].channel_id+'&entry_id='+rData.images[i].entry_id+'" target="_blank">'+ rData.images[i].entry_id +'</a></td>';
			HTML += '<td><span class="label label-waiting">Waiting</span></td>';
			HTML += '</tr>';
		}

		HTML += '</tbody>';
		$('#regen_images').find('tbody').remove();
		$('#regen_images').find('thead').after(HTML);
	}, 'json');

	return false;
};

//********************************************************************************* //

ChannelImages.StartRegen = function(Event){

	// Get the first in queue
	var Current = $('#regen_images').find('.label-waiting:first').closest('tr');

	Params = {};
	Params.XID = EE.XID;
	Params.ajax_method = 'regenerate_image_size';
	Params.image_id = Current.attr('data-id');

	Current.find('.label-waiting').removeClass('label-waiting').addClass('label-info').html('Processing');

	$.ajax({
		type: "POST",
		url: ChannelImages.AJAX_URL,
		data: Params,
		success: function(rData){
			if (rData.success == 'yes')	{
				Current.find('.label-info').removeClass('label-info').addClass('label-success').html('Done');
				Current.fadeOut('fast', function(){
					Current.remove();
				});
				ChannelImages.StartRegen(); // Shoot the next one!
			}
			else{
				Current.find('.label-info').removeClass('label-info').addClass('label-important').html('Failed');
			}
		},
		dataType: 'json',
		error: function(xhr){
			Current.find('.label-info').removeClass('label-info').addClass('label-important').html('Failed');
			Current.find('.label-important').after('&nbsp;&nbsp;<a href="#" class="label label-inverse show_ajax_error" style="color:#fff">Show Error</a>');
			Current.find('.show_ajax_error').data('ajax_error', xhr.responseText);
		}
	});


	return false;
};

//********************************************************************************* //

ChannelImages.ShowAjaxError = function(Event){
	Event.preventDefault();
	$('#error_log').find('.body').html( $(Event.target).data('ajax_error') );

	$('#error_log').show();

	$('html, body').stop().animate({
		scrollTop: $('#error_log').offset().top
	}, 1000);
	return false;
};

//********************************************************************************* //

ChannelImages.ImportMatrixImages = function(Event){

	var Current = jQuery(Event.target).closest('table').find('.CI_IMAGES').find('.Queued:first');
	var Params = jQuery(Event.target).closest('form').find(':input').serializeArray();

	if (Current.length === 0) return false;

	Params.push({name: 'ajax_method', value:'import_matrix_images'});
	Params.push({name: 'entry_id', value:Current.attr('rel')});
	Params.image_id = Current.attr('rel');

	Current.removeClass('Queued').addClass('Uploading');

	$.ajax({
		type: "POST",
		url: ChannelImages.AJAX_URL,
		data: Params,
		success: function(rData){
			if (rData.success == 'yes')	{
				ChannelImages.ImportMatrixImages(Event);
				Current.removeClass('Uploading').addClass('Done');
			}
			else{
				Current.removeClass('Uploading').addClass('Error');
			}
		},
		dataType: 'json',
		error: function(XMLHttpRequest, textStatus, errorThrown){
			Current.removeClass('Uploading').addClass('Error');
		}
	});

	return false;
};

//********************************************************************************* //

ChannelImages.Debug = function(msg){
	try {
		console.log(msg);
	}
	catch (e) {	}
};

//********************************************************************************* //
