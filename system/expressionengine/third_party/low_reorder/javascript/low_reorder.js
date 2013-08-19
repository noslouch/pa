/**
 * Low Reorder JavaScript
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */

// Anonymous wrapper
(function($){

/**
 * Stuff to execute on page load
 */	
$(function(){

	// Show/hide category options
	(function(){
		var $options = $('.category-options'),
			$select = $('#settings-category-options');

		var toggle = function(){
			var show = '#category-' + $select.val();
			$options.slideUp(100);
			$(show).slideDown(100);
		};

		$select.change(toggle);
	})();

	// Search field options
	(function(){
		var $tmpl = $('#field-template'),
			$add  = $('#search-settings button');

		var addFilter = function(event, key, val) {
			// Clone the filter template and remove the id
			var $newFilter = $tmpl.clone().removeAttr('id');

			// If a key is given, set it
			if (key)  $newFilter.find('select').val(key);

			// If a val is given, set it
			if (val) $newFilter.find('input').val(val);

			// Add it just above the add-button
			$add.before($newFilter);

			// If it's a click event, slide down the new filter,
			// Otherwise just show it
			if (event) {
				event.preventDefault()
				$newFilter.slideDown(100);
			} else {
				$newFilter.show();
			}
		};

		// If we have reorder fields pre-defined, add them to the list
		if (typeof LOW_Reorder_fields != 'undefined') {
			for (var i in LOW_Reorder_fields) {
				addFilter(null, i, LOW_Reorder_fields[i]);
			}
		}

		// Enable the add-button
		$add.click(addFilter);

		// Enable all future remove-buttons
		$('#search-settings').delegate('button.remove', 'click', function(event){
			event.preventDefault();
			$(this).parent().remove();
		});
	})();


	// Category select drop down
	$('#reorder-category-select select').change(function(e){
		var url = $('#reorder-category-select input[name=url]').val();
		var val = $(this).val();
		location.href = url+val;
	});

	// Sortable magic
	$('#low-reorder').sortable({
		axis: 'y',
		//containment: $('#reorder-container'),
		containment: $('#mainContent'),
		items: 'li'
	});

	$('#low-reorder li').mousedown(function(){
		$(this).addClass('grabbing');
	}).mouseup(function(){
		$(this).removeClass('grabbing');
	});	

});

})(jQuery);
