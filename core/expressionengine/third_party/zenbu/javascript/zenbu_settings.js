$(document).ready(function () {
	
	/**
	*	========================
	*	General settings slider
	*	========================
	*	Inspired from EE's control panel style guide
	*	Using the "Forum preferences accordion"
	*	@link	http://expressionengine.com/user_guide/development/cp_styles/index.html#forum_preferences_accordion
	*/
	$(".editAccordion > h3").css("cursor", "pointer");

	$(".editAccordion h3").click(function() {
		if ($(this).hasClass("collapsed")) {
			$(this).siblings().slideDown("fast");
			$(this).removeClass("collapsed").parent().removeClass("collapsed");
		}
		else {
			$(this).siblings().slideUp("fast");
			$(this).addClass("collapsed").parent().addClass("collapsed");
		}
	});
	
	
	/**
	*	============================
	*	Scripts for settings section
	*	============================
	*/
	
	/**
	*	Live Look options switcher
	*/
	
	$("select.livelook-settings").bind('change', function () {
		var theValue = $(this).val();
		if(theValue == 'use_custom_segments')
		{
			$(this).closest("td").find("label.seg-option").removeClass("invisible").children("input").removeClass("invisible").removeAttr('disabled');
			$(this).closest("td").find("span.livelook-custom-segments").addClass("invisible");
		} else {
			$(this).closest("td").find("label.seg-option").addClass("invisible").children("input").addClass("invisible").attr('disabled');
			$(this).closest("td").find("span.livelook-custom-segments").removeClass("invisible");
		}
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
	
	/**
	*  Renumber input fields for order
	*/
	function renumberListItems() {    
	    $("table.mainTable").each( function () {
		    var i = 1;
		    
		    $(this).children("tbody").children("tr").children("td.field-order").each( function() {
		       $(this).children("input").val(i);
		       $(this).children("span").html(i);
		       i++;
		    });
		    
	    });
	}
	
	// Set up the disableSelection function
	// so that text doesn't get selected when dragging rows
	jQuery.fn.extend({ 
        disableSelection : function() { 
                return this.each(function() { 
                        this.onselectstart = function() { return false; }; 
                        this.unselectable = "on"; 
                        jQuery(this).css('user-select', 'none'); 
                        jQuery(this).css('-o-user-select', 'none'); 
                        jQuery(this).css('-moz-user-select', 'none'); 
                        jQuery(this).css('-khtml-user-select', 'none'); 
                        jQuery(this).css('-webkit-user-select', 'none'); 
                }); 
        } 
	}); 
	
	// we don't want to add the sortable until the dom is ready
    renumberListItems();

    $("table.settingsTable tbody").sortable({
    	cancel: '.not-sortable',
    	start: function(){
        	$(this).children('tr:empty').html('<td colspan="5">&nbsp;</td>');
        	$(this).children('tr.ui-sortable-helper').addClass('hover');
        },
        stop: function(event, ui) {
        	renumberListItems();
	        $(this).parent("table").tablesorter({
						widgets: ['zebra'],
						headers: { 0: { sorter: false}, 1: { sorter: false}, 2: { sorter: false}, 3: { sorter: false} }
						});
        },
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        helper: fixHelper,
        revert: 200,
        cursor: 'move',
        distance: 15
    }).disableSelection();
	    
	    
	/**
	* ========================================================
	* On submit validation 
	* ========================================================
	*/ 

	$(".pageContents form").data('original-form-data', origFormData);

    $("button.submit").click(function () {
    
    	/** 
    	 * ---------------------------------------
    	 * ...for "no fields displayed" situation
    	 * ---------------------------------------
    	 */
    	var hasNoFieldsDisplayed = false;
    	var channelNoFields = new Array();
    	
    	

    	$("table.settingsTable").each(function(index, elem) {
    		if ($(this).children("tbody").children("tr").children("td:nth-child(3)").children("input:checked").length == 0)
    		{
    			channelNoFields.push('- ' + $(this).prev("h2").text());
    			hasNoFieldsDisplayed = true;
    		}
    	});
    	
    	if (hasNoFieldsDisplayed === true)
    	{
    		$(this).children("span").removeClass('invisible');
			$(this).children("span.onsubmit").addClass('invisible');
			$("span.loader").addClass('invisible');

    		var part1 = $("div.warnings span.part1").text();
	    	var part2 = $("div.warnings span.part2").text();
	    	var theChannelTitle = channelNoFields.join("\n");
			var answer = confirm( part1 + theChannelTitle + part2);
			
			if (answer)
			{
				$(this).children("span").addClass('invisible');
				$(this).children("span.onsubmit").removeClass('invisible');
				$("span.loader").removeClass('invisible');
				$(this).parent("form").submit();
			} else {
				$(this).children("span").removeClass('invisible');
				$(this).children("span.onsubmit").addClass('invisible');
				$("span.loader").addClass('invisible');
				return false;
			}
		}
    	
    });

	/**
	*  ====================
	*  Filtering channels
	*  ====================
	*/
	
	var origFormData = $(".pageContents form").serialize();

	// When a tab is clicked
	$("a.nav_ch").bind('click', function (e) {

		checkUnsaved(e);
		
	})

	// When it's a dropdown
	var origVal = $("select#channel_select").val();

	$("select#channel_select").bind('change', function (e) {
		
		cont = checkUnsaved(e);

		if(cont)
		{
			// Move to the new page
			theChannelId = $(this).val();
			window.location = EE.BASE + "&C=addons_modules&M=show_module_cp&module=zenbu&method=settings&channel_id=" + theChannelId;
		
		} else {

			// Reset the select field to original value
			$(this).val(origVal);
		
		}
	});

	/**
	* ========================================================
	* On channel change validation. Check for unsaved data 
	* ========================================================
	*/

	function checkUnsaved(e)
	{
		var newFormData = $(".pageContents form").serialize();
		
		if(newFormData != origFormData)
		{
			warn = $("div.warnings span.forgottosave").text();

			answer = confirm( warn );
			
			if ( ! answer)
			{
				e.preventDefault();
				return false;
			}
		}

		return true;
	}


	/**
	 * ========================================================
	 * "Copy to member groups" slider toggle
	 * ========================================================
	 */
    $("a#copySettingsTo").click(function () {

    	$(this).addClass('invisible');
    	$(this).next(".copySettingsTo").slideDown('500');

    });

    /**
	 * ========================================================
	 * Mass checker
	 * ========================================================
	 */
	$("input.mass_checker").click(function () {
		checkWhat = $(this).attr('data-check');
		checkedMode = $(this).attr('checked');

		if(checkedMode)
		{
			$("input[name*=" + checkWhat + "]").attr('checked', true);
		} else {
			$("input[name*=" + checkWhat + "]").attr('checked', false);
		}
		
		
	});
	    	    
	    
});