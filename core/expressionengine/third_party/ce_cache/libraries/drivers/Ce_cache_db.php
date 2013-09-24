<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Database driver.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_db extends Ce_cache_driver
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
		return $this->EE->db->table_exists( 'ce_cache_db_driver' );
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
	 * @param $id The cache item's id.
	 * @param string $content The content to store.
	 * @param int $seconds The time to live for the cached item in seconds. Zero (0) seconds will result store the item for a long, long time. Default is 360 seconds.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360 )
	{
		$this->delete( $id );

		//create the data array
		$data = array(
			'id' => $id,
			'ttl' => $seconds,
			'made' => time(),
			'content' => $content
		);

		$sql = $this->EE->db->insert_string( 'exp_ce_cache_db_driver', $data );

		//attempt to store the data
		return $this->EE->db->query( $sql );
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @param $id The cache item's id.
	 * @return mixed
	 */
	public function get( $id )
	{
		//fetch the data
		$results = $this->EE->db->query( '
		SELECT ttl,
		made,
		content
		FROM exp_ce_cache_db_driver
		WHERE id = ?', array( $id ) );

		if ( $results->num_rows() > 0 )
		{
			$data = $results->row_array();

			//if seconds is set to 0 then the cache is never deleted, unless done so manually
			if ( $data['ttl'] != 0 && time() > $data['made'] + $data['ttl'] )
			{
				//the item has expired, get rid of it
				$this->delete( $id );

				return false;
			}

			//return the content
			return $data['content'];
		}

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
		$this->EE->db->query( 'DELETE FROM exp_ce_cache_db_driver WHERE id = ?', array( $id ) );
		return true;
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
		//fetch the data
		$results = $this->EE->db->query( "SELECT
		made,
		ttl,
		CASE ttl WHEN '0' then '0' ELSE ( made + ttl) END as expiry,
		CASE ttl WHEN '0' then '0' ELSE ( CAST( made AS UNSIGNED) + CAST( ttl AS UNSIGNED) - UNIX_TIMESTAMP() ) END as ttl_remaining,
		content
		FROM exp_ce_cache_db_driver
		WHERE id = ?", array( $id ) );

		if ( $results->num_rows() > 0 )
		{
			$data = $results->row_array();

			//if seconds is set to 0 then the cache is never deleted, unless done so manually
			if ( $data['ttl'] != 0 && time() > $data['expiry'] )
			{
				//the item has expired, get rid of it
				$this->delete( $id );

				return false;
			}

			//get the content size
			$size = parent::size( $data['content'] );

			//set the size variables
			$data[ 'size' ] = parent::convert_size( $size );
			$data[ 'size_raw' ] = $size;

			//include the content in the final array?
			if ( ! $get_content )
			{
				unset( $data['content'] );
			}

			return $data;
		}

		return false;
	}

	/**
	 * Purges the entire cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		return $this->EE->db->empty_table( 'exp_ce_cache_db_driver' );
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * This method differs from the other drivers, as it also returns the metadata for the objects, without the 'content' item. Most of the other driver implementations only return the item id.
	 *
	 * @param $relative_path The relative path from the cache base.
	 * @return array|bool
	 */
	public function get_all( $relative_path )
	{
		$results = $this->EE->db->query( "
		SELECT
		SUBSTRING( id, " . ( strlen( $relative_path ) + 1 )  . ") as id,
		ttl,
		CASE ttl WHEN '0' then '0' ELSE (made + ttl) END as expiry,
		CASE ttl WHEN '0' then '0' ELSE (made + ttl - UNIX_TIMESTAMP() ) END as ttl_remaining,
		made
		FROM exp_ce_cache_db_driver
		WHERE SUBSTRING( id, 1, " . strlen( $relative_path )  . ") = '" . $this->EE->db->escape_str( $relative_path ) . "'
		ORDER BY id ASC" );

		if ( $results->num_rows() > 0 )
		{
			$rows = $results->result_array();
			$results->free_result();

			//loop through and expire the entries that need it
			foreach ( $rows as $index => $row )
			{
				if ( $row['ttl_remaining'] < 0 )
				{
					$this->delete( $relative_path . $row['id'] );
					unset( $rows[$index] );
				}
			}

			return $rows;
		}

		return false;
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @return array|bool
	 */
	public function info()
	{
		//TODO add this in
		return false;
	}
}