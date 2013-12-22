<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Module file.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */

class Ce_cache
{
	//a reference to the instantiated class factory
	private $drivers;

	//debug mode flag
	private $debug = false;

	//the relative directory path to be appended to the cache path
	private $id_prefix = '';

	//a flag to indicate whether or not the cache is setup
	public $is_cache_setup = false;

	//this will hold the actual URL, or the URL specified by the user
	private $cache_url = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE = get_instance();

		//if the template debugger is enabled, and a super admin user is logged in, enable debug mode
		$this->debug = false;
		if ( $this->EE->session->userdata['group_id'] == 1 && $this->EE->config->item('template_debugging') == 'y' )
		{
			$this->debug = true;
		}

		//include CE Cache Utilities
		if ( ! class_exists( 'Ce_cache_utils' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_utils.php';
		}
	}

	/**
	 * This method will check if the cache id exists, and return it if it does. If the cache id does not exists, it will cache the data and return it.
	 * @return string
	 */
	public function it()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the tagdata
		$tagdata = $this->no_results_tagdata();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $tagdata;
		}

		//check the cache for the id
		$item = $this->get( true );

		if ( $item === false ) //the item could not be found for any of the drivers
		{
			//specify that we want the save method to return the content
			$this->EE->TMPL->tagparams['show'] = 'yes';

			//attempt to save the content
			return $this->save();
		}

		//the item was found, parse the item and return it
		return $this->process_return_data( $item );
	}

	/**
	 * Save an item to the cache.
	 * @return string
	 */
	public function save()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//did the user elect to ignore this tag?
		if ( strtolower( $this->EE->TMPL->fetch_param( 'ignore_if_dummy' ) ) == 'yes' && $this->drivers[0]->name() == 'dummy' ) //ignore this entire tag if the dummy driver is being used
		{
			return $this->EE->TMPL->no_results();
		}

		//don't process googlebot save requests, as it can caused problems by hitting an insane number of non-existant URLs
		//note: we do this here, because we still want to return pages quickly to google bot if they are already cached, we just don't want to save pages it requests
		if ( $this->EE->config->item( 'ce_cache_block_bots' ) != 'no' && $this->is_bot() )
		{
			$this->drivers = Ce_cache_factory::factory( array( 'dummy' ) );
			$this->is_cache_setup = false;
		}

		//get the tagdata
		$tagdata = $this->no_results_tagdata();

		//trim the tagdata?
		$should_trim = strtolower( $this->determine_setting( 'trim', 'no' ) );
		$should_trim = ( $should_trim == 'yes' || $should_trim == 'y' || $should_trim == 'on' );

		//trim here in case the data needs to be returned early
		if ( $should_trim )
		{
			$tagdata = trim( $tagdata );
		}

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $tagdata;
		}

		//get the time to live (defaults to 60 minutes)
		$ttl = (int) $this->determine_setting( 'seconds', '3600' );

		//save the previous tags
		$previous_tags = isset( $this->EE->session->cache[ 'Ce_cache' ]['tags'] ) ? $this->EE->session->cache[ 'Ce_cache' ]['tags'] : '';

		//clear the current tags
		$this->EE->session->cache[ 'Ce_cache' ]['tags'] = '';

		//flag that caching is happening--important for escaped content
		$this->EE->session->cache[ 'Ce_cache' ]['is_caching'] = true;

		//do we need to process the data?
		if ( $this->EE->TMPL->fetch_param( 'process' ) != 'no' ) //we need to process the data
		{
			//we're going to escape the logged_in and logged_out conditionals, since the Channel Entries loop adds them as variables.
			$tagdata = str_replace( array( 'logged_in', 'logged_out' ), array( 'ce_cache-in_logged', 'ce_cache-out_logged' ), $tagdata );

			//pre parse hook
			if ($this->EE->extensions->active_hook('ce_cache_pre_parse'))
			{
				$tagdata = $this->EE->extensions->call('ce_cache_pre_parse', $tagdata);
			}

			//parse the data
			$tagdata = $this->parse_as_template( $tagdata );

			//post parse hook
			if ($this->EE->extensions->active_hook('ce_cache_post_parse'))
			{
				$tagdata = $this->EE->extensions->call('ce_cache_post_parse', $tagdata);
			}
		}

		$tagdata = $this->unescape_tagdata( $tagdata );

		//make sure the template debugger is not getting cached, as that is bad
		$debugger_pos = strpos( $tagdata, '<div style="color: #333; background-color: #ededed; margin:10px; padding-bottom:10px;"><div style="text-align: left; font-family: Sans-serif; font-size: 11px; margin: 12px; padding: 6px"><hr size=\'1\'><b>TEMPLATE DEBUGGING</b><hr' );
		if ( $debugger_pos !== false )
		{
			$tagdata = substr_replace( $tagdata, '', $debugger_pos, -1 );
		}

		//pre save hook
		if ($this->EE->extensions->active_hook('ce_cache_pre_save'))
		{
			$tagdata = $this->EE->extensions->call('ce_cache_pre_save', $tagdata, 'fragment');
		}

		//trim again since the data may be much different now
		if ( $should_trim )
		{
			$tagdata = trim( $tagdata );
		}

		//loop through the drivers and try to save the data
		foreach ( $this->drivers as $driver )
		{
			if ( $driver->set( $id, $tagdata, $ttl ) === false ) //save unsuccessful
			{
				$this->log_debug_message( __METHOD__, "Something went wrong and the data for '{$id}' was not cached using the " . $driver->name() . " driver." );
			}
			else //save successful
			{
				$this->log_debug_message( __METHOD__, "The data for '{$id}' was successfully cached using the " . $driver->name() . " driver." );

				//if we are saving the item for the first time, we are going to keep track of the drivers and ids, so we can clear the cached items later if this ends up being a 404 page
				if ( $driver->name() != 'dummy' )
				{
					$this->EE->session->cache[ 'Ce_cache' ]['cached_items'][] = array( 'driver' => $driver->name(), 'id' => $id );

					$this->register_the_shutdown();
				}

				break;
			}
		}

		//flag that caching is finished--important for escaped content
		$this->EE->session->cache[ 'Ce_cache' ]['is_caching'] = false;

		//save the tags, if applicable
		if ( $this->drivers[0]->name() != 'dummy' )
		{
			$this->save_tags( $id, $this->EE->TMPL->fetch_param( 'tags' ) . $this->EE->session->cache[ 'Ce_cache' ]['tags'] );
		}

		//add the new tags to the previous tags, so they can be used for the static driver
		$this->EE->session->cache[ 'Ce_cache' ]['tags'] = $previous_tags . $this->EE->session->cache[ 'Ce_cache' ]['tags'];

		if ( $this->EE->TMPL->fetch_param( 'show' ) == 'yes' )
		{
			//parse any segment variables
			return $this->parse_vars( $tagdata );
		}

		unset( $tagdata );

		return '';
	}

	/**
	 * Save the static page.
	 */
	public function stat()
	{
		//is the user logged in?
		$logged_in = ($this->EE->session->userdata['member_id'] != 0);

		//see if there is a reason to prevent caching the page
		if (
			( isset( $this->EE->session->cache['ep_better_workflow']['is_preview'] ) && $this->EE->session->cache['ep_better_workflow']['is_preview'] === true ) //better workflow draft
			|| ( isset( $_GET['bwf_dp'] ) && $_GET['bwf_dp'] == 't' ) //another bwf check (from Matt Green)
			|| ( isset( $_GET['publisher_status'] ) && $_GET['publisher_status'] == 'draft' ) // publisher check (from Fusionary)
			|| $this->ee_string_to_bool( $this->determine_setting( 'off', 'no' ) ) //ce cache is off
			|| ( $this->EE->config->item( 'ce_cache_block_bots' ) != 'no' && $this->is_bot() ) //bot page
			|| ($this->ee_string_to_bool( $this->determine_setting( 'logged_in_only', 'no', 'static' ) ) && ! $logged_in ) //logged in only, but not logged in
			|| ($this->ee_string_to_bool( $this->determine_setting( 'logged_out_only', 'no', 'static' ) ) && $logged_in ) //logged out only, but is logged in
			|| ( ! empty( $_POST ) && $this->ee_string_to_bool( $this->determine_setting( 'ignore_post_requests', 'yes' ) ) && $_POST != array( 'entry_id' => '' ) ) //a POST page and ignore_post_requests is set to "yes"
		) //no caching
		{
			return;
		}

		if ( ! isset( $this->EE->session->cache[ 'Ce_cache' ][ 'static' ] ) )
		{
			//make sure we set the cache_url for the path
			$this->determine_cache_url();

			//get the time to live (defaults to 60 minutes)
			$this->EE->session->cache[ 'Ce_cache' ][ 'static' ] = array(
				'seconds' => (int) $this->determine_setting( 'seconds', '3600' ),
				'tags' => $this->EE->TMPL->fetch_param( 'tags' )
			);
		}

		$this->register_the_shutdown();
	}

	private function register_the_shutdown()
	{
		//register the shutdown function if needed
		if ( empty( $this->EE->session->cache[ 'Ce_cache' ][ 'shutdown_is_registered' ] ) )
		{
			$this->EE->session->cache[ 'Ce_cache' ][ 'shutdown_is_registered' ] = true;

			//register the shutdown function
			register_shutdown_function( array( $this, 'shut_it_down' ) );
		}
	}

	/**
	 * Escapes the passed-in content so that it will not be parsed before being cached.
	 * @return string
	 */
	public function escape()
	{
		$tagdata = false;

		//if there is pre_escaped tagdata, use it
		$tag_parts = $this->EE->TMPL->tagparts;
		if ( is_array( $tag_parts ) && isset( $tag_parts[2] ) )
		{
			if ( isset( $this->EE->session->cache[ 'Ce_cache' ]['pre_escape'][ 'id_' . $tag_parts[2] ] ) )
			{
				$tagdata = $this->EE->session->cache[ 'Ce_cache' ]['pre_escape'][ 'id_' . $tag_parts[2] ];
			}
		}

		if ( $tagdata === false ) //there was no pre-escaped tagdata, get the no_results tagdata
		{
			$tagdata = $this->no_results_tagdata();
		}

		if ( trim( $tagdata ) == '' ) //there is no tagdata
		{
			return $tagdata;
		}
		else if ( empty( $this->EE->session->cache[ 'Ce_cache' ]['is_caching'] ) ) //we're not inside of a tagdata loop
		{
			return $this->parse_vars( $tagdata );
		}

		//create a 16 character placeholder
		$placeholder = '-ce_cache_placeholder:' . hash( 'md5', $tagdata );// . '_' . mt_rand( 0, 1000000 );

		//add to the cache
		$this->EE->session->cache[ 'Ce_cache' ]['placeholder-keys'][] = $placeholder;
		$this->EE->session->cache[ 'Ce_cache' ]['placeholder-values'][] = $tagdata;

		//return the placeholder
		return $placeholder;
	}

	/**
	 * Add one or more tags.
	 *
	 * @return string
	 */
	public function add_tags()
	{
		//get the tagdata
		$tagdata = trim( $this->EE->TMPL->tagdata );
		if ( empty( $tagdata ) )
		{
			return $this->EE->TMPL->no_results();
		}

		//make sure the tags session cache exists
		if ( empty( $this->EE->session->cache[ 'Ce_cache' ]['tags'] ) )
		{
			$this->EE->session->cache[ 'Ce_cache' ]['tags'] = '';
		}

		//turn the tagdata into cleaned tags
		$tagdata = strtolower( trim( $tagdata ) );
		$tags = explode( '|', $tagdata );
		foreach ( $tags as $index => $tag )
		{
			$tag = trim( $tag );

			if ( empty( $tag ) ) //remove empty tag
			{
				unset( $tags[ $index ] );
			}
			else //add the cleaned up tag
			{
				$tags[ $index ] = $tag;
			}
		}
		$tags = implode( '|', $tags );

		//add the tags
		$this->log_debug_message( __METHOD__, 'The following tags were added: ' . $tags );
		$this->EE->session->cache[ 'Ce_cache' ]['tags'] .= '|' . $tags;

		//return an empty string
		return '';
	}

	/**
	 * Alias for the add_tags method.
	 *
	 * @return string
	 */
	public function add_tag()
	{
		return $this->add_tags();
	}

	/**
	 * Returns whether or not a driver is supported.
	 * @return int
	 */
	public function is_supported()
	{
		//get the driver
		$driver = $this->EE->TMPL->fetch_param( 'driver' );

		//load the class if needed
		$this->include_factory();

		//see if the driver is supported
		return ( Ce_cache_factory::is_supported( $driver ) ) ? 1 : 0;
	}

	/**
	 * Get an item from the cache.
	 * @param bool $internal_request Was this method requested from this class (true) or from the template (false).
	 * @return bool|int
	 */
	public function get( $internal_request = false )
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $this->EE->TMPL->no_results();
		}

		//loop through the drivers and attempt to find the cache item
		foreach ( $this->drivers as $driver )
		{
			$item = $driver->get( $id );

			if ( $item !== false ) //we found the item
			{
				$this->log_debug_message( __METHOD__, "The '{$id}' item was found for the " . $driver->name() . " driver." );

				//process the data and return it
				return $this->process_return_data( $item );
			}
		}

		//the item was not found in the cache of any of the drivers
		return ( $internal_request ) ? false : $this->EE->TMPL->no_results();
	}

	/**
	 * Delete something from the cache.
	 * @return string|void
	 */
	public function delete()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $this->EE->TMPL->no_results();
		}

		//loop through the drivers and attempt to delete the cache item for each one
		foreach ( $this->drivers as $driver )
		{
			if ( $driver->delete( $id ) !== false )
			{
				$this->log_debug_message( __METHOD__, "The '{$id}' item was deleted for the " . $driver->name() . " driver." );
			}
		}

		//delete all of the current tags for this item
		$this->EE->db->query( 'DELETE FROM exp_ce_cache_tagged_items WHERE item_id = ?', array( $id ) );
	}

	/**
	 * Manually clears items and/or tags, and optionally refreshes the cleared items.
	 * @return void
	 */
	public function clear()
	{
		//get the items
		$items = $this->EE->TMPL->fetch_param( 'items' );
		$items = empty( $items ) ? array() : explode( '|', $this->reduce_pipes( $items, false ) );

		//get the tags
		$tags = $this->EE->TMPL->fetch_param( 'tags' );
		$tags = empty( $tags ) ? array() : explode( '|', $this->reduce_pipes( $tags ) );

		//do we need to continue?
		if ( empty( $items ) && empty( $tags ) ) //we don't have any items or tags
		{
			return;
		}

		//refresh?
		$refresh = $this->EE->TMPL->fetch_param( 'refresh' );
		$refresh_time = 1;
		if ( is_numeric( $refresh ) )
		{
			$refresh_time = round( $refresh );
			$refresh = true;
		}
		else
		{
			$refresh = false;
		}

		//load the cache break class, if needed
		if ( ! class_exists( 'Ce_cache_break' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_break.php';
		}

		//instantiate the class break and call the break cache method
		$cache_break = new Ce_cache_break();
		$cache_break->break_cache( $items, $tags, $refresh, $refresh_time );
	}

	/**
	 * Get information about a cached item.
	 *
	 * @return string
	 */
	public function get_metadata()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $this->EE->TMPL->no_results();
		}

		//the array of meta data items
		$item = array();

		//loop through the drivers and attempt to find the cache item
		foreach ( $this->drivers as $driver )
		{
			//get the info
			if( !! $info = $driver->meta( $id, false  ) )
			{
				//info contains the keys: 'expiry', 'made', 'ttl', 'ttl_remaining', and 'content'
				//add in legacy keys
				$info['expire'] = $info['expiry'];
				$info['mtime'] = $info['made'];
				//add in driver key
				$info['driver'] = $driver->name();
				$item = $info;
				break;
			}
		}

		//make sure we have at least one result
		if ( count( $item ) == 0 )
		{
			return $this->EE->TMPL->no_results();
		}

		//get the tagdata
		$tagdata = $this->no_results_tagdata();

		//parse the conditionals
		$tagdata = $this->EE->functions->prep_conditionals( $tagdata, $item );

		//return the parsed tagdata
		return $this->EE->TMPL->parse_variables_row( $tagdata, $item );
	}

	/**
	 * Purges the cache.
	 * @return void
	 */
	public function clean()
	{
		$site_only = trim( $this->EE->TMPL->fetch_param( 'site_only', 'yes' ) );
		$force = $this->ee_string_to_bool( trim( $this->EE->TMPL->fetch_param( 'force', 'yes' ) ) );

		//get the driver classes
		$drivers = $this->get_drivers_array( true, $force );
		$this->include_factory();
		$this->drivers = Ce_cache_factory::factory( $drivers, true );

		//loop through the drivers and purge their respective caches
		foreach ( $this->drivers as $driver )
		{
			if ( $this->ee_string_to_bool( $site_only ) )
			{
				//get the site name
				$site = Ce_cache_utils::get_site_label();
				$site = 'ce_cache/' . $site;
				$site = rtrim( $site ) . '/'; //make sure there is a trailing slash for this to work

				//attempt to get the items for the path
				if ( false === $items = $driver->get_all( $site ) )
				{
					$this->log_debug_message( __METHOD__, "No items were found for the current site cache for the " . $driver->name() . " driver." );
					return;
				}

				//we've got items
				foreach ( $items as $item )
				{
					if ( $driver->delete( $site . ( ( $driver == 'db' || $driver == 'apc' ) ? $item['id'] : $item ) ) === false )
					{
						$this->log_debug_message( __METHOD__, "Something went wrong, and the current site cache for the " . $driver->name() . " driver was not cleaned successfully." );
					}
				}
				unset( $items );

				return;
			}
			else
			{
				if ( $driver->clear() === false )
				{
					$this->log_debug_message( __METHOD__, "Something went wrong, and the cache for the " . $driver->name() . " driver was not cleaned successfully." );
				}
				else
				{
					$this->log_debug_message( __METHOD__, "The cache for the " . $driver->name() . " driver was cleaned successfully." );
				}
			}
		}
	}

	/**
	 * Deprecated. Doesn't return anything.
	 * @return mixed
	 */
	public function cache_info()
	{
		return $this->EE->TMPL->no_results();
	}

	/**
	 * Breaks the cache. This method is an EE action (called from the CE Cache extension).
	 *
	 * @return void
	 */
	public function break_cache()
	{
		//debug mode
		if ( $this->EE->input->get_post( 'act_test', true ) === 'y' )
		{
			$this->EE->lang->loadfile( 'ce_cache' );
			echo lang('ce_cache_debug_working');
			exit();
		}

		//this method is not intended to be called as an EE template tag
		if ( isset( $this->EE->TMPL ) )
		{
			return;
		}

		//load the cache break class, if needed
		if ( ! class_exists( 'Ce_cache_break' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_break.php';
		}

		//instantiate the class break and call the break cache method
		$cache_break = new Ce_cache_break();
		$cache_break->break_cache_hook( null, null );
	}

	/**
	 * Simple method to log a debug message to the EE Debug console.
	 *
	 * @param string $method
	 * @param string $message
	 * @return void
	 */
	private function log_debug_message( $method = '', $message = '' )
	{
		if ( $this->debug )
		{
			$this->EE->TMPL->log_item( "&nbsp;&nbsp;***&nbsp;&nbsp;CE Cache $method debug: " . $message );
		}
	}

	/**
	 * Gets the user-specified drivers array
	 *
	 * @param bool $allow_static
	 * @return array
	 */
	private function get_drivers_array( $allow_static = false, $override_prevent = false )
	{
		//get the user-specified drivers
		$drivers = $this->determine_setting( 'drivers', '' );

		if ( ! $allow_static )
		{
			//make sure the static driver is not included
			$drivers = str_replace( 'static', '', $drivers );
		}

		if ( ! empty( $drivers ) ) //we have driver settings
		{
			if ( ! is_array( $drivers ) )
			{
				$drivers = explode( '|', $drivers );
			}
		}
		else //no drivers specified, see if we have some legacy settings
		{
			$drivers = array();

			//determine the adapter
			$adapter = $this->determine_setting( 'adapter' );
			if ( ! empty( $adapter ) ) //if not set to a valid value, set to 'file'
			{
				$drivers[] = $adapter;
			}

			//determine the backup adapter
			$backup = $this->determine_setting( 'backup' );
			if ( ! empty( $backup ) ) //if not set to a valid value, set to 'dummy'
			{
				$drivers[] = $backup;
			}
		}

		if ( count( $drivers ) == 0 ) //still no drivers specified, default to 'file'
		{
			$drivers[] = 'file';
		}

		//is the user logged in?
		$logged_in = ($this->EE->session->userdata['member_id'] != 0);

		//see if there is a reason to prevent caching the current page (like the current page is a better workflow draft, or ce cache is off)
		if ( ! $override_prevent
			&& (
				( isset( $this->EE->session->cache['ep_better_workflow']['is_preview'] ) && $this->EE->session->cache['ep_better_workflow']['is_preview'] === true ) //better workflow draft
				|| ( isset( $_GET['bwf_dp'] ) && $_GET['bwf_dp'] == 't' ) //another bwf check (from Matt Green)
				|| ( isset( $_GET['publisher_status'] ) && $_GET['publisher_status'] == 'draft' ) // publisher check (from Fusionary)
				|| $this->ee_string_to_bool( $this->determine_setting( 'off', 'no' ) )  //cache is off
				|| ($this->ee_string_to_bool( $this->determine_setting( 'logged_in_only', 'no', 'fragment' ) ) && ! $logged_in ) //logged in only, but not logged in
				|| ($this->ee_string_to_bool( $this->determine_setting( 'logged_out_only', 'no', 'fragment' ) ) && $logged_in ) //logged out only, but is logged in
				|| ( ! empty( $_POST ) && $this->ee_string_to_bool( $this->determine_setting( 'ignore_post_requests', 'yes' ) ) && $_POST != array( 'entry_id' => '' ) ) //a POST page and ignore_post_requests is set to "yes"
			)
		)
		{
			//set the drivers to dummy
			$drivers = array( 'dummy' );
		}

		return $drivers;
	}

	/**
	 * Loads the cache factory class, if needed
	 */
	private function include_factory()
	{
		//load the class if needed
		if ( ! class_exists( 'Ce_cache_factory' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_factory.php';
		}
	}

	/**
	 * Sets up the cache if needed. This is its own method, as opposed to being in the constructor, because some methods will not need it.
	 */
	private function setup_cache()
	{
		if ( ! $this->is_cache_setup ) //only run if the flag indicated it has not already been setup
		{
			//set the set up flag
			$this->is_cache_setup = true;

			//get the driver classes
			$drivers = $this->get_drivers_array();
			$this->include_factory();
			$this->drivers = Ce_cache_factory::factory( $drivers, true );

			//get the site name
			$site = Ce_cache_utils::get_site_label();

			$this->id_prefix = 'ce_cache/' . $site;

			if ( $this->EE->TMPL->fetch_param( 'global' ) == 'yes' ) //global cache
			{
				$this->id_prefix .= '/global/';
			}
			else //page specific cache
			{
				$this->determine_cache_url();

				//set the id prefix
				$this->id_prefix .= '/local/' . $this->EE->security->sanitize_filename( $this->cache_url, true );
			}

			$this->id_prefix = trim( $this->id_prefix, '/' ) . '/';
		}
	}

	private function determine_cache_url()
	{
		$override = $this->EE->TMPL->fetch_param( 'url_override' );
		if ( $override != false )
		{
			$this->cache_url = $override;
		}
		else
		{
			//triggers:original_uri
			if ( isset( $this->EE->config->_global_vars[ 'triggers:original_paginated_uri' ] ) ) //Zoo Triggers hijacked the URL
			{
				$this->cache_url = $this->EE->config->_global_vars[ 'triggers:original_paginated_uri' ];
			}
			else if ( isset( $this->EE->config->_global_vars[ 'freebie_original_uri' ] ) ) //Freebie hijacked the URL
			{
				$this->cache_url = $this->EE->config->_global_vars[ 'freebie_original_uri' ];
			}
			else //the URL was not hijacked
			{
				$this->cache_url = $this->EE->uri->uri_string();
			}
		}

		$prefix = $this->determine_setting('url_prefix', '');
		if ( ! empty( $prefix ) )
		{
			$this->cache_url = rtrim($prefix, '/') . '/' . $this->cache_url;
		}

		//UTF-8 decode any special characters
		$this->cache_url = utf8_decode( $this->cache_url );
	}

	/**
	 * Determines the given setting by checking for the param, and then for the global var, and then for the config item.
	 * @param string $name The name of the parameter. The string 'ce_cache_' will automatically be prepended for the global and config setting checks.
	 * @param string $default The default setting value
	 * @param string $long_prefix
	 * @return string The setting value if found, or the default setting if not found.
	 */
	private function determine_setting( $name, $default = '', $long_prefix = '' )
	{
		if ( ! empty( $long_prefix ) )
		{
			$long_prefix = $long_prefix . '_';
		}

		$long_name = 'ce_cache_' . $long_prefix . $name;
		if ( $this->EE->TMPL->fetch_param( $name ) !== false ) //param
		{
			$default = $this->EE->TMPL->fetch_param( $name );
		}
		else if ( isset( $this->EE->config->_global_vars[ $long_name ] ) && $this->EE->config->_global_vars[ $long_name ] !== false ) //first check global array
		{
			$default = $this->EE->config->_global_vars[ $long_name ];
		}
		else if ( $this->EE->config->item( $long_name ) !== false ) //then check config
		{
			$default = $this->EE->config->item( $long_name );
		}

		return $default;
	}

	/**
	 * Parses the tagdata as if it were a template.
	 * @param string $tagdata
	 * @return string
	 */
	public function parse_as_template( $tagdata = '' )
	{
		//store the current template object
		$TMPL2 = $this->EE->TMPL;
		unset($this->EE->TMPL);

		//create a new template object
		$temp = new EE_Template();
		$this->EE->TMPL = $temp;
		$temp->start_microtime = $TMPL2->start_microtime;
		$temp->template = '';
		$temp->final_template = '';
		$temp->fl_tmpl = '';
		$temp->tag_data	= array();
		$temp->var_single = array();
		$temp->var_cond	= array();
		$temp->var_pair	= array();
		$temp->plugins = $TMPL2->plugins;
		$temp->modules = $TMPL2->modules;
		$temp->loop_count = 0;
		$temp->depth = 0;
		$temp->parse_tags();
		$temp->process_tags();

		//parse the current tagdata
		$temp->parse( $tagdata );

		//get the parsed tagdata back
		$tagdata = $temp->final_template;

		if ( $this->debug )
		{
			//these first items are boilerplate, and were already included in the first log - like "Parsing Site Variables", Snippet keys and values, etc
			unset( $temp->log[0], $temp->log[1], $temp->log[2], $temp->log[3], $temp->log[4], $temp->log[5], $temp->log[6] );

			$TMPL2->log = array_merge( $TMPL2->log, $temp->log );
		}

		//now let's check to see if this page is a 404 page
		if ( $this->is_404() )
		{
			$this->EE->output->out_type = '404';
			$this->EE->TMPL->template_type = '404';
			$this->EE->TMPL->final_template = $this->unescape_tagdata( $tagdata );
			$this->EE->TMPL->cease_processing = true;
			$this->EE->TMPL->no_results();
			$this->EE->session->cache[ 'Ce_cache' ]['is_404'] = true;
		}

		//restore the original template object
		$this->EE->TMPL = $TMPL2;

		unset($TMPL2, $temp);

		//call the post parse hook for this data
		if ( $this->EE->extensions->active_hook( 'template_post_parse' ) )
		{
			$tagdata = $this->EE->extensions->call( 'template_post_parse', $tagdata, false, $this->EE->config->item('site_id') );
		}

		//return the tagdata
		return $tagdata;
	}

	/**
	 * Determines whether or not EE has 404 headers set
	 */
	private function is_404()
	{
		if ( isset( $this->EE->output->headers[0] ) )
		{
			foreach ( $this->EE->output->headers as $value )
			{
				foreach ( $value as $v )
				{
					if ( strpos( $v, '404' ) !== false ) // a 404 header was found
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Parses segment and global variables. Used to parse data in escape tags.
	 * @param string $str
	 * @return mixed
	 */
	public function parse_vars( $str )
	{
		//remove the comments
		$str = $this->EE->TMPL->remove_ee_comments( $str );

		//parse segment variables
		if ( strpos( $str, '{segment_' ) !== false )
		{
			for ( $i = 1; $i < 10; $i++ )
			{
				$str = str_replace( '{segment_' . $i . '}', $this->EE->uri->segment( $i ), $str );
			}
		}

		//parse global variables
		$str = $this->EE->TMPL->parse_variables_row( $str, $this->EE->config->_global_vars );

		//parse current_time
		$str = $this->current_time( $str );

		return $str;
	}

	/**
	 * Helper method that simplifies the data parsing and returning process.
	 *
	 * @param string $str
	 * @return string
	 */
	public function process_return_data( $str )
	{
		//parse globals and segment variables in case there were escaped during parsing
		$str = $this->parse_vars( $str );

		//parse current_time
		$str = $this->current_time( $str );

		//insert the action ids
		$str = $this->insert_action_ids( $str );
		return $str;
	}

	/**
	 * Replaces the {current_time} variable with the actual current time. Useful if the variable was escaped. This method mimics the functionality from the Template class.
	 *
	 * @param string $str
	 * @return string
	 */
	public function current_time( $str )
	{
		if ( strpos( $str, '{current_time' ) === false )
		{
			return $str;
		}

		if ( preg_match_all( '/{current_time\s+format=([\"\'])([^\\1]*?)\\1}/', $str, $matches ) )
		{
			for ($j = 0; $j < count($matches[0]); $j++)
			{
				if ( version_compare( APP_VER, '2.6.0', '<' ) )
				{
					$str = str_replace($matches[0][$j], $this->EE->localize->decode_date($matches[2][$j], $this->EE->localize->now), $str);
				}
				else
				{
					$str = str_replace($matches[0][$j], $this->EE->localize->format_date($matches[2][$j]), $str);
				}
			}
		}

		return str_replace( '{current_time}', $this->EE->localize->now, $str);
	}

	/**
	 * The following is a lot like the Functions method of inserting the action ids, except that this will first find the actions. The original method does not look to find the ids on cached data (it just stores them in an array as they are called).
	 *
	 * @param string $str
	 * @return string
	 */
	public function insert_action_ids( $str )
	{
		//will hold the actions
		$actions = array();

		//do we need to check for actions?
		if ( strpos( $str, LD . 'AID:' ) !== false && preg_match_all( '@' . LD . 'AID:([^:}]*):([^:}]*)' . RD . '@Us', $str, $matches, PREG_SET_ORDER ) ) //actions found
		{
			foreach ( $matches as $match )
			{
				$actions[ $match[ 1 ] ] = $match[ 2 ];
			}
		}
		else //no actions to parse
		{
			return $str;
		}

		//create the sql
		$sql = "SELECT action_id, class, method FROM exp_actions WHERE";
		foreach ( $actions as $key => $value )
		{
			$sql .= " (class= '" . $this->EE->db->escape_str( $key ) . "' AND method = '" . $this->EE->db->escape_str( $value ) . "') OR";
		}

		//run the query
		$query = $this->EE->db->query( substr( $sql, 0, -3 ) );

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result_array() as $row )
			{
				$str = str_replace( LD . 'AID:' . $row[ 'class' ] . ':' . $row[ 'method' ] . RD, $row[ 'action_id' ], $str );
			}
		}

		return $str;
	}

	/**
	 * Determines the id to use.
	 *
	 * @param string $method The calling method.
	 * @return string|bool The id on success, or false on failure.
	 */
	public function fetch_id( $method )
	{
		if ( $this->EE->TMPL->fetch_param( 'global' ) == 'yes' ) //global cache
		{
			$id = $this->EE->TMPL->fetch_param( 'id', '' );
		}
		else //page specific cache
		{
			$id = $this->determine_setting( 'id', 'item' );
		}

		$id = trim( $id );

		//get the id
		if ( empty( $id ) )
		{
			$this->log_debug_message( $method, "An id was not specified." );

			return false;
		}
		if ( ! $this->id_is_valid( $id ) )
		{
			$this->log_debug_message( $method, "The specified id '{$id}' is invalid. An id may only contain alpha-numeric characters, dashes, and underscores." );
			return false;
		}

		//add the id prefix
		return trim( Ce_cache_utils::remove_duplicate_slashes( $this->id_prefix . $id ), '/' );
	}

	/**
	 * Validates an id.
	 *
	 * @param string $id
	 * @return int 1 for valid, 0 for invalid
	 */
	public function id_is_valid( $id )
	{
		return preg_match( '@[^\s]+@i', $id );
	}

	/**
	 * Little helper method to convert parameters to a boolean value.
	 *
	 * @param $string
	 * @return bool
	 */
	public function ee_string_to_bool( $string )
	{
		return ( $string == 'y' || $string == 'yes' || $string == 'on' || $string === true );
	}

	public function no_results_tagdata()
	{
		//$tagdata = $this->EE->TMPL->tagdata;
		$index = 0;
		foreach ( $this->EE->TMPL->tag_data as $i => $tag_dat )
		{
			if ( $this->EE->TMPL->tagchunk == $tag_dat['chunk'] )
			{
				$index = $i;
			}
		}

		return $this->EE->TMPL->tag_data[$index]['block'];
	}

	/**
	 * This is a shutdown function registered when an item is saved.
	 */
	public function shut_it_down()
	{
		//determine if this is a 404 page
		$is_404 = (
			$this->EE->config->item( 'ce_cache_exclude_404s' ) != 'no' //we are not excluding 404 pages (in other words, we are caching 404 pages)
			&& (
				isset( $this->EE->session->cache[ 'Ce_cache' ]['is_404'] ) //if previously evaluated to be a 404 page by fragment caching
				|| $this->EE->output->out_type == '404' //or if the output type is set to a 404 page
				|| $this->is_404() //or if there is a 404 header
			)
		);

		if ( $is_404 ) //this is a 404 page
		{
			//if there are cached items, and there are drivers, let's delete the cached items
			if ( isset ( $this->EE->session->cache[ 'Ce_cache' ]['cached_items'] ) && ! empty( $this->drivers ) )
			{
				//loop through each driver
				foreach ( $this->drivers as $driver )
				{
					foreach ( $this->EE->session->cache[ 'Ce_cache' ]['cached_items'] as $index => $item )
					{
						if ( $item['driver'] == $driver->name() )
						{
							$driver->delete( $item['id'] );
							unset( $this->EE->session->cache[ 'Ce_cache' ]['cached_items'][$index] );
						}
					}
				}
			}

			//remove the cached items from memory (although this will probably happen soon anyway)
			unset( $this->EE->session->cache[ 'Ce_cache' ]['cached_items'] );
		}
		else //this is not a 404 page (or it is a 404 page, but the config setting says not to exclude them)
		{
			if ( isset( $this->EE->session->cache[ 'Ce_cache' ][ 'static' ] ) ) //we have a static page, let's cache it
			{
				//load the class if needed
				$this->include_factory();

				//setup the driver
				$drivers = Ce_cache_factory::factory( 'static' );
				foreach ( $drivers as $driver )
				{
					$id = 'ce_cache/' . Ce_cache_utils::get_site_label() . '/static/' . $this->EE->security->sanitize_filename( $this->cache_url, true );

					//get the final template
					$final = $this->EE->TMPL->final_template;

					//handle feed issues
					if ( $this->EE->output->out_type == 'feed' )
					{
						//this normally happens in the output class, so we need to take care of it here
						$final = preg_replace( '@<ee\:last_update>(.*?)<\/ee\:last_update>@', '', $final );
						$final = preg_replace( '@{\?xml(.+?)\?}@', '<?xml\\1?'.'>', $final);
					}

					//pre save hook
					if ( $this->EE->extensions->active_hook( 'ce_cache_pre_save' ) )
					{
						$final = $this->EE->extensions->call('ce_cache_pre_save', $final, 'static');
					}

					//get the headers
					$headers = array();

					//get the headers
					if ( isset( $this->EE->output->headers ) && is_array( $this->EE->output->headers ) )
					{
						foreach( $this->EE->output->headers as $header )
						{
							if ( isset( $header[0] ) )
							{
								$headers[] = $header[0];
							}
						}
					}

					//attempt to save the cache
					if ( $driver->set( $id, $final, $this->EE->session->cache[ 'Ce_cache' ][ 'static' ][ 'seconds' ], $headers ) === false ) //save unsuccessful
					{
						//probably too late to log debug messages - oh well
						$this->log_debug_message( __METHOD__, "Something went wrong and the data for '{$this->cache_url}' was not cached using the " . $driver->name() . " driver." );
					}
					else //save successful
					{
						//probably too late to log debug messages - oh well
						$this->log_debug_message( __METHOD__, "The data for '{$this->cache_url}' was successfully cached using the " . $driver->name() . " driver." );

						//get the tags
						$tags = $this->EE->session->cache[ 'Ce_cache' ][ 'static' ][ 'tags' ];

						//add in the tags
						if ( isset( $this->EE->session->cache[ 'Ce_cache' ]['tags'] ) )
						{
							$tags .=  $this->EE->session->cache[ 'Ce_cache' ]['tags'];
						}

						$this->save_tags( $driver->clean_id( $id ), $tags );
					}
				}
			}
		}
	}

	/**
	 * A very simple method to attempt to determine if the current user agent is a bot
	 *
	 * @return bool
	 */
	public function is_bot()
	{
		if ( ! isset( $this->EE->session->cache[ 'Ce_cache' ][ 'is_bot' ] ) )
		{
			$user_agent = $this->EE->input->user_agent();

			$this->EE->session->cache[ 'Ce_cache' ][ 'is_bot' ] = (bool)( ! empty( $user_agent ) && preg_match( '@bot|spider|crawl|curl@i', $user_agent ) );
		}

		return $this->EE->session->cache[ 'Ce_cache' ][ 'is_bot' ];
	}


	/**
	 * Swaps out placeholders with their escaped values.
	 *
	 * @param null $tagdata
	 * @return mixed|null
	 */
	public function unescape_tagdata( $tagdata = null )
	{
		if ( ! isset( $tagdata ) )
		{
			$tagdata = $this->no_results_tagdata();
		}

		//unescape any content escaped by the escape() method
		if ( isset( $this->EE->session->cache[ 'Ce_cache' ]['placeholder-keys'] ) )
		{
			$tagdata = str_replace( $this->EE->session->cache[ 'Ce_cache' ]['placeholder-keys'], $this->EE->session->cache[ 'Ce_cache' ]['placeholder-values'], $tagdata);

			$tagdata = str_replace( '{::segment_', '{segment_', $tagdata );
		}

		//unescape any escaped logged_in and logged_out conditionals if they were escaped above
		if ( $this->EE->TMPL->fetch_param( 'process' ) != 'no' )
		{
			//now we'll swap the logged_in and logged_out variables back to their old selves
			$tagdata = str_replace( array( 'ce_cache-in_logged', 'ce_cache-out_logged' ), array( 'logged_in', 'logged_out' ), $tagdata );
		}

		return $tagdata;
	}

	/**
	 * Saves any tags that are specified in the tag parameter.
	 *
	 * @param string $id
	 * @param string/bool $tag_string The string from $this->EE->TMPL->fetch_param( 'tags' )
	 */
	public function save_tags( $id, $tag_string = '' )
	{
		//tag the content if applicable
		if ( $tag_string !== false )
		{
			//cleanup the tag string
			$tag_string = $this->reduce_pipes( $tag_string );

			//explode into tags
			$temps = explode( '|', $tag_string );

			$data = array();

			//loop through the items
			foreach ( $temps as $temp )
			{
				$temp = trim( $temp );
				if ( empty( $temp ) )
				{
					$this->log_debug_message( __METHOD__, 'An empty tag was found and will not be applied to the saved item "' . $id . '".' );
					continue;
				}

				if ( strlen( $temp ) > 100 )
				{
					$this->log_debug_message( __METHOD__, 'The tag "' . $temp . '" could not be saved for the "' . $id . '" item, because it is over 100 characters long.' );
					continue;
				}

				$data[] = array( 'item_id' => $id, 'tag' => $temp );
			}
			unset( $temps );

			//delete all of the current tags for this item
			$this->EE->db->query( 'DELETE FROM exp_ce_cache_tagged_items WHERE item_id = ?', array( $id ) );

			//add in the new tags
			if ( count( $data ) > 1 )
			{
				$this->EE->db->insert_batch( 'ce_cache_tagged_items', $data );
			}
			else if ( count( $data ) > 0 )
			{
				$this->EE->db->insert( 'ce_cache_tagged_items', $data[0] );
			}

			unset( $data );
		}
	}

	private function reduce_pipes( $string, $make_lowercase = true )
	{
		$string = trim( $string, '|' ); //trim pipes
		$string = str_replace( '||', '', $string ); //remove double pipes (empty tags)
		if ( $make_lowercase )
		{
			$string = strtolower( $string ); //convert to lowercase
		}

		return $string;
	}
}
/* End of file mod.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/mod.ce_cache.php */