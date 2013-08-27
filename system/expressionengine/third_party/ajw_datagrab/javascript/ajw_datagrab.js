$(function(){

	var dialog = $('<div id="popup"></div>')
				.html('Put some stuff here...')
				.dialog({
					autoOpen: false,
					width: 480,
					height: 320,
					resizable: false,
					position: ["center", "center"],
					modal: true,
					draggable: true,
					title: 'Configure field',
					open: function (q, r) {
						// $("#popup").html( "Value is : " + $("select[name=comments]").val() );
					},
					close: function (q, r) {
						// $("button[name=configure_comments]").html('OK');
					}
				});

		$(".popup").click( function() {
			dialog.dialog('open');
		});

});