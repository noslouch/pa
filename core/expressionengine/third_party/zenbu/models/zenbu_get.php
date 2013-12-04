<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Zenbu_get extends Zenbu_mcp {


	function Zenbu_get()
	{
		parent::Zenbu_mcp();
	}	
	

	/**
	* Checks if channel is in correct site
	* @param	array	$channel_id
	*/
	function _verify_site_channel($channel_id)
	{
		$assigned_data = $this->EE->zenbu_get->get_assigned_data();

		foreach($channel_id as $key => $ch_id)
		{
			if( ! array_key_exists($ch_id, $assigned_data['channels']) && $ch_id != "0")
			{
				$default_channel_id[] = 0;
				return $default_channel_id;
			} else {
				return $channel_id;
			}
		}
	}
	

	/**
	* function _get_channel_first_dropdown
	* A simple function to retrieve channel data to be displayed in Zenbu's channel select dropdown
	* @return	array 
	*/
	function _get_channel_first_dropdown()
	{
		$output['channel_first_dropdown']['labels']['channel_id'] = 'Channel';
		return $output;
	}
	

	/**
	* function _get_first_dropdown
	* Create arrays to display the combinations of first drop downs for each rule, per channel
	* @return	array 
	*/
	function _get_first_and_orderby_dropdown($settings)
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'first_and_orderby_dropdown'))
		{
			return $this->EE->session->cache('zenbu', 'first_and_orderby_dropdown');
		}

		// The standard listing - no channels
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['title']											= $this->EE->lang->line('entry_title');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['id']												= $this->EE->lang->line('entry_id');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['url_title']											= $this->EE->lang->line('url_title');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['cat_id']											= $this->EE->lang->line('category');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['status']											= $this->EE->lang->line('status');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['author']											= $this->EE->lang->line('author');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['sticky']											= $this->EE->lang->line('is_sticky');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels']['any_cf_title']									= $this->EE->lang->line('any_custom_fields_titles');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels'][$this->EE->lang->line('date')]['date']			= $this->EE->lang->line('date');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels'][$this->EE->lang->line('date')]['expiration_date']	= $this->EE->lang->line('expiration_date');
		$output['rules_first_dropdown']['ch_id_0']['dropdown_labels'][$this->EE->lang->line('date')]['edit_date']		= $this->EE->lang->line('edit_date');
		
		
		// Dropdown for "order by"
		// Might as well build the dropdowns here to avoid repeating ourselves	
		$orderby_dd_array = array(
			"entry_date"		=> "entry_date",
			"id"				=> "entry_id",
			"title"				=> "title",
			"url_title"			=> "url_title",
			"category"			=> "category",
			"expiration_date"	=> "expiration_date",
			"edit_date"			=> "edit_date",
			"url_title"			=> "url_title",
			"status"			=> "status",
			"channel"			=> "channel",
			"author"			=> "author",
			"is_sticky"			=> "is_sticky",
			"comments"			=> "comments",
			);

		$orderby_dd_array_0 = $orderby_dd_array;
		
		// Add a few dropdown items based on settings
		if(isset($settings['setting'][0]['show_autosave']['show']) && $settings['setting'][0]['show_autosave']['show'] == 'y')
		{
			$orderby_dd_array_0['autosave'] = "autosave";
		}

		// "Order by" dropdown for no channels
		foreach($orderby_dd_array_0 as $key => $lang_key)
		{
			$output['orderby_dropdown']['ch_id_0']['dropdown_labels'][$key] = $this->EE->lang->line($lang_key);
		}		
		
		// The other listings - with channel
		$results = $this->EE->db->query("/* Query data to build first dropdown */
			SELECT exp_channels.channel_id, 
			exp_channel_fields.field_id, 
			exp_channel_fields.field_label
			FROM exp_channels, exp_channel_fields 
			WHERE exp_channel_fields.site_id = ".$this->site_id."
			ORDER BY exp_channel_fields.field_order ASC"
		);
		 
		if($results->num_rows() > 0)
		{
			/**
			 * Get all custom fields
			 */
			$all_custom_fields = $this->_get_field_ids();

			foreach($results->result_array() as $row)
			{
				$ch_id							= $row['channel_id'];
				$ch_id_array[]					= $row['channel_id'];
				$orderby_dd_array_ch_{$ch_id}	= $orderby_dd_array;

				// Standard list
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['title']											= $this->EE->lang->line('entry_title');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['id']												= $this->EE->lang->line('entry_id');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['url_title']												= $this->EE->lang->line('url_title');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['cat_id']											= $this->EE->lang->line('category');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['status']											= $this->EE->lang->line('status');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['author']											= $this->EE->lang->line('author');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['sticky']											= $this->EE->lang->line('is_sticky');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels']['any_cf_title']										= $this->EE->lang->line('any_custom_fields_titles');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$this->EE->lang->line('date')]['date']				= $this->EE->lang->line('date');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$this->EE->lang->line('date')]['expiration_date']	= $this->EE->lang->line('expiration_date');
				$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$this->EE->lang->line('date')]['edit_date']			= $this->EE->lang->line('edit_date');
				

				// Add a few dropdown items based on settings
				if(isset($settings['setting'][$ch_id]['show_autosave']['show']) && $settings['setting'][$ch_id]['show_autosave']['show'] == 'y')
				{
					$orderby_dd_array_ch_{$ch_id}['autosave'] = "autosave";
				}

				// "Order by" dropdown for channels
				foreach($orderby_dd_array_ch_{$ch_id} as $key => $lang_key)
				{
					$output['orderby_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$key] = $this->EE->lang->line($lang_key);
				}
				
				if(isset($settings['setting'][$ch_id]['show_custom_fields']))
				{
					$shown_custom_fields = explode("|", $settings['setting'][$ch_id]['show_custom_fields']);
					
					// Override to show all fields if general setting says so.
					if ( isset($settings['setting']['general']['enable_hidden_field_search']) && $settings['setting']['general']['enable_hidden_field_search'] == 'y' )
					{
						$shown_custom_fields = isset($all_custom_fields[$ch_id]['id']) ? $all_custom_fields[$ch_id]['id'] : array();
					}

				} else {
					$shown_custom_fields = array();
				}

				if( in_array($row['field_id'], $shown_custom_fields) ) 
				{
					$output['rules_first_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$this->EE->lang->line('custom_fields')]['field_'.$row['field_id']] = $row['field_label'];
					
					$output['orderby_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$this->EE->lang->line('custom_fields')]['field_id_'.$row['field_id']] = $row['field_label'];
				}	

			}
		} else {

			/*
			 *	This is a special situation, 
			 *	as it happens if there is not
			 *	even one custom field in EE!
			 */
			$assigned_data = $this->get_assigned_data();
			
			foreach($assigned_data['channels'] as $ch_id => $ch_name)
			{
				foreach($orderby_dd_array_0 as $key => $lang_key)
				{
					$output['orderby_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$key] = $this->EE->lang->line($lang_key);
				}
			}
			
		}

		/**
		*	===========================================
		*	Extension Hook zenbu_add_column
		*	===========================================
		*
		*	Adds another standard setting row in the Display Settings section
		*	* This hook is used again here to add the field to visible columns based on settings
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
					foreach($ch_id_array as $key => $ch_id)
					{
						if(	isset($fal['column']) && 
							(isset($settings['setting'][$ch_id][$fal['column']]) && $settings['setting'][$ch_id][$fal['column']] == 'y') )
						{
							$column = (strncmp($fal['column'], 'show_', 5) == 0) ? substr($fal['column'], 5) : $fal['column'];
							$output['orderby_dropdown']['ch_id_'.$ch_id]['dropdown_labels'][$column]	= $this->EE->lang->line($column);
						}
					}

					if(	isset($fal['column']) && 
						(isset($settings['setting'][0][$fal['column']]) && $settings['setting'][0][$fal['column']] == 'y') )
					{
						$column = (strncmp($fal['column'], 'show_', 5) == 0) ? substr($fal['column'], 5) : $fal['column'];
						$output['orderby_dropdown']['ch_id_0']['dropdown_labels'][$column]	= $this->EE->lang->line($column);
					}
					
				}
				unset($hook_fields_and_labels);
			}
		}
		
		
		
		$results->free_result();

		$this->EE->session->set_cache('zenbu', 'first_and_orderby_dropdown', $output);

		return $output;	
	}
	

	/**
	* function _get_second_dropdown
	* Create arrays to display the combinations of second dropdowns for each rule
	* @return	array
	*/
	function _get_second_dropdown()
	{
		$output = array();

		$labels = array('is' => 'is');
		foreach($labels as $value => $label)
		{
			$output['channel_second_dropdown']['labels'][$value] = $label;
		}
		
		$labels = array(
			'is' => $this->EE->lang->line('is'), 
			'isnot' => $this->EE->lang->line('isnot')
			);
		foreach($labels as $value => $label)
		{
			$output['second_dropdown_type1']['labels'][$value] = $label;
		}
		
		$labels = array(
			'contains' 			=> $this->EE->lang->line('contains'), 
			'doesnotcontain' 	=> $this->EE->lang->line('doesnotcontain'), 
			);
		foreach($labels as $value => $label)
		{
			$output['second_dropdown_type2']['labels'][$value] = $label;
		}
		
		$labels = array(
			'contains' 			=> $this->EE->lang->line('contains'), 
			'doesnotcontain' 	=> $this->EE->lang->line('doesnotcontain'), 
			'beginswith' 		=> $this->EE->lang->line('beginswith'), 
			'doesnotbeginwith' 	=> $this->EE->lang->line('doesnotbeginwith'), 
			'endswith' 			=> $this->EE->lang->line('endswith'), 
			'doesnotendwith' 	=> $this->EE->lang->line('doesnotendwith'), 
			'containsexactly' 	=> $this->EE->lang->line('containsexactly'),
			'isempty'		 	=> $this->EE->lang->line('isempty'),
			'isnotempty' 		=> $this->EE->lang->line('isnotempty'),
			);
		foreach($labels as $value => $label)
		{
			$output['second_dropdown_type3']['labels'][$value] = $label;
		}

		return $output;
	}
	

	/**
	 * function _get_general_form_variables
	 * Get a few dropdowns and default values for main controller
	 * @return array Various static data used in Zenbu
	 */
	function _get_general_form_variables()
	{
		//	----------------------------------------
		//	Sticky
		//	----------------------------------------
		$vars['sticky']['dropdown_labels'] = array(
			"" => $this->EE->lang->line('sticky_both'),
			"n" => $this->EE->lang->line('not_sticky'),
			"y" => $this->EE->lang->line('is_sticky'),
		); 

		//	----------------------------------------
		//	Entry date range
		//	----------------------------------------
		$vars['entry_date']['dropdown_labels'] = array(
			""			=> $this->EE->lang->line('by_entry_date'),
			"1"			=> $this->EE->lang->line('in_past_day'),
			"7"			=> $this->EE->lang->line('in_past_week'),
			"30"		=> $this->EE->lang->line('in_past_month'),
			"180"		=> $this->EE->lang->line('in_past_six_months'),
			"365"		=> $this->EE->lang->line('in_past_year'),
			"&#43;1"	=> $this->EE->lang->line('next_day'),
			"&#43;7"	=> $this->EE->lang->line('next_week'),
			"&#43;30"	=> $this->EE->lang->line('next_month'),
			"&#43;180"	=> $this->EE->lang->line('next_six_months'),
			"&#43;365"	=> $this->EE->lang->line('next_year'),
			"range"		=> $this->EE->lang->line('between_these_dates'),
		);
		
		//	----------------------------------------
		//	Limit
		//	----------------------------------------
		$vars['limit']['dropdown_labels'] = array(
			//"1" => "1".'&nbsp;'.$this->EE->lang->line('result'),
			//"2" => "2".'&nbsp;'.$this->EE->lang->line('results'),
			"5" 	=> $this->EE->lang->line('show').'&nbsp;'."5".'&nbsp;'.$this->EE->lang->line('results'),
			"10" 	=> $this->EE->lang->line('show').'&nbsp;'."10".'&nbsp;'.$this->EE->lang->line('results'),
			"25" 	=> $this->EE->lang->line('show').'&nbsp;'."25".'&nbsp;'.$this->EE->lang->line('results'),
			"50" 	=> $this->EE->lang->line('show').'&nbsp;'."50".'&nbsp;'.$this->EE->lang->line('results'),
			"100" 	=> $this->EE->lang->line('show').'&nbsp;'."100".'&nbsp;'.$this->EE->lang->line('results'),
			"150" 	=> $this->EE->lang->line('show').'&nbsp;'."150".'&nbsp;'.$this->EE->lang->line('results'),
			"200" 	=> $this->EE->lang->line('show').'&nbsp;'."200".'&nbsp;'.$this->EE->lang->line('results'),
		);

		$max_results_per_page = $this->EE->session->cache('zenbu', 'max_results_per_page') != "" ? $this->EE->session->cache('zenbu', 'max_results_per_page') : '';

		if( ! empty($max_results_per_page))
		{
			$vars['limit']['dropdown_labels'][$max_results_per_page] = $this->EE->lang->line('show').'&nbsp;'.$max_results_per_page.'&nbsp;'.$this->EE->lang->line('results');
		}
		ksort($vars['limit']['dropdown_labels']);

		//	----------------------------------------
		//	Sort
		//	----------------------------------------
		$vars['sort']['dropdown_labels'] = array(
			"desc" 			=> $this->EE->lang->line('desc'),
			"asc" 			=> $this->EE->lang->line('asc'),
		);

		//	----------------------------------------
		//	Misc values
		//	----------------------------------------
		$vars['hidden'] = array(
			'S' => '0',
			'D' => 'cp',
			'C' => 'addons_modules',
			'M' => 'show_module_cp',
			'module' => $this->addon_short_name
			);

		$vars['can_delete_self_entries']	= $this->EE->session->userdata['can_delete_self_entries'];
		$vars['can_delete_all_entries']		= $this->EE->session->userdata['can_delete_all_entries'];

		//	----------------------------------------
		//	Action URLs
		//	----------------------------------------
		$vars['action_url']			= "C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=index";
		$vars['action_url_entries']	= "C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=multi_edit";
		$vars['action_url_manage_searches']	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=manage_searches";

		return $vars;
	}
	

	/**
	* function _get_settings
	* Retrieve settings for member group
	* @return	array User member group settings 
	*/
	function _get_settings($pre_save = FALSE)
	{
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'settings') && $pre_save === FALSE)
		{
			return $this->EE->session->cache('zenbu', 'settings');
		}

		$output = array();
		
		$results = $this->EE->db->query("/* Zenbu _get_settings */ \n SELECT zms.show_fields, zms.show_custom_fields, zms.field_order, zms.extra_options, zms.general_settings, z.can_access_settings, z.can_admin, z.can_copy_profile, z.edit_replace, z.can_view_group_searches, z.can_admin_group_searches 
		FROM exp_zenbu_member_settings zms
		JOIN exp_zenbu z ON z.member_group_id = " . $this->member_group_id . "
		WHERE zms.member_id = " . $this->member_id . "
		AND zms.site_id = " . $this->site_id . "
		AND z.site_id = " . $this->site_id);

		if($results->num_rows() == 0)
		{
			$results = $this->EE->db->query("/* Zenbu _get_settings - Fallback on member group settings */ \n SELECT *
			FROM exp_zenbu
			WHERE member_group_id = " . $this->member_group_id . "
			AND site_id = " . $this->site_id);
		}
				
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				// Fetch results for the settings view
				$show_fields_array			= unserialize($row['show_fields']);
				$show_custom_fields_array	= unserialize($row['show_custom_fields']);
				$field_order_array			= unserialize($row['field_order']);
				$extra_options_array		= unserialize($row['extra_options']);
				
				// Use $show_fields_array array to go through channel ids and merge data
				if($show_fields_array && $pre_save === FALSE)
				{
					foreach($show_fields_array as $channel_id => $array)
					{
						$output['setting'][$channel_id] = array_merge($array, $show_custom_fields_array[$channel_id], $field_order_array[$channel_id], $extra_options_array[$channel_id]);
					}
				}

				// Set up settings in pre-save format. Used for merging with save data.
				if($show_fields_array && $pre_save !== FALSE)
				{
					foreach($show_fields_array as $channel_id => $array)
					{
						$output['setting']['show_fields'][$channel_id]			= $array;
						$output['setting']['show_custom_fields'][$channel_id]	= $show_custom_fields_array[$channel_id];
						$output['setting']['field_order'][$channel_id]			= $field_order_array[$channel_id];
						$output['setting']['extra_options'][$channel_id]		= $extra_options_array[$channel_id];
					}	
				}
				
				$output['setting']['general']					= (isset($row['general_settings'])) ? unserialize($row['general_settings']) : array();
				
				foreach($this->permissions as $permission)
				{
					$output['setting'][$permission]	= $row[$permission];	
				}

			}
		} else {
			// If the row hasn't been created yet, create it from the default settings (member_group_id = 0).
			// Do not grant admin privileges until Super admin or other allowed group does
			$output['setting'] = $this->EE->zenbu_db->_insert_default_settings($this->member_group_id, '');
			
		}
		
		$results->free_result();
		
		if($pre_save === FALSE)
		{
			$this->EE->session->set_cache('zenbu', 'settings', $output);
		}

		return $output;
		
	}


	/**
	 * function get_assigned_data
	 * A way to get a user's assigned channels or modules,
	 * which can be used outside of the CP.
	 * @return $array The assigned data for the current user
	 */
	function get_assigned_data()
	{

		// Leave if we have this in cache already
		if($this->EE->session->cache('zenbu', 'assigned_data'))
		{
			return $this->EE->session->cache('zenbu', 'assigned_data');
		}

		//	----------------------------------------
		//	Assigned channels
		//	----------------------------------------
		if($this->member_group_id == 1)
		{
			$sql = "/* Zenbu get_assigned_data - channels */ SELECT channel_id, channel_title FROM exp_channels WHERE site_id = " . $this->site_id . " ORDER BY channel_title ASC";
		} else {
			$sql = "/* Zenbu get_assigned_data - channels */ SELECT cmg.channel_id, c.channel_title 
					FROM exp_channels c 
					JOIN exp_channel_member_groups cmg ON cmg.channel_id = c.channel_id 
					WHERE cmg.group_id = " . $this->member_group_id . "
					AND c.site_id = " . $this->site_id . "
					ORDER BY c.channel_title ASC";
		}

		$results = $this->EE->db->query($sql);
		
		$output['channels'] = array();
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output['channels'][$row['channel_id']] 	= $row['channel_title'];
			}
		}
		
		$results->free_result();

		//	----------------------------------------
		//	Assigned modules
		//	----------------------------------------
		if($this->member_group_id == 1)
		{
			$sql = '';
			$output['modules'] = array();
		} else {
			$sql = "/* Zenbu get_assigned_data - modules */ SELECT m.module_id, mmg.group_id 
					FROM exp_modules m 
					LEFT JOIN exp_module_member_groups mmg ON mmg.module_id = m.module_id";
		}

		if(empty($sql))
		{
			$this->EE->session->set_cache('zenbu', 'assigned_data', $output);
			return $output;
		}

		$results = $this->EE->db->query($sql);
		
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output['modules'][$row['module_id']] 	= ($row['group_id'] == $this->member_group_id) ? 1 : 0;	
			}
		}
		
		$results->free_result();
		
		$this->EE->session->set_cache('zenbu', 'assigned_data', $output);
		
		return $output;
	}


	/**
	* function _get_saved_searches
	* Retrieve member's saved search data
	* @return	array Rule ID and label 
	*/
	function _get_saved_searches()
	{
		$settings = $this->_get_settings();
		$settings = $settings['setting'];

		switch($settings)
		{
			case ($settings['can_view_group_searches'] == 'y' && $settings['can_admin_group_searches'] == 'n'):
				$type = 'member_group';
			break;
			case ($settings['can_view_group_searches'] == 'y' && $settings['can_admin_group_searches'] == 'y'):
				$type = 'all_member_groups';
			break;
			default:
				$type = 'member';
			break;
		}

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'saved_searches_'.$type))
		{
			return $this->EE->session->cache('zenbu', 'saved_searches_'.$type);
		}

		switch($type)
		{
			case 'member_group':
				$sql = "WHERE ((member_id = 0
						AND member_group_id = " . $this->member_group_id . ") OR member_id = " . $this->member_id . ") ";
			break;
			case 'all_member_groups':
				$sql = "WHERE (z.member_id = 0 OR z.member_id = " . $this->member_id . ")";
			break;
			default:
				$sql = "WHERE (z.member_id = " . $this->member_id . "
						AND z.member_group_id = 0)";
			break;
		}

		$results = $this->EE->db->query("/* Zenbu _get_search_searches */ \n SELECT * 
			FROM exp_zenbu_saved_searches z
			LEFT JOIN exp_member_groups mg 
			ON z.member_group_id = mg.group_id 
			" . $sql . "
			AND z.site_id = " . $this->site_id . "
			ORDER BY z.rule_order asc");
		
		$output = array();

		$output['search_listing_type'] = $type;
		
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				
				if($row['member_id'] == 0)
				{
					$output['group'][$row['member_group_id']]['group_id']	= $row['member_group_id']; 
					$output['group'][$row['member_group_id']]['group_name']	= $row['group_title']; 
					$output['group'][$row['member_group_id']]['rule_'.$row['rule_id']]['rule_label'] 	= $row['rule_label'];
					$output['group'][$row['member_group_id']]['rule_'.$row['rule_id']]['rule_id'] 	= $row['rule_id'];		
				
				} else {
				
					$output['member'][$row['rule_id']]['rule_label'] 	= $row['rule_label'];
					$output['member'][$row['rule_id']]['rule_id'] 	= $row['rule_id'];
				
				}
				
			}
		}
		
		$results->free_result();
		
		$this->EE->session->set_cache('zenbu', 'saved_searches_'.$type, $output);

		return $output;
	}
	
	
	/**
	* function _get_search_rules
	* Retrieve member's rules taken from saved search
	* @return	array Rule ID and label 
	*/
	function _get_search_rules($rule_id)
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'search_rules'))
		{
			return $this->EE->session->cache('zenbu', 'search_rules');
		}

		$results = $this->EE->db->query("/* Zenbu _get_search_rules */ \n SELECT rules 
		FROM exp_zenbu_saved_searches WHERE rule_id = " . $rule_id . " AND site_id = " . $this->site_id);
		
		$output = array();
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output = unserialize($row['rules']);
			}
		}
		
		$results->free_result();
		
		$this->EE->session->set_cache('zenbu', 'search_rules', $output);

		return $output;
	}
	
	
	/**
	 * Get authors that have posted
	 * @param 	$channel_id, $member_group_id
	 * @return	array
	 */
	function _get_author_dropdowns()
	{	

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'author_dropdown'))
		{
			return $this->EE->session->cache('zenbu', 'author_dropdown');
		}

		$results = $this->EE->db->query("/* Zenbu _get_author_dropdowns */ \n SELECT m.member_id, m.screen_name, c.channel_id 
		FROM exp_members m
		JOIN exp_channel_titles c ON m.member_id = c.author_id
		WHERE c.site_id = " . $this->site_id . "
		ORDER BY m.screen_name ASC");
			
		if($results->num_rows() > 0)
		{
				foreach($results->result_array() as $row)
				{
					$output['authors']['ch_id_0']['dropdown_labels']['0'] = $this->EE->lang->line('by_author');
					$output['authors']['ch_id_0']['dropdown_labels'][$row['member_id']] = $row['screen_name'];
				
					$output['authors']['ch_id_'.$row['channel_id']]['dropdown_labels']['0'] = $this->EE->lang->line('by_author');
					$output['authors']['ch_id_'.$row['channel_id']]['dropdown_labels'][$row['member_id']] = $row['screen_name'];
				}
		} else {
			$output['authors']['ch_id_0']['dropdown_labels']['0'] = $this->EE->lang->line('by_author');
		}
		
		$results->free_result();

		$this->EE->session->set_cache('zenbu', 'author_dropdown', $output);

		return $output;
			
	}
	
	
	/**
	 * Get authors that have posted
	 * @return	array
	 */
	function _get_authors()
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'authors'))
		{
			return $this->EE->session->cache('zenbu', 'authors');
		}

		$output = array();
		// First check if user can see other member's entries. If they can't, no point in populating this field any more.
		if($this->EE->session->userdata['can_view_other_entries'] == 'n')
		{
			return $output;
		}
		
		$results = $this->EE->db->query("/* Zenbu _get_authors */ \n SELECT exp_members.member_id, exp_members.screen_name FROM exp_members");
		
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output[$row['member_id']] = $row['screen_name'];
			}
		}
		
		$results->free_result();

		$this->EE->session->set_cache('zenbu', 'authors', $output);

		return $output;
	
	}
	

	/**
	 * Get authors last authors for entries with versioning
	 * @return	array
	 */
	function _get_last_authors()
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'last_authors'))
		{
			return $this->EE->session->cache('zenbu', 'last_authors');
		}

		$output = array();
		// First check if user can see other member's entries. If they can't, no point in populating this field any more.
		if($this->EE->session->userdata['can_view_other_entries'] == 'n')
		{
			return $output;
		}
		
		$results = $this->EE->db->query("/* Zenbu _get_last_authors */ \n SELECT exp_entry_versioning.version_id, exp_entry_versioning.entry_id, exp_members.screen_name 
			FROM exp_entry_versioning, exp_members 
			WHERE exp_entry_versioning.author_id = exp_members.member_id"
			);
		
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output[$row['entry_id']][$row['version_id']]['screen_name'] = $row['screen_name'];
			}
		}
		
		foreach($output as $entry_id => $version_array)
		{
			$array_max = array_keys($version_array);
			$key_max = max($array_max);
			$output[$entry_id] = $version_array[$key_max];
		}
		
		$results->free_result();

		$this->EE->session->set_cache('zenbu', 'last_authors', $output);
		
		return $output;
	}
	
	
	/**
	 * Retrieve statuses based on presence of channel id or not
	 * @param 	int $channel_id
	 * @return	array status properties
	 */
	function _get_status_dropdowns()
	{	

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'status_dropdowns'))
		{
			return $this->EE->session->cache('zenbu', 'status_dropdowns');
		}

		$assigned_data = $this->EE->zenbu_get->get_assigned_data();

		foreach($assigned_data['channels'] as $id => $title)
		{
			$channel_id[] = $id;
		}	
		
		
		/* Do the query */
		$results = $this->EE->db->query("/* Zenbu _get_status_dropdowns */ \n SELECT exp_statuses.status_id, exp_statuses.status, exp_statuses.highlight, exp_channels.channel_id 
			FROM exp_statuses 
			JOIN exp_channels ON exp_channels.status_group = exp_statuses.group_id 
			WHERE exp_statuses.site_id = ".$this->site_id);
		
		$result_channel_array 	= array();
		$ch_id 					= 0;
		if($results->num_rows() > 0)
		{				
			foreach($results->result_array() as $row)
			{
				$result_channel_array[] = $row['channel_id'];
				$ch_id 					= $row['channel_id'];
				$output['status']['ch_id_'.$ch_id]['dropdown_labels'][''] = $this->EE->lang->line('by_status');
				$output['status']['ch_id_'.$ch_id]['dropdown_labels'][$row['status']] = ucfirst($row['status']);
				
				// Pick up all statuses for "All channels"
				$output['status']['ch_id_0']['dropdown_labels']['0'] = $this->EE->lang->line('by_status');
				$output['status']['ch_id_0']['dropdown_labels'][$row['status']] = ucfirst($row['status']);
			}
		} 
		
		$results->free_result();
		
		foreach($channel_id as $key => $channel_id)
		{
			if( ! in_array($channel_id, $result_channel_array)) {
				
				// Populate at least Open/Closed if no results, which is a sign that the channel has no associated status group
				$ch_id = $channel_id;
				$output['status']['ch_id_'.$ch_id]['dropdown_labels'][''] = $this->EE->lang->line('by_status');
				$output['status']['ch_id_'.$ch_id]['dropdown_labels']['open'] = ucfirst($this->EE->lang->line('open'));
				$output['status']['ch_id_'.$ch_id]['dropdown_labels']['closed'] = ucfirst($this->EE->lang->line('closed'));
			}
		}
			
		// Just in case, add Open/Closed for "All channels", again if necessary when there are no results from the query	
		$output['status']['ch_id_0']['dropdown_labels']['0'] = $this->EE->lang->line('by_status');
		$output['status']['ch_id_0']['dropdown_labels']['open'] = $this->EE->lang->line('open');
		$output['status']['ch_id_0']['dropdown_labels']['closed'] = $this->EE->lang->line('closed');

		$this->EE->session->set_cache('zenbu', 'status_dropdowns', $output);

		return $output;
	} // END function _get_statuses2
	
	
	/**
	 * Retrieve statuses based on presence of channel id or not
	 * @param 	int $channel_id
	 * @return	array status properties
	 */
	function _get_statuses()
	{
		
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'statuses'))
		{
			return $this->EE->session->cache('zenbu', 'statuses');
		}

		/* Do the query */
		$results = $this->EE->db->query("/* Zenbu _get_statuses */ \n SELECT exp_statuses.status_id, exp_statuses.status, exp_statuses.highlight FROM exp_statuses WHERE exp_statuses.site_id = ".$this->site_id." GROUP BY exp_statuses.status");
		
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output[$row['status']]['id'] = $row['status_id'];
				$output[$row['status']]['status'] = $row['status'];
				$output[$row['status']]['highlight'] = $row['highlight'];
				if (isset($row['highlight']{0}))
				{
					$row['highlight'] = $row['highlight']{0} == '#' ?  $row['highlight'] : '#' . $row['highlight'];
				}
				$output[$row['status']]['cell_output'] = '<span style="color: '.$row['highlight'].'">'.ucfirst($row['status']).'</span>';
			}
		} else { // If a channel doesn't have a status assigned yet, or there are no statuses at all, show default open/closed statuses
			$output['open']['id'] = 1;
			$output['open']['status'] = $this->EE->lang->line('open');
			$output['open']['cell_output'] = '<span style="color: #009933">'.$this->EE->lang->line('open').'</span>';
			
			$output['closed']['id'] = 2;
			$output['closed']['status'] = $this->EE->lang->line('closed');
			$output['closed']['cell_output'] = '<span style="color: #990000">'.$this->EE->lang->line('closed').'</span>';
		}
		
		$results->free_result();

		$this->EE->session->cache('zenbu', 'statuses', $output);

		return $output;
	} // END function _get_statuses
	
	
	/**
	 * Retrieve category dropdowns for rules
	 * @param 	array $installed Array of installed addons
	 * @return	object category dropdown
	 */
	function _get_category_dropdowns($installed = array())
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'category_dropdowns'))
		{
			return $this->EE->session->cache('zenbu', 'category_dropdowns');
		}

		$assigned_data = $this->EE->zenbu_get->get_assigned_data();

		foreach($assigned_data['channels'] as $id => $title)
		{
			$channel_id[] = $id;
		}
		
		$channel_id_array = $channel_id;
		$channel_id_string = implode(',', $channel_id);
		$cat_array = array();
		$category_permissions_installed = (in_array('Category_permissions_ext', $installed['extensions'])) ? TRUE : FALSE;
		
		// Extra processing if "Category Rights" module is installed.
		// Build a query snippet for final query if user has restricted categories
		$permitted_cats = "";
		$permitted_cat_array = array();
		if($category_permissions_installed === TRUE)
		{
			// Load the Category Permissions class and let it
			// do the heavy lifting to fetch permitted categories
			$this->EE->load->add_package_path(PATH_THIRD . '/category_permissions');
			$this->EE->load->model('category_permissions_model');
			$permitted_cats = $this->EE->category_permissions_model->get_member_permitted_categories($this->member_id);
			$this->EE->load->remove_package_path(PATH_THIRD . '/category_permissions');
			
			if( ! empty($permitted_cats))
			{
				$permitted_cat_array = $permitted_cats;
				$permitted_cats = '';
				$this->output['allowed_categories'] = implode($permitted_cat_array, ", ");
				$this->EE->session->set_cache('zenbu', 'permitted_cats', $permitted_cat_array);
			
			} elseif($this->member_group_id != 1) {
			
				$permitted_cats = "AND cat_id IN (0)";
				$this->output['allowed_categories'] = '0';
				$this->EE->session->set_cache('zenbu', 'permitted_cats', array());
			
			}
		}
		
		// Get category group ids for the channel		
		$results = $this->EE->db->query("/* Zenbu category query */\nSELECT REPLACE(cat_group, '|', ',') as cat_groups, channel_id FROM exp_channels WHERE site_id = ".$this->site_id);
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$ch_id = $row['channel_id'];
				$cat_groups = $row['cat_groups'];
				$cat_groups = preg_replace('{(\,)\1+}','$1', $cat_groups);

				//	----------------------------------------
				//	Add default category dropdown when no channel is present
				//	----------------------------------------
				$this->output['categories']['ch_id_0']['dropdown_labels'][''] = $this->EE->lang->line('by_categories');
				$this->output['categories']['ch_id_0']['dropdown_labels']['none'] = $this->EE->lang->line('entries_with_no_categories');
				
				//	----------------------------------------
				//	...for when a channel is present
				//	----------------------------------------
				$this->output['categories']['ch_id_'.$ch_id]['dropdown_labels'][''] = $this->EE->lang->line('by_categories');
				$this->output['categories']['ch_id_'.$ch_id]['dropdown_labels']['none'] = $this->EE->lang->line('entries_with_no_categories');
				
				// Need to remove the first comma if "None" was also selected among category groups in Channel preferences
				// Personally, "None" shouldn't even be an option in the multiselect field!
				$cat_groups = (substr($cat_groups, 0, 1) == ',') ? substr($cat_groups, 1) : $cat_groups;
				// Remove the last comma if it's the last thing in the list
				$cat_groups = (substr($cat_groups, -1, 1) == ',') ? substr($cat_groups, 0, -1) : $cat_groups;
				
				// Get the categories and prepare for listing
				if( ! empty($cat_groups)) // Sometimes no cat groups are set in the DB
				{ 		
					// Do the query
					$cat_array = array();
					$results = $this->EE->db->query("/* Zenbu _get_category_dropdowns */ \n SELECT exp_categories.cat_id, exp_categories.parent_id, exp_categories.cat_name, exp_categories.cat_url_title, exp_categories.cat_order, exp_category_groups.group_id, exp_category_groups.group_name 
					FROM exp_categories 
					LEFT JOIN exp_category_groups ON exp_category_groups.group_id = exp_categories.group_id 
					WHERE exp_categories.group_id IN (".$cat_groups.") ".$permitted_cats." 
					AND exp_categories.site_id = ".$this->site_id." 
					ORDER BY exp_categories.cat_order");
					if($results->num_rows() > 0)
					{
						foreach($results->result_array() as $row)
						{
							$cat_array[$row['cat_id']] = array(
								'group_id' 			=> $row['group_id'],
								'group_name' 		=> $row['group_name'],
								'parent_id' 		=> $row['parent_id'],
								'cat_order' 		=> $row['cat_order'],
								'id'				=> $row['cat_id'],
								'cat_name'			=> $row['cat_name'],
								'cat_url_title'		=> $row['cat_url_title'],
								);
							//$output['categories']['ch_id_'.$ch_id]['dropdown_labels'][$row['group_name']][$row['cat_id']] = $row['cat_name'];
						}
					}
					
					
					// Going through first-level categories
					/** ------------------------
					*	NOTE: Usually permitted cats downstream of a parent, non-permitted cat
					*	should not be displayed.
					*	To enable this behavior, modify conditional when Category Permissions is installed
					*/
					$cat_array_all = array();
					foreach($cat_array as $cat_id => $array)
					{
						$cat_array_all[] = $cat_id;
						// First level
						$level = 0;
						
						if( $array['parent_id'] == 0 )
						{
							// No channel
							$this->output['categories']['ch_id_0']['dropdown_labels'][$array['group_name']][$cat_id] = $array['cat_name'];

							// With channel
							$this->output['categories']['ch_id_'.$ch_id]['dropdown_labels'][$array['group_name']][$cat_id] = $array['cat_name'];
							
							// Must build this array to have unique id : category arrays (prevents overwriting when cat_name is same for more than one category)
							$this->output['categories']['cat_url_title'][$cat_id] = $array['cat_url_title'];
							
							// …then going down recursively with the _get_sub_categories function
							$this->_get_sub_categories($cat_id, $cat_array, $level, $ch_id);
						}
					}
					
					//	-----------------------------------
					//	Remove non-permitted categories
					//	-----------------------------------
					//	Excempt Super Admins from this processing
					if($category_permissions_installed === TRUE && $this->member_group_id != 1)
					{
						// Retrieve unwanted categories (i.e. non-permitted categories)
						$unwanted_cats = array_diff($cat_array_all, $permitted_cat_array);
						
						foreach($unwanted_cats as $key => $unwanted_cat_id)
						{
							foreach($cat_array as $cat_id => $array)
							{
								//	Empty $permitted_cat_array happens to users with NO permitted categories
								//	In which case, the whole category dropdown is removed
								if(empty($permitted_cat_array))
								{
									unset($this->output['categories']['ch_id_'.$ch_id]['dropdown_labels'][$array['group_name']]);
								} else {
									unset($this->output['categories']['ch_id_'.$ch_id]['dropdown_labels'][$array['group_name']][$unwanted_cat_id]);
								}
							}
						}
						
					}
			
				}
			}
		}

		$results->free_result();

		$this->EE->session->set_cache('zenbu', 'category_dropdowns', $this->output);
		
		return $this->output;
	}
	
		
	/**
	 * Retrieve sub categories
	 * @param 	int $parent_id The cat ID of the parent
	 * @param 	array $cat_array Array of all needed category characteristics, with cat_id as key
	 * @param 	int $level The category level "deepness"
	 * @return	object category dropdown
	 */
	function _get_sub_categories($parent_id, $cat_array, $level, $channel_id, $permitted_cat_array = array())
	{
		$c = 0;
		foreach($cat_array as $cat_id => $array)
		{
			if($parent_id == $array['parent_id'])
			{
				// Going down a level, so add symbol for subcategory
				if($c == 0)
				{
					$level++;
					$c++;
				}
				$sub_indicator = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level);
				
				//	----------------------------------------
				//	Output the dropdown option
				//	----------------------------------------

				// No channel
				$this->output['categories']['ch_id_0']['dropdown_labels'][$cat_array[$cat_id]['group_name']][$cat_array[$cat_id]['id']] = $sub_indicator.' '. /* '('.$cat_id.') '.*/ $cat_array[$cat_id]['cat_name'];

				// With channel
				$this->output['categories']['ch_id_'.$channel_id]['dropdown_labels'][$cat_array[$cat_id]['group_name']][$cat_array[$cat_id]['id']] = $sub_indicator.' '. /* '('.$cat_id.') '.*/ $cat_array[$cat_id]['cat_name'];
				
				// Must build this array to have unique id : category arrays (prevents overwriting when cat_name is same for more than one category)
				$this->output['categories']['cat_url_title'][$cat_id] = $cat_array[$cat_id]['cat_url_title'];
				
				// Check for any deper levels
				$this->_get_sub_categories($cat_array[$cat_id]['id'], $cat_array, $level, $channel_id, $permitted_cat_array);

			}
		}
	}
	
	
	/**
	 * Setup category listing for view
	 * @param 	int $entry_id
	 * @return	string comma separated list of categories
	 */
	function _get_category_list($entry_array)
	{
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'category_list'))
		{
			return $this->EE->session->cache('zenbu', 'category_list');
		}

		$output = "";
		if( empty($entry_array))
		{
			return $output;
		}
		
		$entry_ids = "";
		foreach($entry_array as $key => $entry_id)
		{
			$entry_ids .= $entry_id.', ';
		}
		$entry_ids = substr($entry_ids, 0 , -2);
		
		$results = $this->EE->db->query("SELECT c.cat_id, c.cat_name, cp.entry_id FROM exp_categories c NATURAL JOIN exp_category_posts cp WHERE c.site_id = ".$this->site_id." AND cp.entry_id IN (".$entry_ids.") ORDER BY c.cat_name ASC");
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output[$row['entry_id']][$row['cat_id']] = $row['cat_name'];
			}
		}
		
		$results->free_result();

		$this->EE->session->set_cache('zenbu', 'category_list', $output);
		
		return $output;
	}
	
	
	/**
	 * _get_channel_data
	 * Retrieves channel data to populate dropdown
	 * @param 	int $member_group_id
	 * @return	array channel dropdown
	 *
	 */
	function _get_channel_data($member_group_id)
	{
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'channel_data'))
		{
			return $this->EE->session->cache('zenbu', 'channel_data');
		}

		$output = array();
		//$site_id = $this->EE->session->userdata['site_id'];
		$output['channels']['dropdown_labels']['0'] = $this->EE->lang->line('by_channel');
		$output['channels']['channel_data']['0']['id'] = '0';
		$output['channels']['channel_data']['0']['channel_title'] = $this->EE->lang->line('multi_channel_entries');
		$assigned_data = $this->EE->zenbu_get->get_assigned_data();

		foreach($assigned_data['channels'] as $channel_id => $channel_title)
		{
			$output['channels']['dropdown_labels'][$channel_id] = $channel_title;
			$output['channels']['channel_data'][$channel_id]['id'] = $channel_id;
			$output['channels']['channel_data'][$channel_id]['channel_title'] = $channel_title;
		}

		/**
		*	===========================================
		*	Extension Hook zenbu_modify_channel_data
		*	===========================================
		*
		*	Modifies the channel array, used for the Zenbu 
		*	channel dropdown, for example
		*	@return $output 	array	An array containing channel-related data
		*/
		if ($this->EE->extensions->active_hook('zenbu_modify_channel_data') === TRUE)
		{
			$output = $this->EE->extensions->call('zenbu_modify_channel_data', $output);
		 	if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->session->set_cache('zenbu', 'channel_data', $output);

		return $output;
	}
	
	
	/**
	* Function _get_file_upload_prefs
	* @return	array upload preferences
	*/
	function _get_file_upload_prefs()
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'file_upload_prefs'))
		{
			return $this->EE->session->cache('zenbu', 'file_upload_prefs');
		}

		$result = array();
		if (version_compare(APP_VER, '2.4', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			$result = $this->EE->file_upload_preferences_model->get_file_upload_preferences($this->member_group_id);
			return $result;
		}

		if (version_compare(APP_VER, '2.1.5', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			$result = $this->EE->file_upload_preferences_model->get_upload_preferences($this->member_group_id);
		} else {
			$this->EE->load->model('tools_model');
			$result = $this->EE->tools_model->get_upload_preferences($this->member_group_id);
		}

		// Use upload destination ID as key for row for easy traversing
		$output = array();
		if( ! empty($result) && $result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				$output[$row['id']]['url'] = $row['url'];
				$output[$row['id']]['server_path'] = $row['server_path'];
			}
		}

		$this->EE->session->set_cache('zenbu', 'file_upload_prefs', $output);

		return $output;

	} // END function _get_file_upload_prefs

	
	/**
	* Function _get_field_ids
	* Retrieve field id, fieldtypes, name, and more
	* @return	array 	Fieldtype data
	*/
	function _get_field_ids()
	{
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'file_ids'))
		{
			return $this->EE->session->cache('zenbu', 'file_ids');
		}

		$output = array();
		
		//$channel_id = ($channel_id != "") ? "AND exp_channels.channel_id = ".$channel_id : ''; 
		$results = $this->EE->db->query("/* Zenbu _get_field_ids */ \n SELECT exp_channels.channel_id, 
			 exp_channel_fields.field_id, 
			 exp_channel_fields.field_label,
			 exp_channel_fields.field_type,
			 exp_channel_fields.field_settings,
			 exp_channel_fields.field_text_direction
			 FROM exp_channels, exp_channel_fields 
			 WHERE exp_channel_fields.group_id = exp_channels.field_group
			 AND exp_channel_fields.site_id = ".$this->site_id . "
			 ORDER BY exp_channel_fields.field_order ASC"
			 );
	 
	 	if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$ch_id = $row['channel_id'];
				
				// Basic field data
				$output['field'][$row['field_id']] = $row['field_label'];
				$output['fieldtype'][$row['field_id']] = $row['field_type'];
				$output['id'][$row['field_id']] = $row['field_id'];
				$output['field_text_direction'][$row['field_id']] = $row['field_text_direction'];
				$output['settings'][$row['field_id']] = unserialize(base64_decode($row['field_settings']));
				
				// Basic field data, per channel
				$output[$ch_id]['field'][$row['field_id']] = $row['field_label'];
				$output[$ch_id]['fieldtype'][$row['field_id']] = $row['field_type'];
				$output[$ch_id]['id'][$row['field_id']] = $row['field_id'];
				$output[$ch_id]['field_text_direction'][$row['field_id']] = $row['field_text_direction'];
				$output[$ch_id]['settings'][$row['field_id']] = unserialize(base64_decode($row['field_settings']));

				// Get Class
				$ft_class = $row['field_type'].'_ft';
				load_ft_class($ft_class);
				
				if(class_exists($ft_class))
				{
					$ft_object = create_object($ft_class);
					// Build array of special dropdowns (eg. contains/doesnotcontain)
					if(isset($ft_object->dropdown_type))
					{
						switch ($ft_object->dropdown_type)
						{
							case "contains_doesnotcontain":
								$output['field_option_type']['field_'.$row['field_id']] = 'contains_doesnotcontain';
							break;
							case "date":
								$output['field_option_type']['field_'.$row['field_id']] = 'date';
							break;
							case "standard":
								$output['field_option_type']['field_'.$row['field_id']] = 'standard';
							break;
							default:
								$output['field_option_type']['field_'.$row['field_id']] = 'standard';
							break;	
						}
						
					} else {
						$output['field_option_type']['field_'.$row['field_id']] = 'standard';
					}
				}
				
				// Fieldtype dropdowns
				$output['custom_fields']['ch_id_'.$ch_id]['dropdown_labels']['CUSTOM FIELDS']['field_'.$row['field_id']] = $row['field_label'];
			}
		}	
			
		$results->free_result();

		//	----------------------------------------
		//	Field option types for other entry elements
		//	- 	Not field-related per-se, but might as well 
		//		do it here while we're at it
		//	----------------------------------------
		$entry_elements = array(
			'title'				=> 'standard', 
			'url_title'			=> 'standard', 
			'id'				=> 'is_isnot',
			'cat_id'			=> 'is_isnot',
			'status'			=> 'is_isnot',
			'author'			=> 'is_isnot',
			'sticky'			=> 'is_isnot',
			'any_cf_title'		=> 'standard',
			'date'				=> 'date',
			'expiration_date'	=> 'date',
			'edit_date'			=> 'date'
		);

		foreach($entry_elements as $elem => $type)
		{
			$output['field_option_type'][$elem] = $type;
		}

		$this->EE->session->set_cache('zenbu', 'file_ids', $output);
		
		return $output;
			
	} // END _get_field_ids
	
	
	/**
	* Function _get_basic_template_data
	* Retrieve template group and name, used for Live Look setting
	* @param 	int $channel_id
	* @return	array template data
	*/
	function _get_basic_template_data($channel_id = "", $use_channel_as_key = FALSE)
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'basic_template_data'))
		{
			return $this->EE->session->cache('zenbu', 'basic_template_data');
		}

		$output = array();
		if( ! empty($channel_id) && is_array($channel_id))
		{
			$channel_id = implode(',', $channel_id);
			$channel_filter = ($channel_id != "0") ? " AND exp_channel_titles.channel_id IN (".$channel_id.")" : '';
		} else {
			$channel_filter = "";
		}
		
		$key = ($use_channel_as_key === TRUE) ? 'channel_id' : 'entry_id';
		
		$query = $this->EE->db->query("/* Zenbu _get_basic_template_data */ \n SELECT exp_channels.channel_id, exp_channel_titles.entry_id, exp_templates.template_name, exp_template_groups.group_name, exp_template_groups.site_id
			FROM (exp_template_groups)
			JOIN exp_templates ON exp_templates.group_id = exp_template_groups.group_id
			JOIN exp_channels ON exp_channels.live_look_template = exp_templates.template_id
			JOIN exp_channel_titles ON exp_channel_titles.channel_id = exp_channels.channel_id
			WHERE exp_channel_titles.site_id = " . $this->site_id . $channel_filter);
		
		if($query->num_rows() > 0)
		{
				foreach($query->result_array() as $row)
				{
					$output[$row[$key]]['site_id']			= $row['site_id'];
					$output[$row[$key]]['group_name'] 		= $row['group_name'];
					$output[$row[$key]]['template_name'] 	= $row['template_name'];
				}
		}
		
		$query->free_result();

		$this->EE->session->set_cache('zenbu', 'basic_template_data', $output);
		
		return $output;

	} // END _get_basic_template_data

	
	/**
	* Gets a list of installed and accessible modules
	* @return array	Simple array of installed modules
	*/
	function _get_installed_addons()
	{
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'installed_addons'))
		{
			return $this->EE->session->cache('zenbu', 'installed_addons');
		}

		$output = array();
				
		$query = $this->EE->db->query("/* Zenbu _get_installed_addons */ \n SELECT m.module_id, m.module_name, m.module_version, e.extension_id, e.class, e.version 
		FROM exp_modules m, exp_extensions e");
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$output['modules'][$row['module_id']] = $row['module_name'];	// Modules
				$output['modules_versions'][$row['module_name']] = $row['module_version'];
				$output['extensions'][$row['extension_id']] = $row['class'];			// Extensions
				$output['extensions_versions'][$row['class']] = $row['version'];
			}
		}
		
		$query->free_result();

		$this->EE->session->set_cache('zenbu', 'installed_addons', $output);
		
		return $output;
	}
	

	/**
	*
	* @param $entry_array	array	an array of entry ids
	* 
	*/
	function _get_core_entry_data($entry_array)
	{

		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'core_entry_data'))
		{
			return $this->EE->session->cache('zenbu', 'core_entry_data');
		}

		$output = array();
		if(empty($entry_array))
		{
			return $output;
		}
		$this->EE->db->select(array("entry_id", "title", "channel_id"));
		$this->EE->db->from("channel_titles");
		$this->EE->db->where_in("entry_id", $entry_array);
		$query = $this->EE->db->get();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$output[$row['entry_id']]['id'] 			= $row['entry_id'];
				$output[$row['entry_id']]['title'] 			= $row['title'];
				$output[$row['entry_id']]['channel_id'] 	= $row['channel_id'];
			}
		}
		
		$query->free_result();

		$this->EE->session->set_cache('zenbu', 'core_entry_data', $output);
		
		return $output;
		
	}
	
	
	/**
	* Gets an array of page data if Pages module is installed
	* @return array	Raw array of entry settings for the Pages module
	*/
	function _get_pages()
	{
		// Return data if already cached
		if($this->EE->session->cache('zenbu', 'pages'))
		{
			return $this->EE->session->cache('zenbu', 'pages');
		}

		$output = array();
		
		$this->EE->db->from('sites');
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get();
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$output = isset($row['site_pages']) ? unserialize(base64_decode($row['site_pages'])) : array();
			}
		}
		
		$query->free_result();
		
		// Make this an entry_id => URI array 
		$output = (isset($output[$this->site_id]['uris'])) ? $output[$this->site_id]['uris'] : array();
		
		$this->EE->session->set_cache('zenbu', 'pages', $output);

		return $output;
	} // END _get_pages

	// --------------------------------------------------------------------

	/**
	 * ========================
	 * Function _convert_channel_to_string
	 * ========================
	 * Converts channel_id to string (if necessary) when
	 * needed in that format
	 *
	 * @param 	array $channel_id
	 * @return	string $channel_id
	 */
	private function _convert_channel_to_string($channel_id)
	{
		if(empty($channel_id))
		{
			$channel_id = 0;
		} else {

			if(is_array($channel_id))
			{
				foreach($channel_id as $key => $val)
				{
					$channel_id = $val;
				}
			}
		}

		return $channel_id;
	} // END _convert_channel_to_string

	// --------------------------------------------------------------------
	
	
	/**
	 * ========================
	 * Function _get_entry_data
	 * ========================
	 * Does the heavy lifting by retrieving custom field_data
	 * Builds the query and prepares the output
	 *
	 * @param 	array $settings
	 * @param	array $field_ids
	 * @param	int $channel_id
	 * @param	int $cat_id
	 * @param	int $author_id
	 * @param	string $status
	 * @param	string $sticky
	 * @param	int $date
	 * @param	int $limit
	 * @param	int $perpage
	 * @param	string $in
	 * @param	string $search_in
	 * @param	string $keyword
	 * @return	array Entry data and formatted displays
	 */
	function _get_entry_data($settings, $field_ids, $rules, $channel_id, $output_channel, $categories, $fieldtypes)
	{	
		$this->EE->load->model('zenbu_display');
		$this->EE->load->helper('display');
		$this->EE->load->helper('loader');
		
		$channel_id 		= $this->_convert_channel_to_string($channel_id);
		$assigned_data		= $this->EE->zenbu_get->get_assigned_data();
		$output				= array();
		$entry_array		= array(); // Used later for a simple array of result entries
		$installed_addons	= $this->EE->zenbu_get->_get_installed_addons();
		$comment_module		= (in_array('Comment', $installed_addons['modules'])) ? TRUE : FALSE;
		$pages_module		= (in_array('Pages', $installed_addons['modules'])) ? TRUE : FALSE;
		$extra_options		= isset($settings['setting'][$channel_id]['extra_options']) ? $settings['setting'][$channel_id]['extra_options'] : array();
		
		/**
		 * Not very elegant, but if all fields are to be searched into,
		 * replace $field_ids array temporarily with an all-fields array
		 * Restore "to be shown" fields after the query is done
		 */
		if( isset($settings['setting']['general']['enable_hidden_field_search']) && $settings['setting']['general']['enable_hidden_field_search'] == 'y' && $channel_id != 0 )
		{
			$field_ids_to_show = $field_ids;
			$field_ids = $this->_get_field_ids();
		}

		$default_order 	= (isset($settings['setting']['general']['default_order'])) ? $settings['setting']['general']['default_order'] : "entry_date";
		$default_sort 	= (isset($settings['setting']['general']['default_sort'])) ? $settings['setting']['general']['default_sort'] : "desc";
		
		// Get limit and perpage values if flash data.
		$limit 			= ($this->EE->session->cache('zenbu', 'limit')) ? $this->EE->session->cache('zenbu', 'limit') : $this->EE->input->get_post('limit', TRUE);
		// Check if limit is available from return_to_zenbu data
		$limit 			= (isset($settings['setting']['general']['limit'])) ? $settings['setting']['general']['limit'] : $limit;
		$perpage 		= ($this->EE->session->cache('zenbu', 'perpage')) ? ($this->EE->session->cache('zenbu', 'perpage')) : $this->EE->input->get_post('perpage', TRUE);
		/*$perpage 			= (isset($settings['setting']['general']['perpage'])) ? $settings['setting']['general']['perpage'] : $perpage;*/
		
		// Replace perpage with new values if stored as session value
		if($this->EE->input->get('zenbu', TRUE) === FALSE)
		{
			if(isset($_SESSION['zenbu']['perpage']) && $this->EE->input->get('return_to_zenbu') == 'y')
			{
				$perpage = $_SESSION['zenbu']['perpage'];
			}
		}

		$orderby 		= ($this->EE->input->get_post('orderby', TRUE)) ? $this->EE->input->get_post('orderby', TRUE) : $default_order;
		$sort 			= ($this->EE->input->get_post('sort', TRUE)) ? $this->EE->input->get_post('sort', TRUE) : $default_sort;
		
		
		//$output_date 			= $this->_get_member_date_settings();
		$output_status 			= $this->_get_statuses();
		$output_authors 		= $this->_get_authors();
		$output_last_authors 	= $this->_get_last_authors();
		$output_upload_prefs 	= $this->_get_file_upload_prefs();
		$output_templates 		= $this->_get_basic_template_data($channel_id);
		$output_pages			= ($pages_module === TRUE) ? $this->_get_pages() : array();
		
		// SQL_CALC_FOUND_ROWS will help get total_results later
		$this->EE->db->select('SQL_NO_CACHE SQL_CALC_FOUND_ROWS exp_channel_titles.entry_id, exp_channel_titles.url_title, exp_channel_titles.author_id', false);
		$this->EE->db->where('channel_titles.site_id', $this->site_id);
		
		// First check if user can see other member's entries. If they can't, search in user's entries only.
		if($this->EE->session->userdata['can_view_other_entries'] == 'n')
		{
			$this->EE->db->where('channel_titles.author_id', $this->member_id);
		}
		
		//	----------------------------------------
		// 	Add query based on settings
		// 	Based on channel_id (if not, the query is made, but not set to display... for now)
		//	----------------------------------------
		
		$this->EE->db->select("channel_titles.channel_id"); // This has to be done, since channel id is important
		$queries = array(
			"show_id"				=> "",
			"show_title"			=> "channel_titles.title",
			"show_channel"			=> "channel_titles.channel_id",
			"show_url_title" 		=> "channel_titles.url_title",
			"show_author"			=> "channel_titles.author_id",
			"show_status"			=> "channel_titles.status",
			"show_sticky"			=> "channel_titles.sticky",
			"show_entry_date"		=> "channel_titles.entry_date",
			"show_expiration_date"	=> "channel_titles.expiration_date",
			"show_edit_date"		=> "channel_titles.edit_date",
			"show_comments"			=> "channel_titles.comment_total",
			"show_view_count"		=> array(
										"channel_titles.view_count_one",
										"channel_titles.view_count_two",
										"channel_titles.view_count_three",
										"channel_titles.view_count_four",
										),
			"show_view"				=> array(
										"channels.live_look_template",
										"channel_titles.url_title",
										),
			"show_categories"		=> "",
			"show_last_author"		=> "",
			"show_autosave"			=> "channel_entries_autosave.entry_id AS autosave_entry_id",
			);
		foreach($queries as $option => $query)
		{
			switch ($option)
			{
				case "show_categories": case "show_id": case "show_channel":
					$output[$option] = (isset($settings['setting'][$channel_id][$option]) && $settings['setting'][$channel_id][$option] == 'y') ? 'y' : 'n';
				break;
				case "show_comments":
					if($comment_module === TRUE)
					{
						(isset($settings['setting'][$channel_id][$option]) && !empty($settings['setting'][$channel_id][$option])) ? $this->EE->db->select($query) : '';
						
						$output[$option] = (isset($settings['setting'][$channel_id][$option]) && $settings['setting'][$channel_id][$option] == 'y') ? 'y' : 'n';
					}
				break;
				default:
					if (isset($settings['setting'][$channel_id][$option]) && ! empty($settings['setting'][$channel_id][$option]) && ! empty($query))
					{
						if(is_array($query))
						{
							foreach($query as $key => $query_multi)
							{
								$this->EE->db->select($query_multi);
							}
						} else {
							$this->EE->db->select($query);		
						}
					}
					
					$output[$option] = (isset($settings['setting'][$channel_id][$option]) && $settings['setting'][$channel_id][$option] == 'y') ? 'y' : 'n';
				break;
			}
		}

		/**
		*	===========================================
		*	Extension Hook zenbu_add_column
		*	===========================================
		*
		*	Adds another standard setting row in the Display Settings section
		*	* This hook is used again here to add the field to dropdowns
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
					$option = isset($fal['column']) ? $fal['column'] : '';
					$output[$option] = isset($settings['setting'][$channel_id][$option]) && $settings['setting'][$channel_id][$option] == 'y' ? 'y' : 'n';
				}
				unset($hook_fields_and_labels);
			}
		}
	
		$count = 0;
		if( ! empty($channel_id) && count($channel_id) == 1 )
		{
			$this->EE->db->where("channel_titles.channel_id", $channel_id);
		} else {
			$this->EE->db->where_in("channel_titles.channel_id", array_flip($assigned_data['channels']));
		}
		
		$already_queried_matrix = FALSE;
		$already_queried_ch_img = FALSE;
		
		foreach($rules as $rule)
		{
			if(isset($rule['field']))
			{
				switch ($rule['field'])
				{
					case 'any_cf_title':
						$keyword = trim($this->EE->db->escape_like_str($rule['val']));
						if ( ! empty($keyword) && isset($field_ids['id'])) 
						{
							$where = "";
							switch($rule['cond'])
							{
								case "contains":
									$where = "(exp_channel_titles.title LIKE '%" . $keyword . "%' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " LIKE '%" . $keyword . "%' OR ";
									}
								break;
								case "doesnotcontain":
									$where = "(exp_channel_titles.title NOT LIKE '%" . $keyword . "%' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " NOT LIKE '%" . $keyword . "%' OR ";
									}
								break;
								case "beginswith":
									$where = "(exp_channel_titles.title LIKE '" . $keyword . "%' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " LIKE '" . $keyword . "%' OR ";
									}
								break;
								case "doesnotbeginwith":
									$where = "(exp_channel_titles.title NOT LIKE '%" . $keyword . "%' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " NOT LIKE '%" . $keyword . "%' OR ";
									}
								break;
								case "endswith":
									$where = "(exp_channel_titles.title LIKE '%" . $keyword . "' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " LIKE '%" . $keyword . "' OR ";
									}
								break;
								case "doesnotendwith":
									$where = "(exp_channel_titles.title NOT LIKE '%" . $keyword . "' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " NOT LIKE '%" . $keyword . "' OR ";
									}
								break;
								case "containsexactly":
									$where = "(exp_channel_titles.title LIKE '" . $keyword . "' OR ";
									foreach($field_ids['id'] as $key => $f_id)
									{
										$where .= "exp_channel_data.field_id_".$f_id . " LIKE '" . $keyword . "' OR ";
									}
								break;
							}
							$where = substr($where, 0, -4) . ')';
							$this->EE->db->where($where);
						}
					break;
					case 'id':
						if($rule['cond'] == 'is' && ! empty($rule['val']))
						{
							$this->EE->db->where('exp_channel_titles.entry_id', $rule['val']);
						} elseif($rule['cond'] = 'isnot' && ! empty($rule['val'])) {
							$this->EE->db->where('exp_channel_titles.entry_id !=', $rule['val']);
						}
					break;
					case 'channel_id': // Keeping this if ever is/is not is used
						if($rule['cond'] == 'is' && $rule['val'] != "0")
						{
							//$this->EE->db->where('exp_channel_titles.channel_id', $rule['val']);
						} elseif($rule['cond'] = 'isnot' && $rule['val'] != "0") {
							$this->EE->db->where('exp_channel_titles.channel_id !=', $rule['val']);
						}
					break;
					case 'cat_id':
						/**
						 * Add category filetering if cat_id is present
						 */
						$cat_id = $rule['val'];
						$cat_cond = $rule['cond'];
						
						
						/**
						*	Specific Category ID provided
						*/
						if( is_numeric($cat_id) )
						{
							//
							// Under the effect of Category Permissions
							//
							if(in_array('Category_permissions_ext', $installed_addons['extensions']) && $this->member_group_id != 1)
							{
								if( $this->EE->session->cache('zenbu', 'permitted_cats') )
								{
								
									$permitted_cats = $this->EE->session->cache('zenbu', 'permitted_cats');
								
								} else {
								
									$this->EE->load->add_package_path(PATH_THIRD . '/category_permissions');
									$this->EE->load->model('category_permissions_model');
									$permitted_cats = $this->EE->category_permissions_model->get_member_permitted_categories($this->member_id);
									$this->EE->load->remove_package_path(PATH_THIRD . '/category_permissions');
								
								}
								
								if( ! empty($permitted_cats))
								{
									
									$cat_where_in = $permitted_cats;
									
									if( ! in_array($cat_id, $cat_where_in) )
									{
										$entry_id_array[] = 0;
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
									} else {
										$entry_id_array = array();
										// Check if cat_id is part of allowed cat_ids in channel
										// The following output is a category id/name array. If channel has no associated category group, set variable to empty array to skip category filtering
										
										// Have $cat_cond conditional here, and intersect $cat_id & $cat_where_in for "isnot" condition
										if($cat_cond == "is")
										{
											$results = $this->EE->db->query("SELECT entry_id FROM exp_category_posts WHERE cat_id IN (".$cat_id.")");
										} else {
											$cat_id_single[] = $cat_id;
											$cat_id_leftover = array_diff($cat_where_in, $cat_id_single);
											$cat_id_leftover = implode(",", $cat_id_leftover);
											$results = $this->EE->db->query("SELECT entry_id FROM exp_category_posts WHERE cat_id IN (".$cat_id_leftover.")");
										}
										
										if($results->num_rows() > 0)
										{
											foreach($results->result_array() as $row)
											{
												$entry_id_array[] = $row['entry_id'];
											}
										} else {
												$entry_id_array[] = 0; // Yields no results, as no entry has an id of 0
										}
										
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
										
									}
								} elseif(empty($permitted_cats)) {
									// This user is neither a Super Admin nor a user with permitted categories. Show nothing!
									$entry_id_array[] = 0;
									$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
								}
								
							//	
							// Not under Category Permissions
							//
							} else {
								$entry_id_array = array();
								// Check if cat_id is part of allowed cat_ids in channel
								// The following output is a category id/name array. If channel has no associated category group, set variable to empty array to skip category filtering
								$results = $this->EE->db->query("SELECT entry_id FROM exp_category_posts WHERE cat_id IN (".$cat_id.")");
								if($results->num_rows() > 0)
								{
									foreach($results->result_array() as $row)
									{
										$entry_id_array[] = $row['entry_id'];
									}
								} else {
										$entry_id_array[] = 0; // Yields no results, as no entry has an id of 0
								}
								
								if($cat_cond == "is")
								{
									$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
								} else {
									$this->EE->db->where_not_in('channel_titles.entry_id', $entry_id_array);
								}
							}
							
							
						/**
						*	"No categories" - "none"
						*/
						} elseif ( $cat_id == "none") {
						
							//
							// Under the effect of Category Permissions
							//
							if(in_array('Category_permissions_ext', $installed_addons['extensions']) && $this->member_group_id != 1)
							{
								if( $this->EE->session->cache('zenbu', 'permitted_cats') )
								{

									$permitted_cats = $this->EE->session->cache('zenbu', 'permitted_cats');
								
								} else {
								
									$this->EE->load->add_package_path(PATH_THIRD . '/category_permissions');
									$this->EE->load->model('category_permissions_model');
									$permitted_cats = $this->EE->category_permissions_model->get_member_permitted_categories($this->member_id);
									$this->EE->load->remove_package_path(PATH_THIRD . '/category_permissions');
								
								}


								if( ! empty($permitted_cats))
								{
									// Build WHERE … IN statement
									$cat_where_in = implode($permitted_cats, ',');
									$cat_where_in = (empty($cat_where_in)) ? 0 : $cat_where_in;
									
									// For category filter to "none" under Category Permissions, show nothing, as entries without categories should not be shown							
									$entry_id_array[] = 0; // Yields no results later down, as no entry has an id of 0
									
									// Query for the opposite of "is… none" (i.e. "is not … none"), which means show all entries with permitted categories
									$results = $this->EE->db->query("SELECT entry_id FROM exp_category_posts WHERE cat_id IN (" . $cat_where_in . ")");
									if($results->num_rows() > 0)
									{
										foreach($results->result_array() as $row)
										{
											$entry_id_array_isnot[] = $row['entry_id'];
										}
									} else {
										$entry_id_array_isnot[] = 0;
									}
									
									if($cat_cond == "is")
									{
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
									} else {
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array_isnot);
									}
								
								} elseif( empty($permitted_cats) ) {
									// This user is neither a Super Admin nor a user with permitted categories. Show nothing!
									$entry_id_array[] = 0;
									$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
								}
							
							//	
							// Not under Category Permissions
							//
							} else {
								
								// Drill down to cat_id / cat name array
								foreach($categories['categories'] as $channel_id_string => $dropdown_labels)
								{
									// go through the following only when $channel_id_string has "ch_id_". For "cat_url_title", etc, skip this 
									if(strncmp($channel_id_string, 'ch_id_', 6) == 0)
									{
										foreach($dropdown_labels as $dropdown_label => $cat_dropdown_array)
										{
												foreach($cat_dropdown_array as $cat_group_name => $cat_array)
												{
													if($cat_group_name != "" && $cat_group_name != "none")
													{
														foreach($cat_array as $single_cat_id => $cat_name)
														{
															$cat_array_raw[$cat_name] = $single_cat_id;
														}
													}
												}
											
										}
									}
								}
					
								if( ! empty($cat_array_raw))
								{
									// Build WHERE … IN statement
									$cat_where_in = '';
									foreach($cat_array_raw as $name => $id)
									{
										if(is_numeric($id))
										{
											$cat_where_in .= $id.', '; 
										}
									}
									$cat_where_in = substr($cat_where_in, 0, -2);
									
									// Create similar entry array as above, but with all categories from channel
									$results = $this->EE->db->query("SELECT entry_id FROM exp_category_posts WHERE cat_id IN  (".$cat_where_in.")");
									if($results->num_rows() > 0)
									{
										foreach($results->result_array() as $row)
										{
											$entry_id_array[] = $row['entry_id'];
										}
									} else {
											$entry_id_array[] = 0; // Yields no results, as no entry has an id of 0
									}
									
									if($cat_cond == "is")
									{
										$this->EE->db->where_not_in('channel_titles.entry_id', $entry_id_array);
									} else {
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
									}
								}
							}
						
						/**
						*	"All categories"
						*/
						} elseif ( empty($cat_id) ) {
							
							//
							// Under the effect of Category Permissions
							//
							if(in_array('Category_permissions_ext', $installed_addons['extensions']) && $this->member_group_id != 1)
							{
								if( $this->EE->session->cache('zenbu', 'permitted_cats') )
								{
									$permitted_cats = $this->EE->session->cache('zenbu', 'permitted_cats');
								} else {
									$this->EE->load->add_package_path(PATH_THIRD . '/category_permissions');
									$this->EE->load->model('category_permissions_model');
									$permitted_cats = $this->EE->category_permissions_model->get_member_permitted_categories($this->member_id);
									$this->EE->load->remove_package_path(PATH_THIRD . '/category_permissions');
								}
								
								if( $permitted_cats && ! empty($permitted_cats))
								{
									$cat_where_in = implode($permitted_cats, ", ");
									$cat_where_in = (empty($cat_where_in)) ? 0 : $cat_where_in;

									// Create similar entry array as above, but with all categories from channel
									$results = $this->EE->db->query("SELECT entry_id FROM exp_category_posts WHERE cat_id IN  (".$cat_where_in.")");
									if($results->num_rows() > 0)
									{
										foreach($results->result_array() as $row)
										{
											$entry_id_array[] = $row['entry_id'];
										}
									} else {
											$entry_id_array[] = 0; // Yields no results, as no entry has an id of 0
									}
									
									if($cat_cond == "is")
									{
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
									} else {
										// "Category - is not - All categories": Not entries with and without categories, so basically nothing.
										// Odd filter, but it's present, so set up for it.
										$entry_id_array[] = 0;
										$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
									}

								} elseif($this->member_group_id != 1) {
									// This user is neither a Super Admin nor a user with permitted categories. Show nothing!
									$entry_id_array[] = 0;
									$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
								}
							
							//	
							// Not under Category Permissions
							//	
							} else {
								// "Category - is not - All categories": Not entries with and without categories, so basically nothing.
								// Odd filter, but it's present, so set up for it.
								if($cat_cond == "isnot")
								{
									$entry_id_array[] = 0;
									$this->EE->db->where_in('channel_titles.entry_id', $entry_id_array);
								}
							}
				
						}
					break;
					case 'status':
						/* 
						 * Add status filetering if status is present
						 */
						$status = $rule['val'];
						
						if( ! empty($status) && $status != 'all')
						{
							$where = '';
							if($rule['cond'] == 'is')
							{
								$where = "exp_channel_titles.status = '" . $status. "'";
							} elseif($rule['cond'] == 'isnot') {
								$where = "exp_channel_titles.status != '" . $status. "'";
							}
						
						
							/**
							*	=====================================
							*	Extension Hook zenbu_filter_by_status
							*	=====================================
							*
							*	Enables the addition of extra queries when filtering entries by status
							*	@param	int		$channel_id	The currently selected channel_id
							*	@param	string	$status		The currently selected status
							*	@param	array	$rule		The current entry filter rule array
							*	@param	string	$where		The partial query string
							*	@return $where 	string 		The modified query string
							*
							*/
							if ($this->EE->extensions->active_hook('zenbu_filter_by_status') === TRUE)
							{
								$where = $this->EE->extensions->call('zenbu_filter_by_status', $channel_id, $status, $rule, $where);
								if ($this->EE->extensions->end_script === TRUE) return;
							}
							
							if( ! empty($where))
							{
								$this->EE->db->where($where);
							}
						 }
						 
					break;
					case 'author':
						/*
						 * Add filtering by author
						 */
						$author_id = $rule['val'];
						if( ! empty($author_id) && is_numeric($author_id))
						{
							if($rule['cond'] == 'is')
							{
								$this->EE->db->where("channel_titles.author_id", $author_id);
							} elseif($rule['cond'] == 'isnot') {
								$this->EE->db->where("channel_titles.author_id !=", $author_id);
							}
						}
					break;
					case 'sticky':
						/* 
						 * Add filetering based on sticky
						 */
						$sticky = $rule['val'];
						if( ! empty($sticky))
						{
							if($rule['cond'] == 'is')
							{
								$this->EE->db->where("channel_titles.sticky", $sticky);
							} elseif($rule['cond'] == 'isnot') {
								$this->EE->db->where("channel_titles.sticky !=", $sticky);
							}
						}
					break;
					case 'date': case 'expiration_date': case 'edit_date':
						switch($rule['field'])
						{
							case 'date':
								$column = 'entry_date';
							break;
							default:
								$column = $rule['field'];
							break;
						}

						
						$date = $rule['val'];

						if( ! empty($date))
						{
							$now = $this->EE->localize->now;
							
							if(strncmp($date, '+', 1) == 0)
							{
								// THE FUTURE!
								$date			= substr($date, 1);
								$date			= $date*24*60*60; // Convert to seconds
								$date			= $now + $date;
								$comparator1	= "<";
								$comparator2	= ">";
							} elseif ($date != "range") {
								// The past
								$date			= $date*24*60*60; // Convert to seconds
								$date			= $now - $date;
								$comparator1	= ">";
								$comparator2	= "<";
							} else {
								// The Range
								$date_from		= strtotime($rule['date_from']);
								$date_to		= strtotime($rule['date_to']) + 86400;
							}

							// Edit date is stored as MySQL time. Need to convert to MySQL time in that case.
							if($rule['field'] == 'edit_date')
							{
								if($date == "range")
								{
									$date_from = mdate('%Y%m%d%H%i%s', $date_from);
									$date_to = mdate('%Y%m%d%H%i%s', $date_to);
								} else {
									$date = mdate('%Y%m%d%H%i%s', $date);
									$now = mdate('%Y%m%d%H%i%s', $now);
								}
							}
						
							if($rule['cond'] == "is")
							{
								if($date == "range")
								{
									$this->EE->db->where("channel_titles." . $column . " >= ", $date_from);
									$this->EE->db->where("channel_titles." . $column . " <= ", $date_to);
								} else {
									$this->EE->db->where("channel_titles." . $column . " ".$comparator1." ", $date);
									$this->EE->db->where("channel_titles." . $column . " ".$comparator2." ", $now);
								}

							} else {

								if($date == "range")
								{
									$where = "(exp_channel_titles." . $column . " < " . $date_from . " OR exp_channel_titles." . $column . " > " . $date_to . ")";
								} else {
									$where = "(exp_channel_titles." . $column . " ".$comparator2." ".$date." OR exp_channel_titles." . $column . " ".$comparator1." ".$now.")";
								}

								$this->EE->db->where($where);
							}
							
						}
					break;
					case 'title': case 'url_title':
						$keyword = trim($this->EE->db->escape_like_str($rule['val']));
						if ( ! empty($keyword)) 
						{
							$where = "";
							switch($rule['cond'])
							{
								case "contains":
									$where = "exp_channel_titles.".$rule['field']." LIKE '%" . $keyword . "%'";
								break;
								case "doesnotcontain":
									$where = "exp_channel_titles.".$rule['field']." NOT LIKE '%" . $keyword . "%'";
								break;
								case "beginswith":
									$where = "exp_channel_titles.".$rule['field']." LIKE '" . $keyword . "%'";
								break;
								case "doesnotbeginwith":
									$where = "exp_channel_titles.".$rule['field']." NOT LIKE '%" . $keyword . "%'";
								break;
								case "endswith":
									$where = "exp_channel_titles.".$rule['field']." LIKE '%" . $keyword . "'";
								break;
								case "doesnotendwith":
									$where = "exp_channel_titles.".$rule['field']." NOT LIKE '%" . $keyword . "'";
								break;
								case "containsexactly":
									$where = "exp_channel_titles.".$rule['field']." LIKE '" . $keyword . "'";
								break;
							}
							
							$this->EE->db->where($where);
						}
					break;
					case 'all':
						$keyword = trim($this->EE->db->escape_like_str($rule['val']));
						if ( ! empty($keyword)) 
						{
							if( ! empty($field_ids['field']))
							{
								$count = 1;
								// The following is to add parenthesis in this part of the query
								$where = "";
								foreach($field_ids['field'] as $field_id => $value)
								{
									
									if($rule['cond'] == "notcontains")
									{
										$where .= ($count == 1) ? '(exp_channel_data.field_id_'.$field_id.' NOT LIKE "%' . $keyword . '%"' : ' AND exp_channel_data.field_id_'.$field_id.' NOT LIKE "%'. $keyword . '%"';
									} elseif($rule['cond'] == "contains") {
										$where .= ($count == 1) ? '(exp_channel_data.field_id_'.$field_id.' LIKE "%' . $keyword . '%"' : ' OR exp_channel_data.field_id_'.$field_id.' LIKE "%' . $keyword . '%"';
									}
									$count++;
								}
								// Close up query section containing parenthesis
								if($rule['cond'] == "notcontains")
								{
									$where .= ' AND exp_channel_titles.title NOT LIKE "%' . $keyword . '%")';
								} elseif($rule['cond'] == "contains") {
									$where .= ' OR exp_channel_titles.title LIKE "%' . $keyword . '%")';
								}
								( ! is_null($where) && ! empty($where)) ? $this->EE->db->where($where) : '';
							}
						}
					break;
					case (strncmp($rule['field'], "field_", 6) == 0) :
						$keyword = trim($this->EE->db->escape_like_str($rule['val']));
						$field_id = substr($rule['field'], 6);
						$where = "";
						if(isset($field_ids['field'][$field_id]))
						{
							if(isset($fieldtypes['fieldtype'][$field_id]))
							{
								
								/**
								*	====================================
								*	Adding third-party fieldtype classes
								*	====================================
								*/
								$ft_class = $fieldtypes['fieldtype'][$field_id].'_ft';
								load_ft_class($ft_class);
								
								if(class_exists($ft_class))
								{
									$ft_object = create_object($ft_class);
									
									if(method_exists($ft_object, 'zenbu_result_query')) 
									{
										
										$already_queried = 'already_queried_'.$fieldtypes['fieldtype'][$field_id];
										
										// The TRUE/FALSE value for $already_queries_FIELDNAME 
										// is to avoid declaring a table twice in the FROM MySQL statement
										$$already_queried = (isset($$already_queried) && $$already_queried === TRUE) ? TRUE : FALSE;
										
										// $installed_addons (optional arg in fieldtype class)
										$ft_object->zenbu_result_query($rules, $field_id, $fieldtypes, $$already_queried, $installed_addons, $settings['setting'][$channel_id]);
										
										// Set $already_queries_FIELDNAME to TRUE so that FALSE is nenver passed again in the zenbu_result_query
										$$already_queried = TRUE;
									} else {
										if( ! empty($keyword))
										{
											$where = '';
											switch($rule['cond'])
											{
												case "contains":
													$where = "exp_channel_data.field_id_".$field_id." LIKE '%" . $keyword . "%'";
												break;
												case "doesnotcontain":
													$where = "(exp_channel_data.field_id_".$field_id." NOT LIKE '%" . $keyword . "%' OR exp_channel_data.field_id_".$field_id." IS NULL)";
												break;
												case "beginswith":
													$where = "exp_channel_data.field_id_".$field_id." LIKE '" . $keyword . "%'";
												break;
												case "doesnotbeginwith":
													$where = "(exp_channel_data.field_id_".$field_id." NOT LIKE '" . $keyword . "%' OR exp_channel_data.field_id_".$field_id." IS NULL)";
												break;
												case "endswith":
													$where = "exp_channel_data.field_id_".$field_id." LIKE '%" . $keyword . "'";
												break;
												case "doesnotendwith":
													$where = "(exp_channel_data.field_id_".$field_id." NOT LIKE '%" . $keyword . "' OR exp_channel_data.field_id_".$field_id." IS NULL)";
												break;
												case "containsexactly":
													$where = "exp_channel_data.field_id_".$field_id." LIKE '" . $keyword . "'";
												break;
												case "isempty":
													$where = "(exp_channel_data.field_id_".$field_id." = ''  
																OR exp_channel_data.field_id_".$field_id." IS NULL)";
												break;
												case "isnotempty":
													$where = "(exp_channel_data.field_id_".$field_id." != '' 
																AND exp_channel_data.field_id_".$field_id." IS NOT NULL)";
												break;
											}
											
											if( ! empty($where))
											{
												$this->EE->db->where($where);
											}
											
										} else {
											$where = '';
											switch($rule['cond'])
											{
												case "isempty":
													$where = "(exp_channel_data.field_id_".$field_id." = '' 
																OR exp_channel_data.field_id_".$field_id." IS NULL)";
												break;
												case "isnotempty":
													$where = "(exp_channel_data.field_id_".$field_id." != '' 
																AND exp_channel_data.field_id_".$field_id." IS NOT NULL)";
												break;
											}
											
											if( ! empty($where))
											{
												$this->EE->db->where($where);
											}
											
										}
									}
								} // if class_exists
								
							} // if
						} else {
							($rule['cond'] == "notin") ? $this->EE->db->not_like("channel_titles.title", $keyword) : $this->EE->db->like("channel_titles.title", $keyword);
						}
					
				}
				
				
				
			}
		}
		
		
		/* 
		 * Parse field query conditions
		 */
		if( ! empty($field_ids['field']))
		{
			foreach($field_ids['field'] as $field_id => $value)
			{
				$this->EE->db->select("channel_data.field_id_".$field_id);
			}
		}
		
		
		/**
		 *
		 * Last few touches…
		 *
		 */
		$this->EE->db->from('channel_titles');
		$this->EE->db->join('channels', "exp_channels.channel_id = exp_channel_titles.channel_id"/*, 'left'*/);

		// Join autosave query if display autosave data is set
		if(isset($settings['setting'][$channel_id]['show_autosave']) && ! empty($settings['setting'][$channel_id]['show_autosave']))
		{
			$this->EE->db->join('channel_entries_autosave', 'exp_channel_titles.entry_id = exp_channel_entries_autosave.original_entry_id', 'left');
		}

		// If channel is 0 ("All channels") with a "any title/basic custom field" rule, or if channel is not 0, add the exp_channel_data table
		if($channel_id != 0 || ($channel_id == 0 && find_rule('field', 'any_cf_title', $rules) === TRUE))
		{
			$this->EE->db->join('channel_data', "exp_channel_titles.entry_id = exp_channel_data.entry_id"/*, 'left'*/);
		}
		//$this->EE->db->distinct();
		
		/**
		 * Add filtering based on entry limit
		 */
		$sort = (empty($sort)) ? 'desc' : $sort;
		switch ($orderby)
		{
			case "id":
				$this->EE->db->order_by('channel_titles.entry_id', $sort);
			break;
			case "title":
				$this->EE->db->order_by('channel_titles.title', $sort);
			break;
			case "url_title":
				$this->EE->db->order_by('channel_titles.url_title', $sort);
			break;
			case "entry_date":
				$this->EE->db->order_by('channel_titles.entry_date', $sort);
			break;
			case "expiration_date":
				$this->EE->db->order_by('channel_titles.expiration_date', $sort);
			break;
			case "edit_date":
				$this->EE->db->order_by('channel_titles.edit_date', $sort);
			break;
			case "url_title":
				$this->EE->db->order_by('channel_titles.url_title', $sort);
			break;
			case "status":
				$this->EE->db->order_by('channel_titles.status', $sort);
			break;
			case "channel":
				$this->EE->db->order_by('exp_channels.channel_title', $sort);
			break;
			case "author":
				$this->EE->db->join('exp_members', 'exp_channel_titles.author_id = exp_members.member_id');
				$this->EE->db->order_by('exp_members.screen_name', $sort);				
			break;
			case "category":
				/* Can potentially slow down performance since two tables are being pulled in */
				$this->EE->db->join('category_posts AS cp', 'cp.entry_id = exp_channel_titles.entry_id', 'left');
				$this->EE->db->join('categories AS c', 'c.cat_id = cp.cat_id', 'left');
				$this->EE->db->order_by('group_concat(c.cat_name ORDER BY c.cat_name)', $sort);
			break;
			case "is_sticky":
				$this->EE->db->order_by('channel_titles.sticky', $sort);
			break;
			case "comments":
				$this->EE->db->order_by('channel_titles.comment_total', $sort);
			break;
			case "autosave":
				$this->EE->db->order_by('channel_entries_autosave.entry_id', $sort);
			break;
			case (strncmp($orderby, 'field_id_', 9) == 0):
				$this->EE->db->order_by('channel_data.'.$orderby, $sort);
			break;
			case ( ! empty($orderby)):
				
				/**
				*	===========================================
				*	Extension Hook zenbu_custom_order_sort
				*	===========================================
				*
				*	Adds custom sorting to Zenbu results
				*	@param $sort 	string	The sort order (asc or desc)
				*	@return void 			Build your order_by() Active Record statements in the extension
				*/
				if ($this->EE->extensions->active_hook('zenbu_custom_order_sort') === TRUE)
				{
					$this->EE->extensions->call('zenbu_custom_order_sort', $sort);
				 	if ($this->EE->extensions->end_script === TRUE) return;
				}

				
			break;
			default:
				$this->EE->db->order_by('channel_titles.entry_date', 'desc');
			break;
		}
		
		$this->EE->db->group_by("channel_titles.entry_id");
		
		//	----------------------------------------
		//	Determining result limit
		//	----------------------------------------
		if(empty($limit))
		{
			$limit = isset($settings['setting']['general']['default_limit']) ? $settings['setting']['general']['default_limit'] : $this->default_limit;

		} else {
			$limit = $limit;
		}
		$perpage = (empty($perpage)) ? 0 : $perpage;
		$this->EE->db->limit($limit, $perpage);

		/**
		*	======================================
		*	Extension Hook zenbu_entry_query_end
		*	======================================
		*
		*	Any last words? Enables adding additional
		*	Active Record patterns/commands before
		*	committing the completed Active Record query
		*	@return void
		*
		*/
		if ($this->EE->extensions->active_hook('zenbu_entry_query_end') === TRUE)
		{
			$this->EE->extensions->call('zenbu_entry_query_end');
			if ($this->EE->extensions->end_script === TRUE) return;
		}

		$final_results	= $this->EE->db->get();

		$output['compiled_query'] = $this->EE->db->last_query();
		
		$total_query	= $this->EE->db->query("/* Zenbu getting total results */ \n SELECT FOUND_ROWS() as total_rows"); // Must be run right after the previous query to get all results
		
		foreach($total_query->result_array() as $row)
		{
			$output['total_results'] = $row['total_rows'];
			$this->EE->session->set_cache('zenbu', 'total_results', $output['total_results']);
		}
		$output['showing']		= ($perpage == 0) ? 1 : $perpage;
		$output['showing_to']	= ($limit+$perpage > $output['total_results']) ? $output['total_results'] : $limit + $perpage;
		
		
		
		/**
		* Query is built, results are in, i's dotted and t's crossed…
		*
		* Now outputting the results
		* First make some general queries to retrieve data, then pass through zenbu_display class
		* @return array $output		Array of entry data to be passed to view
		*
		*/
		if($final_results->num_rows() > 0)
		{
				/**
				 * If all fields are to be searched into, now's a good place to
				 * restore the "to be shown" fields in Zenbu, since the query for
				 * all fields has been completed.
				 */
				if( isset($settings['setting']['general']['enable_hidden_field_search']) && $settings['setting']['general']['enable_hidden_field_search'] == 'y' && $channel_id[0] != 0 )
				{
					$field_ids = $field_ids_to_show;
				}

				//
				// Let's get the bulk of found data and use it to make fewer queries
				// This is better than many small (and sometimes big) queries for each entry result.
				//
				$rel_array = array();
				$playa_array = array();
				foreach($final_results->result_array() as $row)
				{
					// Get a basic list of entry ids
					$entry_array[] = $row['entry_id'];
					
					// Get a basic list of rel data
					if( ! empty($field_ids))
					{
						foreach($field_ids['field'] as $field_id => $field_label)
						{
							$rel_array[$row['entry_id']]['field_id_'.$field_id] = $row['field_id_'.$field_id];
						}
					}
				}
				
				// Get basic data from fields that store *some* data in
				// actual custom field (i.e. not in separate table)
				if( ! empty($field_ids))
				{
					$matrix_installed			= FALSE;
					$tagger_installed			= FALSE;
					$playa_installed			= FALSE;
					$channel_images_installed	= FALSE;
					foreach($field_ids['field'] as $field_id => $field_label)
					{
						/**
						*	====================================
						*	Adding third-party fieldtype classes
						*	====================================
						*/
						$ft_class = $fieldtypes['fieldtype'][$field_id].'_ft';
						load_ft_class($ft_class);

						if(class_exists($ft_class))
						{
							$ft_object = create_object($ft_class);
							$ft_data 	= $fieldtypes['fieldtype'][$field_id].'_data';
							// Optional variables for simple fields:
							// $keyword: 				..will this be obsolete?
							// $output_upload_prefs: 	add if you need upload settings
							// $settings: 				add if you need saved display settings
							// $rel_array: 				add if you need relationship data
							$$ft_data 	= (method_exists($ft_object, 'zenbu_get_table_data')) ? $ft_object->zenbu_get_table_data($entry_array, $field_ids['id'], $channel_id, $output_upload_prefs, $settings, $rel_array) : array();
						} else {
							$ft_data 	= $fieldtypes['fieldtype'][$field_id].'_data';
							$$ft_data 	= array();
						}
					}
				}
				
				
				//
				// Here, let's get basic data for fields and other elements which store data in other tables
				//
				$keyword 		= ( ! isset($keyword)) ? '' : $keyword;
				$category_list 	= $this->_get_category_list($entry_array);
								
				foreach($final_results->result_array() as $row)
				{
					// Build filter for entry title link
					$filter_array	= array("return_to_zenbu" => "y");
					$filter_array	= base64_encode(serialize($filter_array));
					
					// ID
					$output['entry'][$row['entry_id']]['id'] = (isset($row['entry_id'])) ? $this->EE->zenbu_display->highlight($row['entry_id'], $rules, 'id') : '';
					
					// Title
					if(isset($row['title']))
					{
						$output['entry'][$row['entry_id']]['title'] =
							anchor(BASE.AMP."C=content_publish".AMP."M=entry_form".AMP."channel_id=".$row['channel_id'].AMP."entry_id=".$row['entry_id'].AMP."filter=".$filter_array, $this->EE->zenbu_display->highlight($row["title"], $rules, "title"), 'class="zenbu_entry_form_link"');
						/**
						*	===========================================
						*	Extension Hook zenbu_modify_title_display
						*	===========================================
						*
						*	Modifies the display of the "title" column in the entry listing
						*	@param	string	$output			The output string to be displayed in the Zenbu column
						*	@param	array	$entry_array	An array containing all the entry_ids of the entry listing results
						*	@param	int		$row			An array of row data for the current entry
						*	@return string	$output			The final output to be displayed in the Zenbu column
						*/
						if ($this->EE->extensions->active_hook('zenbu_modify_title_display') === TRUE)
						{
							$output['entry'][$row['entry_id']]['title'] = $this->EE->extensions->call('zenbu_modify_title_display', $output['entry'][$row['entry_id']]['title'], $entry_array, $row);
						 	if ($this->EE->extensions->end_script === TRUE) return;
						}
					}
					
					// Channel ID
					$output['entry'][$row['entry_id']]['channel_id'] = (isset($row['channel_id'])) ? $row['channel_id'] : '';
					
					// URL Title
					$output['entry'][$row['entry_id']]['url_title'] = (isset($row['url_title'])) ? $row['url_title'] : '';
					
					// Entry date
					$custom_date_format = (isset($settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_1'])) ? $settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_1'] : '';
					if(isset($settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_2']) && 
						! empty($settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_2']) && 
						$row['entry_date'] > $this->local_time)
					{
						$custom_date_format = $settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_2'];
					}
					$output['entry'][$row['entry_id']]['entry_date'] = (isset($row['entry_date'])) ? $this->EE->zenbu_display->_display_date($row['entry_date'], '', 'unix', $custom_date_format) : '';
					
					// Expiration date
					$custom_date_format = (isset($settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_1'])) ? $settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_1'] : '';
					if(isset($settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_2']) && 
						! empty($settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_2']) && 
						$row['expiration_date'] < $this->local_time)
					{
						$custom_date_format = $settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_2'];
					}
					$output['entry'][$row['entry_id']]['expiration_date'] = (isset($row['expiration_date'])) ? $this->EE->zenbu_display->_display_date($row['expiration_date'], '', 'unix', $custom_date_format) : '';
					
					// Edit date
					$custom_date_format = (isset($settings['setting'][$channel_id]['extra_options']["show_edit_date"]['date_option_1'])) ? $settings['setting'][$channel_id]['extra_options']["show_edit_date"]['date_option_1'] : '';
					$output['entry'][$row['entry_id']]['edit_date'] = (isset($row['edit_date'])) ? $this->EE->zenbu_display->_display_date($row['edit_date'], '', 'mysql', $custom_date_format) : '';
					
					// Channel name
					$output['entry'][$row['entry_id']]['channel'] = ( isset($row['channel_id'], $output_channel['channels']['channel_data'][$row['channel_id']]) ) ? $output_channel['channels']['channel_data'][$row['channel_id']]['channel_title'] : '';
					
					// Status
					$output['entry'][$row['entry_id']]['status'] = (isset($row['status']) && isset($output_status[$row['status']]['cell_output'])) ? $output_status[$row['status']]['cell_output'] : '';
					/**
					*	===========================================
					*	Extension Hook zenbu_modify_status_display
					*	===========================================
					*
					*	Modifies the display of the "status" column in the entry listing
					*	@param	string	$output			The output string to be displayed in the Zenbu column
					*	@param	array	$entry_array	An array containing all the entry_ids of the entry listing results
					*	@param	int		$row			An array of row data for the current entry
					*	@param	array	$statuses		An array containing all entry status data
					*	@return string	$output			The final output to be displayed in the Zenbu column
					*/
					if ($this->EE->extensions->active_hook('zenbu_modify_status_display') === TRUE)
					{
						$output['entry'][$row['entry_id']]['status'] = $this->EE->extensions->call('zenbu_modify_status_display', $output['entry'][$row['entry_id']]['status'], $entry_array, $row, $output_status);
					 	if ($this->EE->extensions->end_script === TRUE) return;
					}
					
					// Sticky
					$output['entry'][$row['entry_id']]['sticky'] = (isset($row['sticky'])) ? $this->EE->lang->line($row['sticky']) : '';
					
					// Author
					$output['entry'][$row['entry_id']]['author'] = (isset($row['author_id']) && isset($output_authors[$row['author_id']])) ? $output_authors[$row['author_id']] : '';
					
					// Last author ("Last edited by…")
					$output['entry'][$row['entry_id']]['last_author'] = (isset($row['entry_id']) && isset($output_last_authors[$row['entry_id']])) ? $output_last_authors[$row['entry_id']]['screen_name'] : '';
					
					// Comments
					if($comment_module === TRUE) {
						$output['entry'][$row['entry_id']]['comments'] = ( isset($row['comment_total'], $output_channel['channels']['channel_data'][$row['channel_id']]) ) ? $this->EE->zenbu_display->_display_comments($row['comment_total'], $row['entry_id'], $output_channel['channels']['channel_data'][$row['channel_id']]['id'], $comment_module) : '';
					}
					
					// LiveLook
					$output['entry'][$row['entry_id']]['view'] = $this->EE->zenbu_display->_display_template($row['entry_id'], $row['url_title'], $channel_id, $row['channel_id'], $output_templates, $settings, $output_pages);
					
					// Categories
					$output['entry'][$row['entry_id']]['categories'] = $this->EE->zenbu_display->_display_category_list($row['entry_id'], $category_list, $installed_addons, $categories, $extra_options);
					/**
					*	=============================================
					*	Extension Hook zenbu_modify_category_display
					*	=============================================
					*
					*	Modifies the display of the "category" column in the entry listing
					*	@param	string	$output			The output string to be displayed in the Zenbu column
					*	@param	array	$entry_array	An array containing all the entry_ids of the entry listing results
					*	@param	int		$row			An array of row data for the current entry
					*	@param  array 	$category_list 	A multi-dimensional array of cat_id/cat_name, for each entry_id
					*	@return string	$output			The final output to be displayed in the Zenbu column
					*/
					if ($this->EE->extensions->active_hook('zenbu_modify_category_display') === TRUE)
					{
						$output['entry'][$row['entry_id']]['categories'] = $this->EE->extensions->call('zenbu_modify_category_display', $output['entry'][$row['entry_id']]['categories'], $entry_array, $row, $category_list);
					 	if ($this->EE->extensions->end_script === TRUE) return;
					}
					
					// Entry view counts
					$row_view_counts[1]	= (isset($row['view_count_one'])) ? $row['view_count_one'] : '';
					$row_view_counts[2]	= (isset($row['view_count_two'])) ? $row['view_count_two'] : '';
					$row_view_counts[3]	= (isset($row['view_count_three'])) ? $row['view_count_three'] : '';
					$row_view_counts[4]	= (isset($row['view_count_four'])) ? $row['view_count_four'] : '';
		
					if( ! is_array($channel_id) && isset($settings['setting'][$channel_id]['extra_options']))
					{
						$output['entry'][$row['entry_id']]['view_count'] = $this->EE->zenbu_display->_display_view_counts($settings['setting'][$channel_id]['extra_options'], $row_view_counts);
					}

					// Autosave
					$output['entry'][$row['entry_id']]['autosave'] = isset($row['autosave_entry_id']) ? anchor(BASE . AMP . "C=content_publish" . AMP . "M=entry_form" . AMP . "channel_id=" . $row['channel_id'] . AMP . "entry_id=" . $row['autosave_entry_id'] . AMP . "use_autosave=y", lang('view')) : NBS;


					/**
					*	===========================================
					*	Extension Hook zenbu_modify_standard_cell_data
					*	===========================================
					*
					*	Modifies the display of standard entry data columns in the entry listing
					*	@param	string	$output			The output string to be displayed in the Zenbu column. **You must return the 
					*	                        		original array!**
					*	@param	array	$entry_array	An array containing all the entry_ids of the entry listing results
					*	@param	int		$row			An array of row data for the current entry
					*	@return string	$output			The final output to be displayed in the Zenbu column
					*/
					if ($this->EE->extensions->active_hook('zenbu_modify_standard_cell_data') === TRUE)
					{
						//var_dump($output['entry'][$row['entry_id']]);echo '<hr />';
						$output['entry'][$row['entry_id']] = $this->EE->extensions->call('zenbu_modify_standard_cell_data', $output['entry'][$row['entry_id']], $entry_array, $row);
					 	if ($this->EE->extensions->end_script === TRUE) return;
					 	//var_dump($output['entry'][$row['entry_id']]).BR;
					}
					
					//	----------------------------------------
					//	Custom Fields
					//	----------------------------------------
					if( ! empty($field_ids))
					{
						foreach($field_ids['field'] as $field_id => $field_label)
						{
							
							$ft_class = $fieldtypes['fieldtype'][$field_id].'_ft';
							$ft_table_data = $fieldtypes['fieldtype'][$field_id].'_data';
							
							$table_data = (isset($$ft_table_data)) ? $$ft_table_data : array();
			
							if(class_exists($ft_class))
							{
								$ft_object = create_object($ft_class);
								$field_data	= (method_exists($ft_object, 'zenbu_display')) ? $ft_object->zenbu_display($row['entry_id'], $channel_id, $row['field_id_'.$field_id], $table_data, $field_id, $settings, $rules, $output_upload_prefs, $installed_addons, $fieldtypes) : $row['field_id_'.$field_id];
							} else {
								$field_data	= ( ! empty($row['field_id_'.$field_id])) ? highlight($row['field_id_'.$field_id], $rules, "field_".$field_id) : '&nbsp;';
							}

							/**
							*	=============================================
							*	Extension Hook zenbu_modify_field_cell_data
							*	=============================================
							*	Modify custom field cell data before output & display in Zenbu
							* 	@param 	string 	$field_data 	The current data to be displayed in the Zenbu column cell
							* 	@param 	array 	$info_data 	  	An array of the current entry_id, field_id, and an array
							* 	                       			of field information (ids, fieldtype, name...)
							* 	@return string 	$field_data 	The modified data to be displayed in the Zenbu column cell 
							*/
							if ($this->EE->extensions->active_hook('zenbu_modify_field_cell_data') === TRUE)
							{
								$info_data						= array_merge($field_ids, $row);
								$info_data['current_field_id']	= $field_id;

								$field_data = $this->EE->extensions->call('zenbu_modify_field_cell_data', $field_data, $info_data);
							 	if ($this->EE->extensions->end_script === TRUE) return;
							}	
									
							$output['entry'][$row['entry_id']]['fields'][$field_id] = $field_data;
							$field_data = '';
							
						} // END foreach
					} // END if

					/**
					*	===========================================
					*	Extension Hook zenbu_entry_cell_data
					*	===========================================
					*	Add data to display in the custom/third-party entry row cell
					* 	@param 	int 	$row['entry_id']  	The current Entry ID
					* 	@param 	array 	$entry_array 	  	An array of all entries found by Zenbu
					* 	@param 	int 	$channel_id 		The current channel ID for the entry
					* 	@return array 	$output 		  	An array of data used by Zenbu. 
					*/
					if ($this->EE->extensions->active_hook('zenbu_entry_cell_data') === TRUE)
					{
						$ext_result_array = $this->EE->extensions->call('zenbu_entry_cell_data', $row['entry_id'], $entry_array, $row['channel_id']);
					 	if ($this->EE->extensions->end_script === TRUE) return;
					}

					if(isset($ext_result_array))
					{
						foreach($ext_result_array as $ext_f => $data)
						{
							$output['entry'][$row['entry_id']][$ext_f] = $data;
						}
					}
					
				}
		}
		
		// Set up pagination
		$this->EE->load->library('pagination');
		$p_config = $this->EE->zenbu_display->_pagination_config('ajax_results', $output['total_results'], $limit);

		$this->EE->pagination->initialize($p_config);

		$output['pagination'] = $this->EE->pagination->create_links();
	
		return $output;
		
	} // END functio  _get_entry_data

	/**
	 * _get_single_cell_data
	 * Get result array for a single entry
	 * @param  array $entry_data 
	 * @param  array $fields     
	 * @return array             
	 */
	function _get_single_cell_data($entry_data, $fields)
	{
		$output 												= '';
		$entry_array[$entry_data['id']]							= $entry_data['id'];
		$entry_id 												= $entry_data['id'];
		$channel_id												= $this->_convert_channel_to_string($entry_data['channel_id']);

		$field_id												= $entry_data['field_id'];
		$data													= $entry_data['data'];
		$rel_array[$entry_data['id']]['field_id_'.$field_id]	= $data;

		$settings				= $this->_get_settings();
		$output_upload_prefs	= $this->_get_file_upload_prefs();
		$installed_addons		= $this->_get_installed_addons();
		$extra_options			= isset($settings['setting'][(string) $channel_id]['extra_options']) ? $settings['setting'][(string) $channel_id]['extra_options'] : array();

		$this->EE->load->model('zenbu_display');
		$this->EE->load->helper(array('display', 'loader', 'date', 'url', 'text'));

		if( ! is_numeric($field_id))
		{
			switch($field_id)
			{
				case 'title':

					// Build filter for entry title link
					$filter_array	= array("return_to_zenbu"	=> "y");
					$filter_array 	= base64_encode(serialize($filter_array));

					// Title
					$output = anchor(BASE.AMP."C=content_publish".AMP."M=entry_form".AMP."channel_id=".$channel_id.AMP."entry_id=".$entry_data['id'].AMP."filter=".$filter_array, $data, 'class="zenbu_entry_form_link"');

					/**
					*	===========================================
					*	Extension Hook zenbu_modify_title_display
					*	===========================================
					*
					*	Modifies the display of the "title" column in the entry listing
					*	@param	string	$output			The output string to be displayed in the Zenbu column
					*	@param	array	$entry_array	An array containing all the entry_ids of the entry listing results
					*	@param	int		$row			An array of row data for the current entry
					*	@return string	$output			The final output to be displayed in the Zenbu column
					*/
					if ($this->EE->extensions->active_hook('zenbu_modify_title_display') === TRUE)
					{
						$output = $this->EE->extensions->call('zenbu_modify_title_display', $output, $entry_array, $entry_data);
					 	if ($this->EE->extensions->end_script === TRUE) return;
					}
					
				break;
				case 'url_title':
					$output = (isset($data)) ? $data : '';
				break;
				case 'entry_date':

					// Entry date
					$custom_date_format = (isset($settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_1'])) ? $settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_1'] : '';
					
					if(isset($settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_2']) && 
						! empty($settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_2']) && 
						$data > $this->local_time)
					{
						$custom_date_format = $settings['setting'][$channel_id]['extra_options']["show_entry_date"]['date_option_2'];
					}

					$output = isset($data) ? $this->EE->zenbu_display->_display_date($data, '', 'unix', $custom_date_format) : '';

				break;
				case 'expiration_date':
				
					// Expiration date
					$custom_date_format = (isset($settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_1'])) ? $settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_1'] : '';
					if(isset($settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_2']) && 
						! empty($settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_2']) && 
						$data < $this->local_time)
					{
						$custom_date_format = $settings['setting'][$channel_id]['extra_options']["show_expiration_date"]['date_option_2'];
					}

					$output = isset($data) ? $this->EE->zenbu_display->_display_date($data, '', 'unix', $custom_date_format) : '';
				
				break;
				case 'edit_date':

					// Edit date
					$custom_date_format = isset($settings['setting'][$channel_id]['extra_options']["show_edit_date"]['date_option_1']) ? $settings['setting'][$channel_id]['extra_options']["show_edit_date"]['date_option_1'] : '';
					$output = (isset($data)) ? $this->EE->zenbu_display->_display_date($data, '', 'mysql', $custom_date_format) : '';

				break;
				case 'status':
					// Status
					$statuses	= $this->_get_statuses();
					$output		= isset($data) && isset($statuses[$data]['cell_output']) ? $statuses[$data]['cell_output'] : '';
				break;
				case 'sticky':
					$output	= (isset($data)) ? $data : '';
					$output	= $output = 'y' ? $this->EE->lang->line('yes') : $this->EE->lang->line('no');
				break;
				case 'category':
					// $categories	= $this->EE->zenbu_get->_get_category_dropdowns($installed_addons);
					// $category_list 	= $this->_get_category_list(array($entry_data['id']));
					// $output = $this->EE->zenbu_display->_display_category_list($entry_data['id'], $category_list, $installed_addons, $categories, $extra_options);
					// /**
					// *	=============================================
					// *	Extension Hook zenbu_modify_category_display
					// *	=============================================
					// *
					// *	Modifies the display of the "category" column in the entry listing
					// *	@param	string	$output			The output string to be displayed in the Zenbu column
					// *	@param	array	$entry_array	An array containing all the entry_ids of the entry listing results
					// *	@param	int		$row			An array of row data for the current entry
					// *	@param  array 	$category_list 	A multi-dimensional array of cat_id/cat_name, for each entry_id
					// *	@return string	$output			The final output to be displayed in the Zenbu column
					// */
					// if ($this->EE->extensions->active_hook('zenbu_modify_category_display') === TRUE)
					// {
					// 	$output = $this->EE->extensions->call('zenbu_modify_category_display', $output['entry'][$row['entry_id']]['categories'], $entry_array, $row, $category_list);
					//  	if ($this->EE->extensions->end_script === TRUE) return;
					// }
				break;
				case 'author':
					$authors	= $this->_get_authors();
					$output		= isset($authors[$data]) ? $authors[$data] : '';
				break;

			}

			return $output;
		}
		

		// Get basic data from fields that store *some* data in
		// actual custom field (i.e. not in separate table)
		if( ! empty($fields['id']))
		{
			$matrix_installed			= FALSE;
			$tagger_installed			= FALSE;
			$playa_installed			= FALSE;
			$channel_images_installed	= FALSE;
			
			/**
			*	====================================
			*	Adding third-party fieldtype classes
			*	====================================
			*/
			$ft_class = $fields['fieldtype'][$field_id].'_ft';
			load_ft_class($ft_class);

			if(class_exists($ft_class))
			{
				$ft_object = create_object($ft_class);
				$ft_data 	= $fields['fieldtype'][$field_id].'_data';
				// Optional variables for simple fields:
				// $keyword: 				..will this be obsolete?
				// $output_upload_prefs: 	add if you need upload settings
				// $settings: 				add if you need saved display settings
				// $rel_array: 				add if you need relationship data
				$$ft_data 	= (method_exists($ft_object, 'zenbu_get_table_data')) ? $ft_object->zenbu_get_table_data($entry_array, $fields['id'], $channel_id, $output_upload_prefs, $settings, $rel_array) : array();
			} else {
				$ft_data 	= $fields['fieldtype'][$field_id].'_data';
				$$ft_data 	= array();
			}
			
		}

		// Custom Fields	
		$ft_class = $fields['fieldtype'][$field_id].'_ft';
		$ft_table_data = $fields['fieldtype'][$field_id].'_data';
		
		$table_data = (isset($$ft_table_data)) ? $$ft_table_data : array();

		if(class_exists($ft_class))
		{
			$ft_object = create_object($ft_class);
			$field_data	= (method_exists($ft_object, 'zenbu_display')) ? $ft_object->zenbu_display($entry_data['id'], $channel_id, $data, $table_data, $field_id, $settings, array(), $output_upload_prefs, $installed_addons, $fields) : $data;
		} else {
			$field_data	= '-';//( ! empty($row['field_id_'.$field_id])) ? highlight($row['field_id_'.$field_id], array(), "field_".$field_id) : '&nbsp;';
			$field_data	= $field_data;
		}

		/**
		*	=============================================
		*	Extension Hook zenbu_modify_field_cell_data
		*	=============================================
		*	Modify custom field cell data before output & display in Zenbu
		* 	@param string 	$field_data 	The current data to be displayed in the Zenbu column cell
		* 	@param array 	$info_data 	  	An array of the current entry_id, field_id, and an array
		* 	                       			of field information (ids, fieldtype, name...)
		* 	@return string 	$field_data 	The modified data to be displayed in the Zenbu column cell 
		*/
		if ($this->EE->extensions->active_hook('zenbu_modify_field_cell_data') === TRUE)
		{

			$info_data						= array_merge($fields, $entry_data);
			$info_data['current_field_id']	= $field_id;
			$info_data['channel_id']		= $channel_id;
			$info_data['entry_id']			= $entry_data['id'];

			$field_data = $this->EE->extensions->call('zenbu_modify_field_cell_data', $field_data, $info_data);
		 	if ($this->EE->extensions->end_script === TRUE) return;
		}	
	
		$output = $field_data;
		return $output;

		
	}
    

}

?>