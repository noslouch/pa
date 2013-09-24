/**
 * CE Cache - Control Panel JavaScript File
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */


/**
 * Method to disable text selection of an element.
 * Original idea from http://stackoverflow.com/questions/2700000/how-to-disable-text-selection-using-jquery
 */
(function( $ )
{
	$.fn.disableSelectionCustom = function()
	{
		return this.each( function()
		{
			$( this ).attr( 'unselectable', 'on' )
				.css({
					'-webkit-user-select' : 'none',
    				'-khtml-user-select' : 'none',
    				'-moz-user-select' : 'none',
    				'-o-user-select' : 'none',
    				'user-select' : 'none'
				})
				.each( function()
				{
					this.onselectstart = function()
					{
						return false;
					};
				});
		});
	};

	$.fn.enableSelectionCustom = function()
	{
		return this.each( function()
		{
			$( this ).attr( 'unselectable', 'off' )
				.css({
					'-webkit-user-select' : 'text',
    				'-khtml-user-select' : 'text',
    				'-moz-user-select' : 'text',
    				'-o-user-select' : 'text',
    				'user-select' : 'text'
				})
				.each( function()
				{
					this.onselectstart = function()
					{
						return false;
					};
				});
		});
	};
})( jQuery );

/*
CE Cache AJAX control panel.
 */
$( function( base ) {

	//let the user know if there is an error loading the required options and quit.
	if ( undefined == base )
	{
		alert( base.lang.install_error );
		return;
	}

	//this is the ajax that makes it happen
	base.getLevel = function( path )
	{
		$('#ce_cache_items_list').empty();
		$('#ce_cache_loader').addClass('ce_cache_empty');

		$.jsonp({
			url: base.urls.getLevel,
			data: {
				'path': path,
				'prefix': base.prefix,
				'driver': base.driver,
				'secret': base.secret
			},
			cache: false,
			callbackParameter: 'callback',
			success: function( response )
			{
				$('#ce_cache_loader').removeClass('ce_cache_empty');

				if ( response.success )
				{
					if ( response.data.items_html != '' )
					{
						$('#ce_cache_items_list').html( response.data.items_html );
					}
					else
					{
						$('#ce_cache_items_list').html( '<p>' + base.lang.no_items_found + '</p>' );
					}

					$('#ce_cache_breadcrumbs').html( response.data.breadcrumbs_html );
				}
				else //a problem occurred
				{
					if ( undefined !== response.message ) //there is a message
					{
						$('#ce_cache_items_list').html( '<p>' + response.message + '</p>' );
					}
					else //no error message
					{
						$('#ce_cache_items_list').html( '<p>' + base.lang.unknown_error + '</p>' );
					}
				}
			},
			error: function(xOptions, textStatus)
			{
				base.showErrorResponse( textStatus );
			}
		});
	};

	base.showErrorResponse = function( message )
	{
		$('<div>')
			.appendTo('body')
			.attr('title', base.lang.ajax_error_title )
			.html( '<p>' + base.lang.ajax_error + '</p><br>')
			.append( $('<code>').text( message ) )
			.dialog({
				modal: true,
				close: function( event, ui )
				{
					$( ui.target).dialog('destroy').remove();
				},
				width: '80%'
			})
			.dialog( 'open' );
	};

	//this is the ajax that makes it happen
	base.deleteItem = function( path, item, refresh, refresh_time )
	{
		$.jsonp({
			url: base.urls.deleteItem,
			data: {
				'path': path,
				'prefix': base.prefix,
				'driver': base.driver,
				'secret': base.secret,
				'refresh': refresh,
				'refresh_time' : refresh_time
			},
			cache: false,
			callbackParameter: 'callback',
			success: function( response )
			{
				if ( response.success )
				{
					if ( $.contains( $('#ce_cache_items_list ol'), item ) )
					{
						item.remove();
					}
				}
				else //a problem occurred
				{
					if ( undefined !== response.message ) //there is a message
					{
						alert( response.message );
					}
					else //no error message
					{
						alert( base.lang.unknown_error );
					}
				}
			},
			error:function (xOptions, textStatus)
			{
				base.showErrorResponse( textStatus );
			}
		});
	};

	//setup
	base.setup = function()
	{
		//setup live links for breadcrumbs
		$( document ).delegate( '.ce_cache_folder .ce_cache_name', 'click', function( e )
		{
			e.preventDefault();

			base.getLevel( $( this ).closest( 'li' ).data( 'path' ) );

			return false;
		});

		//setup live links for navigation
		$( document ).delegate( '#ce_cache_breadcrumbs a', 'click', function( e )
		{
			e.preventDefault();

			base.getLevel( $( this ).data( 'path' ) );

			return false;
		});

		//ce_cache_refresh_time_holder
		$( document ).delegate( '#ce_cache_refresh_items', 'click', function()
		{
			$('#ce_cache_refresh_time_holder').css('display', ( $(this).is(':checked') ) ? 'block' : 'none' );
		});

		//setup live links for navigation
		$( document ).delegate( '.ce_cache_delete', 'click', function( e )
		{
			e.preventDefault();

			var item = $( this ).closest( 'li' );

			var type = ( item.hasClass('ce_cache_folder') ) ? 'folder' : 'file';

			var is_global = item.data('path').substring(0,6) == 'global';

			var dialogHTML = '';
			var buttons = {};

			if ( type == 'folder' ) //folder
			{
				//the dialog buttons and respective functionality
				buttons[ base.lang.delete_child_items_button ] = function()
				{
					$( "#ce_cache_delete_dialog" ).dialog( 'close' );

					var refresh = $('#ce_cache_refresh_items').is(':checked');
					var refresh_time = ( refresh ) ? $('#ce_cache_refresh_time option:selected').val() : 0;

					base.deleteItem( item.data( 'path' ), item, refresh, refresh_time );
				};
				buttons[ base.lang.cancel ] = function()
				{
					$( "#ce_cache_delete_dialog" ).dialog( 'close' );
				};

				//the dialog HTML to display
				dialogHTML += '<p>' + base.lang.delete_child_items_confirmation.replace( '%s', $( this ).closest('li').find('.ce_cache_name').text() ) + '</p>' +
					'<p';
				if ( is_global ) //hide the refresh option if this is a global item
				{
					dialogHTML += ' style="display: none"';
				}
				dialogHTML += '><input type="checkbox" id="ce_cache_refresh_items" value=""> <label for="ce_cache_refresh_items">' + base.lang.delete_child_items_refresh + '</label></p>' +
					'<p id="ce_cache_refresh_time_holder" style="display: none;">' + base.lang.delete_child_items_refresh_time + ' <select id="ce_cache_refresh_time"><option value="0" selected="selected">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></p>';

				$( '#ce_cache_delete_dialog' ).html( dialogHTML ).dialog(
				{
					modal: true,
					buttons: buttons
				});
			}
			else //file
			{
				//the dialog buttons and respective functionality
				buttons[ base.lang.delete_child_item_button ] = function()
				{
					$( '#ce_cache_delete_dialog' ).dialog( 'close' );

					base.deleteItem( item.data( 'path' ), item, $('#ce_cache_refresh_item').is(':checked'), 0 );
				};
				buttons[ base.lang.cancel ] = function()
				{
					$( "#ce_cache_delete_dialog" ).dialog( 'close' );
				};

				//the dialog HTML to display
				dialogHTML += '<p>' + base.lang.delete_child_item_confirmation.replace( '%s', $( this ).closest('li').find('.ce_cache_name').text() ) + '</p>' +
					'<p';
				if ( is_global ) //hide the refresh option if this is a global item
				{
					dialogHTML += ' style="display: none"';
				}
				dialogHTML += '><input type="checkbox" id="ce_cache_refresh_item" value=""> <label for="ce_cache_refresh_item">' + base.lang.delete_child_items_refresh + '</label></p>';

				$( '#ce_cache_delete_dialog' ).html( dialogHTML ).dialog({
					modal: true,
					buttons: buttons
				});
			}

			return false;
		});

		//kick things off
		base.getLevel( '/' );
	};

	//setup live links for navigation
	$( document ).delegate( '.ce_cache_view', 'click', function()
	{
		$(this).attr( 'href', EE.BASE + '&C=addons_modules&M=show_module_cp&module=ce_cache&method=view_item&driver=' + base.driver + '&item=' + base.prefix + encodeURIComponent( $( this ).closest( 'li' ).data( 'path' ) ) );

		return true;
	});

	base.setup();

	/*
	$( 'li.ce_cache_branch > div.ce_cache_inner .ce_cache_name' ).disableSelectionCustom().click( function( e ) {
		e.preventDefault();

		$( this ).closest( 'li.ce_cache_branch' ).toggleClass( 'open closed' );

		return false;
	})
	*/
}(ceCacheOptions) );