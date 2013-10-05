// ********************************************************************************* //
var ChannelImages = ChannelImages ? ChannelImages : {};
//********************************************************************************* //

$(document).ready(function() {

	$('.ImportMatrixImages .submit').click(ChannelImages.ImportMatrixImages);

	$('.ImportMatrixImages').delegate('a.show_ajax_error', 'click', function(e){
		$('#ci_ajax_error').show().find('iframe').contents().find('html').html($(e.target).data('ajax_error'));
		return false;
	});

});


//********************************************************************************* //

ChannelImages.ImportMatrixImages = function(e){
	var target = jQuery(e.target);
	var btnText = target.html();
	var parent = target.closest('form');
	var Current = parent.find('.CI_IMAGES').find('.Queued:first');
	var Params = parent.find(':input').serializeArray();

	if (Current.length === 0) return false;

	Params.push({name: 'ajax_method', value:'import_matrix_images'});
	Params.push({name: 'entry_id', value:Current.attr('rel')});
	Params.image_id = Current.attr('rel');

	Current.removeClass('Queued').addClass('label-info');
	target.html('importing, please wait...');
	parent.find('.errormsg').html('');

	$.ajax({
		type: 'POST',
		url: ChannelImages.AJAX_URL,
		data: Params,
		success: function(rData){
			if (rData.success == 'yes')	{
				ChannelImages.ImportMatrixImages(e);
				Current.removeClass('label-info').addClass('label-success');
			}
			else{
				Current.removeClass('label-info').addClass('label-important');
				parent.find('.errormsg').html('<strong style="color:red">'+rData.body+'</strong>');
			}

			target.html(btnText);
		},
		dataType: 'json',
		error: function(xhr, textStatus, errorThrown){
			Current.removeClass('label-info').addClass('label-important');
			target.html(btnText);
			parent.find('.errormsg').html('<strong style="color:red">AJAX Error!<a href="#" class="show_ajax_error">&nbsp;&nbsp;&nbsp;Click here to display the server response</strong>').find('a').data('ajax_error', xhr.responseText);
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
