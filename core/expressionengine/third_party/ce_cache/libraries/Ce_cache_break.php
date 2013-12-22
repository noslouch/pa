<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Cache Break Class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */

class Ce_cache_break
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE = get_instance();

		//include CE Cache Utilities
		if ( ! class_exists( 'Ce_cache_utils' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_utils.php';
		}

		$this->async = ( $this->EE->config->item( 'ce_cache_async' ) != 'no' );
		$this->curl_enabled = ( $this->EE->config->item( 'ce_cache_curl' ) != 'no' );
	}

	/**
	 * Allows the cache to manually be broken.
	 *
	 * @param array $items
	 * @param array $tags
	 * @param bool $refresh
	 * @param int $refresh_time
	 */
	public function break_cache( $items = array(), $tags = array(), $refresh = true, $refresh_time = 1 )
	{
		//let's not worry about timing out
		set_time_limit( 0 );

		//load the class if needed
		if ( ! class_exists( 'Ce_cache_factory' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_factory.php';
		}

		//get the driver classes
		$classes = Ce_cache_factory::factory( Ce_cache_factory::$valid_drivers );

		//get the site name
		$site = Ce_cache_utils::get_site_label();

		//the prefix for this site
		$prefix = 'ce_cache/' . $site . '/';

		//items to delete without refreshing
		$deletes = array(); //key => item_id

		//items to refresh
		$refreshers = array(); //item_id => refresh_time

		if ( count( $tags ) > 0 ) //we have one or more tags
		{
			//add these tags to the overall tags array
			$temps = $tags;

			//escape all of the tags, as we will be using them in a query
			foreach ( $temps as $index => $temp )
			{
				$temps[ $index ] = $this->EE->db->escape_str( $temp );
			}

			$tagged_items = $this->EE->db->query( "
			SELECT item_id
			FROM exp_ce_cache_tagged_items
			WHERE SUBSTRING( item_id, 1, " . strlen( $prefix )  . " ) = '" . $this->EE->db->escape_str( $prefix ) . "'
			AND tag IN ( '" . implode( "', '", $temps ) . "' )
			ORDER BY item_id ASC" );

			if ( $tagged_items->num_rows() > 0 )
			{
				$hits = $tagged_items->result_array();

				foreach ( $hits as $hit )
				{
					if ( $refresh )
					{
						$refreshers[ $hit['item_id'] ] = $refresh_time;
					}
					else
					{
						$deletes[] = $hit['item_id'];
					}
				}
				unset( $hits );
			}
			$tagged_items->free_result();
		}

		//get all items
		foreach ( $items as $item )
		{
			$sub = substr( $item, 0, 5 );

			if ($sub == 'globa') //a global item
			{
				if (substr($item, -1, 1) == '/') //a path, not an item
				{
					foreach ($classes as $class)
					{
						$driver = $class->name();

						//attempt to get all items
						$hits = $class->get_all($prefix . $item);
						if ($hits !== false && is_array($hits)) //we have the items
						{
							//loop through and add the items to the all_items array
							foreach ($hits as $hit)
							{
								$deletes[] = $prefix . $item . (($driver == 'db' || $driver == 'apc') ? $hit['id'] : $hit);
							}
						}
						unset($hits);
					}
				}
				else //an item
				{
					//global items have no way of being refreshed, so just delete them
					$deletes[] = $prefix . $item;
				}
			}
			else if ( $sub  == 'local' || $sub  == 'stati' ) //a local or static item or path
			{
				$is_static = ( $sub == 'stati' );

				if ( substr( $item, -1, 1 ) == '/' ) //a path, not an item
				{
					foreach ( $classes as $class )
					{
						$driver = $class->name();

						if ( $is_static && $driver != 'static' ) //not the static driver
						{
							continue;
						}

						//attempt to get all items
						$hits = $class->get_all( $prefix . $item );
						if ( $hits !== false && is_array( $hits ) ) //we have the items
						{
							//loop through and add the items to the all_items array
							foreach ( $hits as $hit )
							{
								if ( ! $refresh ) //delete right away
								{
									$deletes[] = $prefix . $item . ( ( $driver == 'db' || $driver == 'apc' ) ? $hit['id'] : $hit );
								}
								else //delete and refresh
								{
									$refreshers[ $prefix . $item . ( ( $driver == 'db' || $driver == 'apc' ) ? $hit['id'] : $hit ) ] = $refresh_time;
								}
							}
						}
						unset( $hits );
					}
				}
				else //an item
				{
					if ( ! $refresh ) //delete right away
					{
						$deletes[] = $prefix . $item;
					}
					else //delete and refresh
					{
						$refreshers[ $prefix . $item ] = $refresh_time;
					}
				}
			}
		}

		//now let's clear the tags
		if ( count( $tags ) > 0 )
		{
			$this->EE->db->where_in( 'tag', $tags );
			$this->EE->db->delete( 'ce_cache_tagged_items' );
		}
		//unset the tags array as it is no longer needed
		unset( $tags );

		//now that we have all of our items to delete, let's delete them
		foreach( $classes as $class ) //loop through the driver classes
		{
			//loop through the delete items and delete them
			foreach ( $deletes as $item )
			{
				$class->delete( $item );
			}
		}

		//merge the delete array with the items from the refreshers array
		$deletes = array_merge( $deletes, array_keys( $refreshers ) );

		//now let's clear any applicable tagged items
		if ( count( $deletes ) > 0 )
		{
			$this->EE->db->where_in( 'item_id', $deletes );
			$this->EE->db->delete( 'ce_cache_tagged_items' );
		}

		//unset the deletes array as it is no longer needed
		unset( $deletes );

		//create the URL
		$url = $this->EE->config->slash_item('site_url');

		//see if this install has the ability to recreate the cache
		$can_recreate = ( function_exists( 'curl_init' ) || function_exists( 'fsockopen' ) );

		if ( $can_recreate ) //delete and refresh the items
		{
			//make sure that allow_url_fopen is set to true if permissible
			@ini_set('allow_url_fopen', true);
			//some servers will not accept the asynchronous requests if there is no user_agent
			@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

			$urls = array();

			foreach ( $refreshers as $item => $time )
			{
				//loop through the driver classes and delete this item
				foreach( $classes as $class )
				{
					$class->delete( $item );
				}

				//trim the prefix and 'local/' or 'static/' from the beginning of the path
				$path = ( strpos($item, $prefix . 'local/') === 0 ) ? substr( $item, strlen( $prefix ) + 6 ) : substr( $item, strlen( $prefix ) + 7 );

				//find the last '/'
				$last_slash = strrpos( $path, '/' );

				//if a last '/' was found, get the path up to that point
				$path = ( $last_slash === false ) ? '' : substr( $path, 0, $last_slash );

				$urls[$url . $path] = $time;
			}

			$this->refresh_urls( $urls );
		}
		else //just delete the items, as there is no way to recreate them
		{
			//loop through the driver classes
			foreach( $classes as $class )
			{
				//loop through the delete items and delete them
				foreach ( $refreshers as $item => $time )
				{
					$class->delete( $item );
				}
			}
		}
	}

	/**
	 * This method breaks the cache. The secret can be set in the config.
	 *
	 * @param array  $ids The entry ids
	 * @param string $secret
	 * @return void
	 */
	public function break_cache_hook( $ids, $secret )
	{
		//debug mode
		if ( $this->EE->input->get_post( 'break_test', true ) === 'y' )
		{
			echo 'working';
			exit();
		}

		if ( ! isset( $secret ) ) //the secret was not passed in, check the GET and POST data
		{
			//grab the channel_id from the get/post data
			$secret = $this->EE->input->get_post( 'secret', true );

			if ( $secret === false ) //still no secret, no reason to stick around
			{
				return;
			}
		}

		$real_secret = $this->EE->config->item( 'ce_cache_secret' );
		if ( ! $real_secret )
		{
			$real_secret = '';
		}

		$real_secret = substr( md5( $real_secret ), 0, 10 );

		//check the passed in secret against the real secret
		if ( $secret != $real_secret )
		{
			return;
		}

		if ( ! isset( $ids ) )
		{
			$ids = $this->EE->input->get_post( 'ids', true );

			if ( $ids === false ) //still no secret, no reason to stick around
			{
				return;
			}

			$ids = explode( '|', $ids );
		}

		//make sure all ids are numeric
		foreach ( $ids as $index => $id )
		{
			if ( ! is_numeric( $id ) || $id < 1 )
			{
				unset( $ids[$index] );
			}
		}

		//make sure we still have some ids
		if ( ! count( $ids ) ) //no ids
		{
			return;
		}

		//let's not worry about timing out
		set_time_limit( 0 );

		//load the class if needed
		if ( ! class_exists( 'Ce_cache_factory' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_factory.php';
		}

		//get the driver classes
		$classes = Ce_cache_factory::factory( Ce_cache_factory::$valid_drivers );

		//get the site name
		$site = Ce_cache_utils::get_site_label();

		//the prefix for this site
		$prefix = 'ce_cache/' . $site . '/';

		//get all of the data for each entry
		$results = $this->EE->db->query( "SELECT ct.entry_id, ct.channel_id, ct.title, ct.url_title, ct.entry_date, ct.edit_date, c.channel_title, c.channel_name, cm.username as author_username, ct.author_id
		FROM exp_channel_titles ct
		LEFT JOIN exp_channels c
		ON ct.channel_id = c.channel_id
		LEFT JOIN exp_members cm
		ON ct.author_id = cm.member_id
		WHERE ct.entry_id IN ( '" . implode( "', '", $ids ) . "' )
		ORDER BY ct.channel_id ASC
		" );

		unset( $ids );

		if ( $results->num_rows() > 0 ) //we found the data
		{
			$entries = $results->result_array();
			$results->free_result(); //free up memory
		}
		else //no results were found
		{
			return;
		}

		//get saved settings if they exist
		$results = $this->EE->db->query( "SELECT * FROM exp_ce_cache_breaking ORDER BY channel_id ASC" );

		if ( $results->num_rows() > 0 ) //we found the channel
		{
			$settings = $results->result_array();
			$results->free_result(); //free up memory
		}
		else //no settings were found
		{
			return;
		}

		//tags to remove
		$tags = array(); //key => tag

		//items to delete without refreshing
		$deletes = array(); //key => item_id

		//items to refresh
		$refreshers = array(); //item_id => refresh_time

		//loop through all the setting rows
		foreach ( $settings as $setting )
		{
			$channel_id = $setting['channel_id'];

			//determine the entries that are applicable to these settings
			if ( $channel_id == 0 )
			{
				$channel_entries = $entries;
			}
			else
			{
				$channel_entries = array();

				foreach ( $entries as $entry )
				{
					if ( $channel_id == 0 || $channel_id == $entry['channel_id'] )
					{
						$channel_entries[] = $entry;
					}
				}
			}

			$refresh = ( $setting['refresh'] == 'y' );
			$refresh_time = $setting['refresh_time'];

			//get the items by tag
			$temps = explode( '|', $setting['tags'] );
			//parse the tags for each entry to turn any variables into actual tags
			$temps = $this->parse_setting_variables( $temps, $channel_entries );

			if ( count( $temps ) > 0 ) //we have one or more tags
			{
				//add these tags to the overall tags array
				$tags = array_merge( $tags, $temps );

				//escape all of the tags, as we will be using them in a query
				foreach ( $temps as $index => $temp )
				{
					$temps[ $index ] = $this->EE->db->escape_str( $temp );
				}

				$tagged_items = $this->EE->db->query( "
				SELECT item_id
				FROM exp_ce_cache_tagged_items
				WHERE SUBSTRING( item_id, 1, " . strlen( $prefix )  . " ) = '" . $this->EE->db->escape_str( $prefix ) . "'
				AND tag IN ( '" . implode( "', '", $temps ) . "' )
				ORDER BY item_id ASC" );

				if ( $tagged_items->num_rows() > 0 )
				{
					$hits = $tagged_items->result_array();

					foreach ( $hits as $hit )
					{
						if ( $refresh )
						{
							$refreshers[ $hit['item_id'] ] = $refresh_time;
						}
						else
						{
							$deletes[] = $hit['item_id'];
						}
					}
					unset( $hits );
				}
				$tagged_items->free_result();
			}

			$items = explode( '|', $setting['items'] );

			//parse the item paths for each entry to turn any variables into actual item paths
			$items = $this->parse_setting_variables( $items, $channel_entries );

			//get all items
			foreach ( $items as $item )
			{
				$sub = substr( $item, 0, 5 );

				//an item
				if ( ! $refresh ) //delete right away
				{
					$deletes[] = $prefix . $item;
				}
				else //delete and refresh
				{
					$refreshers[ $prefix . $item ] = $refresh_time;
				}

				if ( $sub  == 'local' || $sub  == 'stati' ) //a local or static item or path
				{
					$is_static = ( $sub == 'stati' );

					//a path, so get any child items
					if ( substr( $item, -1, 1 ) == '/' ) //a path, not an item
					{
						foreach ( $classes as $class )
						{
							$driver = $class->name();

							if ( ( $is_static && $driver != 'static') //static, but not static driver
								|| ( ! $is_static && $driver == 'static' ) ) //local, but static driver
							{
								continue;
							}

							//attempt to get all items
							$hits = $class->get_all( $prefix . $item );

							if ( $hits !== false && is_array( $hits ) ) //we have the items
							{
								//loop through and add the items to the all_items array
								foreach ( $hits as $hit )
								{
									if ( ! $refresh ) //delete right away
									{
										$deletes[] = $prefix . $item . ( ( $driver == 'db' || $driver == 'apc' ) ? $hit['id'] : $hit );
									}
									else //delete and refresh
									{
										$refreshers[ $prefix . $item . ( ( $driver == 'db' || $driver == 'apc' ) ? $hit['id'] : $hit ) ] = $refresh_time;
									}
								}
							}
							unset( $hits );
						}
					}
				}
				else if ($sub == 'globa') //a global item
				{
					if (substr($item, -1, 1) == '/') //a path, not an item
					{
						foreach ($classes as $class)
						{
							$driver = $class->name();

							//attempt to get all items
							$hits = $class->get_all($prefix . $item);

							if ($hits !== false && is_array($hits)) //we have the items
							{
								//loop through and add the items to the all_items array
								foreach ($hits as $hit)
								{
									$deletes[] = $prefix . $item . (($driver == 'db' || $driver == 'apc') ? $hit['id'] : $hit);
								}
							}
							unset($hits);
						}
					}
					else //an item
					{
						//global items have no way of being refreshed, so just delete them
						$deletes[] = $prefix . $item;
					}
				}
			}
		}

		//now let's clear the tags
		if ( count( $tags ) > 0 )
		{
			$this->EE->db->where_in( 'tag', $tags );
			$this->EE->db->delete( 'ce_cache_tagged_items' );
		}
		//unset the tags array as it is no longer needed
		unset( $tags );

		//now that we have all of our items to delete, let's delete them
		foreach( $classes as $class ) //loop through the driver classes
		{
			//loop through the delete items and delete them
			foreach ( $deletes as $item )
			{
				$class->delete( $item );
			}
		}

		//merge the delete array with the items from the refreshers array
		$deletes = array_merge( $deletes, array_keys( $refreshers ) );

		//now let's clear any applicable tagged items
		if ( count( $deletes ) > 0 )
		{
			$this->EE->db->where_in( 'item_id', $deletes );
			$this->EE->db->delete( 'ce_cache_tagged_items' );
		}

		//unset the deletes array as it is no longer needed
		unset( $deletes );

		//create the URL
		$url = $this->EE->config->slash_item('site_url');

		//see if this install has the ability to recreate the cache
		$can_recreate = ( function_exists( 'curl_init' ) || function_exists( 'fsockopen' ) );

		if ( $can_recreate ) //delete and refresh the items
		{
			$url_prefix = $this->determine_setting('url_prefix', '');

			if ( ! empty( $prefix ) )
			{
				$url = rtrim($url, '/');
				$url = rtrim($url, $url_prefix);
			}

			//make sure that allow_url_fopen is set to true if permissible
			@ini_set('allow_url_fopen', true);
			//some servers will not accept the asynchronous requests if there is no user_agent
			@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

			$urls = array();

			foreach ( $refreshers as $item => $time )
			{
				//loop through the driver classes and delete this item
				foreach( $classes as $class )
				{
					$class->delete( $item );
				}

				//trim the prefix and 'local/' or 'static/' from the beginning of the path
				$path = (strpos($item, $prefix . 'local/') === 0 ) ? substr( $item, strlen( $prefix ) + 6 ) : substr( $item, strlen( $prefix ) + 7 );

				//find the last '/'
				$last_slash = strrpos( $path, '/' );

				//if a last '/' was found, get the path up to that point
				$path = ( $last_slash === false ) ? '' : substr( $path, 0, $last_slash );

				$urls[ Ce_cache_utils::remove_duplicate_slashes( $url . '/' . $path ) ] = $time;
			}

			//since we are deleting entries, we're going to make this happen as close to the end of execution as possible,
			//since we want to be sure that the entries are removed from the database before refreshing any URLs
			register_shutdown_function( array( $this, 'refresh_urls' ), $urls );
		}
		else //just delete the items, as there is no way to recreate them
		{
			//loop through the driver classes
			foreach( $classes as $class )
			{
				//loop through the delete items and delete them
				foreach ( $refreshers as $item => $time )
				{
					$class->delete( $item );
				}
			}
		}
	}

	/**
	 * Refresh the caches for the specified URLs.
	 *
	 * @param $urls Array in the format URL => time
	 */
	public function refresh_urls( $urls )
	{
		if ( ! is_array( $urls ) )
		{
			return;
		}

		//now let's loop through our refresh items and refresh them
		foreach ( $urls as $url => $time )
		{
			@sleep( $time );

			//try to request the page, first using cURL, then falling back to fsocketopen
			if ( ! $this->curl_it( $url ) ) //send a cURL request
			{
				//attempt a fsocketopen request
				$this->fsockopen_it( $url );
			}
		}
	}

	/**
	 * Parses each tag/path variable with the entry data
	 *
	 * @param $tags_or_paths
	 * @param $entries
	 * @return array
	 */
	private function parse_setting_variables( $tags_or_paths, $entries )
	{
		$finals = array();

		foreach ( $tags_or_paths as $source )
		{
			foreach( $entries as $entry )
			{
				$temp = $source;

				//entry_date format
				if ( preg_match_all( '#\{entry_date\s+format=([\"\'])([^\\1]*?)\\1\}#', $temp, $matches, PREG_SET_ORDER )  )
				{
					foreach ( $matches as $match )
					{
						if ( isset( $match[2] ) )
						{
							if ( version_compare( APP_VER, '2.6.0', '<' ) )
							{
								$temp = str_replace( $match[0], $this->EE->localize->decode_date( $match[2], $entry['entry_date'] ) , $temp );
							}
							else
							{
								$temp = str_replace( $match[0], $this->EE->localize->format_date( $match[2], $entry['entry_date'] ) , $temp );
							}

						}
					}
				}
				//edit_date format
				if ( preg_match_all( '#\{edit_date\s+format=([\"\'])([^\\1]*?)\\1\}#', $temp, $matches, PREG_SET_ORDER )  )
				{
					if ( version_compare( APP_VER, '2.6.0', '<' ) )
					{
						foreach ( $matches as $match )
						{
							if ( isset( $match[2] ) )
							{
								$temp = str_replace( $match[0], $this->EE->localize->decode_date( $match[2], $this->EE->localize->timestamp_to_gmt( $entry['edit_date'] ) ) , $temp );
							}
						}
					}
					else
					{
						$this->EE->load->helper('date');
						foreach ( $matches as $match )
						{
							if ( isset( $match[2] ) )
							{
								$temp = str_replace( $match[0], $this->EE->localize->format_date( $match[2], mysql_to_unix( $entry['edit_date'] ) ) , $temp );
							}
						}
					}
				}

				//setup find and replace arrays
				$find = array();
				$replace = array_values( $entry );
				foreach ( $entry as $key => $value )
				{
					$find[] = '{' . $key . '}';
				}

				//replace the values
				$finals[] = str_replace( $find, $replace, $temp );
			}
		}

		return array_unique( $finals );
	}

	/**
	 * A simple method that leverages cURL to make a quick GET request. If the request is asynchronous, it has a quick timeout (500ms or 1 second, depending on the PHP version) that essentially calls the URL without waiting around for a response.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function curl_it( $url )
	{
		if ( ! $this->curl_enabled )
		{
			return false;
		}

		if ( function_exists( 'curl_init' ) ) //cURL should work
		{
			$curl = curl_init( $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); //no output
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, false ) ; //no timeout
			curl_setopt( $curl, CURLOPT_NOSIGNAL, true );
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //no ssl verification
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));

			//follow location only works if there are no open_basedir or safe_mode restrictions
			if ( ! ini_get('open_basedir') && ! ini_get('safe_mode') )
			{
				curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
			}

			if ( $this->async ) //fire off the request and timeout as soon as possible
			{
				if ( defined( 'CURLOPT_TIMEOUT_MS' ) )
				{
					curl_setopt( $curl, CURLOPT_TIMEOUT_MS, 500 );
				}
				else
				{
					curl_setopt( $curl, CURLOPT_TIMEOUT, 1 );
				}

				curl_exec( $curl );
			}
			else if (curl_exec($curl) === false) //the synchronous request failed
			{
				curl_close( $curl );
				return false;
			}

			curl_close( $curl );

			return true;
		}

		return false;
	}

	/**
	 * A simple method that leverages native PHP sockets to make a GET request. If the request is asynchronous, it has a quick timeout (1s) that essentially calls the URL without waiting around for a response.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function fsockopen_it( $url )
	{
		if ( function_exists( 'fsockopen' ) ) //no cURL, try fsocketopen
		{
			//parse the URL
			$parts = @parse_url( $url );

			//check to make sure there wasn't an error parsing the URL
			if ( $parts === false ) //there was a problem, so we'll stop here
			{
				return false;
			}

			//open the socket
			$fp = @fsockopen( $parts['host'], isset( $parts['port'] ) ? $parts['port'] : 80, $errno, $errstr, ( $this->async ) ? 1 : null );

			//check to make sure there wasn't an error opening the socket
			if ( $fp === false )
			{
				return false;
			}

			//determine if there is a query string
			$query = ( isset( $parts['query'] ) && ! empty( $parts['query'] ) ) ?  $parts['query'] : '';

			$path = isset( $parts['path'] ) ? $parts['path'] : '';

			$out = 'GET ' . $path . ( ! empty( $parts['query'] ) ? '?' . $query : '' ) . ' HTTP/1.1' . "\r\n";
			$out .= 'Host: ' . $parts['host'] . "\r\n";
			$out .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
			$out .= 'Content-Length: ' . strlen( $query ) . "\r\n";
			$out .= 'Connection: Close' . "\r\n\r\n";
			@fwrite( $fp, $out );
			@fclose( $fp );
			return true;
		}

		return false;
	}

	//private function
	private function determine_setting( $name, $default = '' )
	{
		$name = 'ce_cache_' . $name;

		//override this setting if set in the config or global vars array
		if ( isset( $this->EE->config->_global_vars[ $name ] ) && $this->EE->config->_global_vars[ $name ] !== false ) //first check global array
		{
			$default = $this->EE->config->_global_vars[ $name ];
		}
		else if ( $this->EE->config->item( $name ) !== false ) //then check config
		{
			$default = $this->EE->config->item( $name );
		}

		return $default;
	}
}