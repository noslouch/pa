$(document).ready(function () {
	
	/**
	*  Show loading/saving/updating spinner after clicking button
	*/
	$("#filterMenu button.submit, button.withloader").click(function () {
		$(".loader").removeClass('invisible');
		$(this).children("span").addClass('invisible');
		$(this).children("span.onsubmit").removeClass('invisible');
	});

	/** Toggle-all effect
	*	Toggles checkboxes in same column
	*	---------------------------------
	*	Note: Looks like there's a event binded to th elements from EE ~2.4
	*	that enables toggling in child checkboxes already. However, we want
	*	disabled checkboxes to stay checked, so must override the behaviour.
	*/
	$("th, input.toggleAll").live('click', function () {
		var thisClass = $(this).attr('id');
		if ($(this).attr('checked') || $(this).children("input").attr('checked'))
		{
			$("input."+thisClass+":not(:disabled)").attr('checked', true);
			$("input:disabled").attr('checked', true);
		} 
		else 
		{
			$("input."+thisClass+":not(:disabled)").attr('checked', false);
			$("input:disabled").attr('checked', true);
		};
	});
	
	/**
	* Select-all effect
	* Toggles checkboxes in same column and
	* adds color to whole row
	*/
	$("input.selectAll").live('click', function () {
		var thisClass = $(this).attr('id');
		if ($(this).attr('checked'))
		{
			$("input."+thisClass).attr('checked', true);
			$("input."+thisClass).parent('td').parent('tr').addClass('selected');
		} 
		else 
		{
			$("input."+thisClass).attr('checked', false);
			$("input."+thisClass).parent('td').parent('tr').removeClass('selected');
		};
	});
	
		
	/**
	*   Row hover effect
	*/
	$("td.label, td.hoverable, tr.entryRow").live('mouseover', function() {
		$(this).addClass('hover');
		$(this).parent('tr').addClass('hover');
		$(this).parent('tr').children('td').addClass('hover');
	});
	$("td.label, td.hoverable, tr.entryRow").live('mouseout', function() {
		$(this).removeClass('hover');
		$(this).parent('tr').removeClass('hover');
		$(this).parent('tr').children('td').removeClass('hover');
	});

	/**
	*   Click-from-table-cell effect
	*/
	$("td.clickable").live('click', function() {
		if ($(this).children("input:checkbox").attr('checked'))
		{
			$(this).children("input").attr('checked', false);
		} 
		else 
		{
			$(this).children("input").attr('checked', true);
		};
		
		var thisClass = $(this).children("input[type=checkbox]").attr('class');
		
		if ($("td").children("input."+thisClass+":checked").length >= 0)
		{
			$("th").children("input#"+thisClass+"[type=checkbox]").attr('checked', false);
		}
		
		if ($("td").children("input."+thisClass+":not(:checked)").length == 0)
		{
			$("th").children("input#"+thisClass+"[type=checkbox]").attr('checked', true);
		}
		
	});
	
	/**
	*   Click-and-color-row effect
	*/
	$("td.selectable").live('click', function() {
		if ($(this).children("input:checkbox").attr('checked'))
		{
			$(this).children("input").attr('checked', false);
			$(this).parent('tr').removeClass('selected');
		} 
		else 
		{
			$(this).children("input").attr('checked', true);
			$(this).parent('tr').addClass('selected');
		};		
	});
	
	/**
	* Fix for checkbox: If checkbox is clicked directly,
	* The above negates that effect, yielding nothing.
	* Below fixes this by negating the negation (O_o?) 
	*/
	$("td.clickable input:checkbox, td.selectable input:checkbox").live('click', function() {
		if ($(this).attr('checked'))
		{
			$(this).attr('checked', false);
		} 
		else 
		{
			$(this).attr('checked', true);
		};
	});
	

	
	
});