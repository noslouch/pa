$(document).ready(function () {

	//	----------------------------------------
	//	Show loading/saving/updating spinner after clicking button
	//	----------------------------------------
	$("body").delegate("button.withloader", "click", function () {
		$(".loader").removeClass('invisible');
		$(this).find("span").addClass('invisible');
		$(this).find("span.onsubmit").removeClass('invisible');
	});

	//	----------------------------------------
	//	Display dialog box for copy saving
	//	----------------------------------------
	$("body").delegate("a.fancybox-inline", 'click', function() {
		$(this).next('div').clone().dialog({
			modal: true,
			width: 500,
		});
	});

	//	----------------------------------------
	//	Show "Edit label" text on hover
	//	----------------------------------------
	$("span.rule_label").hover(function () {
		$(this).addClass('hover');
		$(this).find('span').removeClass('invisible');
	});

	//	----------------------------------------
	//	Remove "Edit label" text when not hovering
	//	----------------------------------------
	$("span.rule_label").mouseleave(function () {
		$(this).removeClass('hover');
		$(this).find('span').addClass('invisible');
	});

	//	----------------------------------------
	//	Remove text and present input text field
	//	when title is clicked
	//	----------------------------------------
	$("span.rule_label").click(function () {
		$(this).addClass('invisible');
		$(this).next("div.search_title_form").removeClass("invisible");
		$(this).next("div.search_title_form").find('input[type=text]').focus();
	});

	//	----------------------------------------
	//	Save label text on blur, through ajax
	//	----------------------------------------
	$("div.search_title_form form input").blur(function () {
		
		here		= $(this);
		formUrl 	= $(this).closest('form').attr('action');
		formData 	= $(this).closest('form').serialize();

		$.ajax({
			type: 	"POST",
			dataType: "json",
			url: 	formUrl,
			data: 	formData,
			success: function(results){
				here.closest("div.search_title_form").addClass('invisible');
				here.closest("div.search_title_form").prev("span.rule_label").html(results + '<span class="invisible"><i class="icon-edit icon-large"></i></span>');
				here.closest("div.search_title_form").prev("span.rule_label").removeClass('invisible');
			},
			error: function(results, a, b){
				here.closest("div.search_title_form").addClass('invisible');
				here.closest("div.search_title_form").prev("span.rule_label").html(results);
				here.closest("div.search_title_form").prev("span.rule_label").removeClass('invisible');
				console.log(results);
			}
		});	
	});

	/**
	*	==============================
	*	Sortable
	*	==============================
	*/
	
	/**
	*  Make table rows sortable!
	*  Return a helper with preserved width of cells
	*/
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

    $("table.settingsTable tbody").sortable({
    	cancel: 'table.settingsTable tr th, .not-sortable',
    	start: function(){
        	$(this).children('tr:empty').html('<td colspan="5">&nbsp;</td>');
        	$(this).children('tr.ui-sortable-helper').addClass('hover');
        },
        stop: function(event, ui) {
	        $(this).parent("table").tablesorter({
				widgets: ['zebra'],
				headers: { 0: { sorter: false}, 1: { sorter: false}, 2: { sorter: false}, 3: { sorter: false} }
			});

			here		= $(this);
			formUrl 	= EE.BASE + '&C=addons_modules&M=show_module_cp&module=zenbu&method=update_search';
			formData	= $(this).find('td input').serialize();


			$.ajax({
				type: 	"POST",
				//dataType: "json",
				url: 	formUrl,
				data: 	formData,
				success: function(results){
					console.log(results);
				},
				error: function(results, a, b){
					console.log('ERROR');
					//console.log(results);
				}
			});	

        },
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        helper: fixHelper,
        revert: 200,
        cursor: 'move',
        distance: 15
    }).disableSelection();

	//	----------------------------------------
	//	Warning on deletion
	//	----------------------------------------
	
	$("a.delete").click(function () {

		var warning = $("div.warnings span.saved_search_delete_warning").text();
		var answer = confirm( warning );
		
		if (answer)
		{
			return;
		} else {
			return false;
		}
	});

});