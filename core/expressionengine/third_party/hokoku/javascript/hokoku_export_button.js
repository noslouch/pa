$(document).ready(function () {

	/**
	 * Show dialog with export options
	 * =================================
	 */
	$("button#hokoku-export-button").click(function(){
		$('#hokoku-modal').dialog({
			modal: true,
			title: $('#hokoku-modal').find('#exportoptions').attr('data-title'),
		});
	});

	/**
	 * Saving of Zenbu details in session if
	 * moving away from page using "Manage profiles" 
	 */
	$("li.manage-profiles a").live('click', function (e) {
		var query = $(".pageContents").children("form#filterMenu").serialize();
		var manage_profiles_link = $(this).attr("href");
		theAction = $("form#filterMenu").attr("action");
		theNewAction = theAction.replace('&module=zenbu&method=index', '&module=zenbu&method=save_rules_by_session');

		// If Command/Ctrl button is pressed,
		// dont prevent default behaviour. Might miss out on
		// saving filter rules in session, but at least the 
		// new tab opening will work fairly universally.
		if( ! e.ctrlKey && ! e.metaKey)
		{
			e.preventDefault();
		}

		$.ajax({
			type: 		"POST",
			//dataType: "json",
			url: 		theNewAction,
			data: 		query,
			success: function(results){
						if(e.ctrlKey || e.metaKey)
						{
						} else {
							window.location.href = manage_profiles_link;
						}
				     },
			error: function(results, url){
						console.log("Entry Link: Error with session-storing function (" + results + url + ") ");
				     }
		});	
	});

	/**
	 * ==============
	 * Export binding
	 * ==============
	 */
	$(".export").click(function () {
		
		var here = $(this);
		
		if($(this).attr("id") == "openprofiles")
		{

			if($(this).children("span.onsubmit").hasClass("invisible"))
			{
				$(this).children("span").addClass("invisible");
				$(this).children("span.onsubmit").removeClass("invisible");
			} else {
				$(this).children("span").removeClass("invisible");
				$(this).children("span.onsubmit").addClass("invisible");
			}
			return false;
		}

		var profile_id = $(this).val() != "undefined" ? $(this).val() : "";

		/**
		*	Data for ajax function to save search in session
		*/
		var query = $(".pageContents").children("form#filterMenu").serialize();

		/**
		*	Modify the search form URL to Hokoku 
		*/
		theAction = $("form#filterMenu").attr("action");
		
		/**
		 * 	Remove other buttons to avoid triggering 
		 * 	two exports at the same time, which can get 
		 * 	rough on resources.
		 */
		$('button.export, li.header').addClass('invisible');
		$(this).removeClass('invisible').attr('disabled', 'disabled');

		$('.hokoku_controls').removeClass('invisible');
		
		/**
		*	Save search in session, then submit to Hokoku
		*/
		runExport(here, theAction, profile_id, '', 0, query);
		
	});

	/**
	 * ==============
	 * Export cancelling
	 * ==============
	 */
	$('body').delegate('.exportcancel', 'click', function(){
		
		here = $(this);

		$('button.export').each(function(){
			if( ! $(this).hasClass('invisible') )
			{
				$(this).attr('data-status', 'cancel');
				$(this).html(here.attr('data-label'));
			}
		})
	});

	/**
	 * =========
	 * runExport
	 * =========
	 * @param  {here}		here		The button
	 * @param  {theAction}	theAction	The Action URL
	 * @param  {profile_id}	profile_id	The currently selected profile_id
	 * @param  {hash}		hash		The current export progress hash
	 * @param  {progress}	progress	A numerical value of progress
	 * @param  {query}		query		The form data
	 * @return void
	 */
	function runExport(here, theAction, profile_id, hash, progress, query)
	{
		theNewAction = theAction.replace('&module=zenbu&method=index', '&module=hokoku&method=export').replace(/&hash=[A-Za-z0-9]*/g, "").replace(/&profile_id=[0-9]*/g, "") + "&profile_id=" + profile_id + "&hash=" + hash;
		downloadUrl = theAction.replace('&module=zenbu&method=index', '&module=hokoku&method=download').replace(/&hash=[A-Za-z0-9]*/g, "").replace(/&profile_id=[0-9]*/g, "") + "&profile_id=" + profile_id + "&filename="; // encoded filename gets added later

		$("form#filterMenu").attr("action", "").attr("action", theNewAction );

		$.ajax({

			type:     	"POST",
			dataType: 	"json",
			url:      	theNewAction,
			data: 		query,
			beforeSend: function(){

					// Change the clicked button's message to "Exporting..."
					exportingMessage	= here.prev('span.exportingmessage').html();
					originalLabel		= here.attr('data-label');
					here.html(exportingMessage.replace(/\[HOKOKU_EXPORT_PROGRESS\]/g, progress));
					$("div.progressbar div").css({ 'width' : progress + '%' });
			
				},
			success: function(results){
					
					if(results.no_data == 'y')
					{
						alert(results.message);

						// Revert to the original form's action="" URL
						$("form#filterMenu").attr("action", theAction);

						// Revert to the original export profile label
						here.html(originalLabel);

						return;
					}

					//console.log(results);
					if(results.continue == 'y')
					{
						progress = results.progress;

						if(here.attr('data-status') == "cancel")
						{
							// Revert to the original form's action="" URL
							$("form#filterMenu").attr("action", theAction);

							// Revert to the original export profile label
							here.html(originalLabel);

							// Re-display export buttons, hide controls and progress bar
							$('button.export, li.header').removeClass('invisible');
							$('.hokoku_controls').addClass('invisible');

							// Remove cancel status to allow starting the export again
							here.removeAttr('disabled').removeAttr('data-status');

							hash = ''; // This likely does nothing
							console.log('Hokoku - Cancelling export');
							return;

						} else {
							runExport(here, theAction, profile_id, results.hash, progress, query)
						}
						
						return;
					
					} else {

						// Revert to the original form's action="" URL
						$("form#filterMenu").attr("action", theAction);
						
						// Revert to the original export profile label
						here.html(originalLabel);

						// Re-display export buttons, hide controls and progress bar
						$('button.export, li.header').removeClass('invisible');
						$('.hokoku_controls').addClass('invisible');
						
						// Remove cancel status to allow starting the export again
						here.removeAttr('disabled');

						// Download the file. results is the decoded filename
						//console.log(downloadUrl + results.message);
						location.href = downloadUrl + results.message;
						return;
					}
				
				},
			error: function(results, url){
				
					console.log('Error:');
					console.log(results);
				
				}
		});
	}



});