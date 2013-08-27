(function($) {


ContentElements.bind('fieldpack_pill', 'display', function(element){
	new ptPill($('select', element));
});


})(jQuery);
