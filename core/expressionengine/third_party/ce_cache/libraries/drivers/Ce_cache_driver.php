<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Driver abstract class.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
abstract class Ce_cache_driver
{
	protected $debug = false;

	public function __construct()
	{
		$this->EE = get_instance();

		//if the template debugger is enabled, and a super admin user is logged in, enable debug mode
		$this->debug = false;
		if ( $this->EE->session->userdata['group_id'] == 1 && $this->EE->config->item('template_debugging') == 'y' )
		{
			$this->debug = true;
		}
	}

	/**
	 * Is the driver supported?
	 *
	 * @abstract
	 * @return bool
	 */
	abstract public function is_supported();

	/**
	 * The driver name.
	 *
	 * @abstract
	 * @return void
	 */
	abstract public function name();

	//------------------------------------ cache item methods ------------------------------------
	/**
	 * Stores a cache item.
	 *
	 * @abstract
	 * @param $id
	 * @param string $content
	 * @param string $seconds
	 * @return bool
	 */
	abstract public function set( $id, $content = '', $seconds = '' );

	/**
	 * Retrieve an item from the cache.
	 *
	 * @abstract
	 * @param $id
	 * @return mixed
	 */
	abstract public function get( $id );

	/**
	 * Remove an item from the cache.
	 *
	 * @abstract
	 * @param $id
	 * @return bool
	 */
	abstract public function delete( $id );

	/**
	 * Gives information about the item.
	 *
	 * @abstract
	 * @param $id
	 * @param bool $get_content Include the content in the return array?
	 * @return array|bool An array with the keys 'expiry', 'made', 'ttl', 'ttl_remaining', 'size', and 'size_raw' and 'content' (if $get_content is set to true) on success, or false on failure
	 */
	abstract public function meta( $id, $get_content = true );

	//------------------------------------ cache methods ------------------------------------
	/**
	 * Purges the entire cache.
	 *
	 * @abstract
	 * @return bool
	 */
	abstract public function clear();

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * @abstract
	 * @param $relative_path
	 * @return array
	 */
	abstract public function get_all( $relative_path );

	/**
	 * Retrieves all of the cached items (or folder paths) at the specified relative path for 1 level of depth.
	 *
	 * @param $relative_path
	 * @return array
	 */
	public function get_level( $relative_path )
	{
		$items = $this->get_all( $relative_path );

		if ( empty( $items ) )
		{
			return array();
		}

		$driver = $this->name();

		if (  $driver == 'db' || $driver == 'apc' ) //db and apc drivers
		{
			$files = array();
			$folders = array();

			foreach ( $items as $index => $item )
			{
				$slash_pos = strpos( $item['id'], '/' ); //is there a slash?

				if ( $slash_pos !== false ) //a directory (first segment of item path at a higher level)
				{
					$folders[] = substr( $item['id'], 0, $slash_pos + 1 );
				}
				else //an item at the current level
				{
					$files[$index] = $items[$index];
				}
			}
			$folders = array_unique( $folders );
			$dirs = array();
			foreach ( $folders as $folder )
			{
				$dirs[] = array( 'id' => $folder );
			}

			$items = array_merge( $dirs, $files );
			unset( $folders, $files, $dirs );

			return $items;
		}
		else //other drivers
		{
			foreach ( $items as $index => $item )
			{
				$slash_pos = strpos( $item, '/' );

				if ( $slash_pos !== false ) //a directory (first segment of item path at a higher level)
				{
					$items[ $index ] = substr( $item, 0, $slash_pos + 1 );
				}
			}

			return array_unique( $items );
		}
	}


	/**
	 * Retrieves basic info about the cache.
	 *
	 * @abstract
	 * @return array|bool
	 */
	abstract public function info();

	//------------------------------------ helpers ------------------------------------
	/**
	 * Removes double slashes, except when they are preceded by ':', so that 'http://', etc are preserved.
	 *
	 * @param string $str The string from which to remove the double slashes.
	 * @return string The string with double slashes removed.
	 */
	protected function remove_duplicate_slashes( $str )
	{
		return preg_replace( '#(?<!:)//+#', '/', $str );
	}

	/**
	 * Determines if an id is valid or if it contains invalid characters.
	 *
	 * @param $id
	 * @return bool
	 */
	protected function id_is_valid( $id )
	{
		$sanitized = $this->EE->security->sanitize_filename( $id, true );
		return ( $id === $sanitized );
	}

	/**
	 * Simple method to log a debug message to the EE Debug console.
	 *
	 * @param string $method
	 * @param string $message
	 * @return void
	 */
	protected function log_debug_message( $method = '', $message = '' )
	{
		$this->EE->TMPL->log_item( "&nbsp;&nbsp;***&nbsp;&nbsp;CE Cache - " . $this->name() . " - $method debug: " . $message );
	}

	/**
	 * Converts a file size from bytes to a human readable format.
	 *
	 * A method derived from a function originally posted by xelozz -at- gmail.com 18-Feb-2010 10:34 to http://us2.php.net/manual/en/function.memory-get-usage.php#96280
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param int $size Bytes.
	 * @param int $precision
	 * @return string Human readable file size.
	 */
	public static function convert_size( $size, $precision = 2 )
	{
		if ( ! is_numeric( $size ) || $size <= 0 )
		{
			return '0 b';
		}

		if ( ! is_numeric( $precision ) || $precision < 0 )
		{
			$precision = 2;
		}

		$unit = array( 'b', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' );

		$i = floor( log( $size, 1024 ) );

		if ( isset( $unit[$i] ) )
		{
			return @round( $size / pow( 1024, $i ), $precision ) . ' ' . $unit[$i];
		}
		else
		{
			return $size . ' ' . 'b';
		}
	}

	/**
	 * Get the file size of a string.
	 * @static
	 * @param $string
	 * @return int
	 */
	protected static function size( $string )
	{
		if ( function_exists( 'mb_strlen' ) )
		{
			return mb_strlen( $string, '8bit' );
		}
		else
		{
			return strlen( $string );
		}
	}
}