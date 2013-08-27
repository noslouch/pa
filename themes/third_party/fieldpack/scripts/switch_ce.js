(function($) {


ContentElements.bind('fieldpack_switch', 'display', function(element){
	new ptSwitch($('select', element));
});


})(jQuery);
