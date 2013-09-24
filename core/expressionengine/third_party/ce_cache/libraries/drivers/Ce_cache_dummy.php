<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Dummy driver.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_dummy extends Ce_cache_driver
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
		return true;
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
	 * Store a cache item.
	 *
	 * @param $id The cache item's id.
	 * @param string $content The content to store.
	 * @param int $seconds The time to live for the cached item in seconds. Zero (0) seconds will result store the item for a long, long time. Default is 360 seconds.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360 )
	{
		return true;
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @param $id The cache item's id.
	 * @return bool
	 */
	public function get( $id )
	{
		return false;
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param $id The cache item's id.
	 * @return bool
	 */
	public function delete( $id )
	{
		return true;
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
		return false;
	}

	/**
	 * Purges the entire cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		return true;
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * @param $relative_path The relative path from the cache base.
	 * @return array
	 */
	public function get_all( $relative_path )
	{
		return false;
	}

	/**
	 * Retrieves all of the cached items (or folder paths) at the specified relative path for 1 level of depth.
	 *
	 * @abstract
	 * @param $relative_path
	 * @return array
	 */
	public function get_level( $relative_path )
	{
		return array();
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @return array|bool
	 */
	public function info()
	{
		return false;
	}
}