$(document).ready(function () {

	// Show Search button after document is ready
	$("button.zenbusearch").removeClass("invisible");

	// Remove "Modules" link in breadcrumb
	$("#breadCrumb").children("ol").children("li").eq(2).hide();

	/**
	* =======================================
	* Category "show more categories" button
	* =======================================
	*/
	$("body").delegate("a.more-categories", "click", function(){
		$(this).next("span.more-categories").removeClass('invisible');
		$(this).addClass('invisible');
		$(this).siblings("span.more-categories-spacer").addClass("invisible");
	});

	// $("body").delegate("span.more-categories", "click", function(){
	// 	$(this).siblings("a.more-categories, span.more-categories-spacer").removeClass('invisible');
	// 	$(this).addClass('invisible');
	// });

	/**
	* =======================================
	* Rules-based filtering
	* =======================================
	*/

	/**
	* ==========================
	* ON LOAD
	* ==========================
	*/
	adjustSavedSearchesHeight();

	var channelVal = $("select.channel").val();
	
	$("input[name='rule[1][val]'], select[name='rule[1][val]']").focus();

	/**
	*	Adding typeWatch delay when typing.
	*	Less calls == less sluggishness
	*/
	var typeWatchOptions = {
		callback: function () {
			doSearch();
		},
		wait: 750,
		highlight: false,
		captureLength: 0
	}

	/**
	 * Bind search after typing delay
	 */
	$("#filterMenu fieldset td.third input").typeWatch(typeWatchOptions); 
		
	/**
	* ===================
	* ON CHANNEL CHANGE
	* ===================
	*/
	// Changing channel
	// Governs what comes next for cat_id, status, author and custom fields
	$("body").delegate("select.channel", "change", function () {
		// If a "rapid successive reload error" text is displayed, remove it
		$("p.rapidloaderror").hide(); 

		// Get channel_id from dropdown
		var channelVal = $(this).val();
		resetToOneRule();
		setRulesListingAndValues(channelVal);
		resetSecondInput();
		resetThirdInput();
		setOrderbyDropdown(channelVal);
		adjustSavedSearchesHeight();

		// Add channel_id to Display Settings URL,
		// if present
		$('div.rightNav').find('a').each(function(){
			
			if($.contains($(this).attr('href'),'method=settings'))
			{
				$(this).attr('href', $(this).attr('href').replace(/channel_id=([0-9]*)/, 'channel_id=' + channelVal));
			}

		});
	});
	
	
	/**
	* =========================
	* ON RULE CHANGE
	* =========================
	*
	*/
	// Rules
	// Governs what gets pulled in and displayed depending on what the rule is based on
	$("body").delegate("select.first-input", "change", function () {

		var channelVal = $("div#channel_rule").find("span.third select").val();
		var theSelector = $(this).val();
		var theSelectorType	= $(this).find("option:selected").attr('data-type')

		switch(theSelector)
		{
			case "cat_id":
				var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
				var theCopy3 = $("div.reserve").find(".cat_ch_id_" + channelVal).clone();
			break;
			case "status":
				var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
				var theCopy3 = $("div.reserve").find(".status_ch_id_" + channelVal).clone();
			break;
			case "author":
				var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
				var theCopy3 = $("div.reserve").find(".author_ch_id_" + channelVal).clone();
			break;
			case "sticky":
				var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
				var theCopy3 = $("div.reserve").find(".sticky").clone();
			break;
			case "date": case "expiration_date": case "edit_date":
				var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
				var theCopy3 = $("div.reserve").find(".date").clone();
			break;
			case "title":
				var theCopy2 = $("div.reserve").find(".second-input-type3").clone();
				var theCopy3 = $("div.reserve").find(".title").clone();
			break;
			case "id":
				var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
				var theCopy3 = $("div.reserve").find(".fieldinput").clone();
			break;
			case "any_cf_title":
				var theCopy2 = $("div.reserve").find(".second-input-type3").clone();
				var theCopy3 = $("div.reserve").find(".fieldinput").clone();
			break;
			default:

				switch(theSelectorType)
				{
					case 'contains_doesnotcontain':
						// Place contains/doesnotcontain select field
						var theCopy2 = $("div.reserve").find(".second-input-type2").clone();
						var theCopy3 = $("div.reserve").find(".fieldinput").clone();
					break;
					case 'date':
						var theCopy2 = $("div.reserve").find(".second-input-type1").clone();
						var theCopy3 = $("div.reserve").find(".date").clone();
					break;
					default:
						// Place extensive select field
						var theCopy2 = $("div.reserve").find(".second-input-type3").clone();
						var theCopy3 = $("div.reserve").find(".fieldinput").clone();
					break;
				}

				$(this).closest("tr.rule").find("td.second").html(theCopy2).find('select').removeAttr("disabled");

				$(this).closest("tr.rule").find("td.third").html(theCopy3).find('input, select').removeAttr("disabled").typeWatch(typeWatchOptions);

				numberTheRules();

				return;

			break;
		}
		
		if(theCopy2 != undefined)
		{
			$(this).closest("tr.rule").find("td.second").html(theCopy2).find('input, select').removeAttr("disabled").typeWatch(typeWatchOptions);

			$(this).closest("tr.rule").find("td.third").html(theCopy3).find('input, select').removeAttr("disabled").typeWatch(typeWatchOptions);

			numberTheRules();

		}
		
	});
	
	/**
	*	Disabling third input field if "is empty" or "is not empty" is selected in second field.
	*
	*/
	$("body").delegate("select.second-input-type3", "change", function () {

		var theSelector = $(this).val();
		
		switch(theSelector)
		{
			case 'isempty': case 'isnotempty':
				$(this).closest('tr.rule').find('td.third input').val('').addClass('invisible');
			break;
			default:
				$(this).closest('tr.rule').find('td.third input').removeAttr('readonly').removeClass('invisible');
			break;
		}

	});
	

	/**
	* =======================
	* Adding a filtering rule
	* =======================
	*/
	$("body").delegate("button.addrule", 'click', function () {
		var lastRule = $(this).parent("td.controls").parent("tr.rule");
		
		if($("tr.rule").size() == 1)
		{
			lastRule.find("td.controls button.addrule").after("<button type='button' class='removerule'><i class='icon-minus-sign icon-2x'></i></button>");
		}
		
		var contents = lastRule.html();
		lastRule.after('<tr class="rule">' + contents + '</tr>');
		var newRule = $(this).parent("td.controls").parent("tr.rule").next("tr.rule");
		newRule.children("td.first").children("select").focus();
		
		resetThirdInput();
		resetSecondInput();
		numberTheRules();

	});


	

	/**
	* =========================
	* Removing a filtering rule
	* =========================
	*/
	$("body").delegate("button.removerule", 'click', function () {
		$(".loader").removeClass('invisible');
		
		var controls = $(this).closest("td.controls").html();
		var lastRule = $(this).closest("tr.rule");
		var prevRule = $(this).closest("tr.rule").prev("tr.rule");
		
		prevRule.children("td.controls").html(controls);
		if($("tr.rule").size() > 1)
		{
			lastRule.remove();
			if($("tr.rule").size() == 1)
			{
				$("tr.rule").find("td.controls button.removerule").remove();
			}
		} else {
			$("tr.rule").find("td.controls button.removerule").remove();
		}
		
		numberTheRules();
		resetThirdInput();
		resetSecondInput();
		
		/**
		* Reload results when rule is removed
		* Script taken from lower below in this script 
		*/
		doSearch();
		
	});
	
	
	/**
	 * ===============================================
	 * Function adjustSavedSearchesHeight
	 * Adjusts the right-side "saved searches" section to
	 * follow the filter rules display
	 * ===============================================
	 */
	function adjustSavedSearchesHeight()
	{
		newHeight = $('#rulefields').height() - 2; // Getting rid of the top-bottom border
		$('#savedsearches').css("height", newHeight);
		$('#savedsearches fieldset div').css("height", newHeight - 20);

	}
	

	/**
	* ===============================================
	* Function setRulesListingAndValues
	* Goes through rules and resets values
	* ===============================================
	*/
	function setRulesListingAndValues(channelVal)
	{
		$("tr.rule").each(function(key, elem) {
			var origValue1 = $(elem).find("td.first select").val();
			var origValue2 = $(elem).find("td.second select").val();
			var origValue3 = $(elem).find("td.third input, td.third select").val();
			var theCopy1 = $("div.reserve").find("select.first_rule_ch_id_" + channelVal).clone();
			
			// If the theCopy1 object is empty (i.e. dropdown doesn't exist),
			// get default channel 0 dropdown to avoid a first dropdown disappearing act
			if(theCopy1.length == 0)
			{
				theCopy1 = $("div.reserve").find("select.first_rule_ch_id_0").clone();
			}
			
			switch (origValue1)
			{
				case "cat_id":
					var theCopy3 = $("div.reserve").find("select.cat_ch_id_" + channelVal).clone();
				break;
				case "status":
					var theCopy3 = $("div.reserve").find("select.status_ch_id_" + channelVal).clone();
				break;
				case "author":
					var theCopy3 = $("div.reserve").find("select.author_ch_id_" + channelVal).clone();
				break;
				case "sticky":
					var theCopy3 = $("div.reserve").find("select.sticky").clone();
				break;
				case "date": case "expiration_date": case "edit_date":
					var theCopy3 = $("div.reserve").find("select.date").clone();
				break;
				case "title":
					var theCopy3 = $("div.reserve").find(".title").clone();
				break;
				case "id":
					var theCopy3 = $("div.reserve").find(".fieldinput").clone();
				break;
				default:
					var theCopy3 = $("div.reserve").find(".fieldinput").clone();
				break;
			}
			
			$(elem).find("td.first").html(theCopy1).find("select").removeAttr("disabled").val(origValue1);
			
			if(origValue2 == 'isempty' || origValue2 == 'isnotempty')
			{
				$(elem).find("td.third").html(theCopy3).find("input").val('').addClass('invisible');
			} else {
				$(elem).find("td.third").html(theCopy3).find("select").removeAttr("disabled").val(origValue3);
			}
			numberTheRules();
		});
	}
	

	/**
	* ==================================================
	* Function setOrderbyDropdown
	* Refreshes the "order by" dropdown based on channel
	* ==================================================
	*/
	function setOrderbyDropdown(channelVal)
	{
		var origValue1 = $("select.orderby").val()
		var theCopy1 = $("div.reserve").find("select.orderby_ch_id_" + channelVal).clone();
			
		// If the theCopy1 object is empty (i.e. dropdown doesn't exist),
		// get default channel 0 dropdown to avoid a first dropdown disappearing act
		if(theCopy1.length == 0)
		{
			theCopy1 = $("div.reserve").find("select.orderby_ch_id_0").clone();
		}
		
		$("div.limit").find("span.orderby").html(theCopy1).find("select").removeAttr("disabled").val(origValue1);
		
	}

	
	/**
	* ===============================================
	* Function resetThirdInput
	* Goes through rules and fixes third form element
	* ===============================================
	*/
	function resetThirdInput()
	{
		var channelVal = $("div#channel_rule").find("span.third select").val();
		
		$("tr.rule").each(function(key, elem) {
			
			var theSelector		= $(this).find("td.first select").val();
			var theSelectorType	= $(this).find("td.first select option:selected").attr('data-type');
			var theSelector2	= $(this).find("td.second select").val();
			var theSelector3	= $(this).find("td.third input, td.third select").val();

			// Date range
			selectedDateFrom	= $(this).find("td.third span.date input").eq(0).val();
			selectedDateTo		= $(this).find("td.third span.date input").eq(1).val();
			isDate				= false;

			switch(theSelector)
			{
				case "cat_id":
					var theCopy = $("div.reserve").find(".cat_ch_id_" + channelVal).clone();
				break;
				case "status":
					var theCopy = $("div.reserve").find(".status_ch_id_" + channelVal).clone();
				break;
				case "author":
					var theCopy = $("div.reserve").find(".author_ch_id_" + channelVal).clone();
				break;
				case "sticky":
					var theCopy = $("div.reserve").find(".sticky").clone();
				break;
				case "date": case "expiration_date": case "edit_date":
					var theCopy = $("div.reserve").find(".date").clone();
				break;
				case "title":
					var theCopy = $("div.reserve").find(".title").clone();
				break;
				case "id":
					var theCopy = $("div.reserve").find(".fieldinput").clone();
				break;
				default:
					var theCopy = $("div.reserve").find(".fieldinput").clone();

					switch(theSelectorType)
					{
						case 'contains_doesnotcontain':
							var theCopy = $("div.reserve").find(".fieldinput").clone();
						break;
						case 'date':
							var theCopy = $("div.reserve").find(".date").clone();
						break;
						default:
							var theCopy = $("div.reserve").find(".fieldinput").clone();
						break;
					}

				break;
			}
			
			if(theSelector2 == 'isempty' || theSelector2 == 'isnotempty')
			{

				$(this).find("td.third").empty().html(theCopy).find('input, select').val('').addClass('invisible');

			} else if ( theSelector == 'date' || theSelector == 'expiration_date' || theSelector == 'edit_date' || theSelectorType == 'date' ) {

				$(this).find("td.third").empty().html(theCopy).find('input, select').removeAttr("disabled").val(theSelector3);
				
				// Show range fields if "range" is selected
				if( theSelector3 == 'range')
				{
					
					$(this).find("td.third span.date").removeClass('invisible').find("input").eq(0).removeAttr('disabled').val(selectedDateFrom);
					$(this).find("td.third span.date").removeClass('invisible').find("input").eq(1).removeAttr('disabled').val(selectedDateTo);

				}
			} else {

				$(this).find("td.third").empty().html(theCopy).find('input, select').val(theSelector3).removeAttr("disabled");

			}

			// Add typeWatch listener
			$(this).find("td.third input").typeWatch(typeWatchOptions); 

			numberTheRules();
			
		});
	}
	

	/**
	* ===============================================
	* Function resetSecondInput
	* Goes through rules and fixes second form element
	* ===============================================
	*/
	function resetSecondInput()
	{
		var channelVal = $("div#channel_rule").find("span.third select").val();
		
		$("tr.rule").each(function(key, elem) {
			var theSelector		= $(this).find("td.first select").val();
			var theSelectorType	= $(this).find("td.first select option:selected").attr('data-type');
			var theSelector2	= $(this).find("td.second select").val();
			var theSelector3	= $(this).find("td.third input, td.third select").val();
			
			switch(theSelector)
			{
				case "cat_id": case "status": case "author": case "sticky": case "date": case "expiration_date": case "edit_date": case "id":
					var theCopy = $("div.reserve").find(".second-input-type1").clone();
				break;
				case "title": case "any_cf_title" :
					var theCopy = $("div.reserve").find(".second-input-type3").clone();
				break;
				default:
					var theCopy = $("div.reserve").find(".second-input-type3").clone();

					switch(theSelectorType)
					{
						case 'contains_doesnotcontain':
							var theCopy = $("div.reserve").find(".second-input-type2").clone();
						break;
						case 'date':
							var theCopy = $("div.reserve").find(".second-input-type1").clone();
						break;
						default:
							var theCopy = $("div.reserve").find(".second-input-type3").clone();
						break;
					}
				break;
			}
			
			// Set the previous value for the new field.
			$(this).find("td.second").empty().html(theCopy).find('input, select').val(theSelector2).removeAttr("disabled");

			// If the option is not found within the new select dropdown
			// Reset the selected value to the first element to avoid having a blank value in the dropdown
			// Eg. Title "contains" XXXXX, then add a rule which adds Category:
			//     Category doesn't have "contains" in second rule, so reset to "is"
			if(theCopy.find("option[value="+theSelector2+"]").length == 0)
			{
				$(this).find("td.second").empty().html(theCopy).find('select option:first-child').attr("selected", "selected");
			}
			
			// The value of the second dropdown has changed
			// Get this new value
			theSelector2 = $(this).find("td.second select").val();
			
			// Hide third dropdown if second is set to empty or not empty
			if(theSelector2 == 'isempty' || theSelector2 == 'isnotempty')
			{
				$(this).find("td.third input, td.third select").val('').addClass('invisible');
			}
			
			numberTheRules();
			
		});
	}

	
	/**
	* ==============
	* Function numberTheRules
	* Goes through rules and numbers them for server-side processing through POST
	* ==============
	*/
	function numberTheRules()
	{
		var count = 0;
		$("tr.rule").each(function(key, val) {
			count++;
			
			var theName1 = $(val).find("td.first input, td.first select").attr('name');
			var theName2 = $(val).find("td.second input, td.second select").attr('name');
			var theName3 = $(val).find("td.third input, td.third select").attr('name');
			
			/**
			 * Date Range variables
			 */
			var theDateRange_from = $(val).find("td.third span input.date_from").attr('name');
			var theDateRange_to = $(val).find("td.third span input.date_to").attr('name');

			$(val).find("td.first input, td.first select").attr('name', theName1.replace(/rule\[[0-9]\]/, 'rule[]'));
			$(val).find("td.second input, td.second select").attr('name', theName2.replace(/rule\[[0-9]\]/, 'rule[]'));
			
			if(theName3 !== undefined)
			{
				$(val).find("td.third input, td.third select").attr('name', theName3.replace(/rule\[[0-9]\]/, 'rule[]'));
			}

			/**
			 * Date Range
			 */
			if(theDateRange_from !== undefined && theDateRange_to !== undefined)
			{
				$(val).find("td.third span input.date_from").attr('name', theDateRange_from.replace(/rule\[[0-9]\]/, 'rule[]'));
				$(val).find("td.third span input.date_to").attr('name', theDateRange_to.replace(/rule\[[0-9]\]/, 'rule[]'));
			}
		});
		
		var count = 0;
		$("tr.rule").each(function(key, val) {
			count++;
			var theName1 = $(val).find("td.first input, td.first select").attr('name');
			var theName2 = $(val).find("td.second input, td.second select").attr('name');
			var theName3 = $(val).find("td.third input, td.third select").attr('name');

			/**
			 * Date Range variables
			 */
			var theDateRange_from = $(val).find("td.third span input.date_from").attr('name');
			var theDateRange_to = $(val).find("td.third span input.date_to").attr('name');

			$(val).find("td.first input, td.first select").attr('name', theName1.replace(/rule\[\]/, 'rule['+count+']'));
			$(val).find("td.second input, td.second select").attr('name', theName2.replace(/rule\[\]/, 'rule['+count+']'));
			$(val).find("td.third input, td.third select").attr('name', theName3.replace(/rule\[\]/, 'rule['+count+']'));

			/**
			 * Date Range
			 */
			if(theDateRange_from !== undefined && theDateRange_to !== undefined)
			{
				$(val).find("td.third span input.date_from").attr('name', theDateRange_from.replace(/rule\[\]/, 'rule['+count+']'));
				$(val).find("td.third span input.date_to").attr('name', theDateRange_to.replace(/rule\[\]/, 'rule['+count+']'));
			}
		});
		
		adjustSavedSearchesHeight();

	}
	

	/**
	* ==============
	* Function resetToOneRule
	* Simply removes rules to only have one displayed. Used when channel_id changes
	* ==============
	*/
	function resetToOneRule()
	{
		$("tr.rule").each(function(count) {
			if( count != 0)
			{
				$(this).remove();
			} else {
				$(this).find("td.controls button.removerule").remove();
			}
		});
	}
	
	/* END Rules-based filtering scripts */

	/**
	* =======================================
	* Function doSearch
	* AJAX-based refreshing of entry results
	* =======================================
	*/
	function doSearch()
	{

		query = $("form#filterMenu").serialize();
		
		$(".loader").removeClass('invisible');

		$.ajax({
			type:     "POST",
			dataType: "json",
			url:      EE.BASE+"&C=addons_modules&M=show_module_cp&module=zenbu&method=ajax_results",
			data: query,
			success: function(results){
				// Save scrolled table position to return to it, effectively avoiding table "jumpiness"
				// when table is reloaded through AJAX
				scrollPosContainer = $("div#resultArea-inner").position().left;
				scrollPosTable = $("table.resultTable").position().left;
				scrollPosTable = (scrollPosTable * -1) + scrollPosContainer;
				
				// Load results
				$("#resultArea").html(results);
			
				$("div#resultArea-inner").scrollLeft(scrollPosTable);

				$(".loader").addClass('invisible');
				$("button.zenbusearch").find("span").removeClass('invisible');
				$("button.zenbusearch").find("span.onsubmit").addClass('invisible');

				// Custom Zenbu event
				$(document).trigger('zenbu_results_refresh');
			},
			error: function(results, a, b){

				// Would be nice to be able to parse the PHP error from this and display it
				var response = results.responseText;
				
				$("#resultArea").html('<strong>Error</strong>. Most likely due to a PHP error. Try clicking <button class="submit">here</button> for more error details.');

				console.log(response);

				$("#resultArea").children("button.submit").click( function () {
					$("form#filterMenu").submit();
				});

				$(".loader").addClass('invisible');
			}
		});	
	}


	/**
	 * ===========
	 * Datepicker
	 * ===========
	 */
	$("body").delegate("input.dateRange", "focus", function () {
		$(this).datepicker({
			dateFormat: $.datepicker.W3C, 
			defaultDate: new Date(),
			showAnim: 'fadeIn',
			onSelect: function () {
				if( $(this).val() != "" && $(this).siblings().val() != "")
				{
					doSearch();
				}
			}
		});

	});

	/**
	 * Click-the-Search button AJAX magic
	 */
	$("button.zenbusearch").click(function (event) {
		event.preventDefault();
		doSearch();
	});


	/**
	 * On general change of dropdowns
	 */
	$("body").delegate("#filterMenu fieldset select", "change", function () { 
		  
			// Prevent submission if field is date range
			if( $(this).val() == 'range' )
			{
				$(this).next('span.date').removeClass('invisible').find('input').removeAttr('disabled');
				return;
			} else {
				$(this).next('span.date').addClass('invisible').find('input').removeAttr('disabled');
			}

		  doSearch();
	});

	
	/**
	*  Click-from-table-cell effect
	*/
	$("body").delegate("table.resultTable tr td[class!='selectable']", 'click', function() {
		if ($(this).parent('tr').find("td").eq(0).find("input:checkbox").attr('checked'))
		{
			$(this).parent('tr').find("td").eq(0).find('input').attr('checked', false);
			$(this).parent('tr').removeClass('selected');
		} 
		else 
		{
			$(this).parent('tr').find("td").eq(0).find('input').attr('checked', true);
			$(this).parent('tr').addClass('selected');
		};
	});
	

	/**
	*  Negating the above if hyperlink is clicked so not to trigger checkbox
	*/
	$("body").delegate("table.resultTable tr td[class!='selectable'] a", 'click', function() {
		if ($(this).parent('td').parent('tr').find("td").eq(0).find("input:checkbox").attr('checked'))
		{
			$(this).parent('td').parent('tr').find("td").eq(0).find('input').attr('checked', false);
			$(this).parent('td').parent('tr').removeClass('selected');
		} 
		else 
		{
			$(this).parent('td').parent('tr').find("td").eq(0).find('input').attr('checked', true);
			$(this).parent('td').parent('tr').addClass('selected');
		};
	});
	
	

	/**
	 * Pagination AJAX
	 */
	$("body").delegate("#paginationLinks a", 'click', function () {
		
		// Get the GET value array
		// Used for adding perpage in ajax() function
		var vars = {};
		var parts = $(this).attr('href').replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});
	    
		var query = $("form#filterMenu").serialize();
		$(".loader").removeClass('invisible');
		$.ajax({
			type:     "POST",
			dataType: "json",
			url:      EE.BASE+"&C=addons_modules&M=show_module_cp&module=zenbu&method=ajax_results&perpage="+vars['perpage'],
			data: query,
			success: function(results){
				     // Save scrolled table position to return to it, effectively avoiding table "jumpiness"
					// when table is reloaded through AJAX
					scrollPosContainer = $("div#resultArea-inner").position().left;
					scrollPosTable = $("table.resultTable").position().left;
					scrollPosTable = (scrollPosTable * -1) + scrollPosContainer;
					
					// Load results
					$("#resultArea").html(results);
					//$("input[name='perpage']").val(vars['perpage']);
					
					$("div#resultArea-inner").scrollLeft(scrollPosTable);
					$(".loader").addClass('invisible');

					// Custom Zenbu event
					$(document).trigger('zenbu_results_refresh');
			},
			error: function(results){

				    $("#resultArea").html('<strong>Error during pagination</strong>. Most likely due to a PHP error. Try clicking <button class="submit">here</button> for more error details. '+results['statusText'] + ', Status: ' + results['status']);
					$(".loader").addClass('invisible');
			}
		});
		
		return false;
	
	});


	/**
	*  Table sorter
	*/
	$("body").delegate("table.resultTable thead tr th", 'click', function () {
				
		orderby = $(this).attr("id");
		
		// Category filtering not set yet, skip all this if "Category" or other header is clicked
		if(orderby in { 'entry_checkbox' : '', 'live_look' : '', 'view_count' : '', 'last_author' : '' })
		{
			return;
		}
		
		sort = "desc"
		switch ($(this).attr("class"))
		{
			case "headerSortUp":
				sort = "asc";
				$(this).siblings().removeClass("headerSortDown").removeClass("headerSortUp");
				$(this).addClass("headerSortDown");
			break;
			case "headerSortDown":
				sort = "desc";
				$(this).siblings().removeClass("headerSortDown").removeClass("headerSortUp");
				$(this).addClass("headerSortUp");
			break;
		}
		
		//console.log(orderby);
		$("select[name='orderby']").val(orderby);
		$("select.sort").val(sort);
	    
	    doSearch();
		
	});

	

/**
* =======================================
* Saving of rules
* =======================================
*/

$(".savesearchInit").bind('click', function () {
	$(".rule_label").removeClass('invisible');
	$(this).addClass('invisible');
	$("input[name='save_search_name']").focus();
});

$("a.cancelsavesearch").bind('click', function () {
	$(".rule_label").children("input").val("");
	$(".rule_label").addClass('invisible');
	$(".savesearchInit").removeClass('invisible');
});


// The following will make the save button save data,
// as well as pressing the "Enter" key in the input field
$("button.savesearch").bind('click', function () {
	saveSearchAjax();
});

$("input[name='save_search_name']").bind('keyup', function(e) {
	if(e.keyCode == 13) 
	{
		saveSearchAjax();
	}
});

function saveSearchAjax()
{
	var query = $("form#filterMenu").serialize();
	var search_name = $("input[name='save_search_name']").val();
	query = query + '&save_search_name=' + search_name;
	
	if(search_name != '')
	{
		$.ajax({
			type:     	"POST",
			dataType: 	"json",
			url:      	EE.BASE+"&C=addons_modules&M=show_module_cp&module=zenbu&method=save_search",
			data: 		query,
			success: 	function(results) {
						
						$("#savedsearches fieldset div").html(results);
						$("#savedsearches").removeClass('invisible');
						$(".loader").addClass('invisible');
						$(".savesearchInit").removeClass('invisible');
						$(".rule_label").addClass('invisible');
						$("input[name='save_search_name']").val('');
					},
			error: 		function(results) {
						$("#savedsearches").html('<strong>Save Error</strong>. Most likely due to a PHP error. Try clicking <button class="submit">here</button> for more error details. '+results['statusText'] + ', Status: ' + results['status']);
						$(".loader").addClass('invisible');
					}
		});
	}
}
	
	
/**
* ========================
*       Fancybox
* ========================
* Events are on *hover* so
* that fancybox first binds
* and is then available.
* 
*/

	/**
	*  Fancybox for images
	*/
	$("body").delegate("a.fancybox", 'hover', function (e) {
		e.preventDefault();

		$(this).fancybox({
			'overlayShow'		: false,
			'centerOnScroll'	: true,
			'titlePosition'		: 'inside',
			'enableEscapeButton'	: true
		});
	});
	
	/** 
	*  Fancybox for matrix fields
	*/
	$("body").delegate("a.fancyboxmatrix", 'hover', function (e) {
		e.preventDefault();

		$(this).fancybox({
			//'content'			: results,
			'overlayShow'		: false,
			'type'				: 'ajax',
			'centerOnScroll'	: true,
			'autoDimensions'	: true
			//'height'			: 560,
			//'width'				: 300
		});
	});
	
	$("body").delegate("a.fancybox-inline", 'hover', function (e) {
		e.preventDefault();

		$(this).fancybox({
			'enableEscapeButton'	: true
		});
	});
		
	
	/**
	*  Fancybox for Live Look
	*/
	$("body").delegate("a.fancyboxtemplate", 'hover', function (e) {

		// If Cmd/Ctrl+click, don't trigger fancybox
		if(e.ctrlKey || e.metaKey)
		{
			return;	
		}

		e.preventDefault();

		$(this).fancybox({
			'overlayShow'		: true,
			'centerOnScroll'	: true,
			'type'				: 'iframe',
			'width'				: '90%',
			'height'			: '90%',
			'autoScale'			: true,
			'titlePosition'		: 'inside'
		});
		
	});

	/**
	*  Fancybox for iframe
	*/
	$("body").delegate("a.fancyboxiframe", 'hover', function (e) {

		// If Cmd/Ctrl+click, don't trigger fancybox
		if(e.ctrlKey || e.metaKey)
		{
			return;	
		}
		
		e.preventDefault();

		$(this).fancybox({
			'overlayShow'		: true,
			'centerOnScroll'	: true,
			'type'				: 'iframe',
			'autoScale'			: true,
			'titlePosition'		: 'inside'
		});
	});

});
