var PlayaColOpts = {};


(function($) {


Matrix.bind('playa', 'display', function(cell){
	var $field = $('.playa', this);

	// ignore if we can't find that field
	if (! $field.length) return;

	var fieldName = cell.field.id+'['+cell.row.id+']['+cell.col.id+']',
		opts = $.extend({}, PlayaColOpts[cell.col.id], { fieldName: fieldName });

	if ($field.hasClass('playa-dp')) {
		new PlayaDropPanes($field, opts);
	} else {
		new PlayaSingleSelect($field, opts);
	}
});


})(jQuery);
