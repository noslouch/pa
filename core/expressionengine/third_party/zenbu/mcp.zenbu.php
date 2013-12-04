<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * =======
 *  Zenbu
 * =======
 * See more data in your control panel entry listing
 * @version 	1.8.5.2
 * @copyright 	Nicolas Bottari - Zenbu Studio 2011-2013
 * @author 		Nicolas Bottari - Zenbu Studio
 * ------------------------------ 
 * 
 * *** IMPORTANT NOTES ***
 * I (Nicolas Bottari and Zenbu Studio) am not responsible for any
 * damage, data loss, etc caused directly or indirectly by the use of this add-on.
 * @license		See the license documentation (text file) included with the add-on.
 *
 * Description
 * -----------
 * Zenbu is a powerful and customizable entry list manager similar to 
 * ExpressionEngine's Edit Channel Entries section in the control panel. 
 * Accessible from Content » Edit, Zenbu enables you to see, all on the same page:
 * Entry ID, Entry title, Entry date, Author name, Channel name, Live Look, 
 * Comment count, Entry status URL Title, Assigned categories, Sticky state, 
 * All (or a portion of) custom fields for the entry, etc
 * 
 * @link	http://zenbustudio.com/software/zenbu/
 * @link	http://zenbustudio.com/software/docs/zenbu/
 * 
 * Special thanks to Koen Veestraeten (StudioKong) for his excellent bug reporting during the initial 1.x beta
 * @link	http://twitter.com/#!/studiokong
 *
 */

class Zenbu_mcp {
	
	var $default_limit = 25;
	var $addon_short_name = 'zenbu';
	var $permissions = array(
			'can_admin', 
			'can_copy_profile', 
			'can_access_settings', 
			'edit_replace', 
			'can_view_group_searches', 
			'can_admin_group_searches'
		);
	var $non_ft_extra_options = array(
			"date_option_1" 	=> "date_option_1",
			"date_option_2"		=> "date_option_2",
			"view_count_1" 		=> "view_count_1",
			"view_count_2" 		=> "view_count_2",
			"view_count_3" 		=> "view_count_3",
			"view_count_4" 		=> "view_count_4",
			"livelook_option_1" => "livelook_option_1",
			"livelook_option_2" => "livelook_option_2",
			"livelook_option_3" => "livelook_option_3",
			"category_option_1"	=> "category_option_1",
		);
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Zenbu_mcp()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->site_id = $this->EE->session->userdata['site_id'];
		$this->member_group_id = $this->EE->session->userdata['group_id'];
		$this->member_id = ($this->EE->session->cache('zenbu', 'member_id')) ? $this->EE->session->cache('zenbu', 'member_id') : $this->EE->session->userdata['member_id'];
		$this->dbprefix = $this->EE->db->dbprefix;
		$this->cp_call = (REQ == 'CP') ? TRUE : FALSE;
		$this->local_time = version_compare(APP_VER, '2.6', '>') ? $this->EE->localize->now : $this->EE->localize->set_localized_time();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Main Page
	 *
	 * @access	public
	 */
	function index($limit = '', $perpage = '')
	{
		// Get settings from database
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_display');
		$this->EE->load->model('zenbu_db');
		$this->EE->load->helper('loader');
		$settings = $this->EE->zenbu_get->_get_settings();
		$assigned_data = $this->EE->zenbu_get->get_assigned_data();

		// Set flash data if index is called with arguments
		if( ! empty($limit) ) { $this->EE->session->set_cache('zenbu', 'limit', $limit); }
		if( ! empty($perpage) ) { $this->EE->session->set_cache('zenbu', 'perpage', $perpage); }
		
		$this->EE->lang->loadfile('content', 'cp');	// We'll need a few strings from there
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper(array('form', 'date', 'text'));
		
		/**
		*	If there are no channels and in the CP, let's stop
		*/
		if( empty($assigned_data['channels']) && $this->cp_call === TRUE )
		{
			$output['message'] = $this->EE->lang->line("no_channels_exist");
			return $this->EE->load->view('zenbu_message', $output, TRUE);
		}
		
		
		// Fetch default settings to be used:
		$max_results_per_page = (isset($settings['setting']['general']['max_results_per_page'])) ? $settings['setting']['general']['max_results_per_page'] : '';
		$default_filter = (isset($settings['setting']['general']['default_1st_filter'])) ? $settings['setting']['general']['default_1st_filter'] : "cat_id";
		$default_order = (isset($settings['setting']['general']['default_order'])) ? $settings['setting']['general']['default_order'] : "entry_date";
		$default_sort = (isset($settings['setting']['general']['default_sort'])) ? $settings['setting']['general']['default_sort'] : "desc";

		$this->EE->session->set_cache('zenbu', 'max_results_per_page', $max_results_per_page);
		
		/**
		* Retrieve rules based on rule_id as GET variable or on rules arrray as POST variables
		*/
		if($this->EE->input->get_post('rule_id', TRUE))
		{
			$rule_id = $this->EE->input->get_post('rule_id', TRUE);
			$rules = $this->EE->zenbu_get->_get_search_rules($rule_id);
			
			//	Repackage limit, order and sort values
			$other_rules = array("limit" => "limit", "orderby" => "default_order", "sort" => "default_sort");
			foreach($other_rules as $elem => $var)
			{
				if(isset($rules[$elem]))
				{
					// This will be passed in entry_get function
					$settings['setting']['general'][$var] = $rules[$elem];
					// This is used to refresh the default values above
					$$var = $rules[$elem];
					unset($rules[$elem]);
				}
			}
			
		} elseif($this->EE->input->get_post('return_to_zenbu', TRUE) == "y") {
			
			if (session_id() == '')
			{
				session_start();

			}
			
			if(isset($_SESSION['zenbu']['rule']))
			{
				$rules = unserialize($_SESSION['zenbu']['rule']);
				
				//unset($_SESSION['zenbu']['rule']);
			} else {
				$rules = $this->EE->input->get_post('rule', TRUE);
				$rules = ($rules === FALSE) ? array() : $rules;
			}
			
			//	Repackage limit, order and sort values
			$other_rules = array("limit" => "limit", "orderby" => "default_order", "sort" => "default_sort", "perpage" => "perpage");
			foreach($other_rules as $elem => $var)
			{
				if(isset($_SESSION['zenbu'][$elem]))
				{
					// This will be passed in entry_get function
					$settings['setting']['general'][$var] = unserialize($_SESSION['zenbu'][$elem]);
					// This is used to refresh the default values above
					$$var = unserialize($_SESSION['zenbu'][$elem]);
					//unset($_SESSION['zenbu'][$elem]);
					
				}
			}
			
		} else {
			$rules = $this->EE->input->get_post('rule', TRUE);
			$rules = ($rules === FALSE) ? array() : $rules;

			/**
			*	Using method elsewhere
			*/
			if(empty($rules) && $this->EE->input->get('module') != 'zenbu')
			{
				if (session_id() == '')
				{
					session_start();

				}
				
				if(isset($_SESSION['zenbu']['rule']))
				{
					$rules = unserialize($_SESSION['zenbu']['rule']);
					
					//unset($_SESSION['zenbu']['rule']);
				} else {
					$rules = $this->EE->input->get_post('rule', TRUE);
					$rules = ($rules === FALSE) ? array() : $rules;
				}
			}
		}
		
		
		
		// ------------------------------------
		// If &channel_id= *ONLY* is specified
		// ------------------------------------
		if($this->EE->input->get_post('channel_id', TRUE) && empty($rules))
		{
			$channel_id[] = $this->EE->input->get_post('channel_id', TRUE);
			
			// Reset to zenbu's standard page if site_id has changed and channel_id is not part of the site
			$channel_id = $this->EE->zenbu_get->_verify_site_channel($channel_id);
			
			$rules = array(
				"0" => array(
					"field" 	=> "channel_id",
					"cond" 		=> "is",
					"val"		=> $channel_id[0],
				),
				"1" => array(
					"field" 	=> $default_filter,
					"cond" 		=> "is",
					"val"		=> "",
				),
			);
		// ------------------------------------
		// If &channel_id= is not specified
		// ------------------------------------
		} else {
			
			// If there are established rules
			if( ! empty($rules))
			{
				foreach($rules as $rule)
				{
					if(isset($rule['field']))
					{
						switch ($rule['field'])
						{
							case 'channel_id':
								$channel_id[] = $rule['val'];
							break;
							case 'cat_id':
								$cat_id[] = $rule['val'];
							break;
						}
					}
				}
			// If there are no established rules, eg. fresh page load
			} else {
				$channel_id[] = 0;
				$cat_id[] = 0;
				
				$rules = array(
					"0" => array(
						"field" 	=> "channel_id",
						"cond" 		=> "is",
						"val"		=> $channel_id[0],
					),
					"1" => array(
						"field" 	=> $default_filter,
						"cond" 		=> "is",
						"val"		=> "",
					),
				);
			}
		}
		
		// If really, you still don't know the $channel_id, 
		// give it an array value of 0
		$channel_id = isset($channel_id) ? $channel_id : array(0);
		  
		$fields_from_channel = $this->EE->zenbu_get->_get_field_ids();

		//	----------------------------------------
		//	Set up select option type 
		//	(eg. a date-style option, an option that's 
		//	"contains/does not contain" only, etc )
		//	----------------------------------------
		$vars_fields_option_types['option_type'] = $fields_from_channel['field_option_type'];
		
		$vars_fields = array();
		
		$vars_order['field_order'] = array();
		
		foreach($channel_id as $key => $channel_id_val)
		{
			// If a new channel was freshly created, no settings exist yet, at all.
			// Instead of showing just a column of checkboxes,
			// show at least the entry_id and title
			if( ! isset($settings['setting'][$channel_id_val]) )
			{
				$settings['setting'][$channel_id_val] = array(
						'show_id' => 'y',
						'show_title' => 'y',
						'field_order' => array(
							'show_id' => 1,
                    		'show_title' => 2,
                    	),
					);
			}

			if($channel_id_val != "" && isset($settings['setting'][$channel_id_val]['field_order']))
			{
				$field_order = $settings['setting'][$channel_id_val]['field_order'];
			} else {
				$field_order = isset($settings['setting']['0']['field_order']) ? $settings['setting']['0']['field_order'] : array();
			}
			$field_order = array_flip($field_order);
			$vars_order['field_order'] = $field_order;
			
			// Get field_ids of fields to show
			$fields_to_show = (isset($settings['setting'][$channel_id_val]['show_custom_fields']) && !empty($settings['setting'][$channel_id_val]['show_custom_fields'])) ? explode('|', $settings['setting'][$channel_id_val]['show_custom_fields']) : array(); // array of field set to be shown
			
			// Prune fields not to be shown
			$vars_fields = array();
			$has_any_cf_title = find_rule('field', 'any_cf_title', $rules);
			if( ! empty($fields_from_channel['field']))
			{
				$field_attrib = array("id", "field", "fieldtype", "field_text_direction");
				// Field ID and ID, labels and ID, fieldtype and ID, field text direction and ID
				foreach($field_attrib as $key => $attr)
				{
					foreach($fields_from_channel[$attr] as $field_id => $field_id_again)
					{			
						if( ($channel_id_val != 0 && in_array($field_id, $fields_to_show)) || 
							($channel_id_val == 0 && $has_any_cf_title === TRUE) ) {
							$vars_fields[$attr][$field_id] = $field_id_again;
						}
					}
				}
			}
			
		}
		
		//	----------------------------------------
		//	Putting data together
		//	----------------------------------------
		$installed_addons	= $this->EE->zenbu_get->_get_installed_addons();
		$vars_channels		= $this->EE->zenbu_get->_get_channel_data($this->member_group_id);
		$vars_categories	= $this->EE->zenbu_get->_get_category_dropdowns($installed_addons);

		//	----------------------------------------
		//	Block channel access if a channel is 
		//	selected, but not allowed for the user
		//	----------------------------------------
		if( count($channel_id) == 1 && ! array_key_exists($channel_id[0], $vars_channels['channels']['channel_data']))
		{
			show_error($this->EE->lang->line('unauthorized_access_channel'));
		}

		//	----------------------------------------
		//	Saved searches
		//	----------------------------------------
		$saved_searches							= $this->EE->zenbu_get->_get_saved_searches();
		$vars_saved_searches['saved_searches']	= $this->EE->zenbu_display->_display_saved_searches($saved_searches);
		
		//	----------------------------------------
		//	Reduce unnecessary queries if ajax request
		//	---------------------------------------- 
		if(AJAX_REQUEST)
		{
			$vars_authors							= array();
			$vars_statuses							= array();
			$vars_custom_fields						= array();
			
			$vars_first_dropdown_labels				= array($settings);
			$vars_channel_first_dropdown_labels		= array();
			$vars_second_dropdown_labels			= array();
			$vars_channel_second_dropdown_labels	= array();

		} else {

			$vars_authors							= $this->EE->zenbu_get->_get_author_dropdowns();
			$vars_statuses							= $this->EE->zenbu_get->_get_status_dropdowns();
			$custom_fields							= $this->EE->zenbu_get->_get_field_ids();
			$vars_custom_fields['custom_fields']	= empty($custom_fields) ? array() : $custom_fields['custom_fields'];
			
			// $vars_first_dropdown_labels also contains dropdowns for "order by"
			$vars_first_dropdown_labels				= $this->EE->zenbu_get->_get_first_and_orderby_dropdown($settings);
			$vars_channel_first_dropdown_labels		= $this->EE->zenbu_get->_get_channel_first_dropdown();
			$vars_second_dropdown_labels			= $this->EE->zenbu_get->_get_second_dropdown();
			$vars_channel_second_dropdown_labels	= $this->EE->zenbu_get->_get_second_dropdown();
		}
		
		/**
		* ====================================
		* Main function fetching entry results
		* ====================================
		*/
		$vars_entries = $this->EE->zenbu_get->_get_entry_data($settings, $vars_fields, $rules, $channel_id, $vars_channels, $vars_categories, $fields_from_channel); // THE function
		
		$vars_general_purpose = $this->EE->zenbu_get->_get_general_form_variables();

		$vars_other['hide_categories']			= ( ! empty($vars_categories['categories']['disabled'])) ? 'y' : 'n';
		

		/**
		*	======================================
		*	Extension Hook zenbu_after_save_search
		*	======================================
		*
		*	Enables the addition of extra code after the "Save this search" link
		*	@return string 	$vars_other['extra_options_right_save']	The output HTML
		*
		*/
		if ($this->EE->extensions->active_hook('zenbu_after_save_search') === TRUE)
		{
			$vars_other['extra_options_right_save'] = $this->EE->extensions->call('zenbu_after_save_search');
			if ($this->EE->extensions->end_script === TRUE) return;
		} else {
			$vars_other['extra_options_right_save'] = '';
		}

		//	----------------------------------------
		//	Determining the selected limit in dropdown
		//	----------------------------------------
		if(isset($limit) && ! empty($limit))
		{
			// $limit is set if data comes from SESSION data
			$vars_rules['limit_val'] = $limit;

		} elseif(isset($settings['setting']['general']['default_limit'])) {

			$vars_rules['limit_val'] = $settings['setting']['general']['default_limit'];
		
		} else {
		
			$vars_rules['limit_val'] = $this->default_limit;
		
		}
		$vars_rules['orderby_val']			= $default_order; // Is modified from default if data comes from SESSION data
		$vars_rules['sort_val']				= $default_sort; // Is modified from default if data comes from SESSION data
		
		$vars_rules['rules'] = $rules;
		
		// Add fieldtype data from all fields. 
		// Used to determine the second dropdown style (contain/does not contain, etc)
		// Nice to have the whole set available from all fields, just in case
		$vars_all_fields['fieldtype'] = isset($fields_from_channel['fieldtype']) ? $fields_from_channel['fieldtype'] : array();

		$vars = array_merge(
			$vars_fields,
			$vars_fields_option_types,
			$vars_general_purpose,
			$vars_all_fields, 
			$vars_entries, 
			$vars_channels, 
			$vars_categories, 
			$vars_authors, 
			$vars_statuses, 
			$vars_order, 
			$vars_other, 
			$vars_first_dropdown_labels, 
			$vars_channel_first_dropdown_labels, 
			$vars_channel_second_dropdown_labels, 
			$vars_second_dropdown_labels,
			$vars_custom_fields,
			$vars_rules,
			$vars_saved_searches,
			$installed_addons
			);
			
		/**
		*	===========================================
		*	Extension Hook zenbu_modify_data_array
		*	===========================================
		*
		*	Modifies the data array containing most of Zenbu's 
		*	settings and output data. This data is used for the result view
		*	@param  $vars 	array 		The data array before modification 
		*	@return $vars 	array		The modified data array
		*/
		if ($this->EE->extensions->active_hook('zenbu_modify_data_array') === TRUE)
		{
			$vars = $this->EE->extensions->call('zenbu_modify_data_array', $vars);
		 	if ($this->EE->extensions->end_script === TRUE) return;
		}

		//	----------------------------------------
		//	Loading a few scripts and setting in CP
		//	----------------------------------------
		if($this->cp_call)
		{
			//	----------------------------------------
			//	JS Scripts
			//	----------------------------------------
			$this->EE->load->add_package_path(PATH_THIRD.'zenbu'); // Sometimes the wrong package is loaded. This makes sure the zenbu package is loaded.
			$this->EE->cp->add_js_script(array('plugin' => array('dataTables','tablesorter')));
			$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
			$this->EE->cp->load_package_js('typewatch');
			$this->EE->cp->load_package_js('zenbu_index');
			$this->EE->cp->load_package_js('zenbu_script');

			//	----------------------------------------
			//	Stylesheets
			//	----------------------------------------
			$this->EE->zenbu_display->set_head_stylesheets();
			
			//	----------------------------------------
			//	CP Page Title
			//	----------------------------------------
			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = $this->EE->lang->line('edit_channel_entries');
			} else {
				$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('edit_channel_entries'));
			}
			
			//	----------------------------------------
			//	Top Right Navigation
			//	----------------------------------------
			$nav_array = array();
		
			if($settings['setting']['can_access_settings'] == 'y' || $this->member_group_id == 1) {
				$nav_array['<i class=\'icon-cog\'></i> '.$this->EE->lang->line('display_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings".AMP."channel_id=".$channel_id[0];
			}
			if($settings['setting']['can_admin'] == 'y' || $this->member_group_id == 1) {
				$nav_array['<i class=\'icon-group\'></i> '.$this->EE->lang->line('member_access_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
			}

			$this->EE->cp->set_right_nav($nav_array);
		}
		
		if( ($this->EE->input->get_post('module') && $this->EE->input->get_post('module') != 'zenbu') ||
			 $this->cp_call === FALSE )
		{
			return $vars;
		}

		if(AJAX_REQUEST)
		{
			return $this->EE->load->view('_results_ajax', $vars, TRUE);
		} else {		
			return $this->EE->load->view('zenbu_index', $vars, TRUE);
		}

	} // END index()

	// --------------------------------------------------------------------

	
	/*
	*	function save_rules_by_session
	*	Store filter/search rules temporarily as a session variable for later retrieval,
	*	in particular in redirection back to Zenbu
	*/
	function save_rules_by_session()
	{
		if (session_id() == '')
		{
			session_start();
		}
		
		// Just passing by. Would be a shame to lose that XID just for that.
		if(version_compare(APP_VER, '2.7', '>='))
		{
			$this->EE->security->restore_xid();
		}

		$filter_elements = array("rule", "limit", "orderby", "sort", "perpage");
		foreach($filter_elements as $elem)
		{
			$$elem = $this->EE->input->get_post($elem, TRUE);
			$_SESSION['zenbu'][$elem] = serialize($$elem); 
		}
	} // END save_rules_by_session()

	// --------------------------------------------------------------------

	
	/*
	*	function multi_edit
	*	Build view for Zenbu's multi-entry editor
	*/
	function multi_edit()
	{
		
		$this->EE->load->model(array('zenbu_get', 'zenbu_display'));
		$this->EE->lang->loadfile('content', 'cp');	// We'll need a few string from there
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper(array('form', 'date', 'text', 'display_helper'));
		$this->EE->load->add_package_path(PATH_THIRD.'zenbu'); // Sometimes the wrong package is loaded. This makes sure the zenbu package is loaded.
		$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
		$this->EE->cp->load_package_js('zenbu_script');
		$this->EE->cp->load_package_js('multi_edit');

		//	----------------------------------------
		//	Set stylesheets
		//	----------------------------------------		
		$this->EE->zenbu_display->set_head_stylesheets();
		
		$entry_ids = $this->EE->input->get_post('toggle', TRUE);
		$status_header_dropdown = array();
		
		switch ($this->EE->input->get_post('action', TRUE))
		{
		case "edit":

			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = lang('multi_entry_editor');
			} else {
				$this->EE->cp->set_variable('cp_page_title', lang('multi_entry_editor'));
			}

			$this->EE->javascript->output('
				theDate = new Date();
				theDate_hours = theDate.getHours();
				theDate_mins = theDate.getMinutes();
	
				if (theDate_mins < 10) { theDate_mins = "0" + theDate_mins; }
	
				if (theDate_hours > 11) {
					theDate_hours = theDate_hours - 12;
					theDate_am_pm = " PM";
				} else {
					theDate_am_pm = " AM";
				}
	
				theDate_time = " \'"+theDate_hours+":"+theDate_mins+theDate_am_pm+"\'";
			');
			$this->EE->javascript->compile();
			
			$this->EE->db->where_in('entry_id', $entry_ids);
			$this->EE->db->where('site_id', $this->site_id);
			if($this->EE->session->userdata['can_edit_other_entries'] != 'y')
			{
				$this->EE->db->where('author_id', $this->member_id);
			}
			$query = $this->EE->db->get('channel_titles');
			if($query->num_rows() > 0)
			{
				
				foreach($query->result_array() as $row)
				{
					$entry_id = $row['entry_id'];
					$channel_id = $row['channel_id'];
					
					$hidden['entry_id['.$entry_id.']'] = $entry_id;
					$hidden['channel_id['.$entry_id.']'] = $channel_id;
					
					$vars_entry['entry'][$entry_id]['entry_id'] = $entry_id;
					$vars_entry['entry'][$entry_id]['title'] = form_input('title['.$entry_id.']', $row['title'], 'style="width: 96%;"');
					$vars_entry['entry'][$entry_id]['url_title'] = form_input('url_title['.$entry_id.']', $row['url_title'], 'style="width: 96%;"');
					
					// Status
					$status_dropdown = $this->EE->zenbu_get->_get_status_dropdowns($channel_id);
					$status_header_dropdown = array_merge($status_dropdown['status']['ch_id_'.$channel_id]['dropdown_labels'], $status_header_dropdown);
					unset($status_dropdown['status']['ch_id_'.$channel_id]['dropdown_labels']['']);
					$vars_entry['entry'][$entry_id]['status'] = form_dropdown('status['.$entry_id.']', $status_dropdown['status']['ch_id_'.$channel_id]['dropdown_labels'], $row['status'], 'class="status_dropdown"'); 
					
					// Entry date					
					$entry_date = display_date('', '', $row['entry_date'], array(), '', array(), array(), 'unix');
					$vars_entry['entry'][$entry_id]['entry_date'] = form_input('entry_date['.$entry_id.']', $entry_date, 'class="entry_date_'.$entry_id.'" style="width: 96%;"');
					$local_time = version_compare(APP_VER, '2.6', '>') ? $this->EE->localize->format_date($row['entry_date']) : $this->EE->localize->set_localized_time($row['entry_date']); 
					$this->EE->javascript->output('
					$("input.entry_date_'.$entry_id.'").datepicker({
						dateFormat: $.datepicker.W3C + theDate_time, 
						defaultDate: new Date('.($local_time * 1000).')
						});
					');
					
					
					// Sticky
					$checked = ($row['sticky'] == 'y') ? TRUE : FALSE;
					$vars_entry['entry'][$entry_id]['sticky'] = form_checkbox('sticky['.$entry_id.']', 'y', $checked, 'class="sticky"');
					$vars_entry['entry'][$entry_id]['sticky_checked'] = $checked;
					
					// Allow comments
					$checked = ($row['allow_comments'] == 'y') ? TRUE : FALSE;
					$vars_entry['entry'][$entry_id]['allow_comments'] = form_checkbox('allow_comments['.$entry_id.']', 'y', $checked, 'class="allow_comments"');
					$vars_entry['entry'][$entry_id]['allow_comments_checked'] = $checked;
					
				}
				$status_header_dropdown[''] = '---';
			} else {
				show_error($this->EE->lang->line('unauthorized_to_edit'));
			}
			
			$vars_hidden['hidden_fields'] = form_hidden($hidden);
			
			$vars_action['action_url'] = "C=content_edit".AMP."M=update_multi_entries";
			
			$vars_other['status_header_dropdown'] = form_dropdown('status_toggler', $status_header_dropdown);
			
			$vars = array_merge($vars_entry, $vars_hidden, $vars_action, $vars_other);
			
			return $this->EE->load->view('zenbu_multi_edit_entries', $vars, TRUE);
			break;
		case "delete":

			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = $this->EE->lang->line('delete_confirm');
			} else {
				$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('delete_confirm'));
			}
			
			$vars_hidden['hidden_fields'] = "";
			
			$this->EE->db->where_in('entry_id', $entry_ids);
			$this->EE->db->where('site_id', $this->site_id);
			if($this->EE->session->userdata['can_delete_all_entries'] != 'y')
			{
				$this->EE->db->where('author_id', $this->member_id);
			}
			$query = $this->EE->db->get('channel_titles');
			if($query->num_rows() > 0)
			{
				foreach($query->result_array() as $row)
				{
					$entry_id = $row['entry_id'];
					$channel_id = $row['channel_id'];
					
					$hidden['delete[]'] = $entry_id;
					$vars_hidden['hidden_fields'] .= form_hidden($hidden);
					
					$vars_entry['entry'][$entry_id]['entry_id'] = $entry_id;
					$vars_entry['entry'][$entry_id]['title'] = $row['title'];

				}
			} else {
				show_error($this->EE->lang->line('unauthorized_to_delete_others'));
			}
			
			$vars_action['action_url'] = "C=content_edit".AMP."M=delete_entries";
			
			$vars = array_merge($vars_entry, $vars_hidden, $vars_action);
			
			return $this->EE->load->view('zenbu_multi_delete_entries', $vars, TRUE);
			break;
		case "add_categories" OR "remove_categories":
		
			/** Since Category add/remove doesn't go to content_edit->multi_edit_form (and goes instead to zenbu->multi_edit,
			 * Create a form with hidden fields and automatically submit form to to pass same data to content_edit->multi_edit_form
			 * Automatic submit (javascript) is loaded in view.
			 */
			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = $this->EE->lang->line('multi_entry_category_editor');
			} else {
				$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('multi_entry_category_editor'));
			}
			
			$this->EE->cp->load_package_js('zenbu_multi_cat'); 
			
			foreach($entry_ids as $key => $entry_id)
			{
				$hidden['toggle'][] = $entry_id;
			}
			
			$hidden['action'] = $this->EE->input->get_post('action', TRUE);
			
			$vars_hidden['hidden_fields'] = form_hidden($hidden);
			
			$vars_action['action_url'] = "C=content_edit".AMP."M=multi_edit_form".AMP."from_zenbu=y";
			
			$vars = array_merge($vars_hidden, $vars_action);
			
			return $this->EE->load->view('zenbu_multi_cat', $vars, TRUE);
			break;
			
		}
		
	} // END multi_edit()

	// --------------------------------------------------------------------

	
	/**
	 * Ajax results
	 *
	 * @access	public
	 * @return	string Entry listing table, AJAX response 
	 */
	function ajax_results($output = "")
	{
		$output .= $this->index();
		
		$this->EE->output->send_ajax_response($output);

	} // END ajax_results()

	// --------------------------------------------------------------------
	
	
	/**
	 * Ajax search saving
	 *
	 * @access	public
	 * @return	listing of saved searches
	 */
	function save_search() 
	{
		// Retrieve POST variable 'rule'
		$rule_array					= $this->EE->input->get_post('rule', TRUE);
		$entry_ordering['limit']	= $this->EE->input->get_post('limit', TRUE);
		$entry_ordering['orderby']	= $this->EE->input->get_post('orderby', TRUE);
		$entry_ordering['sort']		= $this->EE->input->get_post('sort', TRUE);
		
		$rules = serialize(array_merge($rule_array, $entry_ordering));
		
		$search_name = $this->EE->input->get_post('save_search_name', TRUE);
		$this->EE->load->model('zenbu_db');
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_display');
		
		$this->EE->zenbu_db->save_search($rules, $search_name);
		$saved_searches_array = $this->EE->zenbu_get->_get_saved_searches();
		$output = $this->EE->zenbu_display->_display_saved_searches($saved_searches_array);
		
		$this->EE->output->send_ajax_response($output);
	} // END save_search()

	// --------------------------------------------------------------------


	/**
	 * Ajax search updating, for titles
	 *
	 * @access	public
	 * @return	none
	 */
	function update_search()
	{
		$data[0]['rule_id']		= $this->EE->input->get_post('rule_id', TRUE);
		$data[0]['rule_label']	= $this->EE->input->get_post('search_title', TRUE);

		//	----------------------------------------
		//	Saving saved search order
		//	----------------------------------------
		if($this->EE->input->get_post('rule_order'))
		{
			// Unset the search label data
			unset($data[0]);

			foreach($this->EE->input->get_post('rule_order') as $order => $rule_id)
			{
				$data[$order]['rule_id'] = $rule_id;
				$data[$order]['rule_order'] = $order;
			}
		}
		
		$this->EE->load->model('zenbu_db');
		
		$this->EE->zenbu_db->update_search($data);

		if(AJAX_REQUEST)
		{
			$this->EE->output->send_ajax_response($this->EE->input->get_post('search_title', TRUE));
		} else {
			$return_url		= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";
			$this->EE->functions->redirect($return_url);
		}
		
	} // END update_search()

	// --------------------------------------------------------------------

	
	/**
	 * Search deleting
	 *
	 * @access	public
	 * @return	delete single search and relist saved searches
	 */
	function delete_search() 
	{
		$this->EE->load->model('zenbu_db');
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_display');
		
		$this->EE->zenbu_db->delete_search();
		$saved_searches_array = $this->EE->zenbu_get->_get_saved_searches();
		$output = $this->EE->zenbu_display->_display_saved_searches($saved_searches_array);
		
		if(AJAX_REQUEST)
		{
			$this->EE->output->send_ajax_response($output);
		} else {
			$return_url		= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";
			$this->EE->functions->redirect($return_url);
		}
	} // END delete_search()

	// --------------------------------------------------------------------


	/**
	 * Search copy/assignment
	 *
	 * @access	public
	 * @return	copy single search to member group(s)
	 */
	function copy_search() 
	{
		$this->EE->load->model('zenbu_db');
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_display');
		
		$this->EE->zenbu_db->copy_search();
		
		$return_url		= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";
		$this->EE->functions->redirect($return_url);

	} // END copy_search()

	// --------------------------------------------------------------------


	/**
	 * Manage saved searches
	 */
	function manage_searches()
	{
		
		$this->EE->load->model('zenbu_db');
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_display');
		$this->EE->load->add_package_path(PATH_THIRD.'zenbu'); // Sometimes the wrong package is loaded. This makes sure the zenbu package is loaded.
		$this->EE->cp->load_package_js('zenbu_manage_searches'); 
		$this->EE->cp->load_package_css('zenbu_manage_searches'); 
		
		$this->EE->zenbu_display->set_head_stylesheets();

		$settings = $this->EE->zenbu_get->_get_settings();

		/**
		* -------------------------------
		* Setting up top right navigation
		* -------------------------------
		*/
		if($this->cp_call)
		{

			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = $this->EE->lang->line('manage_saved_searches');
			} else {
				$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('manage_saved_searches'));
			}

			$nav_array['<i class=\'icon-list\'></i> '.$this->EE->lang->line('entries')] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=index";

			$nav_array['<i class=\'icon-bookmark\'></i> '.$this->EE->lang->line('manage_saved_searches')] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";
			
			if($settings['setting']['can_access_settings'] == 'y' || $this->member_group_id == 1)
			{
				$nav_array['<i class=\'icon-cog\'></i> '.$this->EE->lang->line('display_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings".AMP."channel_id=0";
			}
			
			if($settings['setting']['can_admin'] == 'y' || $this->member_group_id == 1)
			{
				$nav_array['<i class=\'icon-group\'></i> '.$this->EE->lang->line('member_access_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
			}
			
			if($settings['setting']['can_access_settings'] != 'y' && $this->member_group_id != 1) {
				$this->EE->cp->set_right_nav($nav_array);
				return $this->EE->lang->line('unauthorized_access');
			}

			$this->EE->cp->set_right_nav($nav_array);

		}

		$searches						= $this->EE->zenbu_get->_get_saved_searches();
		$vars['searches_member']		= isset($searches['member']) ? $searches['member'] : array();
		$vars['searches_group']			= isset($searches['group']) ? $searches['group'] : array();
		$vars['search_listing_type']	= isset($searches['search_listing_type']) ? $searches['search_listing_type'] : '';

		//	----------------------------------------
		//	Remove group searches if you can't admin
		//	- You shouldn't be changing the order, name,
		//	deleting or assigning anyway. 
		//	Don't like that? Remove the following
		//	----------------------------------------
		if($settings['setting']['can_view_group_searches'] == 'y' && $settings['setting']['can_admin_group_searches'] == 'n')
		{
			$vars['searches_group']	= array();
		}

		//	----------------------------------------
		//	Get member groups
		//	----------------------------------------
		$members = array();
		
		$sql = $this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0) AND site_id = ".$this->site_id);

		if($sql->num_rows() > 0) {
			foreach($sql->result_array() as $num => $row)
			{
				$members[$row['group_id']] = $row['group_title'];
			}
		}

		$vars['member_groups'] = $members;

		$vars['copy_search_action_url'] = "C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=copy_search";

		return $this->EE->load->view('zenbu_manage_searches', $vars, TRUE);

	} // END manage_searches()

	// --------------------------------------------------------------------
	
	
	/**
	 * Display settings
	 *
	 * @access	public
	 */
	function settings()
	{
		$this->EE->load->model('zenbu_display');
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_db');
		$this->EE->load->helper('loader');
		
		$this->EE->zenbu_db->_save_settings();
		
		$this->EE->lang->loadfile('content', 'cp');	// We'll need a few strings from there
		
		if(version_compare(APP_VER, '2.6', '>'))
		{
			$this->EE->view->cp_page_title = $this->EE->lang->line('zenbu_module_name').' - '.$this->EE->lang->line('display_settings');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('zenbu_module_name').' - '.$this->EE->lang->line('display_settings'));
		}
		
		// Get settings and modules from the database
		$settings = $this->EE->zenbu_get->_get_settings();
		$installed_addons = $this->EE->zenbu_get->_get_installed_addons();
		
		// Check if comment module is installed
		$comment_module = (in_array('Comment', $installed_addons['modules'])) ? TRUE : FALSE;
		
		/**
		* -------------------------------
		* Setting up top right navigation
		* -------------------------------
		*/
		$nav_array['<i class=\'icon-list\'></i> '.$this->EE->lang->line('entries')] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."channel_id=".$this->EE->input->get('channel_id');

		$nav_array['<i class=\'icon-bookmark\'></i> '.$this->EE->lang->line('manage_saved_searches')] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";
		
		if($settings['setting']['can_access_settings'] == 'y' || $this->member_group_id == 1)
		{
			$nav_array['<i class=\'icon-cog\'></i> '.$this->EE->lang->line('display_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings".AMP."channel_id=0";
		}
		
		if($settings['setting']['can_admin'] == 'y' || $this->member_group_id == 1)
		{
			$nav_array['<i class=\'icon-group\'></i> '.$this->EE->lang->line('member_access_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
		}
		
		if($settings['setting']['can_access_settings'] != 'y' && $this->member_group_id != 1) {
			$this->EE->cp->set_right_nav($nav_array);
			return $this->EE->lang->line('unauthorized_access');
		}
		
		$this->EE->cp->set_right_nav($nav_array);
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper(array('form'));
		$this->EE->load->add_package_path(PATH_THIRD.'zenbu'); // Sometimes the wrong package is loaded. This makes sure the zenbu package is loaded.
		$this->EE->cp->load_package_js('zenbu_script');
		$this->EE->cp->load_package_js('zenbu_settings');
			
		//	----------------------------------------
		//	Set stylesheets
		//	----------------------------------------
		$this->EE->zenbu_display->set_head_stylesheets();

		$this->EE->javascript->compile();  
			
		// Standard labels
		$labels_std = array(
			"show_id"				=> $this->EE->lang->line('entry_id'),
			"show_title"			=> $this->EE->lang->line('title'),
			"show_url_title" 		=> $this->EE->lang->line('url_title'),
			"show_channel" 			=> $this->EE->lang->line('channel'),
			"show_categories" 		=> $this->EE->lang->line('categories'),
			"show_status"			=> $this->EE->lang->line('status'),
			"show_sticky" 			=> $this->EE->lang->line('is_sticky'),
			"show_entry_date" 		=> $this->EE->lang->line('entry_date'),
			"show_expiration_date" 	=> $this->EE->lang->line('expiration_date'),
			"show_edit_date" 		=> $this->EE->lang->line('edit_date'),
			"show_author" 			=> $this->EE->lang->line('author'),
			"show_comments" 		=> $this->EE->lang->line('comments'),
			"show_view" 			=> $this->EE->lang->line('live_look'),
			"show_view_count" 		=> $this->EE->lang->line('view_count'),
			"show_last_author" 		=> $this->EE->lang->line('show_last_author'),
			"show_autosave"			=> $this->EE->lang->line('show_autosave'),
		);

		$vars_other['mass_check_fields'] = $labels_std;
			
		$field_order_std = array( // If no field order set (for eg. for newly created channels)
			'show_id',
			'show_title',
			'show_url_title',
		    'show_channel',
		    'show_categories',
		    'show_status',
		    'show_sticky',
		    'show_entry_date',
		    'show_expiration_date',
		    'show_edit_date',
		    'show_author',
		    'show_comments',
		    'show_view',
		    'show_view_count',
		    'show_last_author',
		    'show_autosave',
		);

		/**
		*	===========================================
		*	Extension Hook zenbu_add_column
		*	===========================================
		*
		*	Adds another standard setting row in the Display Settings section
		*	@return $fields_and_labels 	array	An array containing row data
		*/
		if ($this->EE->extensions->active_hook('zenbu_add_column') === TRUE)
		{
			$hook_fields_and_labels = $this->EE->extensions->call('zenbu_add_column');
		 	if ($this->EE->extensions->end_script === TRUE) return;

		 	if(is_array($hook_fields_and_labels))
			{
				foreach($hook_fields_and_labels as $key => $fal)
				{
					$field_order_std[] = isset($fal['column']) ? $fal['column'] : '';
					$labels_std[$fal['column']] = $fal['label'];
				}
			}
		}

		$field_order_std = array_flip($field_order_std);
		
		if($comment_module === FALSE) {
			unset($labels_std['show_comments']);
			unset($field_order_std['show_comments']);
		}

			
		// Get channels
		$vars_channels = $this->EE->zenbu_get->_get_channel_data($this->member_group_id);
		
		//	----------------------------------------
		// 	Build labels 
		//	----------------------------------------
		foreach($vars_channels['channels']['channel_data'] as $channel_id => $value)
		{
			// Get basic template data
			$channel_id_array[] = $channel_id;
			$livelook_template_array = $this->EE->zenbu_get->_get_basic_template_data($channel_id_array, TRUE);
			
			// Get other field information
			$fields = $this->EE->zenbu_get->_get_field_ids();

			if( ! empty($fields[$channel_id]))
			{
				$field_label_array = $fields[$channel_id]['field'];
				$field_type_array = $fields[$channel_id]['fieldtype'];
				$field_id_array = $fields[$channel_id]['id'];
			} else {
				$field_label_array = array();
				$field_type_array = array();
				$field_id_array = array();
			}
			
			// Set field order
			$field_order = (isset($settings['setting'][$channel_id]['field_order'])) ? $settings['setting'][$channel_id]['field_order'] : $field_order_std;
			
			if($comment_module === FALSE) {
				if(isset($field_order['show_comments']))
				{
					unset($field_order['show_comments']);
				}
			}
			
			
			if( ! empty($fields) && $channel_id != 0)
			{
				foreach($field_id_array as $key => $id)
				{
					$c = 10;
					if(!array_key_exists('field_'.$id, $field_order))
					{
						$field_order['field_'.$id] = $c;
						$c++;
					}	
				}
			}

			// Set extra options
			$extra_options = (isset($settings['setting'][$channel_id]['extra_options'])) ? $settings['setting'][$channel_id]['extra_options'] : array();
			
			// Get array of custom fields to show
			if(isset($settings['setting'][$channel_id]['show_custom_fields']) && !empty($settings['setting'][$channel_id]['show_custom_fields']))
			{
				$fields_to_show = explode('|', $settings['setting'][$channel_id]['show_custom_fields']); // array of field set to be shown
			} else {
				$fields_to_show = array();
			}
			
			// ------------------------------------------------------------------
			// Process fields, their order, their labels, their name="" attribute
			// ------------------------------------------------------------------
			$vars_labels['extra_settings'] = ( ! isset($vars_labels['extra_settings']) || empty($vars_labels['extra_settings'])) ? array() : $vars_labels['extra_settings'];
			foreach($field_order as $table_col => $order)
			{		
				if(substr($table_col, 0, 6) == 'field_')
				{
					// Custom fields
					
					if(in_array(substr($table_col, 6), $field_id_array)) // Checks if field still exists and compares with stored settings
					{
						$field_id = substr($table_col, 6);
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['input_name'] = 'settings['.$channel_id.']['.$table_col.'][show]';
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['order_input_name'] = 'settings['.$channel_id.']['.$table_col.'][field_order]';
 						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['option_title'] = $field_label_array[$field_id];
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['checked'] = (in_array($field_id, $fields_to_show)) ? TRUE : FALSE;
						
						// - Extra options								
						$ft_class = $field_type_array[$field_id].'_ft';
						load_ft_class($ft_class);
						
						if(class_exists($ft_class))
						{
							$ft_object = create_object($ft_class);

							// Set up previously saved settings, if they exit.
							$extra_options_saved_settings = (isset($extra_options[$table_col])) ? $extra_options[$table_col] : array();
							
							// Retrieve extra settings display
							$field_settings = $fields[$channel_id]['settings'][$field_id];
							
							$extra_settings_array = (method_exists($ft_object, 'zenbu_field_extra_settings')) ? $ft_object->zenbu_field_extra_settings($table_col, $channel_id, $extra_options_saved_settings, $field_settings) : array();
							
							// Set up for sending to view
							// "setting_labels" contains the visual output code for each setting row
							$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col] = array_merge($vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col], $extra_settings_array);
							
							// Create a simple list of "extra option" short names for use in view
							// Used to loop through short names in view instead of handling the above "setting_labels" array
							$extra_settings_name_array = array_keys($extra_settings_array);
							foreach($extra_settings_name_array as $key => $extra_settings_name)
							{
								if( ! isset($vars_labels['extra_settings']))
								{
									$vars_labels['extra_settings'] = array();
								}
								
								if(isset($vars_labels['extra_settings']) && ! in_array($extra_settings_name, $vars_labels['extra_settings']))
								{
									$vars_labels['extra_settings'][] = $extra_settings_name;
								}
							}
						}

						// Add non-fieldtype short names to simple list of "extra options"
						// or else these won't appear in the settings view
						$non_ft_extra_settings = $this->non_ft_extra_options;
						
						// Add Pages modules option if installed
						if(in_array('Pages', $installed_addons['modules']))
						{
							$non_ft_extra_settings['livelook_option_4'] = 'livelook_option_4';
						}
						
						// Build the completed "extra_settings" array, to be looped in settings view
						if(isset($vars_labels['extra_settings']))
						{
							$vars_labels['extra_settings'] = array_merge($vars_labels['extra_settings'], $non_ft_extra_settings);
						}					
					}
					
				} else {
					// Cross-checking for fields not part of standard set:
					if(array_key_exists($table_col, $field_order_std))
					{
						

						// Standard fields
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['input_name'] = 'settings['.$channel_id.']['.$table_col.'][show]';
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['order_input_name'] = 'settings['.$channel_id.']['.$table_col.'][field_order]';
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['option_title'] = isset($labels_std[$table_col]) ? $labels_std[$table_col] : $field_order_std[$table_col];
						$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['checked'] = (isset($settings['setting'][$channel_id][$table_col]) && $settings['setting'][$channel_id][$table_col] == 'y') ? TRUE : FALSE;
						
						switch ($table_col)
						{
							case "show_categories";
								$extra_category_option_1 	= (isset($extra_options[$table_col]['category_option_1'])) ? $extra_options[$table_col]['category_option_1'] : '';
								$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]['category_option_1'] = form_label($this->EE->lang->line('no_categories_to_display').NBS.form_input('settings['.$channel_id.']['.$table_col.'][category_option_1]', $extra_category_option_1, 'size="2" maxlength="3"'));
							break;
							case "show_view_count":

								//	----------------------------------------
								// 	Add options for view count
								//	----------------------------------------
								for($i = 1; $i <= 4; $i++)
								{
									${'extra_show_view_count_'.$i} = (isset($extra_options[$table_col]['view_count_'.$i])) ? TRUE : FALSE;
									$option_view_counter_array['view_count_'.$i] = "show_view_count_".$i;
								}
								
								foreach($option_view_counter_array as $label => $lang_string)
								{
									$checked_show_view_count = 'extra_show_'.$label;
									$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col][$label] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.']['.$label.']', 'y', $$checked_show_view_count).'&nbsp;'.$this->EE->lang->line('show_view_count').' '.substr($label, 11)).'<br />';
								}
							break;
							case ($table_col == "show_entry_date" || $table_col == "show_expiration_date" || $table_col == "show_edit_date"):
								$extra_date_option_1 	= (isset($extra_options[$table_col]['date_option_1'])) ? $extra_options[$table_col]['date_option_1'] : '';
								$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]["date_option_1"] = form_label($this->EE->lang->line('date_format').'&nbsp;'.form_input('settings['.$channel_id.']['.$table_col.'][date_option_1]', $extra_date_option_1, 'size="20" class="bottom-margin"'));
								if($table_col != "show_edit_date")
								{
									$extra_date_option_2 	= (isset($extra_options[$table_col]['date_option_2'])) ? $extra_options[$table_col]['date_option_2'] : '';
									$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]["date_option_2"] = BR . form_label($this->EE->lang->line('date_format_future').'&nbsp;'.form_input('settings['.$channel_id.']['.$table_col.'][date_option_2]', $extra_date_option_2, 'size="20"'));
								}
							break;
							case "show_view":

								//	----------------------------------------
								// 	Get options
								//	----------------------------------------
								$extra_livelook_option_1 	= (isset($extra_options[$table_col]['livelook_option_1'])) ? $extra_options[$table_col]['livelook_option_1'] : '';
								$extra_livelook_option_2 	= (isset($extra_options[$table_col]['livelook_option_2'])) ? $extra_options[$table_col]['livelook_option_2'] : '';
								$extra_livelook_option_3 	= (isset($extra_options[$table_col]['livelook_option_3'])) ? $extra_options[$table_col]['livelook_option_3'] : '';
								if(in_array('Pages', $installed_addons['modules']))
								{
									$extra_livelook_option_4 	= (isset($extra_options[$table_col]['livelook_option_4'])) ? TRUE : FALSE;
								}
								
								//	----------------------------------------
								// 	Build set templates
								//	----------------------------------------

								$livelook_custom_hide = $extra_livelook_option_1 == "use_livelook_settings" || empty($extra_livelook_option_1) ? '' : 'invisible';

								if(isset($livelook_template_array[$channel_id]['group_name']) && isset($livelook_template_array[$channel_id]['template_name'])) 
								{

									$livelook_template = BR . '<span class="livelook-custom-segments ' . $livelook_custom_hide . '">' . $livelook_template_array[$channel_id]['group_name'] . '/' . $livelook_template_array[$channel_id]['template_name'] . '/</span>';
									
								} else {

									$livelook_template = BR . '<span class="livelook-custom-segments ' . $livelook_custom_hide . '">' . lang('livelook_not_set').'/ </span>';
								}
								
								//	----------------------------------------
								// 	Disabled custom segment option if Live Look is used
								//	----------------------------------------

								if($extra_livelook_option_1 == "use_livelook_settings" || empty($extra_livelook_option_1))
								{
									$disabled = 'disabled="disabled" class="livelook-custom-segments seg-option invisible"';
									$disabled_arr = array('disabled' => 'disabled', 'class' => 'livelook-custom-segments seg-option invisible');
								} else {
									$disabled = 'class="livelook-custom-segments seg-option"';
									$disabled_arr = array('class' => 'livelook-custom-segments seg-option');
								}

								if( empty($extra_livelook_option_1) && ! isset($livelook_template_array[$channel_id]) )
								{
									$disabled = 'class="livelook-custom-segments seg-option invisible"';
									$disabled_arr = array('class' => 'livelook-custom-segments seg-option invisible');
								}
								
								//	----------------------------------------
								// 	Show Live Look option when it is set and for when multiple channels are displayed in Zenbu 
								// 	Not when Live Look hasn't been set for channel
								//	----------------------------------------

								if( isset($livelook_template_array[$channel_id]) || $channel_id == "0")
								{
									$livelook_options_dropdown['use_livelook_settings'] = $this->EE->lang->line("use_livelook_settings");
								}
								$livelook_options_dropdown['use_custom_segments'] = $this->EE->lang->line("use_custom_segments");
								
								$livelook_options_dropdown2 = array(
									'entry_id_suffix' 		=> $this->EE->lang->line("entry_id"),
									'entry_title_suffix' 	=> $this->EE->lang->line("url_title"),
								);
								
								// Dropdown for Live Look or custom segments
								if($channel_id != "0")
								{
									$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]["livelook_option_1"] = form_dropdown('settings['.$channel_id.']['.$table_col.'][livelook_option_1]', $livelook_options_dropdown, $extra_livelook_option_1, 'class="livelook-settings bottom-margin"' ) . $livelook_template;
								
									// Reset for next loop
									$livelook_options_dropdown = array();
									
									// Build custom segments when single channels are displayed
									$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]["livelook_option_2"] = form_label($this->EE->lang->line('custom_segments') . '&nbsp;' . form_input('settings['.$channel_id.']['.$table_col.'][livelook_option_2]', $extra_livelook_option_2, 'size="20" id="" ' . $disabled ) . ' /', '', $disabled_arr);
									
									$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]["livelook_option_3"] = form_dropdown('settings['.$channel_id.']['.$table_col.'][livelook_option_3]', $livelook_options_dropdown2, $extra_livelook_option_3, 'class="bottom-margin"' );
									
									// Add option for Pages module override if Pages module is installed
									if(in_array('Pages', $installed_addons['modules']))
									{
										$vars_labels['setting_labels'][$channel_id][$field_order[$table_col]][$table_col]["livelook_option_4"] = '<br />'.form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][livelook_option_4]', 'y', $extra_livelook_option_4) . '&nbsp;' . $this->EE->lang->line('livelook_pages_override'));
									}
									
								}
							break;
						}
					}
				}
			}



			//
			// Process new fields when NEW features are added
			//
			foreach($field_order_std as $table_col => $order)
			{
				if( ! array_key_exists($table_col, $field_order))
				{
					// Add new feature fields at the end of setting listing
					$order_num = min($field_order) - 1;
					
					// Create field data
					$vars_labels['setting_labels'][$channel_id][$order_num][$table_col]['input_name'] = 'settings['.$channel_id.']['.$table_col.'][show]';
					$vars_labels['setting_labels'][$channel_id][$order_num][$table_col]['order_input_name'] = 'settings['.$channel_id.']['.$table_col.'][field_order]';
					$vars_labels['setting_labels'][$channel_id][$order_num][$table_col]['option_title'] = isset($labels_std[$table_col]) ? $labels_std[$table_col] : $table_col;
					$vars_labels['setting_labels'][$channel_id][$order_num][$table_col]['checked'] = FALSE;
					
					// Add options for view count
					switch ($table_col)
					{
						case "show_view_count":
							// Add options for view count
							for($i = 1; $i <= 4; $i++)
							{
								${'extra_show_view_count_'.$i} = (isset($extra_options[$table_col]['view_count_'.$i])) ? TRUE : FALSE;
								$option_view_counter_array['view_count_'.$i] = "show_view_count_".$i;
							}
							
							foreach($option_view_counter_array as $label => $lang_string)
							{
								$checked_show_view_count = 'extra_show_'.$label;
								$vars_labels['setting_labels'][$channel_id][$order_num][$table_col][$label] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.']['.$label.']', 'y', $$checked_show_view_count).'&nbsp;'.$this->EE->lang->line('show_view_count').' '.substr($label, 11)).'<br />';
							}
						break;
						case ($table_col == "show_entry_date" || $table_col == "show_expiration_date" || $table_col == "show_edit_date"):
							$extra_date_option_1 	= (isset($extra_options[$table_col]['date_option_1'])) ? $extra_options[$table_col]['date_option_1'] : '';
							$vars_labels['setting_labels'][$channel_id][$order_num][$table_col]["date_option_1"] = form_label($this->EE->lang->line('date_format').'&nbsp;'.form_input('settings['.$channel_id.']['.$table_col.'][date_option_1]', $extra_date_option_1, 'size="20"'));

							if($table_col != "show_edit_date")
							{
								$extra_date_option_2 	= (isset($extra_options[$table_col]['date_option_2'])) ? $extra_options[$table_col]['date_option_2'] : '';
								$vars_labels['setting_labels'][$channel_id][$order_num][$table_col]["date_option_2"] = BR . form_label($this->EE->lang->line('date_format_future').'&nbsp;'.form_input('settings['.$channel_id.']['.$table_col.'][date_option_2]', $extra_date_option_2, 'size="20"'));
							}
						break;
					}
				}
			}

			
			
			//
			// Sort it so that view displays rows in the right order
			//
			if( ! empty($fields))
			{
				ksort($vars_labels['setting_labels'][$channel_id]);
			}
		}
			
		/**
		*	-----------------------------------
		*	General settings
		*	-----------------------------------
		*/
		
		//	----------------------------------------
		//	Maximum results per page (above 200)
		//	----------------------------------------
		$max_results_per_page = (isset($settings['setting']['general']['max_results_per_page'])) ? $settings['setting']['general']['max_results_per_page'] : '';
		$vars_labels['general_settings'][0]['label'] = form_label($this->EE->lang->line('max_results_per_page')).'<div class="subtext">'.$this->EE->lang->line('max_results_per_page_note').'</div>';
		$vars_labels['general_settings'][0]['form_field'] = form_input('settings[general][max_results_per_page]', $max_results_per_page, 'maxlength="4"');

		//	----------------------------------------
		//	Default rule filters (title, cat_id, etc)
		//	----------------------------------------
		$default_start_filters_array = array(
			'title'				=> $this->EE->lang->line('entry_title'),
			'cat_id'			=> $this->EE->lang->line('category'),
			'status'			=> $this->EE->lang->line('status'),
			'author'			=> $this->EE->lang->line('author'),
			'sticky'			=> $this->EE->lang->line('is_sticky'),
			'date'				=> $this->EE->lang->line('date'),
			'expiration_date'	=> $this->EE->lang->line('expiration_date'),
			'edit_date'			=> $this->EE->lang->line('edit_date'),
			'id'				=> $this->EE->lang->line('entry_id'),
			'any_cf_title'		=> $this->EE->lang->line('any_custom_fields_titles'),		
		);
		$default_1st_filter = (isset($settings['setting']['general']['default_1st_filter'])) ? $settings['setting']['general']['default_1st_filter'] : '';
		$vars_labels['general_settings'][1]['label'] = form_label($this->EE->lang->line('default_filter')).'<div class="subtext">'.$this->EE->lang->line('default_filter_note').'</div>';
		$vars_labels['general_settings'][1]['form_field'] = form_dropdown('settings[general][default_1st_filter]', $default_start_filters_array, $default_1st_filter);

		//	----------------------------------------
		//	Default limit
		//	----------------------------------------
		$default_limit_filters_array = $this->EE->zenbu_get->_get_general_form_variables();

		$default_limit_filters_array = $default_limit_filters_array['limit']['dropdown_labels'];

		$default_limit = (isset($settings['setting']['general']['default_limit'])) ? $settings['setting']['general']['default_limit'] : '25';

		$vars_labels['general_settings'][2]['label'] = form_label($this->EE->lang->line('default_limit')).'<div class="subtext">'.$this->EE->lang->line('default_limit_note').'</div>';

		$vars_labels['general_settings'][2]['form_field'] = form_dropdown('settings[general][default_limit]', $default_limit_filters_array, $default_limit);
		
		//	----------------------------------------
		//	Default ordering
		//	----------------------------------------
		$default_ordering_array = array(
			"entry_date" 		=> $this->EE->lang->line('entry_date'),
			"id" 				=> $this->EE->lang->line('entry_id'),
			"title" 			=> $this->EE->lang->line('title'),
			"category" 			=> $this->EE->lang->line('category'),
			"expiration_date" 	=> $this->EE->lang->line('expiration_date'),
			"edit_date" 		=> $this->EE->lang->line('edit_date'),
			"url_title" 		=> $this->EE->lang->line('url_title'),
			"status" 			=> $this->EE->lang->line('status'),
			"channel" 			=> $this->EE->lang->line('channel'),
			"author"			=> $this->EE->lang->line('author'),
			"is_sticky"			=> $this->EE->lang->line('is_sticky'),
			"comments"			=> $this->EE->lang->line('comments'),
		);

		$default_sorting_array = array(
			"desc" 			=> $this->EE->lang->line('desc'),
			"asc" 			=> $this->EE->lang->line('asc'),
		);

		$default_order = (isset($settings['setting']['general']['default_order'])) ? $settings['setting']['general']['default_order'] : 'entry_date';

		$default_sort =	(isset($settings['setting']['general']['default_sort'])) ? $settings['setting']['general']['default_sort'] : 'desc';

		$vars_labels['general_settings'][3]['label'] = form_label($this->EE->lang->line('default_order_sort')).'<div class="subtext">'.$this->EE->lang->line('default_order_sort_note').'</div>';

		$vars_labels['general_settings'][3]['form_field'] = form_dropdown('settings[general][default_order]', $default_ordering_array, $default_order)
			.'&nbsp;'.form_dropdown('settings[general][default_sort]', $default_sorting_array, $default_sort);

		//	----------------------------------------
		//	Show in dropdown and be able to search all custom fields
		//	----------------------------------------
		$enable_hidden_field_search_y = (isset($settings['setting']['general']['enable_hidden_field_search']) && $settings['setting']['general']['enable_hidden_field_search'] == 'y') ? TRUE : FALSE;

		$enable_hidden_field_search_n = (isset($settings['setting']['general']['enable_hidden_field_search']) && $settings['setting']['general']['enable_hidden_field_search'] == 'n') ? TRUE : FALSE;

		$vars_labels['general_settings'][4]['label'] = form_label($this->EE->lang->line('enable_hidden_field_search')).'<div class="subtext">'.$this->EE->lang->line('enable_hidden_field_search_note').'</div>';

		$vars_labels['general_settings'][4]['form_field'] = form_hidden('settings[general][enable_hidden_field_search]', 'n')
			.form_label( form_radio('settings[general][enable_hidden_field_search]', 'y', $enable_hidden_field_search_y).NBS.NBS.$this->EE->lang->line('yes') ).NBS.NBS.NBS.NBS.NBS
			.form_label( form_radio('settings[general][enable_hidden_field_search]', 'n', $enable_hidden_field_search_n).NBS.NBS.$this->EE->lang->line('no'));
			
		// Top right Links/URLs

		$vars_other['current_channel'] = $this->EE->input->get_post('channel_id', TRUE) !== FALSE ? $this->EE->input->get_post('channel_id', TRUE) : '';
		
		$vars_urls['action_url'] = "C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings".AMP."channel_id=".$vars_other['current_channel'];
		
		$vars_urls['settings_view_url'] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings";
		
		$vars_urls['settings_admin_url'] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
		
		// Check if current member group can copy profile to other member groups
		if($settings['setting']['can_copy_profile'] == 'y') {
			
			$sql = ($this->member_group_id != 1) ? 
				$this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0, 1) AND site_id = ".$this->site_id) : 
				$this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0) AND site_id = ".$this->site_id);
			
			if($sql->num_rows() > 0) {
				
				$vars_members['member_groups'] = $sql->result_array();
			}

		} else {	
			
			$vars_members['member_groups'] = array();
		}
		
		// Check if current member group can administrate member access
		$vars_other['can_admin'] = $settings['setting']['can_admin'];
		
		$vars_other['current_member_group'] = $this->member_group_id;
		
		$vars = array_merge($vars_channels, $vars_labels, $settings, $vars_urls, $vars_members, $vars_other);
		
		return $this->EE->load->view('zenbu_display_settings', $vars, TRUE);
		
	} // END function settings
	
	
	/**
	 * Member access settings
	 *
	 * @access	public
	 */
	function settings_admin()
	{
		$this->EE->load->model('zenbu_display');
		$this->EE->load->model('zenbu_get');
		$this->EE->load->model('zenbu_db');
		$this->EE->zenbu_db->_save_settings();
		
		$this->EE->lang->loadfile('content', 'cp');	// We'll need a few string from there
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper(array('form'));
		$this->EE->load->add_package_path(PATH_THIRD.'zenbu'); // Sometimes the wrong package is loaded. This makes sure the zenbu package is loaded.
		$this->EE->cp->load_package_js('zenbu_script');
		
		//	----------------------------------------
		//	Set stylesheets
		//	----------------------------------------
		$this->EE->zenbu_display->set_head_stylesheets();

		$this->EE->javascript->compile();  

		if(version_compare(APP_VER, '2.6', '>'))
		{
			$this->EE->view->cp_page_title = $this->EE->lang->line('zenbu_module_name').' - '.$this->EE->lang->line('member_access_settings');
		} else {	
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('zenbu_module_name').' - '.$this->EE->lang->line('member_access_settings'));
		}

		//	----------------------------------------
		// 	Get settings from the database
		//	----------------------------------------
		$settings = $this->EE->zenbu_get->_get_settings();

		
		//	----------------------------------------
		//	Build right nav links based on member group settings
		//	----------------------------------------
		$nav_array['<i class=\'icon-list\'></i> '.$this->EE->lang->line('entries')] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=index";

		$nav_array['<i class=\'icon-bookmark\'></i> '.$this->EE->lang->line('manage_saved_searches')] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";
		
		if($settings['setting']['can_access_settings'] == 'y' || $this->member_group_id == 1) {
			$nav_array['<i class=\'icon-cog\'></i> '.$this->EE->lang->line('display_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings".AMP."channel_id=0";
		}


		if($settings['setting']['can_admin'] == 'y' || $this->member_group_id == 1) {
			$nav_array['<i class=\'icon-group\'></i> '.$this->EE->lang->line('member_access_settings')]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
		}
		
		// Kick 'em out if they are not authorized
		if($settings['setting']['can_admin'] != 'y' &&  $this->member_group_id != 1) {
			$this->EE->cp->set_right_nav($nav_array);
			return $this->EE->lang->line('unauthorized_access');
		}
		
		$this->EE->cp->set_right_nav($nav_array);

		$results = ($this->member_group_id != 1) ? 
			$this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0, 1) AND site_id = ".$this->site_id) :
			$this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0) AND site_id = ".$this->site_id);

		if($results->num_rows() > 0) {
			foreach($results->result_array() as $num => $row)
			{
				$members[$row['group_id']] = $row['group_title'];

				$settings_query = $this->EE->db->query("/* Zenbu get settings for a specific member group/site */ SELECT * FROM exp_".$this->addon_short_name." WHERE member_group_id = ".$row['group_id']." AND site_id = ".$this->site_id);

				if($settings_query->num_rows() > 0) {
					
					foreach($settings_query->result_array() as $num2 => $row2)
					{
						$vars_member_data['member_groups'][$num]								= $row; // group_id and group_title
						foreach($this->permissions as $permission)
						{
							$vars_member_data['member_groups'][$num][$permission]	= $row2[$permission];
						}
						
					}

				} else {
					
					$vars_member_data['member_groups'][$num] = $this->EE->zenbu_db->_insert_default_settings($row['group_id'], $row['group_title']);

				}
				
			}
		}
		
		if($this->member_group_id == 1)
		{
			$installed_addons	= $this->EE->zenbu_get->_get_installed_addons();
			$module_id			= array_search(ZENBU_NAME, $installed_addons['modules']);
			$query = $this->EE->db->query("/* Zenbu get module access data based on member group */ SELECT * FROM exp_module_member_groups WHERE module_id = " . $module_id);
			$vars_member_data['module_enabled_for'][] = 1; // Super Admins always have this enabled
			if($query->num_rows() > 0) 
			{
				foreach($query->result_array() as $num => $row)
				{
					$vars_member_data['module_enabled_for'][] = $row['group_id'];
				}
			}
			$vars_member_data['enable_module_members'] = $members;
		} else {
			$vars_member_data['enable_module_members'] = array();
			$vars_member_data['module_enabled_for'] = array();
		}

		$vars_urls['action_url'] = "C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
		$vars_urls['settings_view_url'] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings";
		$vars_urls['settings_admin_url'] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings_admin";
		// Check if current member group can administrate member access
		$vars_other['can_admin'] = $settings['setting']['can_admin'];
		$vars_other['current_member_group'] = $this->member_group_id;
		$vars_other['permissions'] = $this->permissions;
		
		$vars = array_merge($vars_member_data, $vars_urls, $vars_other);
		
		return $this->EE->load->view('zenbu_settings_admin', $vars, TRUE);

		
	} // END function settings_admin
	
}
// END CLASS

/* End of file mcp.download.php */
/* Location: ./system/expressionengine/third_party/modules/zenbu/mcp.zenbu.php */