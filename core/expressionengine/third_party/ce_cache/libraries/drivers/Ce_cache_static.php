<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Static driver.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_static extends Ce_cache_driver
{
	private $cache_base = '';

	private $is_flat = false;

	public function __construct()
	{
		parent::__construct();
		$this->EE->load->helper( 'file' );

		//set the base cache path
		$this->cache_base = isset( $_SERVER['DOCUMENT_ROOT'] ) ? $_SERVER['DOCUMENT_ROOT'] : FCPATH;
		$this->cache_base = rtrim( $this->cache_base, '/') . '/static';

		//override this setting if set in the config or global vars array
		if ( isset( $this->EE->config->_global_vars[ 'ce_cache_static_path' ] ) && $this->EE->config->_global_vars[ 'ce_cache_static_path' ] != false ) //first check global array
		{
			$this->cache_base = $this->EE->config->_global_vars[ 'ce_cache_static_path' ];
		}
		else if ( $this->EE->config->item( 'ce_cache_static_path' ) !== false ) //then check config
		{
			$this->cache_base = $this->EE->config->item( 'ce_cache_static_path' );
		}

		//determine if the user opts to use flat files
		if ( $this->EE->config->item( 'ce_cache_static_flat' ) !== false ) //then check config
		{
			$this->is_flat = ( $this->EE->config->item( 'ce_cache_static_flat' ) === true || $this->EE->config->item( 'ce_cache_static_flat' ) == 'yes' || $this->EE->config->item( 'ce_cache_static_flat' ) == 'y' ) ;
		}

		$this->cache_base = rtrim( str_replace( '\\', '/', $this->cache_base ), '/' ) . '/';

		//file permissions
		$this->file_permissions = 0644;
		$file_perms_override = $this->EE->config->item('ce_cache_file_permissions');
		if ( $file_perms_override != FALSE && is_numeric( $file_perms_override ) )
		{
			$this->file_permissions = $file_perms_override;
		}

		//directory permissions
		$this->dir_permissions = 0775;
		$dir_perms_override = $this->EE->config->item('ce_cache_dir_permissions');
		if ( $dir_perms_override != FALSE && is_numeric( $dir_perms_override ) )
		{
			$this->dir_permissions = $dir_perms_override;
		}
	}

	/**
	 * Is the driver supported?
	 *
	 * @return bool
	 */
	public function is_supported()
	{
		//determine if static caching is enabled, by checking the config and global var settings
		$enabled = false;
		if ( isset( $this->EE->config->_global_vars[ 'ce_cache_static_enabled' ] ) && $this->EE->config->_global_vars[ 'ce_cache_static_enabled' ] != false ) //first check global array
		{
			$enabled = $this->EE->config->_global_vars[ 'ce_cache_static_enabled' ];
		}
		else if ( $this->EE->config->item( 'ce_cache_static_enabled' ) !== false ) //then check config
		{
			$enabled = $this->EE->config->item( 'ce_cache_static_enabled' );
		}
		$enabled = ( $enabled == 'y' || $enabled == 'yes' || $enabled == 'on' || $enabled === true );
		if ( ! $enabled )
		{
			return false;
		}

		//get the static cache base path
		$path = rtrim( $this->cache_base, '/' );

		//if there is no path, then caching is not enabled
		if ( empty( $path ) || $path == '/' )
		{
			return false;
		}

		return is_really_writable( $path );
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
	 * @param string $id      The cache item's id.
	 * @param string $content The content to store.
	 * @param int    $seconds The time to live for the cached item in seconds. Zero (0) seconds will store the item for a long, long time. Default is 360 seconds.
	 * @param array  $headers The current headers. These will be saved so that the proper header can be returned for subsequent requests.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360, $headers = array() )
	{
		$id = $this->clean_id( $id );

		if ( $this->is_flat )
		{
			$data = $content;
		}
		else
		{
			//minimum cache time of 5 seconds to avoid redirect loops
			if ( $seconds != 0 && $seconds < 5 )
			{
				$seconds = 5;
			}

			//make sure the headers are an array
			if ( ! is_array( $headers ) )
			{
				$headers = (array) $headers;
			}

			//create the data array
			$data = array(
				'ttl'		=> $seconds,
				'made'		=> time(),
				'content'	=> $content,
				'headers' => $headers
			);
		}

		unset( $content );

		//the file
		$file = $this->cache_base . $id;

		//figure out the base cache directory
		$base = rtrim( $this->cache_base, '/' );

		//figure out the directory path
		$directories = $file;
		if ( false !== $pos = strrpos( $directories, '/' ) ) //get the substring before the last slash
		{
			//get the substring before the last 'segment'
			$directories = rtrim( substr( $directories, 0, $pos ), '/' );
		}
		else
		{
			//if there were no slashes in the id, we have bigger problems...
			return false;
		}

		//create the directories with the correct permissions as needed
		if ( ! @is_dir( $directories ) )
		{
			//turn the directory path into an array of directories
			$directories = explode( '/', substr( $file, strlen( $base ) ) );

			//remove the last item, as it is not a directory
			array_pop( $directories );

			//assign the current variable
			$current = $base;

			//start with base, and add each directory and make sure it exists with the proper permissions
			foreach ( $directories as $directory )
			{
				$current .= '/' . $directory;

				//check if the directory exists
				if ( ! @is_dir( $current ) )
				{
					//try to make the directory with the specified permissions
					if ( ! @mkdir( $current . '/', $this->dir_permissions, true ) )
					{
						$this->log_debug_message( __METHOD__, "Could not create the cache directory '$current/'." );
						break;
					}
				}
			}

			//ensure the directory is writable
			if ( ! is_really_writable( $current ) )
			{
				$this->log_debug_message( __METHOD__, "Cache directory '$current' is not writable." );
				//$this->cache->supported_drivers['file'] = false;
				return false;
			}
		}

		unset( $directories );

		//write the file
		if ( write_file( $file, $this->is_flat ? $data : @serialize( $data ) ) )
		{
			//try to set the file permissions
			@chmod( $file, $this->file_permissions );
			unset( $file, $data );
			return true;
		}

		unset( $file, $data );

		return false;
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @param string $id The cache item's id.
	 * @return mixed
	 */
	public function get( $id )
	{
		$id = $this->clean_id( $id );

		//the file does not exist
		if ( ! is_readable( $this->cache_base . $id ) )
		{
			return false;
		}

		//the file exists read it
		$data = @file_get_contents( $this->cache_base . $id );

		if ( $data === false )
		{
			return false;
		}

		if ( ! $this->is_flat )
		{
			//try to unserialize the data
			$data = @unserialize( $data );

			//make sure the data is unserialized and in the expected format
			if ( empty( $data ) || ! is_array( $data ) || count( $data ) != 4 )
			{
				return false;
			}

			//if seconds is set to 0 then the cache is never deleted, unless done so manually
			if ( $data['ttl'] != 0 && time() > $data['made'] + $data['ttl'] )
			{
				//the file has expired, get rid of it
				@unlink( $this->cache_base . $id );
				return false;
			}

			//return the data
			$data = $data['content'];
		}

		return $data;
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param string $id The cache item's id.
	 * @return bool
	 */
	public function delete( $id )
	{
		$id = $this->clean_id( $id );

		//remove if the file exists
		if ( file_exists( $this->cache_base . $id ) )
		{
			return @unlink( $this->cache_base . $id );
		}

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
		$id = $this->clean_id( $id );

		$file = $this->cache_base . $id;

		//make sure the file exists and we get the data
		if ( ! file_exists( $file ) || false === $data = read_file( $file ) )
		{
			return false;
		}

		if ( $this->is_flat )
		{
			$data = array(
				'ttl'		=> 0,
				'made'		=> filemtime( $file ),
				'content'	=> $data
			);

			$expiry = 0;
		}
		else //not flat
		{
			$data = @unserialize( $data );

			//make sure the data is unserialized and in the expected format
			if ( empty( $data ) || ! is_array( $data ) || count( $data ) != 4 )
			{
				return false;
			}

			//if seconds is set to 0 then the cache is never deleted, unless done so manually
			if ( $data['ttl'] != 0 && time() > $data['made'] + $data['ttl'] )
			{
				//the file has expired, get rid of it
				unlink( $this->cache_base . $id );
				return false;
			}

			//determine the expiration timestamp
			$expiry = ( $data['ttl'] == 0 ) ? 0 : $data['made'] + $data['ttl'];
		}

		//get the content size
		$size = @filesize( $file );
		if ( $size === false )
		{
			$size = parent::size( $data['content'] );
		}

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
	 * @return bool
	 */
	public function clear()
	{
		//delete files and directories
		return delete_files( rtrim( $this->cache_base, '/' ), true );
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * @param string $relative_path The relative path from the cache base.
	 * @return array|bool
	 */
	public function get_all( $relative_path )
	{
		//prep the path characters
		$path = rtrim( $this->remove_duplicate_slashes( $this->cache_base . $relative_path ), '/' );

		//check if the directory exists
		if ( ! @is_dir( $path ) )
		{
			return false;
		}

		//will hold the final file path
		$files = array();

		$path_length = strlen( $path . '/' );

		$iterator = new RecursiveDirectoryIterator( $path );
		foreach( new RecursiveIteratorIterator( $iterator ) as $path => $current )
		{
			if ( $current->isFile() )
			{
				array_push( $files, substr( str_replace( '\\', '/', $path ), $path_length ) );
			}
		}

		sort( $files, SORT_STRING );

		return $files;
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @return array|bool
	 */
	public function info()
	{
		//TODO make this more useful
		return get_dir_file_info( $this->cache_base . 'ce_cache', false );
	}

	/**
	 * Preps the raw id for use with the methods. It removes the 'ce_cache/.../' from the beginning of the id, and adds 'index.html' to the end where necessary.
	 *
	 * @param string $id
	 * @return mixed|string
	 */
	public function clean_id( $id )
	{
		//add in the index.html if the id does not end with 'index.html'
		if ( ! preg_match( '#/index.html$#', $id ) )
		{
			$id = rtrim( $id, '/' ) . '/index.html';
		}

		return $id;
	}
}