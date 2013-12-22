;
<?php if(isset($edit_replace)) :?>

	
	<?php if(version_compare(APP_VER, "2.4", '<')) : ?>
		/**
		* -------------------------------------
		* General code used throughout the CP
		* -------------------------------------
		* Changing top navigation to replace Edit link to Zenbu link
		* Not needed anymore in EE 2.4+
		*/
		
		// Zenbu Content => Edit channel dropdown
		if($("#navigationTabs li.home").next("li.parent").children("ul").children("li").first().children("ul").children("li").children("a").attr("href") !== undefined)
		{
			var editLinkElement = $("#navigationTabs li.home").next("li.parent").children("ul").children("li").eq(1).children("a");
			var publish = $("#navigationTabs li.home").next("li.parent").children("ul").children("li.parent").first().children("a").next("ul").clone(true);
							// home						// Content		// Contentlist	// Publish			// Publish link	// chan list
			
			publish.find("a").each(function () {
				$(this).attr("href", $(this).attr("href").replace(/content_publish&(amp;)?M=entry_form/, "addons_modules&M=show_module_cp&module=zenbu"));
			});
			
			// Add the channel listing to Edit link
			if(publish.length !== 0){
				$("#navigationTabs li.home").next("li.parent").children("ul").children("li").eq(1).addClass("parent").append(publish);
			};
			
			// Change "Edit" link to point to addon
			if($("#navigationTabs li.home").next("li.parent").children("ul").children("li").eq(1).children("a").attr("href") !== undefined)
			{
				$("#navigationTabs li.home").next("li.parent").children("ul").children("li").eq(1).children("a").attr("href", editLinkElement.attr("href").replace(/content_edit/, "addons_modules&M=show_module_cp&module=zenbu"));
			};
		};
				
	<?php endif ?>
	
	/**
	* -------------------------------------
	* General link change throughout the CP
	* -------------------------------------
	* Attempts to change link to content_edit into zenbu links
	* For example, should cover "Edit" links in Structure, link in Overview page, Accessories, Zoo Flexible Admin, etc.
	*/
	$("a[href*=\"C=content_edit\"][class!=z_comment_link]").each(function (){
		$(this).attr("href", $(this).attr("href").replace(/content_edit/, "addons_modules&M=show_module_cp&module=zenbu"));
	});

<?php endif ?>



if ( /&D=cp&C=addons_modules&M=show_module_cp&module=zenbu/.test(window.location.href) )
{
	/**
	* -------------------------------------
	* Zenbu session storage through AJAX
	* -------------------------------------
	* Attempts to store rules in session by sending rules, through AJAX, to a method 
	* which saves rules as session variables, followed by direction to original page
	*/
	$(".pageContents").delegate("a.zenbu_entry_form_link", "click", function (e) {
				
		var query = $(".pageContents").children("form#filterMenu").serialize();
		var entry_form_link = $(this).attr("href");
		
		// If Command/Ctrl button is pressed,
		// dont prevent default behaviour. Might miss out on
		// saving filter rules in session, but at least the 
		// new tab opening will work fairly universally.
		if( ! e.ctrlKey && ! e.metaKey)
		{
			e.preventDefault();
		}

		$.ajax({
			type:     "POST",
			//dataType: "json",
			url:      EE.BASE + "&C=addons_modules&M=show_module_cp&module=zenbu&method=save_rules_by_session",
			data: query,
			success: function(results){
						if(e.ctrlKey || e.metaKey)
						{
						} else {
							window.location.href = entry_form_link;
						}
				     },
			error: function(results, url){
						console.log("Entry Link: Error with session-storing function (" + results + url + ") " + EE.BASE + "&C=addons_modules&M=show_module_cp&module=zenbu&method=save_rules_by_session");
				     }
		});					
	});

	$(".pageContents").delegate("button.zenbu_action_validate", "click", function (event) {

		var query = $(".pageContents").children("form#filterMenu").serialize();
		var here = $(this);
		event.preventDefault();
		
		// Stop running this script if no entry is selected
		var checkedCount = $(".checkcolumn:checked").length;
		if(checkedCount == 0)
		{
				$.ee_notice("<?=lang('selection_required')?>", {"type" : "error"});
				event.preventDefault();
				return false;
		}
		
		$.ajax({
			type:     "POST",
			//dataType: "json",
			url:      EE.BASE + "&C=addons_modules&M=show_module_cp&module=zenbu&method=save_rules_by_session",
			data: query,
			success: function(results){
						$("button.zenbu_action_validate").unbind('click');
						$(".pageContents").undelegate("button.zenbu_action_validate", "click");
						here.trigger('click');
				     },
			error: function(results, url){
						console.log("Button: Error with session-storing function (" + results + url + ") " + EE.BASE + "&C=addons_modules&M=show_module_cp&module=zenbu&method=save_rules_by_session");
				     }
		});
	});

	/**
	* ---------------------------------------
	* Remove Font Awesome <i> tags from top right nav 
	* ---------------------------------------
	*/
	$("div.rightNav").find("span.button a").each(function(){
		$(this).attr('title', $(this).text().trim());
	});
}



if ( /&D=cp&C=addons_modules&M=show_module_cp&module=zenbu&method=multi_edit/.test(window.location.href) || /&D=cp&C=content_edit&M=multi_edit_form&from_zenbu=y/.test(window.location.href) ) 
{
	/** 
	* ---------------------------------------
	* Send category editing back to Zenbu. 
	* ---------------------------------------
	* More elegant solution: prevent default click, 
	* send data to be saved through AJAX, then return to Zenbu
	* "return_to_zenbu=y" attempts to fetch the latest rules saved in session if present
	*
	*/
	if($("input[name='type']").val() == "add" || $("input[name='type']").val() == "remove")
	{
		var formURL = $(".pageContents").children("form").attr("action");
		
		$("input.submit").click(function (event) {
			//
			// Prevent normal clicking of submit button, send through AJAX, 
			// then redirect to Zenbu for a more seamless effect
			//
			event.preventDefault();
			
			var query = $(".pageContents").children("form").serialize();
			
			$.ajax({
				type:     "POST",
				//dataType: "json",
				url:      formURL,
				data: query,
				success: function(results){
							window.location = EE.BASE + "&C=addons_modules&M=show_module_cp&module=zenbu&return_to_zenbu=y";
					     },
				error: function(results){
							$.ee_notice("<?=lang('error')?>", {"type" : "error"});
					     }
			});
		});
	}
};
