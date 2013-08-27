(function($){


Assets.Field.matrixConfs = {};


Matrix.bind('assets', 'display', function(cell){
	var $field = $('.assets-field', this);

	// ignore if we can't find that field
	if (! $field.length) return;

	var fieldName = cell.field.id+'['+cell.row.id+']['+cell.col.id+']',
		fieldId = fieldName.replace(/[^\w\-]+/g, '_');

	$field.attr('id', fieldId);

	cell.assetsField = new Assets.Field(fieldId, fieldName, Assets.Field.matrixConfs[cell.col.id]);
});


})(jQuery);
