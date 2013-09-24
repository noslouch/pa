<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Memcached driver.
 *
 * http://php.net/manual/en/book.memcached.php
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_memcached extends Ce_cache_driver
{
	private $memcached;
	private $is_setup = false;

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
		return extension_loaded( 'memcached' );
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
	 * @link http://www.php.net/manual/en/memcached.set.php
	 * @param string $id The cache item's id.
	 * @param string $content The content to store.
	 * @param int $seconds The time to live for the cached item in seconds. Zero (0) seconds will store the item for a long time. Default is 360 seconds.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360 )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

        //Memcached has problems with long keys, so md5 it is
        $id = md5( $id );

		//create the data array
		$data = array(
			'ttl' => ( ( $seconds > 2592000 ) ? 2592000 : $seconds ), //if the save time is greater than 30 days in seconds, Memcached will think it is a Unix timestamp, which it is not, so we'll set the time to the max allowed value instead (if needed). See http://www.php.net/manual/en/memcached.expiration.php
			'made' => time(),
			'content' => $content
		);

		//attempt to store the data
		return $this->memcached->set( $id, $data, $data['ttl'] );
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @link http://www.php.net/manual/en/memcached.get.php
	 * @param string $id The cache item's id.
	 * @return mixed
	 */
	public function get( $id )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

        //Memcached has problems with long keys, so md5 it is
        $id = md5( $id );

        //fetch the data
		$data = $this->memcached->get( $id );

		//the data should be in the array format we left it in. If it isn't, we'll just return false.
		return ( is_array( $data ) && isset( $data['content'] ) ) ? $data['content'] : false;
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @link http://www.php.net/manual/en/memcached.delete.php
	 * @param string $id The cache item's id.
	 * @return bool
	 */
	public function delete( $id )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//Memcached has problems with long keys, so md5 it is
		$id = md5( $id );

		return $this->memcached->delete( $id );
	}

	/**
	 * Gives information about the item.
	 *
	 * @param string $id The cache item's id.
	 * @param bool $get_content Include the content in the return array?
	 * @return array|bool
	 */
	public function meta( $id, $get_content = true )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//Memcached has problems with long keys, so md5 it is
		$id = md5( $id );

		//attempt to get the stored data
		$data = $this->memcached->get( $id );

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
	 * @link http://www.php.net/manual/en/memcached.flush.php
	 * @return bool
	 */
	public function clear()
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//clear the 'user' cache
		return $this->memcached->flush();
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 * Memcached has no way of getting the cached items, so this will always return false.
	 *
	 * @param string $relative_path The relative path from the cache base.
	 * @return bool
	 */
	public function get_all( $relative_path )
	{
		return false;
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @link http://www.php.net/manual/en/memcached.getstats.php
	 * @return array|bool
	 */
	public function info()
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		return $this->memcached->getStats();
	}

	/**
	 * Sets up the Memcached object if it has not been setup already and adds any servers from the config or global array, or uses the default if none are found.
	 * @link http://www.php.net/manual/en/memcached.addserver.php
	 *
	 * @return bool Return true if the class is setup, or false on failure.
	 */
	private function setup()
	{
		if ( ! $this->is_setup ) //if not already setup
		{
			//servers
			$servers = array();

			//make sure this driver is supported
			if ( ! $this->is_supported() )
			{
				return false;
			}

			//instantiate the class
			$memcached = new Memcached();

			//default server array
			$servers[] = array( '127.0.0.1', 11211 );

			//server config
			$config = '';

			//check for config settings
			if ( ! empty( $this->EE->config->_global_vars[ 'ce_cache_memcached_servers' ] ) ) //first check the global array
			{
				$config = $this->EE->config->_global_vars[ 'ce_cache_memcached_servers' ];
			}
			else if ( $this->EE->config->item( 'ce_cache_memcached_servers' ) != false ) //then check the config
			{
				$config = $this->EE->config->item( 'ce_cache_memcached_servers' );
			}

			if ( ! empty( $config ) ) //we have some settings
			{
				//if the config is not an array, let's explode it out to be one
				if ( ! is_array( $config ) )
				{
					$servers = explode( '|', $config );
					foreach ( $servers as $index => $server )
					{
						$servers[$index] = explode( ',', $server );
					}
				}
				else
				{
					$servers = $config;
				}
			}

			//add each server. Note that while Memcached has an addServers method in addition to its addServer method, Memcache does not. For the sake of consistency, we'll just loop through and use addServer for both.
			foreach ( $servers as $server )
			{
				call_user_func_array( array( $memcached, 'addServer' ), $server );
			}

			$this->memcached = $memcached;

			//flag that setup has been completed
			$this->is_setup = true;
		}

		return true;
	}
}