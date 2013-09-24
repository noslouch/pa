<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Control Panel Library Class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */

class Ce_cache_cp
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE = get_instance();
	}

	/**
	 * Ensures the constructor is called.
	 *
	 * @return void
	 */
	public function Ce_cache_cp()
	{
		$this->__construct();
	}

	/**
	 * Takes an array of list items from the ajax_get_level() method of the CE Cache MCP file and creates the HTML to send to the view.
	 *
	 * @param array $items
	 * @return string The ordered list HTML.
	 */
	public function items_to_html_list( $items )
	{
		//load the language file
		$this->EE->lang->loadfile( 'ce_cache' );

		//load the date helper
		$this->EE->load->helper( 'date' );

		//the time format string
		$time_string = '%Y-%m-%d - %h:%i:%s %a';

		$html = '<ol>';

		foreach ( $items as $item )
		{
			//expiry date
			$item['expiry'] = ( $item['ttl'] == 0 ) ? '&infin;' : mdate( $time_string, $item['made'] + $item['ttl'] );

			//made date
			$item['made'] = mdate( $time_string, $item['made'] );

			$html .= "\n\t" . '<li class="ce_cache_' . $item[ 'type' ] . ( ($item[ 'type' ] == 'folder') ? ' closed' : '' ) . '" data-path="' . $item['id_full'] . '"><div class="ce_cache_inner">';
			$html .= '<span class="ce_cache_name"><span class="ce_cache_icon"><!-- --></span>' . rtrim( $item['id'], '/' ) . '</span>';

			if ( $item['type'] == 'folder' )
			{
				$html .= '<span class="ce_cache_made">&ndash;</span><span class="ce_cache_expiry">&ndash;</span><a href="#" class="ce_cache_delete">' . lang( "ce_cache_delete_children" ) . '</a></div><!-- .ce_cache_inner -->';
				$html .= "\n\t" . '</li>';
			}
			else //file
			{
				$html .= '<span class="ce_cache_made">' . $item['made'] . '</span><span class="ce_cache_expiry"' . ( $item['ttl_remaining'] != 0 ? ' title="' . ( $item['ttl_remaining'] ) . ' ' . lang( "ce_cache_seconds_from_now" ) . '"' : '' ) . '>' . $item['expiry'] . '</span><a href="#" class="ce_cache_delete">' . lang( "ce_cache_delete_item" ) . '</a><a href="#" class="ce_cache_view" target="_blank">' . lang( "ce_cache_view_item" ) . '</a></div><!-- .ce_cache_inner --></li>';
			}
		}

		$html .= "\n" . '</ol>';

		return $html;
	}

	/**
	 * Create the breadcrumb.
	 *
	 * @param string $driver The driver.
	 * @param string $path The cache item path.
	 * @return string
	 */
	public function breadcrumb_html( $driver, $path )
	{
		//load the language file
		$this->EE->lang->loadfile( 'ce_cache' );

		//create the home breadcrumb
		$breadcrumb[] = '<a href="#" data-path="/">' . lang( 'ce_cache_driver_' . $driver ) . '</a>';

		$path = trim( $path, '/' );

		if ( ! empty( $path ) )
		{
			$pieces = explode( '/', $path );

			$current = '';

			foreach ( $pieces as $piece )
			{
				$current .= $piece . '/';
				$breadcrumb[] = '<a href="#" data-path="' . $current . '">' . $piece . '</a>';
			}
		}

		return implode( ' >> ', $breadcrumb );
	}
}