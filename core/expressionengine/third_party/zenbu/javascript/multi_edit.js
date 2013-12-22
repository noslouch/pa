$(document).ready(function () {

	$("select[name='status_toggler']").bind('change', function () {
		var theSelection = $(this).val();
		$("select.status_dropdown").each(function () {
			var theDropdown = $(this);
			var optionArray = $('option', theDropdown);
			
			optionArray.each(function () {
				if($(this).val() == theSelection)
				{
					theDropdown.val(theSelection);
				}
			});
		});
	});


});