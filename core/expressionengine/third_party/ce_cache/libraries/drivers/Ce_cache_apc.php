<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - APC driver.
 *
 * http://www.php.net/manual/en/book.apc.php
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_apc extends Ce_cache_driver
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Is the driver supported?
	 *
	 * @return bool
	 */
	public function is_supported()
	{
		//make sure the extension is loaded and that a core method exists
		return ( extension_loaded( 'apc' ) && function_exists( 'apc_fetch' ) );
	}

	/**
	 * The driver's name.
	 *
	 * @return mixed
	 */
	public function name()
	{
		return str_replace( 'Ce_cache_', '', __CLASS__ );
	}

	/**
	 * Store a cache item. The item will be stored in an array with the keys 'ttl', 'made', and 'content'.
	 *
	 * @link http://www.php.net/manual/en/function.apc-store.php
	 * @param $id The cache item's id.
	 * @param string $content The content to store.
	 * @param int $seconds The time to live for the cached item in seconds. Zero (0) seconds will result store the item for a long, long time. Default is 360 seconds.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360 )
	{
		//create the data array
		$data = array(
			'ttl' => $seconds,
			'made' => time(),
			'content' => $content
		);

		//attempt to store the data
		return apc_store( $id, $data, $seconds );
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @link http://www.php.net/manual/en/function.apc-fetch.php
	 * @param $id The cache item's id.
	 * @return mixed
	 */
	public function get( $id )
	{
		//fetch the data
		$data = apc_fetch( $id );

		//the data should be in the array format we left it in. If it isn't, we'll just return false.
		return ( is_array( $data ) && isset( $data['content'] ) ) ? $data['content'] : false;
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @link http://www.php.net/manual/en/function.apc-delete.php
	 * @param $id The cache item's id.
	 * @return bool
	 */
	public function delete( $id )
	{
		return apc_delete( $id );
	}

	/**
	 * Gives information about the item.
	 *
	 * @param $id The cache item's id.
	 * @param bool $get_content Include the content in the return array?
	 * @return array|bool
	 */
	public function meta( $id, $get_content = true )
	{
		//attempt to get the stored data
		$data = apc_fetch( $id );

		//make sure the data is in the format we save data in
		if ( empty( $data ) || ! is_array( $data ) || count( $data ) != 3 )
		{
			return false;
		}

		//determine the expiration timestamp
		$expiry = ( $data['ttl'] == 0 ) ? 0 : $data['made'] + $data['ttl'];

		//get the content size
		$size = parent::size( $data['content'] );

		//return the meta array
		$final = array(
			//if the time to live is 0, the data will not auto-expire
			'expiry' => $expiry,
			'made' => $data['made'],
			'ttl' => $data['ttl'],
			'ttl_remaining' => ( $data['ttl'] == 0 ) ? 0 : ( $expiry - time() ),
			'size' => parent::convert_size( $size ),
			'size_raw' => $size
		);

		//include the content in the final array?
		if ( $get_content )
		{
			$final['content'] = $data['content'];
		}

		unset( $data, $expiry );

		return $final;
	}

	/**
	 * Purges the entire cache.
	 *
	 * @link http://www.php.net/manual/en/function.apc-clear-cache.php
	 * @return bool
	 */
	public function clear()
	{
		//clear the 'user' cache
		return apc_clear_cache( 'user' );
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * @param $relative_path The relative path from the cache base.
	 * @return array
	 */
	public function get_all( $relative_path )
	{
		$items = array();

		//get the user info
		$info = apc_cache_info( 'user' );

		//make sure we have the data we need
		if ( empty( $info ) || ! isset( $info['cache_list'] ) || ! is_array( $info['cache_list'] ) )
		{
			return false;
		}

		//add the trailing slash
		$relative_path = rtrim( $relative_path, '/' ) . '/';

		//path_length
		$path_length = strlen( $relative_path );

		//the cache list items
		$temps = $info['cache_list'];

		//loop the items
		foreach ( $temps as $temp )
		{
			if ( substr( $temp['info'], 0, $path_length ) == $relative_path ) //the path matches
			{
				//determine the expiration timestamp
				$expiry = ( $temp['ttl'] == 0 ) ? 0 : $temp['mtime'] + $temp['ttl'];

				//create the item array
				$item = array(
					'id' => substr( $temp['info'], $path_length ),
					'made' => $temp['mtime'],
					'ttl' => $temp['ttl'],
					'expiry' => $expiry,
					'ttl_remaining' => ( $temp['ttl'] == 0 ) ? 0 : ( $expiry - time() )
				);

				if ( $item['ttl_remaining'] >= 0 ) //if not expired
				{
					//add the item array to the items array
					$items[] = $item;
				}
			}
		}

		//sort the items
		usort( $items, array( $this, 'sort_items') );

		return $items;
	}

	/**
	 * A callback method for the usort function in the get_all() method.
	 *
	 * @param $a
	 * @param $b
	 * @return bool
	 */
	function sort_items( $a, $b )
	{
		return $a['id'] > $b['id'];
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @link http://www.php.net/manual/en/function.apc-cache-info.php
	 * @return array|bool
	 */
	public function info()
	{
		return apc_cache_info();
	}
}