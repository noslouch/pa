<?php
/**
 * DataGrab Model Class
 *
 * Handles the DataGrab import process
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_model extends CI_Model {
	
	var $datatypes = array();
	
	var $settings;
	var $datatype;
	var $channel_defaults;
	var $batch_limit_completed;
	var $entries;
	var $field_settings;
	var $valid_statuses;
	
	var $check_memory = FALSE;
	var $mem_usage = 0;
	var $current_user = 0;
	
	var $return_data = "";
	
	function Datagrab_model() {
		parent::__construct(); 
	}

	function fetch_datatype_names() {
		
		$this->initialise_types();

		$types = array();
		foreach( $this->datatypes as $type_name => $type ) {
			$types[ $type_name ] = $type->display_name();
		}
		
		return $types;
	}


	function initialise_types() {
		
		if ( ! class_exists('Datagrab_type') ) {
			require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_type'.EXT;
		}	
		
		$path = PATH_THIRD.'ajw_datagrab/datatypes/';
		
		$dir = opendir($path);

		while (($folder = readdir($dir)) !== FALSE) {
			if( is_dir($path.$folder) 
				&& $folder != "." && $folder != ".." 
				&& substr( $folder, 0, 1) != "_" ) {
				$filename = "/dt." . $folder . EXT;
				if ( ! class_exists( $folder ) ) {
					if( file_exists( $path.$folder.$filename ) ) {
						include($path.$folder.$filename);
						if (class_exists($folder)) {
							$this->datatypes[$folder] = new $folder();
						}
					}
				}
			}
		}
		closedir($dir);
		
		ksort( $this->datatypes );
		
		return count( $this->datatypes );
		
	}

	function do_import( $datatype, $settings ) {
	
		$this->datatype = $datatype;
		$this->settings = $settings;
		
		// Initialise
		$this->load->library('api');
		$this->api->instantiate('channel_fields');
		$this->api_channel_fields->fetch_custom_channel_fields();
		$this->api->instantiate('channel_entries');
		
		$this->load->library('addons'); 
		
		/*
		if (function_exists('date_default_timezone_set')) { 
			date_default_timezone_set( $this->config->item('default_site_timezone') );
		}
		*/
		date_default_timezone_set( 'UTC' );

		// Set up the data source
		$this->initialise_types();
		$datatype->initialise( $this->settings );
		$datatype->fetch();

		// Get custom fields from database
		$custom_fields = $this->_fetch_custom_fields_from_channel( $this->settings["import"]["channel"] );

		// Get channel details for default values
		$this->channel_defaults = $this->_fetch_channel_defaults( $this->settings["import"]["channel"] );
		
		// Can the current member use the Channel API (the import might not be running from
		// the Control Panel)?
		$this->_check_member_status();

		// Set up initial variables
		$entries_added = 0;
		$entries_updated = 0;
		$row_num = 0;
		$timestamp = time();
		$this->entries = array(); // Used to store a list of which entries have been imported/updated
		$this->batch_limit_completed = FALSE;

		$this->_tic("import");

		// -------------------------------------------
		// 'ajw_datagrab_pre_import' hook.
		//  - Perform actions before the import is run
		//
			if ($this->extensions->active_hook('ajw_datagrab_pre_import') === TRUE)
			{
				$edata = $this->extensions->call('ajw_datagrab_pre_import', $this);
				if ($this->extensions->end_script === TRUE) return;
			}
		//
		// -------------------------------------------

		// Don't save queries
		$this->db->save_queries = FALSE;

		// Loop over items
		while( $item = $datatype->next() ) {

			// Reset time out
			set_time_limit(30);

			if( $this->check_memory ) { $this->_check_mem_usage("Start"); }

			// Check whether to skip this row or not
			$row_num++;
			if( isset($this->settings["datatype"]["skip"]) && is_numeric($this->settings["datatype"]["skip"]) ) {
				if ( $row_num <= $this->settings["datatype"]["skip"] ) {
					continue;
				}
			}

			if( isset($this->settings["import"]["limit"]) && is_numeric($this->settings["import"]["limit"]) && $this->settings["import"]["limit"] > 0 ) {
				if( !isset( $this->settings["datatype"]["skip"] ) ) {
					$this->settings["datatype"]["skip"] = 0;
				}
				if ( $row_num - $this->settings["datatype"]["skip"] > $this->settings["import"]["limit"] ) {
					$this->batch_limit_completed = TRUE;
					break;
				}
			}

			// -------------------------------------------
			// 'ajw_datagrab_modify_data' hook.
			//  - Perform actions before the import is run
			//
				if ($this->extensions->active_hook('ajw_datagrab_modify_data') === TRUE)
				{
					$item = $this->extensions->call('ajw_datagrab_modify_data', $item);
					if ($this->extensions->end_script === TRUE) return;
				}
			//
			// -------------------------------------------

			// Initialise array to store entry data
			$data = array();

			// Get title
			$data["title"] = $datatype->get_item( $item, $this->settings["config"][ "title" ] );
		
			// Get date
			$date = isset( $this->settings["config"][ "date" ] ) ? $this->settings["config"][ "date" ] : '';
			$data[ "entry_date" ] = $this->_parse_date( $datatype->get_item( $item, $date ) );

			// Get date
			$expiry_date = isset( $this->settings["config"][ "expiry_date" ] ) ? $this->settings["config"][ "expiry_date" ] : '';
			If( $datatype->get_item( $item, $expiry_date ) != "" ) {
				$data[ "expiration_date" ] = $this->_parse_date( $datatype->get_item( $item, $expiry_date ) );
			}
			
			// Get URL title
			if( isset( $this->settings["config"][ "url_title" ] ) ) {
				// Get URL title from data source
				$url_title = $datatype->get_item( $item, $this->settings["config"][ "url_title" ] );
				if( $url_title != "" ) {
					$data["url_title"] =  $url_title;
				}
			}

			if( $this->channel_defaults["url_title_prefix"] != "" ) {
				$url_title = url_title( strtolower( $data["title"] ) );
				$url_title = $this->channel_defaults["url_title_prefix"] . $url_title;
				$data["url_title"] =  $url_title;
			}
			
			// @todo: Load static data

			// Loop over all custom fields in this channel
			foreach( $custom_fields as $field => $field_data ) {

				// Update field's fieldtype settings (for 3rd party fieldtypes)
				$this->_get_channel_fields_settings( $field_data["id"] );

				// print "<p>$field " .  $this->settings["cf"][ $field ] . "</p>";

				// Should we import anything into this field?
				if( isset( $this->settings["cf"][ $field ] ) && $this->settings["cf"][ $field ] != "" ) {

					// Check to see if a handler exists for this field type
					if ( ! class_exists('Datagrab_fieldtype') ) {
						require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_fieldtype'.EXT;
					}	
					if ( ! class_exists('Datagrab_'.$field_data[ "type" ] ) ) {
						if( file_exists( PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_data[ "type" ].EXT ) ) {
							require_once PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_data[ "type" ].EXT;
						}
					}	
					
					// If so, call the prepare_post_data method to format the $data array
					if ( class_exists('Datagrab_'.$field_data[ "type" ]) ) {
						$classname = "Datagrab_".$field_data[ "type" ];
						$ft = new $classname();
						$ft->prepare_post_data( $this, $item, $field_data["id"], $field, $data );
					} else {
						// If no handler exists, just use the value
						$data[ "field_id_" . $field_data["id"] ] = $datatype->get_item( $item, $this->settings["cf"][ $field ] );
					}

					$data[ "field_ft_" . $field_data["id"] ] = $field_data[ "format" ];

				}
			}

			// print_r( $data );
			// print_r( $this->api_channel_fields->settings );

			// Set timestamp field if one is set
			if( isset( $this->settings["config"]["timestamp"] ) && $this->settings["config"]["timestamp"] != "" ) {
				$field_id = $custom_fields[ $this->settings["config"]["timestamp"] ][ "id"];
				$data[ "field_id_".$field_id ] = $timestamp;
			}
			
			// No of seconds to offset dates/times
			$time_offset = $this->settings["config"]["offset"]; 
			$data["entry_date"] += $time_offset;

			// Fetch author id
			$author_id = $this->_fetch_author( $item );

			// Get status
			$status = $this->_fetch_status( $item );

			// Handle Structure module fields
			// @todo: check whether Structure now can handle API entry
			if( $this->db->table_exists('exp_structure_channels') ) {
				// If the structure module tables exists, try and get template id
				$this->db->select( 'template_id' );
				$this->db->from( 'exp_structure_channels' );
				$this->db->where( 'channel_id', $this->channel_defaults["channel_id"] );
				// print( $this->db->_compile_select() ); exit;
				$query = $this->db->get();
				if( $query->num_rows() > 0 ) {

					$row = $query->row_array();

					$data["cp_call"] = TRUE;
					$data["structure__uri"] = url_title( strtolower( $data["title"] ) );
					$data["structure__template_id"]= $row["template_id"];
					$data["structure__parent_id"] = 0;
					
					// Not needed?
					// $data["structure__listing_channel"] = 0;					
					
					// Eek! Workaround Structure 'bug' that expects post data
					$_POST["channel_id"] = $this->channel_defaults["channel_id"];
					$_POST["template_id"] = $data['structure__template_id'];
					$_POST["parent_id"] = $data["structure__parent_id"];
					
					// Structure uses config variable to get site_pages
					// This only gets updated on page load (ie, once at the start
					// of the import) so we have to keep updating it here...
					$this->db->select('site_pages');
					$this->db->where('site_id', $this->config->item('site_id'));
					$query = $this->db->get('sites');
					$site_pages = unserialize(base64_decode($query->row('site_pages')));
					$this->config->config["site_pages"] = $site_pages;
					
					// print_r( $site_pages );
					// print_r( $this->config->config["site_pages"] );
				}
			}

			// Handle Pages module
			if( array_key_exists('pages', $this->addons->get_installed('modules')) && 
				isset( $this->settings["cf"]["ajw_pages"]) && 
			 	$this->settings["cf"]["ajw_pages"] == "y" ) {
				$data["cp_call"] = TRUE;
				if( $this->settings["cf"]["ajw_pages_url"] == "" ) {
					$data["pages__pages_uri"] = url_title( strtolower( $data["title"] ) );
				} else {
					$data["pages__pages_uri"] = $datatype->get_item( $item, $this->settings["cf"]["ajw_pages_url"] );
				}
				$default_template = 1;
				$this->db->select( "configuration_value" );
				$this->db->where( "configuration_name", "template_channel_".$this->channel_defaults["channel_id"] );
				$query = $this->db->get( "exp_pages_configuration" );
				if( $query->num_rows() > 0 ) {
					$row = $query->row_array();
					$default_template = $row["configuration_value"];
				} else {
					$this->db->select( "exp_templates.template_id" );
					$this->db->from( "exp_templates" );
					$this->db->join( "exp_template_groups", "exp_template_groups.group_id = exp_templates.group_id" );
					$this->db->where( "is_site_default", "y" );
					$this->db->where( "template_name", "index" );
					$query = $this->db->get();
					$row = $query->row_array();
					$default_template = $row["template_id"];
				}
				if( $this->settings["cf"]["ajw_pages_template"] == "" ) {
					$data["pages__pages_template_id"] = $default_template;
				} else {
					$template = $datatype->get_item( $item, $this->settings["cf"]["ajw_pages_template"] );					
					$data["pages__pages_template_id"] = $default_template;
					$template_segments = explode("/", $template);
					if( count($template_segments) == 2 ) {
						$this->db->select( "exp_templates.template_id" );
						$this->db->from( "exp_templates" );
						$this->db->join( "exp_template_groups", "exp_template_groups.group_id = exp_templates.group_id" );
						$this->db->where( "group_name", $template_segments[0] );
						$this->db->where( "template_name", $template_segments[1] );
						$query = $this->db->get();
						if( $query->num_rows() > 0 ) {
							$row = $query->row_array();
							$default_template = $row["template_id"];
						}
					}
				}
				$data["pages__pages_template_id"] = $default_template;
			}

			// Handle SEO Lite
			if( isset( $this->settings["cf"]["ajw_seo_lite_title"] ) ) {
				$data["seo_lite__seo_lite_title"] = $datatype->get_item( $item, $this->settings["cf"]["ajw_seo_lite_title"] );
				$data["seo_lite__seo_lite_keywords"] = $datatype->get_item( $item, $this->settings["cf"]["ajw_seo_lite_keywords"] );
				$data["seo_lite__seo_lite_description"] = $datatype->get_item( $item, $this->settings["cf"]["ajw_seo_lite_description"] );
				$data["cp_call"] = TRUE;
			}
    
			// Check for duplicate entry
			if( ! isset( $this->settings["config"][ "unique" ] ) ) {
				$this->settings["config"]["unique"] = '';
			}

			// Check whether this entry is a duplicate
			$entry_id = $this->_is_entry_unique( $data, $this->settings["config"]["unique"], $custom_fields );

			// Do entry_id field check here
			if( isset( $this->settings["config"]["ajw_entry_id"] ) && $this->settings["config"]["ajw_entry_id"] != "" ) {
				// Check whether entry_id exists
				$e_id = $datatype->get_item( $item, $this->settings["config"]["ajw_entry_id"] );
				$this->db->select( "entry_id" );
				$this->db->where( "entry_id", $e_id );
				$this->db->where( "channel_id", $this->channel_defaults["channel_id"] );
				$query = $this->db->get( "exp_channel_titles" );
				if( $query->num_rows() > 0 ) {
					$entry_id = $e_id;
				}
			}

			// Do extra processing now we know if it is an update or not
			foreach( $custom_fields as $field => $field_data ) {

				// Update field's fieldtype settings (for 3rd party fieldtypes)
				$this->_get_channel_fields_settings( $field_data["id"] );

				// Should we import anything into this field?
				if( isset( $this->settings["cf"][ $field ] ) && $this->settings["cf"][ $field ] != "" ) {

					// Check to see if a handler exists for this field type
					if ( ! class_exists('Datagrab_fieldtype') ) {
						require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_fieldtype'.EXT;
					}	
					if ( ! class_exists('Datagrab_'.$field_data[ "type" ] ) ) {
						if( file_exists( PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_data[ "type" ].EXT ) ) {
							require_once PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_data[ "type" ].EXT;
						}
					}	
					
					// If so, call the prepare_post_data method to format the $data array
					if ( class_exists('Datagrab_'.$field_data[ "type" ]) ) {
						$classname = "Datagrab_".$field_data[ "type" ];
						$ft = new $classname();
						// $update = $entry_id > 0 && isset( $this->settings["config"]["update"] ) && $this->settings["config"]["update"] == "y";
						$ft->final_post_data( $this, $item, $field_data["id"], $field, $data, $entry_id );
					} 
					
				}
			}
			$data["cp_call"] = TRUE;
			
  		// print_r( $data ); exit;

			// If it is a new entry...
			if (  $entry_id == 0 ) {
				
			// Insert (titles, data, categories)
		
			$this->return_data .= "<p>Found new entry: " . $data[ "title" ] . "</p>\n";
		
				// Add entry using EE channel API
				
				$data["channel_id"] = $this->channel_defaults["channel_id"]; // Shouldn't need this... (http://expressionengine.com/bug_tracker/bug/13483/)
				$data["status"] = $status;
				$data["author_id"] = $author_id;
				$data["ping_servers"] = array(); // API bug (http://expressionengine.com/bug_tracker/bug/14008/)
				$data["allow_comments"] = $this->channel_defaults["deft_comments"];

				// Find and create categories
				$entry_categories = $this->_setup_categories( $item );
				$data["category"] = $entry_categories;

				$this->api_channel_entries->entry_id = 0; // to work around bug in API (http://expressionengine.com/bug_tracker/bug/13549/)

				// Stop numeric url titles
				if( is_numeric( $data["title"] ) && ! isset( $data["url_title"] ) ) {
					$data["url_title"] = "event-".url_title( $data["title"], $this->config->item('word_separator'), TRUE );
				}

				// Disable notices while running this. Too many 3rd party fieldtypes
				// giving notices
				$errorlevel = error_reporting();
				error_reporting( $errorlevel & ~E_NOTICE );

				if( $this->api_channel_entries->submit_new_entry( 
						$this->channel_defaults["channel_id"], 
						$data ) === FALSE) {
					$this->return_data .= "<p>Could not create new entry: " . $data[ "title" ] . "</p>\n";;
					foreach( $this->api_channel_entries->errors as $eid => $error ) {
						$this->return_data .= "<p>" . $error . " " . $eid . "</p>\n";
					}
				}

				error_reporting( $errorlevel );

				$entry_id = $this->api_channel_entries->entry_id;

				$entries_added++;

				// Do comments
				if( isset( $this->settings["config"]["import_comments"] ) && $this->settings["config"]["import_comments"] == "y") {
					$no_comments = $this->_import_comments( $entry_id, $item );
					if( $no_comments > 0 ) {
						$this->return_data .= "<p>Added " . $no_comments . " comments.</p>\n";
					}
				}
				
			} else {

				if( $data["title"] != "" ) {
					$this->return_data .= "<p>" . $data[ "title" ] . " already exists.</p>\n";
				} else {
					$this->return_data .= "<p>Entry already exists.</p>\n";
				}

				if( isset( $this->settings["config"]["update"] ) && $this->settings["config"]["update"] == "y") {

					// Update entry using EE channel API

					$data["channel_id"] = $this->channel_defaults["channel_id"]; // Shouldn't need this...
					$data["status"] = $status;
					$data["author_id"] = $author_id;
					$data["ping_servers"] = array();
					$data["allow_comments"] = $this->channel_defaults["deft_comments"];

					// Find and create categories
					$entry_categories = $this->_setup_categories( $item, $entry_id );

					// Add entry to categories
					$data["category"] = $entry_categories;

					$this->_prepare_update_data( $entry_id, $data, $custom_fields );

					// Disable notices while running this. Too many 3rd party fieldtypes
					// giving notices
					$errorlevel = error_reporting();
					error_reporting( $errorlevel & ~E_NOTICE) ;
					
					if ($this->api_channel_entries->update_entry( 
								$entry_id, 
								$data) === FALSE) {
						$this->return_data .= "<p>Could not update entry: " . $data[ "title" ] . "</p>\n";
						foreach( $this->api_channel_entries->errors as $error ) {
							$this->return_data .= "<p>" . $error . "</p>\n";
						}
					} else {
						$this->return_data .= "<p>Entry updated</p>\n";
						$entries_updated++;
					}
					
					error_reporting( $errorlevel );
					
					// @todo: do comments

				}

			}

			// Do extra processing now we know if it is an update or not
			foreach( $custom_fields as $field => $field_data ) {

				// Update field's fieldtype settings (for 3rd party fieldtypes)
				$this->_get_channel_fields_settings( $field_data["id"] );

				// Should we import anything into this field?
				if( isset( $this->settings["cf"][ $field ] ) && $this->settings["cf"][ $field ] != "" ) {

					// Check to see if a handler exists for this field type
					if ( ! class_exists('Datagrab_fieldtype') ) {
						require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_fieldtype'.EXT;
					}	
					if ( ! class_exists('Datagrab_'.$field_data[ "type" ] ) ) {
						if( file_exists( PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_data[ "type" ].EXT ) ) {
							require_once PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_data[ "type" ].EXT;
						}
					}	
					
					// If so, call the prepare_post_data method to format the $data array
					if ( class_exists('Datagrab_'.$field_data[ "type" ]) ) {
						$classname = "Datagrab_".$field_data[ "type" ];
						$ft = new $classname();
						$ft->post_process_entry( $this, $item, $field_data["id"], $field, $data, $entry_id );
						//unset( $ft );
					} 
					
				}
			}
			
			$this->entries[] = $entry_id;
			
		} // End of main loop
		
		// Delete entries not updated by this import
		$no_deleted = 0;
		if( isset( $this->settings["config"]["delete_old"] ) && $this->settings["config"]["delete_old"] == "y") {
			$no_deleted += $this->_delete_old_entries( $this->entries );
		}

		if( isset( $this->settings["config"]["delete_by_timestamp"] ) 
			&& $this->settings["config"]["delete_by_timestamp"] == "y") {

			// Find timestamp field
			if( isset( $this->settings["config"]["timestamp"] ) ) {
				$field = "field_id_".$custom_fields[ $this->settings["config"]["timestamp"] ][ "id"];				
			} else {
				$field = "entry_date";
			}

			// Find duration
			if( isset( $this->settings["config"]["delete_by_timestamp_duration"] ) ) {
				$duration = $this->settings["config"]["delete_by_timestamp_duration"];
			} else {
				$duration = 86400;
			}

			$no_deleted += $this->_delete_by_timestamp( $timestamp, $field, $duration );			
		}		

		// Report deleted entries
		if( $no_deleted > 0 ) {
			$this->return_data .= "<p>Deleted " . $no_deleted . " old entries.</p>";
		}

		
		// -------------------------------------------
		// 'ajw_datagrab_post_import' hook.
		//  - Perform actions before the import is run
		//
			if ($this->extensions->active_hook('ajw_datagrab_post_import') === TRUE)
			{
				$edata = $this->extensions->call('ajw_datagrab_post_import', $this);
				if ($this->extensions->end_script === TRUE) return;
			}
		//
		// -------------------------------------------
		
		// Report and update caches
		if ($entries_added > 0) {
			$this->return_data .= "<p>New entries: " . $entries_added . "</p>";
			if ($this->config->item('new_posts_clear_caches') == 'y') {
				$this->functions->clear_caching('all');
			} else {
				$this->functions->clear_caching('sql_cache');
			}
			
		}

		if ($entries_updated > 0) {
			$this->return_data .= "<p>Updated entries: " . $entries_updated . "</p>";
		}
		
		if( $this->batch_limit_completed ) {
			$this->return_data .= "<p>Completed batch of " . $this->settings["import"]["limit"] . " entries (offset by " . $this->settings["datatype"]["skip"] . ")</p>";
		}
		
		$time = $this->_toc("import");
		$this->return_data .= "<p>Time taken: " . $time . " seconds.</p>";
		$this->_check_mem_usage( "", FALSE );
		$this->return_data .= "<p>Memory used: " . number_format($this->mem_usage, 0, ".", ",") . " bytes.</p>";		

		if( $this->current_user == 0 ) {
			$this->session->destroy();
		}
		
		return $this->return_data;
	}
		
	
	/*
		INTERNAL METHODS
	*/

	/**
	 * Get an array of custom field data for a selected channel
	 *
	 * @param string $channel channel_id
	 * @return array $custom_fields array containing custom field data for selected channel
	 */
	function _fetch_custom_fields_from_channel( $channel ) {

		$this->db->select('exp_channel_fields.field_id, 
			exp_channel_fields.field_name, 
			exp_channel_fields.field_label, exp_channel_fields.field_fmt,
			exp_channel_fields.field_type');
		$this->db->from('exp_channel_fields');
		$this->db->join('exp_channels', 'exp_channels.field_group = exp_channel_fields.group_id');
		if( is_numeric($this->settings["import"]["channel"]) ) {
			$this->db->where( 'channel_id', $channel );
		} else {
			$this->db->where( 'channel_name', $channel );
			$this->db->where('exp_channel_fields.site_id', $this->config->item('site_id') );
		}
		$query = $this->db->get();
		$field_ids = '';
		
		$custom_fields = array();
		foreach ( $query->result_array() as $row ) {
			$custom_fields[ $row[ "field_name" ] ][ 'id' ] = $row[ "field_id" ];
			$custom_fields[ $row[ "field_name" ] ][ 'format' ] = $row[ "field_fmt" ];
			$custom_fields[ $row[ "field_name" ] ][ 'type' ] = $row[ "field_type" ];
		}
		
		return $custom_fields;
	}

	/**
	 * Get default settings for a channel
	 *
	 * @param string $channel channel_id
	 * @return array $channel_default array of channel default settings
	 */
	function _fetch_channel_defaults( $channel ) {

		$this->db->select('channel_id, site_id, channel_title, channel_url, 
			rss_url, deft_comments, 
			deft_status, cat_group, field_group, url_title_prefix');
		$this->db->from('exp_channels');
		if( is_numeric($this->settings["import"]["channel"]) ) {
			$this->db->where( 'channel_id', $channel );
		} else {
			$this->db->where( 'channel_name', $channel );
			$this->db->where( 'site_id', $this->config->item('site_id') );
		}
		$query = $this->db->get();
		$channel_defaults = $query->row_array();

		return $channel_defaults;
	}

	/**
	 * Check to see if the current user is logged in with the privileges to perform import, 
	 * if not create a dummy user (used when import is not run from the Control Panel)
	 *
	 */
	function _check_member_status() {

		// If not currently logged in, create a dummy session
		// @todo: currently hard-coded, need to search db for admin user?
		$this->current_user = $this->session->userdata['member_id'];
		if( $this->session->userdata['member_id'] == 0) {
			$this->session->create_new_session(1, TRUE);
			//$this->session->userdata['username']  = "dummy";
			$this->session->userdata['group_id']  = 1;
			$this->session->userdata['can_edit_other_entries'] = 'y';
			$this->session->userdata['can_delete_self_entries'] = 'y';
			$this->session->userdata['can_delete_all_entries'] = 'y';
		}

	}

	/**
	 * Find which author to assign to this entry
	 *
	 * @param array $item current row of data from data source
	 * @return integer $author_id the id of the author to assign to this entry
	 */
	function _fetch_author( $item ) {
		
		// Default author
		$author_id = $this->settings["config"]["author"]; 
		
		// Data field that contains author information
		$author_field = isset( $this->settings["config"]["author_field"] ) ? 
			$this->settings["config"]["author_field"] : ''; 

		// Which field to check: screen name, username, email?		
		$author_check = isset( $this->settings["config"]["author_check"] ) ? 
			$this->settings["config"]["author_check"] : ''; 

		if( $author_check == "member_id" ) {
			$author_check = "exp_members.member_id";
		}

		// Get author id from data if specified
		if ( $author_field != "" && $author_check != "" ) {
			$this->db->select( 'exp_members.member_id' );
			$this->db->from( 'exp_members' );
			$this->db->join( 'exp_member_data', 'exp_members.member_id = exp_member_data.member_id' );
			$this->db->where( $author_check, $this->datatype->get_item( $item, $author_field ) );
			$query = $this->db->get();
			if( $query->num_rows() > 0 ) {
				$row = $query->row_array();
				$author_id = $row["member_id"];
			}
		}
		
		return $author_id;
	}

	/**
	 * Find status to assign to this entry
	 *
	 * @param array $item current row of data from data source
	 * @return string $status status to assign to entry
	 */
	function _fetch_status( $item ) {
		$status = $this->channel_defaults["deft_status"];
		if( isset( $this->settings["config"][ "status" ] ) ) {
			switch( $this->settings["config"]["status"] ) {
				case "default":
				$status = $this->channel_defaults["deft_status"];
				break;
				case "open":
				case "closed":
				$status = $this->settings["config"]["status"];
				break;
				default:
				$status = $this->datatype->get_item( $item, $this->settings["config"]["status"] );
			}
			
			// fetch valid settings from db
			if( !is_array( $this->valid_statuses ) ) {
				$this->valid_statuses = array();
				$this->db->select( "status" );
				$this->db->from( "exp_statuses s" );
				$this->db->join( "exp_channels c", "c.status_group = s.group_id" );
				if( is_numeric($this->settings["import"]["channel"]) ) {
					$this->db->where( 'c.channel_id', $this->settings["import"]["channel"] );
				} else {
					$this->db->where( 'c.channel_name', $this->settings["import"]["channel"] );
					$this->db->where( 'c.site_id', $this->config->item('site_id') );
				}
				$this->db->order_by( "status_order ASC" );
				$query = $this->db->get();
				foreach( $query->result_array() as $row ) {
					$this->valid_statuses[ $row["status"] ] = ucfirst($row["status"]);
				}
			}
			
			// check id setting is a valid custom status for this channel
			if( in_array( $this->settings["config"]["status"], $this->valid_statuses ) ) {
				$status = $this->settings["config"]["status"];
			}
			
		}
		return $status;
	}

	/**
	 * Find a list of categories to assign to this entry and create any that don't exist
	 *
	 * @param array $item current row of data from data source
	 * @return array $entry_categories list of category ids
	 */
	function _setup_categories( $item, $entry_id = FALSE ) {
	
		$c_groups = '';
		if( isset( $this->settings["config"][ "c_groups" ] ) && $this->settings["config"][ "c_groups" ] != "" ) {
			$c_groups = $this->settings["config"][ "c_groups" ];
		}
				
		$entry_categories = array();
		
		$used_groups = array();
		foreach( explode("|", $c_groups) as $cat_group_id ) {
		
			// Find categories from custom field and create if necessary
			$cat_field = '';
			if( isset( $this->settings["config"][ "cat_field_".$cat_group_id ] ) && $this->settings["config"][ "cat_field_".$cat_group_id ] != "" ) {
				$cat_field = $this->datatype->get_item( $item, $this->settings["config"][ "cat_field_".$cat_group_id ] );
				$used_groups[] = $cat_group_id;
			}
			$cat_delimiter = '';
			if( isset( $this->settings["config"][ "cat_delimiter_".$cat_group_id ] ) && $this->settings["config"][ "cat_delimiter_".$cat_group_id ] != "" ) {
				$cat_delimiter = $this->settings["config"][ "cat_delimiter_".$cat_group_id ];
			}

			$new = $this->_find_and_create_categories( 
				isset( $this->settings["config"][ "cat_default_".$cat_group_id ] ) ? $this->settings["config"][ "cat_default_".$cat_group_id ] : '',
				$cat_field,
				$cat_delimiter,
				$cat_group_id,
				$this->channel_defaults["site_id"]
			);
		
			$entry_categories = array_merge( $entry_categories, $new );
		
		}
		
		if( $entry_id !== FALSE ) {
			// Doing an update, so find existing categories
			$this->db->select( "exp_category_posts.cat_id" );
			$this->db->where( "entry_id", $entry_id );
			if( count( $used_groups ) > 0 ) {
				$this->db->where_not_in( "group_id", $used_groups );
			}
			$this->db->join("exp_categories", "exp_category_posts.cat_id = exp_categories.cat_id");
			$query = $this->db->get( "exp_category_posts" );
			foreach( $query->result_array() as $row ) {
				$entry_categories[] = $row["cat_id"];
			}

		}
		
		return $entry_categories;
	}

	/**
	 * Import comments
	 *
	 * @param integer $entry_id id of the new entry
	 * @param array $item current row of data from data source
	 * @return integer the number of comments added
	 */
	function _import_comments( $entry_id, $item ) {
		
		// @note: xml only at the moment?
		// @todo: add more error checking here, eg, missing/empty fields
		// @todo: only works for new imports, not updates at the moment

		// Are there any comments for this entry?
		$no_comments = $this->datatype->get_item( $item, $this->settings["config"]["comment_body"] . "#" );
		
		if( $no_comments > 0 ) {
		
			// If so, loop over the XML and insert as new comments
			for( $i=0; $i<$no_comments; $i++ ) {
				$field = $this->settings["config"]["comment_body"];
				if ( $i > 0 ) {
					$suffix = '#' . ($i+1);
				} else {
					$suffix = '';
				}
				
				$name = $this->datatype->get_item( $item, $this->settings["config"]["comment_author"] . $suffix);
				if( is_array( $name ) ) {
					$name="";
				}
				
				$data = array(
					"site_id" => $this->channel_defaults["site_id"],
					"entry_id" => $entry_id,
					"channel_id" => $this->channel_defaults["channel_id"],
					"author_id" => 0,
					"status" => "o",
					"name" => $name,
					"email" => $this->datatype->get_item( $item, $this->settings["config"]["comment_email"] . $suffix ),
					"url" => $this->datatype->get_item( $item, $this->settings["config"]["comment_url"] . $suffix ),
					"location" => "",
					"ip_address" => "127.0.0.1",
					"comment_date" => $this->_parse_date( 
						$this->datatype->get_item( $item, $this->settings["config"]["comment_date"] . $suffix )
					),
					"comment" => $this->datatype->get_item( $item, $this->settings["config"]["comment_body"] . $suffix )
				);
				$sql = $this->db->insert_string('exp_comments', $data);
				$this->db->query($sql);
			}
			
			// Do stats
			
			$this->db->select( "COUNT(comment_id) as count" );
			$this->db->where( "status", "o" );
			$this->db->where( "entry_id", $entry_id );
			$channel_comments_count = $this->db->get( "exp_comments" );
			
			$this->db->select( "MAX(comment_date) as date" );
			$this->db->where( "status", "o" );
			$this->db->where( "entry_id", $entry_id );
			$this->db->order_by( "comment_date", "desc" );
			$channel_comments_recent = $this->db->get( "exp_comments" );
			
			$data = array();
			if ($channel_comments_count->num_rows() > 0) {
				$row = $channel_comments_count->row_array();
				$data["comment_total"] = $row["count"];
			}
			if ($channel_comments_recent->num_rows() > 0) {
				$row = $channel_comments_recent->row_array();
				$data["recent_comment_date"] = $row["date"];
			}
			if( count( $data ) > 0 ) {
				$this->db->where('entry_id', $entry_id );
				$this->db->update('exp_channel_titles', $data );
			}
			
		}
		
		return $no_comments;
	}

	/**
	 * Delete any old entries
	 *
	 * @param array $entries a list of entries to keep (ie, have just been added)
	 * @return integer the number of deleted entries
	 */
	function _delete_old_entries( $entries ) {
		
		$this->db->select( "entry_id" );
		$this->db->where_not_in( "entry_id", $entries ); 
		$this->db->where( 'channel_id = ', $this->channel_defaults["channel_id"] ); 
		$query = $this->db->get( "exp_channel_titles" );
	
		$delete_ids = array();
		foreach ($query->result() as $row)
		{
		    $delete_ids[] = $row->entry_id;
		}
	
		if( count( $delete_ids ) ) {
			$this->api->instantiate('channel_entries');
			$ret = $this->api_channel_entries->delete_entry( $delete_ids );
			if( $ret == FALSE ) {
				foreach( $this->api_channel_entries->errors as $eid => $error ) {
					$this->return_data .= "<p>Error: " . $error . " " . $eid . "</p>";
				}
			}
		}
		
		return count( $delete_ids );
	}

	/**
	 * Delete entries with a timestamp older than specified
	 *
	 * @param string $timestamp 
	 * @param string $duration 
	 * @return void
	 * @author Andrew Weaver
	 */
	function _delete_by_timestamp( $timestamp, $field, $duration=86400 ) {

		$this->db->select( "exp_channel_titles.entry_id" );
		$this->db->from( "exp_channel_titles" );
		$this->db->join( "exp_channel_data", "exp_channel_titles.entry_id = exp_channel_data.entry_id" );		
		$this->db->where( 'exp_channel_titles.channel_id = ', $this->channel_defaults["channel_id"] ); 
		$this->db->where( $field .' < ', $timestamp-$duration );
		$query = $this->db->get();
	
		$delete_ids = array();
		foreach ($query->result() as $row)
		{
		    $delete_ids[] = $row->entry_id;
		}
	
		if( count( $delete_ids ) ) {
			$this->api->instantiate('channel_entries');
			$ret = $this->api_channel_entries->delete_entry( $delete_ids );
			if( $ret == FALSE ) {
				foreach( $this->api_channel_entries->errors as $eid => $error ) {
					$this->return_data .= "<p>Error: " . $error . " " . $eid . "</p>";
				}
			}
		}
		
		return count( $delete_ids );

	}

	/**
	 * Try to read the date and return as timestamp
	 *
	 * @param string $datestr 
	 * @return int the date
	 */
	function _parse_date( $datestr ) {
		
		//$datestr = preg_replace("/^\s*([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{2,4})/", "\\2/\\1/\\3", $datestr);
		
		// $datestr = str_replace('/', '-', $datestr);
		
		if( is_numeric($datestr) ) {
			return $datestr;
		}
		$date = strtotime( $datestr );
		if ( $date == -1 ) {
			$date = parse_w3cdtf( $datestr );
		}
		if ( $date == -1 || $date == "" ) {
			$date = time();
		}
		return( $date );
	}

	/**
	 * Test whether an entry is unique
	 *
	 * @param array $post 
	 * @param string $unique 
	 * @param array $weblog_to_feed 
	 * @return entry id or 0 if there is no match
	 */
	function _is_entry_unique( $data, $unique, $weblog_to_feed ) {
		
		// If no unique field is provided, always create a new entry

		if( is_array( $unique ) ) {	
			// Remove empty elements
			$unique = array_diff( $unique, array('') );
			if( count( $unique ) == 0 ) {
				return 0;
			}
		}
		
		if ( $unique == "" ) {
			return 0;
		} 
		
		if ( $unique == "title,date" ) {

			$sql = "SELECT * FROM exp_channel_titles 
			WHERE LEFT(title,100) = LEFT('".$this->db->escape_str($data[ "title" ])."',100) AND entry_date = '".$this->db->escape_str($data[ "entry_date" ])."'";
			$query = $this->db->query($sql);

		} else {

			// Build custom query
			$sql = "SELECT * 
			FROM exp_channel_titles t, exp_channel_data d 
			WHERE t.channel_id = \"" . $this->channel_defaults["channel_id"] . "\" 
			AND t.entry_id = d.entry_id";

			// $uniqueArray = explode(",", $unique);
			
			if( is_array( $unique ) ) {
				$uniqueArray = $unique; 
			} else  {
				$uniqueArray = explode( ",", $unique ); 
		 	} 
			   
			foreach ( $uniqueArray as $value ) {
				switch ( $value ) {
					case '': {
						break;
					}
					case 'title': {
						$sql .= " AND " . $value . "=\"" . $this->db->escape_str( $data[ $value ] ) . "\"";
						break;
					}
					case 'date': {
						$sql .= " AND entry_date=\"" . $this->db->escape_str( $data[ "entry_date" ] ) . "\"";
						break;
					}
					default: {
						$name = "field_id_" . $weblog_to_feed[ $value ][ "id" ];
						$sql .= " AND " . $name . "=\"" . $this->db->escape_str( $data[ "field_id_" . $weblog_to_feed[ $value ][ "id" ] ] ) . "\"";
					}
				}
			}

			$query = $this->db->query( $sql );

		}

		// Return matching entry id or zero if no match
		$return_id = 0;
		if ( $query->num_rows > 0) {
			$row = $query->row();
			$return_id = $row->entry_id;
		}
		$query->free_result();

		return $return_id;
	}

	/**
	 * Create a list of categories for an entry
	 *
	 * @param string $cat_default Contains default category name or id
	 * @param string $cat_field Contains category values or ids
	 * @param string $cat_delimiter Delimiter used to split categories in single field
	 * @param string $cat_group Category group id
	 * @param string $site_id Site id
	 * @return array category ids to add to entry
	 * @author Andrew Weaver
	 */
	function _find_and_create_categories( $cat_default, $cat_field, $cat_delimiter="", $cat_group, $site_id ) {

		$entry_categories = array();
		
		if ( $cat_default != "" ) {
			if( is_numeric( $cat_default ) ) {
				// Assume numeric categories are cat_id's
				$entry_categories[] = $cat_default;
			} else {
				$entry_categories[] = $this->_create_category( $cat_default, $cat_group, $site_id );
			}
		}
		
		if ( $cat_field != '' ) {

			if ( $cat_delimiter != "" ) {
				$cats = explode( $cat_delimiter, $cat_field );
			} else {
				$cats = array( $cat_field );
			}
			
			// Remove duplicates (after trimming whitespace)
			foreach( $cats as $idx => $cat ) {
				$cats[ $idx ] = trim( $cat );
			}
			$cats = array_unique( $cats );
			
			// Add category to database
			foreach( $cats as $cat ) {
				if( is_numeric( $cat ) ) {
					// Assume numeric categories are cat_id's
					$entry_categories[] = $cat;
				} else {
					// $entry_categories[] = $this->_create_category( $cat, $cat_group, $site_id );
					// todo: allow sub-categories to created using another delimiter
					$delim_cats = explode('/', $cat);
					$parent_id = 0;
					if ( count( $delim_cats ) == 1 ) {
						$entry_categories[] = $this->_create_category( $cat, $cat_group, $site_id );
					}
					foreach( $delim_cats as $dcat ) {
						$c_id = $this->_create_category( $dcat, $cat_group, $site_id, $parent_id );
						$entry_categories[] = $c_id;
						$parent_id = $c_id;
					}
				}
			}
		}
		
		return $entry_categories;
	}
	
	/**
	 * Create a category
	 *
	 * @param string $category_name 
	 * @param string $category_group 
	 * @return void
	 */
	function _create_category( $category_name, $category_group="", $site_id=1, $parent_id=0 ) {

		// Does this category already exist?
		$category_name = trim($category_name);
		
		$this->db->select( "*" );
		$this->db->where( "cat_name", $category_name );
		$this->db->where( "group_id",  $category_group );
		if( $parent_id != 0 ) {
			$this->db->where( "parent_id",  $parent_id );
		}
		$query = $this->db->get( "exp_categories" );

		if ( $query->num_rows == 0) {
			
			// Category does not exist so create it
			// todo: use Category model here
			
			$insert_array = array(
				'group_id' => $category_group,
				'site_id' => $site_id,
				'cat_name' => $category_name,
				'cat_url_title' => url_title( $category_name, $this->config->item('word_separator'), TRUE ),
				'cat_image' => '',
				'parent_id' => $parent_id
				);
			$this->db->query($this->db->insert_string('exp_categories', $insert_array));
			$category_id = $this->db->insert_id();
			
			 $insert_array = array(
				'cat_id'  	=> $category_id,
				'site_id' 	=> $site_id,
				'group_id' 	=> $category_group
			);
			$this->db->query($this->db->insert_string('exp_category_field_data', $insert_array));
			
			$this->return_data .= "<p>Add category: " . $category_name . " to group " . $category_group . "</p>\n";
			
			return $category_id;
			
		} else {

			// Category already exists, so return its id
			$row = $query->row();
			return $row->cat_id;

		}
	}

	/**
	 * Add categories to an entry
	 *
	 * @param string $entry_id 
	 * @param string $cat_id 
	 * @return void
	 */
	function _add_entry_to_category( $entry_id, $cat_id ) {
		$this->db->query("INSERT IGNORE INTO exp_category_posts (entry_id, cat_id) VALUES ('".$entry_id."', '".$cat_id."')");
		if ($this->config->item('auto_assign_cat_parents') == 'y')
		{	
			$query = $this->db->query("SELECT parent_id FROM exp_categories WHERE cat_id = " . $cat_id);
			$row = $query->row();
			if( $row->parent_id != 0 ) {
				$this->_add_entry_to_category( $entry_id, $row->parent_id );
			}
		}
	}
	
	/**
	 * Fetch a field's fieldtype settings
	 *
	 * @param string $field_id 
	 * @return void
	 * @author Andrew Weaver
	 */
	function _get_channel_fields_settings( $field_id ) {

		if( isset( $this->field_settings[ $field_id ] ) ) return;

		// @todo: cache this
		$this->db->where( 'field_id', $field_id );
		$field_query = $this->db->get('exp_channel_fields');

		foreach ($field_query->result_array() as $row) {

			$field_data = '';
			$field_fmt = '';

			$field_fmt	= $row['field_fmt'];

			$settings = array(
				'field_instructions' => trim($row['field_instructions']),
				'field_text_direction' => ($row['field_text_direction'] == 'rtl') ? 'rtl' : 'ltr',
				'field_fmt' => $field_fmt,
				'field_data' => $field_data,
				'field_name' => 'field_id_'.$field_id,
			);

			$ft_settings = array();
			
			if (isset($row['field_settings']) && strlen($row['field_settings'])) {
				$ft_settings = unserialize(base64_decode($row['field_settings']));
			}

			$settings = array_merge($row, $settings, $ft_settings);

			$this->api_channel_fields->set_settings($field_id, $settings);
			
		}
		
		$this->field_settings[ $field_id ] = $field_id;
		
	}

	function _prepare_update_data( $entry_id, &$data, $custom_fields ) {

		/* EE channel api blanks any fields that don't have a value set 
			
			"as part of the data normalization, custom data with a value of NULL 
			is transformed to an empty string before database insertion."
			
			From: http://expressionengine.com/user_guide/development/api/api_channel_entries.html
			
			Api_channel_entries.php fills the empty fields in 2 places: 
			_base_prep() and _update_entry()
			to obey mysql strict mode 
			(see: http://dev.mysql.com/doc/refman/5.0/en/server-sql-mode.html)

			Options:
			
			1) Don't allow partial updates (or rather use default EE behaviour of clearing data)
			2) Load data array with old data (requires knowledge of 3rd party fieldtypes and is
			   liable to change between fieldtype versions)
			3) Do some tricks with extensions (may not be possible)
			4) Overide update routine with DataGrab's own function 
			   (likely to be EE version specific)
			
		*/

		//print_r( $data ); print_r( $this->settings ); 
		//print_r( $custom_fields ); exit;
		
		$this->db->select( "*" );
		$this->db->from( "exp_channel_titles" );
		$this->db->join( "exp_channel_data", "exp_channel_data.entry_id=exp_channel_titles.entry_id" );
		$this->db->where( "exp_channel_titles.entry_id", $entry_id );
		$query = $this->db->get();
		$row = $query->row_array();

		if( !isset( $data["title"] ) || $data["title"] == "" ) {
			$data["title"] = $row["title"];
		}

		if( !isset($this->settings["config"][ "date" ]) || $this->settings["config"][ "date" ] == "" ) {
			$data["entry_date"] = $row["entry_date"];
		}

		if( !isset($this->settings["config"][ "url_title" ]) || $this->settings["config"][ "url_title" ] == "" ) {
			$data["url_title"] = $row["url_title"];
		}

		if( $this->settings["config"]["status"] == "default" ) {
			$data["status"] = $row["status"];
		}

		if( !isset( $this->settings["config"]["author_field"] ) || $this->settings["config"]["author_field"] == "" ) {
			$data["author_id"] = $row["author_id"];
		}
		
		foreach( $custom_fields as $field_name => $field ) {
			/*
			print_r( $field );
			Array(
			    [id] => 5
			    [format] => none
			    [type] => textarea
			) 
			*/
			
			if( !isset( $data[ "field_id_".$field["id"] ] ) ) {
				
				// Check to see if a handler exists for this field type
				if ( ! class_exists('Datagrab_fieldtype') ) {
					require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_fieldtype'.EXT;
				}	
				if ( ! class_exists('Datagrab_'.$field[ "type" ] ) ) {
					if( file_exists( PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field[ "type" ].EXT ) ) {
						require_once PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field[ "type" ].EXT;
					}
				}	
			
				// If so, call the rebuild_post_data method to format the $data array
				if ( class_exists('Datagrab_'.$field[ "type" ]) ) {
					$classname = "Datagrab_".$field[ "type" ];
					$ft = new $classname();
					$ft->rebuild_post_data( $this, $field["id"], $data, $row );
				} else {
					// If no handler exists, just use the value
					$data[ "field_id_" . $field["id"] ] = $row[ "field_id_".$field["id"] ];
				}
			
			}
		
		}
		// print_r( $data ); exit;
		
	}
	
	function _get_file( $filename, $filedir=1, $fetch_url=FALSE ) {
	
		// Is it in the correct format already?
		if( preg_match('/{filedir_([0-9]+)}/', $filename, $matches) ) {
			return $filename;
		}	
		
		// Is it a filename?
		if( ! preg_match('/http+/', $filename, $matches) ) {
			return "{filedir_" . $filedir . "}" . $filename;
		}	
		
		// Is it a url
		$url = parse_url( $filename );
		if( isset( $url["scheme"] ) ) {

			$this->load->library('filemanager');
			$this->filemanager->xss_clean_off();
		
			$basename = basename($filename);
			$file_path = $this->filemanager->clean_filename(
				$basename, 
				$filedir,
				array('ignore_dupes' => TRUE)
			);
			// If ignore_dupes = FALSE, basename may change
			// $basename = basename($file_path);

			// Does file laready exist?
			if( file_exists( $file_path ) ) {
				// File already exists
				return '{filedir_' . $filedir . '}' . $basename;
			}
		
			if( $fetch_url === TRUE ) {
				// Fetch contents of url
				$content = @file_get_contents( $filename );
				if( $content === FALSE ) {
					// cannot fetch file
					return FALSE;
				}
			
				if( file_put_contents($file_path, $content) === FALSE ) {
					// error copying file to filedir
					return FALSE;
				}
			
				$result = $this->filemanager->save_file(
					$file_path, 
					$filedir, 
					array(
						'title'     => $basename,
						'path'      => dirname($file_path),
						'file_name' => $basename
					)
				);
		
				// Check to see the result
				if ($result['status'] === FALSE) {
					// file not saved
					return FALSE;
				}
				
				return '{filedir_' . $filedir . '}' . $basename;
			}
					
		}	
		
		return FALSE;		
	}
	
		
	/*
		HELPER FUNCTIONS
	*/

	function _check_mem_usage( $label, $display=TRUE ) {
		$mem_usage = memory_get_usage();
		// print "<p>" . $label . ": " . $mem_usage . " (" . number_format( $mem_usage - $this->mem_usage, 0, '.', ',' ) . ")" . "</p>";
		if( $display ) {
			$this->TMPL->log_item('DataGrab: ' . $label . ": " . $mem_usage . " (" . number_format( $mem_usage - $this->mem_usage, 0, '.', ',' ) . ")" );
		}
		$this->mem_usage = $mem_usage;
	}
	
	function _tic( $timer="simple" ) {
		global $TIC;
		$TIC[ $timer ] = microtime(TRUE);
	}
	
	function _toc( $timer="simple" ) {
		global $TIC;
		if( isset( $TIC[ $timer ] ) ) {
			$time = microtime(TRUE) - $TIC[ $timer ];
			return $time;
		}
		return -1;
	}

}

?>