<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Zenbu_db extends Zenbu_mcp {


	function Zenbu_db()
	{
		parent::Zenbu_mcp();
	}
	
	
	/**
	 * function save_search
	 * Saves filtering rules in list of saved searches in exp_zenbu_saved_searches table
	 * @return null
	 */
	function save_search($rules = array(), $search_name = '')
	{
		$data['member_id'] = $this->member_id;
		$data['rules'] = $rules;
		$data['site_id'] = $this->site_id;
		$data['rule_order'] = 999;
		$search_name = (empty($search_name)) ? $this->EE->lang->line('saved_search') : $search_name;
		$data['rule_label'] = $search_name;
		
		$this->EE->db->query($this->EE->db->insert_string($this->addon_short_name.'_saved_searches', $data, TRUE));
	}


	/**
	 * function update_search
	 * Saves filtering rules in list of saved searches in exp_zenbu_saved_searches table
	 * @return null
	 */
	function update_search($data)
	{
		foreach($data as $key => $data)
		{
			$this->EE->db->query($this->EE->db->update_string($this->addon_short_name.'_saved_searches', $data, "rule_id = " . $this->EE->db->escape_str($data['rule_id'])));
		}
	}
	
	
	/**
	 * function delete_search
	 * Removes saved search from list of saved searcheas in exp_zenbu_saved_searches table
	 * @return null
	 */
	function delete_search()
	{
		$rule_id = $this->EE->input->get('rule_id', TRUE);
		
		$this->EE->db->delete($this->addon_short_name.'_saved_searches', array('rule_id' => $rule_id)); 
	}


	/**
	 * function copy_search
	 * Copies saved search to member group(s) in exp_zenbu_saved_searches table
	 * @return null
	 */
	function copy_search()
	{
		$rule_id		= $this->EE->input->get_post('rule_id', TRUE);
		$member_groups	= $this->EE->input->get_post('group_id', TRUE);

		if(empty($member_groups))
		{
			return FALSE;
		}

		$sql = $this->EE->db->query("SELECT * FROM exp_zenbu_saved_searches WHERE rule_id = " . $this->EE->db->escape_str($rule_id));

		if($sql->num_rows() > 0)
		{
			$data = $sql->first_row('array');

			unset($data['rule_id']);
			$data['member_id'] = 0;
			
			foreach($member_groups as $m_id)
			{
				$data['member_group_id'] = $m_id;

				$this->EE->db->query($this->EE->db->insert_string($this->addon_short_name.'_saved_searches', $data, TRUE));
			}

		} else {
			return FALSE;
		}

	}

	
	/**
	 * function _save_settings
	 * Saves settings to the database table exp_zenbu
	 * @return event javascript output
	 */
    function _save_settings()
	{
		$this->EE->load->helper('loader');
		$post_data['settings']					= $this->EE->input->post('settings', FALSE); // FALSE to keep style="", but concerned...
		$post_data['copy_to_members']			= $this->EE->input->post('copy_to_members', TRUE);
		$post_data['members']					= $this->EE->input->post('members', TRUE);
		$post_data['clear_individual_settings']	= $this->EE->input->post('clear_individual_settings', TRUE);
		$post_data['enable_module']				= $this->EE->input->post('enable_module', FALSE);
		$installed_addons						= $this->EE->zenbu_get->_get_installed_addons();
		
		/**
		*	----------------------------------------------------------
		*	Build the extra settings labels array
		*	eg. text_option_1, text_option_2, my_fieldtype_option_1...
		*	----------------------------------------------------------
		*	Go through all fields, load fieldtype classes and fetch
		*	each extra settings label.
		*/
		
		// Determine non-fieldtype extra settings labels
		$non_ft_option_labels = $this->non_ft_extra_options;

		// Add Pages extra option if available
		if(in_array('Pages', $installed_addons['modules']))
		{
			$non_ft_option_labels['livelook_option_4'] = 'livelook_option_4';
		}
		
		// Get field information
		$fields = $this->EE->zenbu_get->_get_field_ids();
		if( ! empty($fields) && isset($fields['id'], $fields['field'], $fields['fieldtype']) )
		{
			$field_label_array = $fields['field'];
			$field_type_array = $fields['fieldtype'];
			$field_id_array = $fields['id'];
		}
		
		$ft_option_labels_array = array();
		
		if(isset($field_id_array))
		{
			foreach($field_id_array as $key => $field_id)
			{
				$ft_class = $field_type_array[$field_id].'_ft';
				load_ft_class($ft_class);
				
				if(class_exists($ft_class))
				{			
				
					// Retrieve extra settings short names
					// Parameters are empty as we just want to retrieve third-party array keys
					$ft_object = create_object($ft_class);
					$extra_settings_array = (method_exists($ft_object, 'zenbu_field_extra_settings')) ? $ft_object->zenbu_field_extra_settings("", "", array()) : array();
	
					// Create a simple list of "extra option" short names
					// Used to loop through short names when saving data
					if( ! empty($extra_settings_array))
					{
						$extra_settings_name_array = array_keys($extra_settings_array);
						foreach($extra_settings_name_array as $key => $extra_settings_name)
						{
							if( ! isset($ft_option_labels_array))
							{
								$ft_option_labels_array = array();
							}
							
							if(isset($ft_option_labels_array) && ! in_array($extra_settings_name, $ft_option_labels_array))
							{
								$ft_option_labels_array[] = $extra_settings_name;
							}
						}
					}
				}
			}
		}
		
		$ft_option_labels_array = array_merge($ft_option_labels_array, $non_ft_option_labels);

		//	END building extra settings labels array
		
		
		/**
		*	---------------------------------------------
		*	Process POST variables for saving in database
		*	---------------------------------------------
		*/
		$data = array();
		if($post_data['settings'] !== FALSE)
		{		
			foreach($post_data['settings'] as $channel_id => $settings)
			{
				if($channel_id == "general" && $channel_id != "0")
				{
					$settings['max_results_per_page'] = (int)$settings['max_results_per_page'];
					$db_data['general_settings'] = serialize($settings);

				} else {

					$form_channel_id =& $channel_id;
					$data['show_std_fields'] = array();
					$data['show_custom_fields'] = "";
					$data['field_order'] = array();
					$data['extra_options'] = array();
					$order_array = array();
					$extra_option_array = array();
				
					foreach($settings as $col => $setting)
					{
						
						if(substr($col, 0, 6) == 'field_' && isset($setting['show']) && $setting['show'] == 'y' && isset($col))
						{
							$data['show_custom_fields'] .= substr($col, 6).'|';
						} elseif(substr($col, 0, 6) != 'field_' && isset($setting['show'])) {
							$data['show_std_fields'][$col] = $setting['show'];
						}
						
						// Prepare ordering
						if(isset($setting['field_order']) && is_numeric($setting['field_order']) && ! isset($order_array[$setting['field_order']]))
						{
							$order_array[$setting['field_order']] = $col;
						}
						
						//	--------------
						//	Extra options
						//	--------------
						//	Fetch validation method for each field if available
						//	If not simply process the settings for update
						$field_id = (substr($col, 0, 6) == 'field_') ? substr($col, 6) : 0;
						$fieldtype_name = ($field_id != 0) ? $field_type_array[$field_id] : '';
						$ft_class = 'Zenbu_'.$fieldtype_name.'_ft';
						
						if(class_exists($ft_class) && method_exists($ft_class, 'zenbu_field_validation'))
						{
							$ft_object = create_object($ft_class);
							if(method_exists($ft_object, 'zenbu_field_validation'))
							{
								// Custom field validation
								$extra_option_array[$col] = $ft_object->zenbu_field_validation($setting);
							}
						} else {
							// If no custom validation, go ahead with preparing settings
							foreach($ft_option_labels_array as $key => $label)
							{
								if(isset($setting[$label]) && ! isset($extra_option_array[$setting[$label]]))
								{
									$extra_option_array[$col][$label] = $setting[$label];

									// Special validation for non-ft field who accepts numerical value (category limit)
									$extra_option_array[$col][$label] = $label == 'category_option_1' && ! is_numeric($setting[$label]) ? '' : $extra_option_array[$col][$label];
								}
							}
						}
							
					}
				
					ksort($order_array);
					$order_array = array_flip($order_array);
					
					$output_data_show_fields[$channel_id] = $data['show_std_fields'];
					$output_data_show_custom_fields[$channel_id]['show_custom_fields'] = (strlen($data['show_custom_fields']) > 0) ? substr($data['show_custom_fields'], 0, -1) : '';
					$output_data_field_order[$channel_id]['field_order'] = $order_array;
					$output_data_extra_options[$channel_id]['extra_options'] = $extra_option_array;	
				} // END if
			} // END foreach
			
			//	----------------------------------------
			//	Get previous setting data and plug in new data
			//	----------------------------------------

			$pre_save_settings = $this->EE->zenbu_get->_get_settings('pre_save');

			$setting_cols = array(
				'show_fields'			=> 'output_data_show_fields',
				'show_custom_fields'	=> 'output_data_show_custom_fields',
				'field_order'			=> 'output_data_field_order',
				'extra_options'			=> 'output_data_extra_options',
				);

			foreach($setting_cols as $col => $v)
			{
				// Plug in new values if present inside of $pre_save_settings

				foreach($pre_save_settings['setting'][$col] as $ch_id => $arr)
				{
					$db_data[$col][$ch_id] = isset(${$v}[$ch_id]) ? ${$v}[$ch_id] : $arr;
				}

				// What if it's a new channel? It won't be in $pre_save_settings,
				// so create this array and add it to data to be saved.

				if( ! isset($pre_save_settings['setting'][$col][$form_channel_id]) && isset($form_channel_id) )
				{
					$db_data[$col][$form_channel_id] = isset(${$v}[$form_channel_id]) ? ${$v}[$form_channel_id] : array();
				}

				$db_data[$col] = serialize($db_data[$col]);							

			}

			/**
			*	Save setting for single member
			*	==============================
			*/
			$db_data['member_id'] = $this->member_id;
			$db_data['site_id'] = $this->site_id;
			$this->EE->db->from($this->addon_short_name . '_member_settings');
			$this->EE->db->where('member_id', $this->member_id);
			$this->EE->db->where('site_id', $this->site_id);
			$results = $this->EE->db->get();
			if($results->num_rows() > 0)
			{
				// If row exists, update
				$sql = $this->EE->db->update_string($this->addon_short_name . '_member_settings', $db_data, "member_id = ".$this->EE->db->escape_str($this->member_id)." AND site_id = ".$this->EE->db->escape_str($this->site_id));
				$this->EE->db->query($sql);
			} else {
				// If row doesn't exist, insert new row
				$sql = $this->EE->db->insert_string($this->addon_short_name . '_member_settings', $db_data);
				$this->EE->db->query($sql);
			}

			
			// ... then update with correct settings
			// for each member group (if any are selected, of course)
			if($post_data['copy_to_members'] !== FALSE)
			{
				unset($db_data['member_id']);
				foreach($post_data['copy_to_members'] as $key => $m_group_id)
				{
					$db_data['member_group_id'] = $m_group_id;
					$db_data['site_id'] = $this->site_id;
					
					$this->EE->db->from($this->addon_short_name);
					$this->EE->db->where('member_group_id', $m_group_id);
					$results = $this->EE->db->get();
					if($results->num_rows() > 0)
					{
						// If row exists, update
						$sql = $this->EE->db->update_string($this->addon_short_name, $db_data, "member_group_id = ".$this->EE->db->escape_str($m_group_id)." AND site_id = ".$this->EE->db->escape_str($this->site_id));
						$this->EE->db->query($sql);
					} else {
						// If row doesn't exist, insert new row
						$sql = $this->EE->db->insert_string($this->addon_short_name, $db_data);
						$this->EE->db->query($sql);
					}

					//
					//	If clear individual settings is enabled
					//	Find users in each member group and remove settings in
					//	exp_zenbu_member_settings
					//
					if($post_data['clear_individual_settings'] !== FALSE)
					{
						$clear_members = array();
						$m_query = $this->EE->db->query('SELECT member_id FROM exp_members WHERE group_id = ' . $this->EE->db->escape_str($m_group_id));
						
						if($m_query->num_rows() > 0)
						{
							foreach($m_query->result_array() as $row)
							{
								$clear_members[] = $row['member_id'];
							}
						}
						if( ! empty($clear_members) )
						{
							$this->EE->db->where_in('member_id', $clear_members);
							$this->EE->db->delete($this->addon_short_name . '_member_settings');
						}
					}
				}
			}
		} // END if $post_data['settings'] !== FALSE
			
		//	----------------------------------------	
		// 	If post data is available to save/copy admin settings,
		// 	do the appropriate queries here for each member group
		//	----------------------------------------
		if($post_data['members'] !== FALSE)
		{
			foreach($post_data['members'] as $member_gr_id => $set)
			{
				foreach($this->permissions as $permission)
				{
					switch($permission)
					{
						case 'can_admin': 
						case 'can_copy_profile': 
						case 'can_access_settings':
							$setting[$permission] = ((isset($set[$permission]) && $set[$permission] == 'y') || $member_gr_id == 1) ? 'y' : 'n';
						break;
						default:
							$setting[$permission] = (isset($set[$permission]) && $set[$permission] == 'y') ? 'y' : 'n';
						break;
					}
				}
				
				// Update
				$sql = $this->EE->db->update_string($this->addon_short_name, $setting, "member_group_id = ".$this->EE->db->escape_str($member_gr_id)." AND site_id = ".$this->EE->db->escape_str($this->site_id));
				$this->EE->db->query($sql);
			}
		}

		/**
		 * 	----------------------------------------
		 *  For Super Admins, save data to enable the module for selected member groups.
		 *  A lot handier than viting each member group settings and enabling the add-on.
		 *  ----------------------------------------
		 */
		if($post_data['enable_module'])
		{
			$module_id = array_search(ZENBU_NAME, $installed_addons['modules']);

			// Remove all member_group module access settings for this module
			// Will add new settings based on submitted data later
			$this->EE->db->delete('exp_module_member_groups', array('module_id' => $module_id)); 

			foreach($post_data['enable_module'] as $key => $group_id)
			{
				if( $group_id != 1)
				{
					// Add member group to be allowed access to Zenbu
					$enable_data['group_id'] = $group_id;
					$enable_data['module_id'] = $module_id;
					$sql = $this->EE->db->insert_string('exp_module_member_groups', $enable_data);
					$this->EE->db->query($sql);

					// Turn on ADD-ONS and ADD-ONS => Modules options for the group.
					// I'll never understand this requirement.
					$enable_access['can_access_addons'] = 'y';
					$enable_access['can_access_modules'] = 'y';
					$this->EE->db->where('group_id', $group_id);
					$this->EE->db->update('member_groups', $enable_access);
				}
			}
		}
			
		if($post_data['members'] !== FALSE || $post_data['settings'] !== FALSE)
		{
		// Display success message
		$this->EE->javascript->output('
    			$.ee_notice("'.$this->EE->lang->line("message_settings_saved").'", {"type" : "success"});
			');
		}
	} // END function _save_settings

	// --------------------------------------------------------------------
	
	
	/**
	* function _insert_default_settings
	* Inserts row with default settings in database table
	* if member group id is not already in table
	* @param	int 	$member_group_id The current member group id
	* @param	string 	$group_name Used in mcp.zenbu.php, function settings_admin
	* @return	array	Output for admin view
	*/
	function _insert_default_settings($member_group_id, $group_name)
	{
		$default_settings_query = $this->EE->db->query("/* Zenbu _insert_default_settings - getting default data */ SELECT * FROM exp_".$this->addon_short_name." WHERE member_group_id = '0'");
		$db_data['member_group_id'] = $member_group_id;
		$db_data['site_id'] = $this->site_id;
		foreach($default_settings_query->result_array() as $row)
		{
			$db_data['show_fields'] = $row['show_fields'];
			$db_data['show_custom_fields'] = $row['show_custom_fields'];
			$db_data['field_order'] = $row['field_order'];
			$db_data['extra_options'] = $row['extra_options'];
		}
		$db_data['can_access_settings'] = 'n'; 	// n
		$db_data['can_admin'] = 'n';			// n
		$db_data['can_copy_profile'] = 'n';		// n
		$db_data['edit_replace'] = 'y'; 		// y
		
		$sql = $this->EE->db->insert_string('exp_'.$this->addon_short_name, $db_data);
		$this->EE->db->query($sql);
		
		$new_member_settings = $this->EE->db->query("SELECT * FROM exp_".$this->addon_short_name." WHERE member_group_id = '".$member_group_id."'");
		if($new_member_settings->num_rows() > 0)
		{
			foreach($new_member_settings->result_array() as $row2)
			{
				// Fetch results for the settings view
				$show_fields_array = unserialize($row['show_fields']);
				$show_custom_fields_array = unserialize($row['show_custom_fields']);
				$field_order_array = unserialize($row['field_order']);
				$extra_options_array = unserialize($row['extra_options']);
				
				foreach($show_fields_array as $channel_id => $array)
				{
					$output['setting'][$channel_id] = array_merge($array, $show_custom_fields_array[$channel_id], $field_order_array[$channel_id], $extra_options_array[$channel_id]);
				}
				
				$output['group_title']			= (isset($group_name)) ? $group_name : '';
				$output['group_id']				= $member_group_id;
				$output['member_group_id']		= $member_group_id;

				foreach($this->permissions as $permission)
				{
					$output[$permission]	= $row2[$permission];	
				}

			}
		}
		
		return $output;
		
	} // END function _insert_default_settings
}