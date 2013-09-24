// ********************************************************************************* //
var Tagger = Tagger ? Tagger : {};
//********************************************************************************* //

jQuery(document).ready(function(){

	var Fields = jQuery('.TaggerField');

	Fields.each(function(i, e){

		var FIELD = $(e);
		var FIELD_ID = FIELD.data('fieldid');
		var FIELD_NAME = FIELD.data('fieldname');

		// Single Input Mode?
		if (FIELD.find('.SingleTagsInput').length > 0){

			FIELD.find('#SingleTagsInput_'+FIELD_ID).tagsInput({
				width:'98%',
				height:'',
				delimiter: '||',
				autocomplete_url: Tagger.ACT_URL + '&ajax_method=tag_search',
				autocomplete: {
					autoFocus:false,
					open:function(){
						FIELD.find('#SingleTagsInput_'+FIELD_ID+'_tag').autocomplete("widget").width(300).addClass('DDTaggerAC');
					}},
				unique:true
			});

		}
		else {
			FIELD.find('.InstantInsert').keypress(Tagger.InstantSearch);

			FIELD.find('.InstantInsert').autocomplete({
				source: Tagger.ACT_URL + '&ajax_method=tag_search',
				autoFocus:false,
				open:function(){
					FIELD.find('.InstantInsert').autocomplete("widget").width(300).addClass('DDTaggerAC');
				},
				select: function(e, ui){
					Tagger.SaveTag(ui.item.value, FIELD_ID);
					setTimeout(function(){
						FIELD.find('.InstantInsert').val('');
					}, 100);
				}
			});
		}

		FIELD.find('.MostUsedTags .tag').click(Tagger.MostUsedTagger);
		FIELD.find('.AssignedTags .tag a').live('click', Tagger.DelTag);
		FIELD.find('.AssignedTags').sortable();

	});

});


//********************************************************************************* //

Tagger.InstantSearch = function(event){
	if (event.which == 13)	{
		var FIELD = $(event.target).closest('.TaggerField');
		var FIELD_ID = $(event.target).closest('.TaggerField').data('fieldid');

		Tagger.SaveTag(event.target.value, FIELD_ID);
		jQuery(this).val('');

		FIELD.find('.InstantInsert').autocomplete('close');
		return false;
	}
};

//********************************************************************************* //

Tagger.MostUsedTagger = function(e){
	e.preventDefault();

	var FIELD_ID = $(e.target).closest('.TaggerField').data('fieldid');
	Tagger.SaveTag( jQuery(this).find('span').html(), FIELD_ID );
};

//********************************************************************************* //

Tagger.SaveTag = function(tag, FIELD_ID){
	var FIELD = $('#TaggerField_'+FIELD_ID);
	var FIELD_NAME = FIELD.data('fieldname');

	if (FIELD.find('.SingleTagsInput').length > 0){
		FIELD.find('#SingleTagsInput_'+Tagger.FieldID).addTag(tag);
		return;
	}

	FIELD.find('.AssignedTags .NoTagsAssigned').hide();

	var dupe = false;
	FIELD.find('.AssignedTags .tag input').each(function(){
		if (jQuery(this).val() == tag) {
			dupe = true;
		}
	});

	if (dupe === false)
	{
		var Tag = jQuery('<div class="tag">'+tag+'<input type="hidden" name="'+FIELD_NAME+'[tags][]" value="'+tag+'"> <a href="#"></a>	</div>');
		FIELD.find('.AssignedTags br').before(Tag);
		//console.log(FIELD.find('.AssignedTags br'));
	}

	return;
};

//********************************************************************************* //

Tagger.DelTag = function()
{
	jQuery(this).closest('.tag').fadeOut('slow', function(){ jQuery(this).remove(); });
	return false;
};

//********************************************************************************* //
