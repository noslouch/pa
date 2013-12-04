$(document).ready(function () {

	//	----------------------------------------
	//	Delete Profile Confirmation Alert
	//	----------------------------------------
	$("a.deleteprofile").bind('click', function(event) {
		event.preventDefault();
		var theProfileLabel = $(this).parent("td").siblings("td").eq(0).html();
		var theUrl = $(this).attr('href');
		var theMessage = $("div#profiledeletewarning").html() + ': ' + theProfileLabel;
		var answer = confirm(theMessage);
		if(answer)
		{
			window.location = theUrl;
		} else {
			return false;
		}
	});

	//	----------------------------------------
	//	Template Tag Helper Dialog
	//	----------------------------------------
	$('a.dialog').click(function(e){
		
		e.preventDefault();
		var here = $(this);

		$.ajax({
			url: $(this).attr('href'),
			type: 'GET',
			success: function(results) {
				$("#hokoku-tag-builder").html(results);
				$("#hokoku-tag-builder").dialog({
					width: '80%',
					modal: true,
					title: here.closest('table').find('th').eq(1).html(),
				});
			}
		})
	});

	//	----------------------------------------
	//	Click-and-select-O-matic
	//	----------------------------------------
	$('body').delegate("input.input-copy", 'click', function () {
		$(this).select();
	});

});