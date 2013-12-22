<?php

define('DATAGRAB_URL', BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ajw_datagrab');
define('DATAGRAB_PATH', 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ajw_datagrab');

/**
 * DataGrab MCP Class
 *
 * DataGrab Module Control Panel class to handle all CP requests
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Ajw_datagrab_mcp {
	
	var $version = '1.8.0';
	var $module_name = "AJW_Datagrab";
	
	var $settings;
	
	function Ajw_datagrab_mcp() {
		$this->EE =& get_instance();
		
		$this->EE->load->model('datagrab_model', 'datagrab');
		
		// Global right hand side navigation
		$this->EE->cp->set_right_nav(array(
			'Documentation' => "http://brandnewbox.co.uk/support/category/datagrab"
		));

	}
	
	/*
	
	CONTROLLER FUNCTIONS
	
	*/
	
	function index() {
		
		// Clear session data
		$this->_get_session('settings');

		// Set page title
		if(version_compare(APP_VER, '2.6', '>=')) {
			$this->EE->view->cp_page_title = "DataGrab";
		} else {
			$this->EE->cp->set_variable('cp_page_title', "DataGrab");
		}
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		// Set data
		$data["title"] = "DataGrab";
		$data["content"] = 'index';
		
		$data["types"] = $this->EE->datagrab->fetch_datatype_names();

		$data["action_url"] = $this->EE->functions->fetch_site_index(0, 0) . QUERY_MARKER . 'ACT=' . $this->EE->cp->fetch_action_id('Ajw_datagrab', 'run_action') . AMP . "id=";

		$this->EE->db->select('id, name, description, passkey');
		$this->EE->db->where('site_id', $this->EE->config->item('site_id') );
		$this->EE->db->order_by('id ASC');
		$query = $this->EE->db->get('exp_ajw_datagrab');
		$data["saved_imports"] = array();
		foreach($query->result_array() as $row) {
			$id = $row["id"];
			$row["name"] = '<a href="'.DATAGRAB_URL.AMP.'method=save'.AMP.'id='.$row["id"].'">' . $row["name"] . '</a>';
			$row[] = '<a href="'.DATAGRAB_URL.AMP.'method=load'.AMP.'id='.$row["id"].'">Configure</a>';
			$row[] = '<a href="'.DATAGRAB_URL.AMP.'method=run'.AMP.'id='.$row["id"].'">Run import</a>';
			$row[] = '<a class="passkey" href="'.$data["action_url"].$id.( $row["passkey"] != '' ? AMP.'passkey='.$row["passkey"] : '' ).'">Import URL</a>';
			$row[] = '<a href="'.DATAGRAB_URL.AMP.'method=delete'.AMP.'id='.$row["id"].'">Delete</a>';
			unset( $row["passkey"] );
			$data["saved_imports"][ $id ] = $row;
		}

		$data["form_action"] = DATAGRAB_PATH.AMP.'method=settings';
		
		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function settings() {
		
		// Handle form input
		$this->_get_input();

		// Set page title
		if(version_compare(APP_VER, '2.6', '>=')) {
			$this->EE->view->cp_page_title = "Settings";
		} else {
			$this->EE->cp->set_variable('cp_page_title', "Settings");
		}

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb( DATAGRAB_URL, $this->EE->lang->line('ajw_datagrab_module_name') );
		
		// $this->cp->add_to_head('<style type="text/css">.tablesize{height:45px!important;}</style>');
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		// Set data
		$data["title"] = "Settings";
		$data["content"] = 'settings';
		
		// Fetch channel name
		$this->EE->db->select('channel_id, channel_title');
		$this->EE->db->where('site_id', $this->EE->config->item('site_id') );
		$query = $this->EE->db->get('exp_channels');
		$data["channels"] = array();
		foreach($query->result_array() as $row) {
			$data["channels"][$row["channel_id"]] = $row["channel_title"];
		}
		$data["channel"] = isset( $this->settings["import"]["channel"] ) ? 
			$this->settings["import"]["channel"] : '';
		
		// Get settings form for type
		$this->EE->datagrab->initialise_types();
		$data["settings"] = $this->EE->datagrab->datatypes[ 
			$this->settings["import"]["type"] ]->settings_form( $this->settings );

		// Form action URL
		$data["form_action"] = DATAGRAB_PATH.AMP.'method=check_settings';
		
		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function check_settings() {

		// Handle form input
		$this->_get_input();

		// Set page title
		if(version_compare(APP_VER, '2.6', '>=')) {
			$this->EE->view->cp_page_title = "Check Settings";
		} else {
			$this->EE->cp->set_variable('cp_page_title', "Check Settings");
		}

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb( DATAGRAB_URL, $this->EE->lang->line('ajw_datagrab_module_name') );

		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		// Set data
		$data["title"] = "Check Settings";
		$data["content"] = 'check_settings';

		$data["rows"] = array();
		$data["errors"] = array();

		$this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->initialise( $this->settings );
		$ret = $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->fetch();
		if( $ret != -1 ) {
			$titles = $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->fetch_columns();
			if( $titles === FALSE ) {
				$data["errors"] = $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->errors;			
			}
			if( $titles != "" ) {
				foreach( $titles as $key => $value ) {
					$data["rows"][] = array( $value );
				}
			}
		} else {
			$data["errors"] = $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->errors;
		}

		// Form action URL
		$data["form_action"] = DATAGRAB_PATH.AMP.'method=configure_import';

		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function configure_import() {
		
		// Handle form input
		
		$this->_get_input();
	
		// Set page title
		
		if(version_compare(APP_VER, '2.6', '>=')) {
			$this->EE->view->cp_page_title = "Configure Import";
		} else {
			$this->EE->cp->set_variable('cp_page_title', "Configure Import");
		}
		

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb( DATAGRAB_URL, $this->EE->lang->line('ajw_datagrab_module_name') );

		// Load helpers
		
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		// Set data
		
		$data["title"] = "Configure Import";
		$data["content"] = 'configure_import';

		// Get custom fields for the selected channel
		
		$this->EE->db->select("field_group, cat_group");
		if( is_numeric($this->settings["import"]["channel"]) ) {
			$this->EE->db->where( 'channel_id', $this->settings["import"]["channel"] );
		} else {
			$this->EE->db->where( 'channel_name', $this->settings["import"]["channel"] );
			$this->EE->db->where('site_id', $this->EE->config->item('site_id') );
		}
		$query = $this->EE->db->get( 'exp_channels' );
		$row = $query->row_array();
		$field_group = $row["field_group"];
		$cat_group = $row["cat_group"];
	
		$this->EE->db->select( 'field_name, field_label, field_type, field_settings' );
		$this->EE->db->where( 'group_id', $field_group );
		$this->EE->db->order_by( 'field_order' );
		$query = $this->EE->db->get( 'exp_channel_fields' );
		
		$data["custom_fields"] = array();
		$data["unique_fields"] = array();
		$data["field_settings"] = array();
		$data["unique_fields"][ "" ] = "";
		$data["unique_fields"][ "title" ] = "Title";
		$data["field_types"] = array();

		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$data["custom_fields"][ $row["field_name"] ] = $row["field_label"];
				$data["unique_fields"][ $row["field_name"] ] = $row["field_label"];
				$data["field_types"][ $row["field_name"] ] = $row["field_type"];
				$data["field_settings"][ $row["field_name"] ] = unserialize(base64_decode( $row["field_settings"] ));
			}
		}

		$this->EE->db->select( 'field_name, field_label' );
		$this->EE->db->where( 'group_id', $field_group );
		$query = $this->EE->db->get( 'exp_channel_fields' );
		
		// Get category groups
		
		$this->EE->db->select( 'group_id, group_name' );
		$this->EE->db->where_in( 'group_id', explode( "|", $cat_group ) );
		$query = $this->EE->db->get( 'exp_category_groups' );
		
		$data["category_groups"] = array();
		// $data["category_groups"][ 0 ] = "";
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$data["category_groups"][ $row["group_id"] ] = $row["group_name"];
			}
		}
		
		// Get list of fields from the datatype

		// print_r( $this->settings );

		$this->EE->datagrab->initialise_types();
		$this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->initialise( $this->settings );
		$this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->fetch();
		$data["data_fields"][""] = "";
		$fields = $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->fetch_columns();
		foreach( $fields as $key => $value ) {
			$data["data_fields"][ $key ] = $value;
		}

		// Get list of authors
		// @todo: filter this list by member groups
		
		$data["authors"] = array();

		$this->EE->db->select( 'member_id, screen_name' );
		// $this->EE->db->where( 'group_id', "1" );
		$query = $this->EE->db->get( 'exp_members' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$data["authors"][ $row["member_id"] ] = $row["screen_name"];
			}
		}
		
		$data["author_fields"] = array(
			"member_id" => "ID",
			"username" => "Username",
			"screen_name" => "Screen Name",
			"email" => "Email address"
		);
		
		$this->EE->db->select( "m_field_id, m_field_label" );
		$this->EE->db->from( "exp_member_fields" );
		$this->EE->db->order_by( "m_field_order ASC" );
		$query = $this->EE->db->get();
		if( $query->num_rows() > 0 ) {
			$member_fields = array();
			foreach( $query->result_array() as $row ) {
				$member_fields["m_field_id_" . $row["m_field_id"] ] = $row["m_field_label"];
			}
			$data["author_fields"]["Custom Fields"] = $member_fields;
		}
		
		
		// Get statuses
		
		$data["status_fields"] = array(
			"default" => "Channel default"
		);
		
		$this->EE->db->select( "status" );
		$this->EE->db->from( "exp_statuses s" );
		$this->EE->db->join( "exp_channels c", "c.status_group = s.group_id" );
		if( is_numeric($this->settings["import"]["channel"]) ) {
			$this->EE->db->where( 'c.channel_id', $this->settings["import"]["channel"] );
		} else {
			$this->EE->db->where( 'c.channel_name', $this->settings["import"]["channel"] );
			$this->EE->db->where( 'c.site_id', $this->EE->config->item('site_id') );
		}
		$this->EE->db->order_by( "status_order ASC" );
		$query = $this->EE->db->get();
		foreach( $query->result_array() as $row ) {
			$data["status_fields"][ $row["status"] ] = ucfirst($row["status"]);
		}
		
		$data["status_fields"] = array_merge( $data["status_fields"], $data["data_fields"] );
		
		// Allow comments - check datatype ?
		
		$allow_comments = isset( $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->datatype_info["allow_comments"] ) ? 
			$this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->datatype_info["allow_comments"] : FALSE;

		// $this->EE->cp->load_package_js('ajw_datagrab');

		if( $allow_comments ) {			
			$data["allow_comments"] = TRUE;
		} else {
			$data["allow_comments"] = FALSE;
		}
		
		// Allow multiple fields?
		$data["allow_multiple_fields"] = isset( $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->datatype_info["allow_multiple_fields"] ) ? 
			$this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->datatype_info["allow_multiple_fields"] : FALSE;
		
		// Pages module
		if (array_key_exists('pages', $this->EE->addons->get_installed('modules'))) {
			$data["pages_installed"] = TRUE;
		} else {
			$data["pages_installed"] = FALSE;
		}
		
		// SEO Lite
		if (array_key_exists('seo_lite', $this->EE->addons->get_installed('modules'))) {
			$data["seo_lite_installed"] = TRUE;
		} else {
			$data["seo_lite_installed"] = FALSE;
		}
		
		$this->EE->db->select( 'field_id, field_label, group_name' );
		$this->EE->db->join( 'exp_field_groups', 'exp_field_groups.group_id = exp_channel_fields.group_id' );
		$this->EE->db->order_by( 'group_name, 	field_order' );
		$query = $this->EE->db->get( 'exp_channel_fields' );
		
		$data["all_fields"] = array();
		$data["all_fields"]["title"] = "Title";
		$data["all_fields"]["exp_channel_titles.entry_id"] = "Entry ID";
		$data["all_fields"]["exp_channel_titles.url_title"] = "URL Title";

		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$data["all_fields"][ $row["group_name"] ][ "field_id_".$row["field_id"] ] = $row["field_label"];
			}
		}
		
		
		// Default settings
		
		if( isset ( $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->config_defaults ) ) {
			foreach( $this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ]->config_defaults as $field => $value ) {
				if( !isset( $this->settings[ $field ] ) ) {
					$this->settings["config"][ $field ] = $value;
				}
			}
		}
		$data['default_settings'] = $this->settings;
		
		$data["cf_config"] = array();
		
		// Build configuration table for custom fields
		foreach( $data["custom_fields"] as $field_name => $field_label ) {

			$field_type = $data["field_types"][ $field_name ];

			if ( ! class_exists('Datagrab_fieldtype') ) {
				require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_fieldtype'.EXT;
			}	
			if ( ! class_exists('Datagrab_'.$field_type ) ) {
				if( file_exists( PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_type.EXT ) ) {
					require_once PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$field_type.EXT;
				}
			}	
			
			if ( class_exists('Datagrab_'.$field_type) ) {
				$classname = "Datagrab_".$field_type;
			} else {
				$classname = "Datagrab_fieldtype";
			}
			$ft = new $classname();
			$data["cf_config"][] = $ft->display_configuration( 
				$field_name, $field_label, $field_type, $data 
			);
		}
		
		// Form action URL
		
		$data["form_action"] = DATAGRAB_PATH.AMP.'method=import';
		$data["back_link"] = DATAGRAB_URL.AMP.'method=settings';
		$data["save_action"] = DATAGRAB_PATH.AMP.'method=save';

		if( $this->EE->input->get( "id" ) ) {
			$data["id"] = $this->EE->input->get( "id" );
		}

		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}


	function import() {

		if( $this->EE->input->post("save") !== FALSE ) {
			return $this->save();
			exit;
		}
		
		$this->_get_input();

		// Set page title
		if(version_compare(APP_VER, '2.6', '>=')) {
			$this->EE->view->cp_page_title = "Results";
		} else {
			$this->EE->cp->set_variable('cp_page_title', "Results");
		}
		

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb( DATAGRAB_URL, $this->EE->lang->line('ajw_datagrab_module_name') );

		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		// Set data
		$data["title"] = "Results";
		$data["content"] = 'results';
		
		// $this->settings = array_merge( $this->settings, $_POST );

		// Allow modifications via get variables
		if( $this->EE->input->get('skip') !== FALSE ) {
			$this->settings["datatype"]["skip"] = $this->EE->input->get('skip');
		}
		if( $this->EE->input->get('limit') !== FALSE ) {
			$this->settings["import"]["limit"] = $this->EE->input->get('limit');
		}
		
		$this->EE->datagrab->initialise_types();
		$data["results"] = $this->EE->datagrab->do_import( 
			$this->EE->datagrab->datatypes[ $this->settings["import"]["type"] ], 
			$this->settings 
			);

		// Set variables for batch imports
		$data["batch"] = $this->EE->datagrab->batch_limit_completed;
		$data["skip"] = isset($this->settings["datatype"]["skip"]) ? $this->settings["datatype"]["skip"] : 0;
		$data["limit"] = isset($this->settings["import"]["limit"]) ? $this->settings["import"]["limit"] : "";
		$data["batch_action"] = DATAGRAB_URL.AMP.'method=import';
		$data['cp_theme_url'] = $this->EE->config->slash_item('theme_folder_url').'cp_themes/default/';

		// Form action URL
		if( isset( $this->settings["import"]["id"] ) ) {
			$data["id"] = $this->settings["import"]["id"];
		} else {
			$data["id"] = 0;
		}
		
		// Form action URL
		$data["form_action"] = DATAGRAB_PATH.AMP.'method=save';
		
		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
		
	}

	function save() {
		
		$id = $this->EE->input->get_post("id", 0);
		
		$this->_get_input();
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		$this->EE->cp->set_breadcrumb( DATAGRAB_URL, $this->EE->lang->line('ajw_datagrab_module_name') );

		// Set data
		if ( $id == 0 ) {
			
			if(version_compare(APP_VER, '2.6', '>=')) {
				$this->EE->view->cp_page_title = "Save import";
			} else {
				$this->EE->cp->set_variable('cp_page_title', "Save import");
			}
			$data["title"] = "Save import";
			$name = "";
			$description = "";
			$passkey = "";

		} else {

			if(version_compare(APP_VER, '2.6', '>=')) {
				$this->EE->view->cp_page_title = "Update import";
			} else {
				$this->EE->cp->set_variable('cp_page_title', "Update import");
			}
			
			$data["title"] = "Update import";
			
			$this->EE->db->where('id', $id );
			$query = $this->EE->db->get('exp_ajw_datagrab');
			$row = $query->row_array();
			
			$name = $row["name"];
			$description = $row["description"];
			$passkey = $row["passkey"];
			
		}
		
		$data["content"] = 'save';
		
		$data["form"] = array(
			array( 
				'<em class="required">*</em> ' .
				form_label('Name', 'name') .
				'<div class="subtext">A title for the import</div>',  
				form_input(
					array(
						'name' => 'name',
						'id' => 'name',
						'value' => $name,
						'size' => '50'
						)
					) 
				),
			array( 
				form_label('Description', 'description') .
				'<div class="subtext">A description of the import</div>', 
				form_textarea(
					array(
						'name' => 'description',
						'id' => 'description',
						'value' => $description,
						'rows' => '4',
						'cols' => '64'
						)
					)
				),
			array( 
				form_label('Passkey', 'passkey') . 
				'<div class="subtext">Add a passkey to increase security against<br/>saved imports being run inadvertently</div>', 
				form_input(
					array(
						'name' => 'passkey',
						'id' => 'passkey',
						'value' => $passkey,
						'style' => 'width:50%'
						)
					) . NBS . 
					form_button(
						array(
							'id' => 'generate', 
							'name' => 'generate',
							'content' => 'Generate random key'
						) 
					)
				) 
		);

		$data["id"] = $id;
		
		// Form action URL
		$data["form_action"] = DATAGRAB_PATH.AMP.'method=do_save';
		
		$this->EE->load->library('javascript');
		$this->EE->javascript->output('
			var chars = "0123456789ABCDEF";
			var string_length = 32;
		$("#generate").click( function() {
			var randomstring = "";
			for (var i=0; i<string_length; i++) {
				var rnum = Math.floor(Math.random() * chars.length);
				randomstring += chars.substring(rnum,rnum+1);
			}
			$("#passkey").val(randomstring);
		});
		');
		$this->EE->javascript->compile();
		
		
		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function do_save() {

		$this->_get_input();

		$this->EE->load->helper('date');

		$id = $this->EE->input->post("id");

		$data = array(
			'name' => $this->EE->input->post( "name" ),
			'description' => $this->EE->input->post( "description" ),
			'passkey' => $this->EE->input->post( "passkey" ),
			'last_run' => now()
		);
		
		if( isset( $this->settings["import"]["type"] ) ) {
			$data['settings'] = serialize( $this->settings );
		} else {
			// Fetch settings from database
			$this->EE->db->select('settings');
			$this->EE->db->where('id', $id);
			$query = $this->EE->db->get('exp_ajw_datagrab');
			$row = $query->row_array();
			$data['settings'] = $row["settings"];
			$this->settings = unserialize( $data['settings'] );
		}

		// Get site_id from channel label
		$this->EE->db->select('site_id');
		if( is_numeric($this->settings["import"]["channel"]) ) {
			$this->EE->db->where( 'channel_id', $this->settings["import"]["channel"] );
		} else {
			$this->EE->db->where( 'channel_name', $this->settings["import"]["channel"] );
			$this->EE->db->where('site_id', $this->EE->config->item('site_id') );
		}
		$query = $this->EE->db->get('exp_channels');
		$channel_defaults = $query->row_array();
		$data["site_id"] = $channel_defaults["site_id"];
		
		if( $id == "" OR $id == "0" ) {
			$this->EE->db->insert('exp_ajw_datagrab', $data);
		} else {
			$this->EE->db->where('id', $id );
			$this->EE->db->update('exp_ajw_datagrab', $data);	
		}

		$this->EE->session->set_flashdata('message_success', "Import saved.");

		$this->EE->functions->redirect(DATAGRAB_URL.AMP."method=index"); 
		
	}

	function load() {

		if ( $this->EE->input->get( "id" ) != 0 ) {
			$this->EE->db->where('id', $this->EE->input->get( "id" ) );
			$query = $this->EE->db->get('exp_ajw_datagrab');
			$row = $query->row_array();
			$this->settings = unserialize($row["settings"]);
			$this->settings["import"]["id"] = $this->EE->input->get( "id" );
			$this->_set_session( 'settings', serialize( $this->settings ) );
		}

		$this->EE->functions->redirect(DATAGRAB_URL.AMP."method=configure_import".AMP."id=".$this->EE->input->get( "id" )); 
	}

	function run() {

		if ( $this->EE->input->get( "id" ) != 0 ) {
			$this->EE->db->where('id', $this->EE->input->get( "id" ) );
			$query = $this->EE->db->get('exp_ajw_datagrab');
			$row = $query->row_array();
			$this->settings = unserialize($row["settings"]);
			$this->settings["import"]["id"] = $this->EE->input->get( "id" );
			$this->_set_session( 'settings', serialize( $this->settings ) );
		}

		$this->EE->functions->redirect(DATAGRAB_URL.AMP."method=import"); 
	}

	function delete() {
		
		$id = $this->EE->input->get( "id" );

		// Set page title
		if(version_compare(APP_VER, '2.6', '>=')) {
			$this->EE->view->cp_page_title = "Confirm delete";
		} else {
			$this->EE->cp->set_variable('cp_page_title', "Confirm delete");
		}
		

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb( DATAGRAB_URL, $this->EE->lang->line('ajw_datagrab_module_name') );

		// Load helpers
		$this->EE->load->helper('form');

		// Set data
		$data["title"] = "Confirm delete";
		$data["content"] = 'delete';
		
		$data["id"] = $id;
		
		// Form action URL
		$data["form_action"] = DATAGRAB_PATH.AMP.'method=do_delete';
		
		// Load view
		return $this->EE->load->view('_wrapper', $data, TRUE);
		
	}

	function do_delete() {
		
		$id = $this->EE->input->post("id");

		if( $id != "" && $id != "0" ) {
			$this->EE->db->where('id', $id );
			$this->EE->db->delete('exp_ajw_datagrab');	
		}
		
		$this->EE->session->set_flashdata('message_success', "Deleted");

		$this->EE->functions->redirect(DATAGRAB_URL.AMP."method=index");
		
	}

	/* 
	
	HELPER FUNCTIONS
	
	*/

	/**
	 * Add $data to user session
	 *
	 * @param string $key 
	 * @param string $data 
	 * @return void
	 */
	function _set_session( $key, $data ) {
		@session_start();
		if ( !isset( $_SESSION[ $this->module_name ] ) ) {
			$_SESSION[ $this->module_name ] = array();
		}
		$_SESSION[ $this->module_name ][ $key ] = $data;
	}

	/**
	 * Retrieve data from session. Data is removed from session unless $keep is
	 * set to TRUE
	 *
	 * @param string $key 
	 * @param string $keep 
	 * @return void $data
	 */
	function _get_session( $key, $keep = FALSE ) {
		@session_start();  
		if( isset( $_SESSION[ $this->module_name ] ) ) {
			if( isset( $_SESSION[ $this->module_name ][ $key ] ) ) {
				$data = $_SESSION[ $this->module_name ][ $key ];
				if ( $keep != TRUE ) {
		    	unset($_SESSION[ $this->module_name ][ $key ]); 
		    	unset($_SESSION[ $this->module_name ]); 
				}
				return( $data );
			}
		}
		return "";
	}

	/**
	 * Handle input from forms, sessions
	 * 
	 * Collects data from forms, query strings and sessions. Only keeps relevant data
	 * for the current import data type. Stores in session to allow back-and-forth
	 * through 'wizard'
	 *
	 */
	function _get_input() {
		
		// Get current settings from session
		$this->settings = unserialize( $this->_get_session( 'settings' ) );

		$datagrab_step = $this->EE->input->get_post("datagrab_step", "default");
		switch( $datagrab_step ) {

			// Step 1: choose import type
			case "index": {
				$this->settings["import"]["type"] = $this->EE->input->get_post("type");
				break;
			}

			// Step 2: set up datatype
			case "settings": {
				$this->settings["import"]["channel"] = 
					$this->EE->input->get_post("channel");
				// Check datatype specific settings
				if( isset( $this->settings["import"]["type"] ) && 
					$this->settings["import"]["type"] != "" ) {
					$this->EE->datagrab->initialise_types();
					$datatype_settings = $this->EE->datagrab->datatypes[ 
						$this->settings["import"]["type"] ]->settings;
					foreach( $datatype_settings as $option => $value ) {
						if( $this->EE->input->get_post( $option ) !== FALSE ) {
							$this->settings["datatype"][ $option ] = 
								$this->EE->input->get_post( $option );
						}
					}
				}
				break;
			}
			
			case "configure_import": {
				
				$allowed_settings = array(
					"type",
					"channel",
					"update",
					"unique",
					"author",
					"author_field",
					"author_check",
					"offset",
					"title",
					"url_title",
					"date",
					"expiry_date",
					"timestamp",
					"delete_old",
					"delete_by_timestamp",
					"delete_by_timestamp_duration",
					"cat_default",
					"cat_field",
					"cat_group",
					"cat_delimiter",
					"id",
					"status",
					"import_comments",
					"comment_author",
					"comment_email",
					"comment_date",
					"comment_url",
					"comment_body",
					"ajw_entry_id",
					"c_groups"
					);

				// Look through permitted settings, check whether a new POST var exists, and update
				foreach( $allowed_settings as $setting ) {
					if( $this->EE->input->post( $setting ) !== FALSE ) {
						$this->settings["config"][ $setting ] = $this->EE->input->post( $setting );
					}
				}

				if( $this->EE->input->post( "limit" ) !== FALSE ) {
					$this->settings["import"][ "limit" ] = $this->EE->input->post( "limit" );
				}

				
				// Hack to handle checkboxes (whose post vars are not set if unchecked)
				// todo: improve this - use hidden field?
				if( $this->EE->input->get("method") == "import" ) {
					$checkboxes = array("update", "delete_old", "import_comments");
					foreach( $checkboxes as $check ) {
						if( !isset( $this->settings["config"][ $check ] ) ) {
							$this->settings["config"][ $check ] = $this->EE->input->post( $check );
						}
					}
				}
				
				// Get category group details
				$cat_settings = array(
					"cat_default",
					"cat_field",
					"cat_delimiter"
				);
				$c_groups = $this->EE->input->post("c_groups");
				foreach( explode("|", $c_groups) as $cat_group_id ) {
					foreach( $cat_settings as $cs ) {
						$setting = $cs . "_" . $cat_group_id;
						if( $this->EE->input->post( $setting ) !== FALSE ) {
							$this->settings["config"][ $setting ] = $this->EE->input->post( $setting );
						}
					}
				}
				
				// Check for custom field settings
				if( isset($this->settings["import"]["channel"]) && $this->settings["import"]["channel"] != "" ) {

					$this->EE->db->select('field_name, field_type');
					$this->EE->db->from('exp_channel_fields');
					$this->EE->db->join('exp_channels', 'exp_channels.field_group = exp_channel_fields.group_id');
					if( is_numeric($this->settings["import"]["channel"]) ) {
						$this->EE->db->where( 'channel_id', $this->settings["import"]["channel"] );
					} else {
						$this->EE->db->where( 'channel_name', $this->settings["import"]["channel"] );
					}
					$query = $this->EE->db->get();

					// Look through field types and see if they need to register any extra variables
					foreach ( $query->result_array() as $row ) {

						if( $this->EE->input->post( $row["field_name"] ) !== FALSE ) {
							$this->settings["cf"][ $row["field_name"] ] = $this->EE->input->post( $row["field_name"] );
						}

						// Do we need to save any extra settings information?
						if ( ! class_exists('Datagrab_fieldtype') ) {
							require_once PATH_THIRD.'ajw_datagrab/libraries/Datagrab_fieldtype'.EXT;
						}	
						if ( ! class_exists('Datagrab_'.$row["field_type"] ) ) {
							if( file_exists( PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$row[ "field_type" ].EXT ) ) {
								require_once PATH_THIRD.'ajw_datagrab/fieldtypes/datagrab_'.$row[ "field_type" ].EXT;
							}
						}	
						
						if ( class_exists('Datagrab_'.$row[ "field_type" ]) ) {
							$classname = "Datagrab_".$row[ "field_type" ];
							$ft = new $classname();
							$type_settings = $ft->register_setting( $row["field_name"] );
							foreach( $type_settings as $fld ) {
								if( $this->EE->input->post( $fld ) !== FALSE ) {
									$this->settings["cf"][ $fld ] = $this->EE->input->post( $fld );
								}
							}
						} 

					}

				}

				// Pages configuration
				if (array_key_exists('pages', $this->EE->addons->get_installed('modules'))) {
					$this->settings["cf"][ "ajw_pages" ] = $this->EE->input->post( 'ajw_pages' );
					$this->settings["cf"][ "ajw_pages_url" ] = $this->EE->input->post( 'ajw_pages_url' );
					$this->settings["cf"][ "ajw_pages_template" ] = $this->EE->input->post( 'ajw_pages_template' );
				}

				// SEO Lite configuration
				if (array_key_exists('seo_lite', $this->EE->addons->get_installed('modules'))) {
					$this->settings["cf"][ "ajw_seo_lite_title" ] = $this->EE->input->post( 'ajw_seo_lite_title' );
					$this->settings["cf"][ "ajw_seo_lite_keywords" ] = $this->EE->input->post( 'ajw_seo_lite_keywords' );
					$this->settings["cf"][ "ajw_seo_lite_description" ] = $this->EE->input->post( 'ajw_seo_lite_description' );
				}

				break;
			}
			

			default: {
			}

		}

		// Get saved import id
		if( $this->EE->input->get( "id" ) !== FALSE ) {
			$this->settings["import"][ "id" ] = $this->EE->input->get_post( "id" );
		}

		// print_r( $this->settings ); exit;

		// Store settings in session
		$this->_set_session( 'settings', serialize( $this->settings ) );
	}

	function clear() {
		$this->_set_session( 'settings', serialize( array() ) );
	}

}

/* End of file mcp.ajw_datagrab.php */