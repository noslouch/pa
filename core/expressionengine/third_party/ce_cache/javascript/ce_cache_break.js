$( function() {

	//bind the refresh checkbox to show/hide the time setting

	var ce_cache_tags_holder = $( '#ce_cache_tags_holder' );
	var ce_cache_items_holder = $( '#ce_cache_items_holder' );

	//add tag functionality
	$( '.ce_cache_add_tag', ce_cache_tags_holder ).click( function( e )
	{
		e.preventDefault();

		var content = '<span class="ce_cache_tag_wrapper"><input name="ce_cache_tag[' + $( '.ce_cache_tag_wrapper' ).size() + ']" type="text" value=""> <a class="ce_cache_remove_tag" href="#ce_cache_remove_tag"></a></span>';

		ce_cache_tags_holder.before( content );

		return false;
	});

	//add item functionality
	$( '.ce_cache_add_item', ce_cache_items_holder ).click( function( e )
	{
		e.preventDefault();

		var content = $( '<span class="ce_cache_item_wrapper"><input name="ce_cache_item[' + $( '.ce_cache_item_wrapper' ).size() + ']" type="text" value="local/"> <a class="ce_cache_remove_item" href="#ce_cache_remove_item"></a></span>' );

		ce_cache_items_holder.before( content );

		return false;
	});


	//remove item/tag functionality
	$('.ce_cache_remove_item, .ce_cache_remove_tag').live( 'click', function( e ) {
		e.preventDefault();

		$( this ).parent().remove();

		return false;
	});

	//add change event
	$('#ce_cache_refresh').change( function( e )
	{
		e.preventDefault();

		if ( $('#ce_cache_refresh').attr( 'checked' ) )
		{
			$( '#ce_cache_refresh_holder' ).show();
		}
		else
		{
			$( '#ce_cache_refresh_holder' ).hide();
		}
	});

	$('#ce_cache_refresh').trigger( 'change' );
});