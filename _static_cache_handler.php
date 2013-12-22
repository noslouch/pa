<?php
define('CE_STATIC_START', microtime(true));

/**
 * CE Cache - Static handler.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_static_handler
{
	//---------------- the following three variables can be changed ----------------
	//the cache folder, relative to this file
	private $cache_folder = 'static/ce_cache/29174f';

	//temporarily disable static caching?
	private $disabled = false;

	//debug mode?
	private $debug = false;

	//show execution time?
	private $show_time = false;


	//---------------- no need to change anything below ----------------

	/**
	 * Handles retrieving the cache file.
	 */
	public function __construct()
	{
		//the path to this file
		$here = str_replace(pathinfo(__FILE__, PATHINFO_BASENAME), '', __FILE__);
		$here = str_replace( '\\', '/', $here );

		if ( ( isset( $_GET['bwf_dp'] ) && $_GET['bwf_dp'] == 't' ) || ( isset($_GET['publisher_status']) && $_GET['publisher_status'] == 'draft' ) ) //better workflow preview or publisher draft
		{
			$this->disabled = true;
		}

		//the cache item
		if ( isset( $_SERVER['REQUEST_URI'] ) && isset( $_SERVER['SCRIPT_NAME'] ) ) //try the request URI
		{
			$item = $_SERVER['REQUEST_URI'];

			if ( strpos( $item, $_SERVER['SCRIPT_NAME'] ) === 0 )
			{
				$item = str_replace( $_SERVER['SCRIPT_NAME'], '', $item );
			}

			//if the item starts with '?/', remove those characters
			if ( strpos( $item, '?/' ) === 0 )
			{
				$item = ltrim( $item, '?/' );
			}

			//check if there is a query string present
			$position = strpos($item, '?');
			if ( $position !== false ) //there is a query string
			{
				//set this as the real query string, in case there is a redirect
				$_SERVER['QUERY_STRING'] = ltrim( substr( $item, $position ), '?' );

				//get the characters up to the query string
				$item = substr( $item, 0, $position );
			}

			//add the index.html, if it's not there already
			if ( strpos(strrev($item), strrev('/index.html')) !== 0 )
			{
				$item = rtrim( $item, '/') . '/index.html';
			}
		}
		else if ( isset( $_SERVER['PATH_INFO'] ) ) //try path info
		{
			$item = $_SERVER['PATH_INFO'];
		}
		else //redirect to the home page
		{
			$this->debug( 'path info not found' );
			exit();
		}

		//make sure the url ends in 'index.html'
		if ( strpos(strrev($item), strrev('/index.html')) !== 0 )
		{
			$this->debug( 'the item "' . $item . '" is invalid' );
			exit();
		}

		//make sure the item starts with a slash
		$item = '/' . ltrim( $item, '/' );

		//create the absolute file path
		$cache_folder = rtrim( $here, '/' ) . '/' . trim( $this->cache_folder, '/' );
		$cache_folder = rtrim( str_replace( '\\', '/', realpath( $cache_folder ) ), '/');
		$file = $cache_folder . '/static'. $item;

		//decode special characters to find the actual file
		$file = utf8_decode( urldecode( $file ) );

		//get the real file path
		$result = realpath( $file );
		if ( ! $result ) //the real path was not found
		{
			$this->debug( 'the realpath "' . $file . '" was not found' );
			exit();
		}
		$file = $result;

		//make sure the real file path is inside the cache directory
		$file = str_replace( '\\', '/', $file );

		if ( strpos( $file, $cache_folder ) !== 0 ) //the file does not start with the cache directory
		{
			$this->debug( 'incorrect directory' );
			exit();
		}

		if ( ! is_file( $file ) ) //the cache file was not found
		{
			$this->debug( 'cache item not found' );
			exit();
		}
		else //the file exists
		{
			//get the file contents
			$data = @file_get_contents( $file );

			if ( $data === false )
			{
				$this->debug( 'problem retrieving cache contents' );
				exit();
			}

			//try to unserialize the data
			$data = @unserialize( $data );

			//make sure the data is unserialized and in the expected format
			if ( $data === false || ! is_array( $data ) || count( $data ) != 4 )
			{
				$this->debug( 'problem unserializing cache contents' );
				exit();
			}

			//if seconds is set to 0 then the cache is never deleted, unless done so manually
			if ( $this->disabled || ( $data['ttl'] != 0 && time() > $data['made'] + $data['ttl'] ) )
			{
				//the file has expired, get rid of it
				@unlink( $file );

				if ( ! is_file( $file ) ) //the cache file was not found
				{
					//request the page again, so it can be cached this time
					$item = preg_replace( '@/index.html$@', '', $item, 1 );

					$this->redirect_to( $item );
					exit();
				}
				else //there was a problem deleting the cache
				{
					$this->debug( 'problem deleting expired cache' );
					exit();
				}
			}

			//set the headers
			if ( is_array( $data['headers'] ) )
			{
				foreach ( $data['headers'] as $header )
				{
					header($header);
				}
			}

			//check for a redirect
			if ( strpos( $data['content'], '{redirect=' ) !== false )
			{
				preg_match( "/\{redirect\s*=\s*(\042|\047)([^\\1]*?)\\1\}/si", $data['content'], $match );

				if ( isset( $match[2] ) )
				{
					$this->redirect_to( $match[2] );
					exit();
				}
			}

			//return the data
			echo $data['content'];

			if ( $this->show_time )
			{
				echo '<!-- Debug: total time - ' . ( microtime(true) - CE_STATIC_START ) . ' -->';
			}
		}
	}

	/**
	 * Redirect to the given URL.
	 *
	 * @param string $path
	 */
	private function redirect_to( $path = '' )
	{
		//add protocol
		$url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';

		//add host
		$url .= $_SERVER['HTTP_HOST'];

		//trim trailing slash
		$url = rtrim( $url, '/' );

		//add path
		if ( $path )
		{
			$url .= ( $path ) ? '/' . trim( $path, '/' ) : '';
		}

		//add query string
		/*
		if ( ! empty( $_SERVER['QUERY_STRING'] ) )
		{
			$url .= '?' . $_SERVER['QUERY_STRING'];
		}
		*/

		//Set no caching
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		//redirect
		header('Location: ' . $url );
		exit();
	}

	/**
	 * If debug mode is enabled, output the debug message. Otherwise, redirect to home.
	 *
	 * @param string $message The debug message to output before exiting, if debug mode.
	 */
	private function debug( $message = '' )
	{
		if ( $this->debug ) //debug mode
		{
			echo '<!-- Debug: ' . $message . ' -->';
			exit();
		}
		$this->redirect_to();
	}
}

//instantiate the class
new Ce_cache_static_handler();
