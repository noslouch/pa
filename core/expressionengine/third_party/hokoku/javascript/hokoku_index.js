$(document).ready(function () {

	$("button.savepreset").click(function (e) {
		$(this).children("span").addClass('invisible');
		$(this).children("span.onsubmit").removeClass('invisible');
	});

	/**
	*	Export options toggler
	*	======================
	*/
	$("select#export_format").change(function() {
		var theFormat = $(this).val();
		$(this).parent("span").parent("form").children("div#exportOptions").children("span").addClass('invisible');
		$(this).parent("span").parent("form").children("div#exportOptions").children("span").children('table').children('tbody').children('tr').children('td').children('input, select').attr("disabled", "disabled");
		$("span.options-" + theFormat).removeClass('invisible');
		$("span.options-" + theFormat).children('table').children('tbody').children('tr').children('td').children('input, select').removeAttr("disabled", "disabled");
	});

	/**
	*	Sample data toggler
	*	===================
	*/
	$("button.exampleData").click(function () {
		$("tbody#exampleData").slideToggle(0);
		$("button.exampleData").removeClass('invisible');
		$(this).addClass('invisible');
	});

	$("button.savepreset").click(function (event) {
		
		if($(this).next("input").hasClass('invisible'))
		{
			event.preventDefault();
			$(this).next("input").removeClass('invisible').removeAttr('disabled').focus();
		}

	});

	/**
	 * 	Excel option trigger for CSV
	 * 	============================
	 */

	$("input[name='excelcompat']").click(function (){


		tDel = $("input[id~=delimiter]");
		tEnc = $("input[id~=enclosure]");

		// Store original value if returning to it
		// by unchecking the Excel checkbox
		if( ! tDel.attr("origValue") && ! tEnc.attr("origValue"))
		{
			tDel.attr("origValue", tDel.val() );
			tEnc.attr("origValue", tEnc.val() );
		}

		if($(this).attr('checked'))
		{
			// Add the Excel-compatible tab value
			tDel.val('TAB').attr("readonly", "readonly");
			tEnc.val('"').attr("readonly", "readonly");

		} else {

			// Place the old value back and reset the attribute... attribution
			tDel.val( tDel.attr("origValue") ).removeAttr("origValue").removeAttr("readonly");
			tEnc.val( tEnc.attr("origValue") ).removeAttr("origValue").removeAttr("readonly");

		}

	});

	

	/**
	*	function doExport()
	*	===================
	*	Call the export method through AJAX.
	*	Loop if necessary for batch export.
	*
	*	@param	none
	*	@return	void
	*/
	function doExport()
	{
		var limit			= parseInt($("input[name='limit']").val());
		var perpage			= parseInt($("input[name='perpage']").val());
		var total_results	= parseInt($("input[name='total_results']").val());
		var query			= $("form#exportform").serialize();

		$(".loader").removeClass('invisible');

		$.ajax({
			type:     "POST",
			//dataType: "json",
			url:      EE.BASE+"&C=addons_modules&M=show_module_cp&module=hokoku&method=export",
			data: query,
			success: function(results) {
				var progressPercent = (limit + perpage) / total_results * 100;
				
				if(progressPercent > 100)
				{
					progressPercent = 100;
				}
				
				// If object returned is a JSON array, convert it to an object
				if($.parseJSON(results) != null)
				{
					results = $.parseJSON(results);
				}

				// If something wrong happens, post output to console
				if( ! results.progress_complete || results.progress_complete == undefined)
				{
					console.log(results);
					results.progress_complete = 'complete';
				}

				$("span#progress").show().html( parseInt(progressPercent, 0) + '% ' + results.progress_complete);
				$("input[name='perpage']").val(limit + perpage);
				
				if(limit + perpage <= total_results)
				{

					doExport();

				} else {

					$(".loader").addClass('invisible');
					$("span#exportcomplete").html(results.export_file_link);
					//console.log(results);

				}
			},
			error: function(results){
				console.log('ERROR: ' + results);
			}
		});
	}


	/**
	*	Export submission
	*	=================
	*/
	$("button.export").bind('click', function (event) {
		event.preventDefault();
		//$(".loader").removeClass('invisible');
		$("span#progress").empty();
		$("span#exportcomplete").empty();
		//$("div#progress").empty();
		$("input[name='limit']").val('100');
		$("input[name='perpage']").val('0');
		
		var query = $("form#exportform").serialize();

		/**
		*	First, check for duplicate files
		*	and give the user the choice to overwrite
		*	or cancel and rename output file
		*/
		$.ajax({
			type:     "POST",
			//dataType: "json",
			url:      EE.BASE+"&C=addons_modules&M=show_module_cp&module=hokoku&method=check_for_file",
			data: query,
			success: function(results) {
				if(results != '' && results != '""')
				{
					$("span#progress").empty();
					$(".loader").addClass('invisible');

					if(results.response == 'confirm')
					{
						var answer = confirm(results.message);
						if(answer === true)
						{
							doExport();
							
						} else {

							return false;
						
						}

					} else if (results.response == 'alert') {
						
						alert(results.message);
					}
				} else {

					doExport();
				
				}
			},
			error: function(results){
				console.log('ERROR: ' . results);
			}
		});	
	});


});