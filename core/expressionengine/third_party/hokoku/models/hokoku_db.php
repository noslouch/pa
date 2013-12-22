<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hokoku_db extends Hokoku_mcp {


	function Hokoku_db()
	{
		parent::Hokoku_mcp();
	}
	
	
	/**
	 * function save_search
	 * Saves preset export profiles
	 * @return null
	 */
	function save_profile()
	{
		$access_settings = $this->EE->hokoku_get->_get_access_settings();
		$profile_data = $this->EE->hokoku_get->_get_export_profiles();
		$profile_type = isset($profile_data['by_profile_id'][$this->EE->input->get('profile_id')]['profile_type']) ? $profile_data['by_profile_id'][$this->EE->input->get('profile_id')]['profile_type'] : 'single';
		
		if($profile_type == 'group')
		{
			$data['member_id'] = 0;
			$data['group_id'] = $profile_data['by_profile_id'][$this->EE->input->get('profile_id')]['group_id'];
		} else {
			$data['member_id'] = $this->member_id;
			$data['group_id'] = $this->group_id;
		}

		$data['site_id'] = $this->site_id;
		$data['profile_label'] = $this->EE->input->get_post('profile_label', TRUE) ? $this->EE->input->get_post('profile_label', TRUE) : 'export';
		$data['export_format'] = $this->EE->input->get_post('export_format', TRUE);
		$data['export_filename'] = $this->EE->input->get_post('filename', TRUE);
		//$data['export_dir'] = $this->EE->input->get_post('upload_dir', TRUE);
		$data['export_settings'] = $this->EE->input->get_post('settings', TRUE) ? serialize($this->EE->input->get_post('settings', TRUE)) : serialize(array());
		
		// If a value for "update_profile" is passed in the form, 
		// update an existing row based on the profile_id also passed in the form.
		// If this isn't provided, add a new row
		if($this->EE->input->get_post('update_profile', TRUE) == 'y')
		{
			$where = 'site_id = '.$this->EE->db->escape_str($this->site_id).' AND member_id = '.$this->EE->db->escape_str($data['member_id']).' AND profile_id = '.$this->EE->db->escape_str($this->EE->input->get_post('profile_id', TRUE));

			$this->EE->db->query($this->EE->db->update_string('exp_hokoku_profiles', $data, $where));
		
		} else {
		
			$this->EE->db->query($this->EE->db->insert_string('exp_hokoku_profiles', $data, TRUE));
		
		}

		//	----------------------------------------
		//	Adding profile to specified member groups
		//	----------------------------------------
		if($this->EE->input->get_post('copy_profile', TRUE) && isset($access_settings[$this->group_id]['can_admin_group_profiles']) && $access_settings[$this->group_id]['can_admin_group_profiles'])
		{
			$data['member_id'] = 0;

			$groups = $this->EE->input->get_post('copy_profile', TRUE);
			
			if(is_array($groups))
			{
				foreach($groups as $group_id)
				{
					$data['group_id'] = $group_id;
					$this->EE->db->query($this->EE->db->insert_string('exp_hokoku_profiles', $data, TRUE));
				}
			}
		}

	}

	/**
	 * function delete_search
	 * Removes preset export profile
	 * @return null
	 */
	function delete_profile($profile_id)
	{
		// Don't do anything is no profile_id is provided!
		if( empty($profile_id) )
		{
			return;
		}

		$this->EE->db->delete('exp_hokoku_profiles', array('profile_id' => $profile_id)); 
	}


	/**
	* function _insert_default_access_settings
	* Inserts row with default settings in database table
	* if member group id is not already in table
	* @param	int 	$member_group_id The current member group id
	* @param	string 	$group_name Used in mcp.zenbu.php, function settings_admin
	* @return	array	Output for admin view
	*/
	function _insert_default_access_settings($member_group_id, $group_name)
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
				
				$output['group_title'] = (isset($group_name)) ? $group_name : '';
				$output['group_id'] = $member_group_id;
				$output['member_group_id'] = $member_group_id;
				$output['can_access_settings'] = $row2['can_access_settings'];
				$output['can_admin'] = $row2['can_admin'];
				$output['can_copy_profile'] = $row2['can_copy_profile'];
				$output['edit_replace'] = $row2['edit_replace'];

			}
		}
		
		return $output;
		
	} // END function _insert_default_settings

	/**
	 * function save_access_settings
	 * Saves settings to the database table exp_hokoku_access_settings
	 * @return event javascript output
	 */
	protected function save_access_settings()
	{

		$installed_addons = $this->EE->hokoku_get->_get_installed_addons();

		$sql = $this->EE->db->query('SELECT group_id FROM exp_' . $this->addon_short_name . '_access_settings WHERE site_id = ' . $this->EE->db->escape_str($this->site_id));

		$member_group_rows = array();
		
		if($sql->num_rows() > 0)
		{
			foreach($sql->result_array() as $row)
			{
				$member_group_rows[] = $row['group_id'];
			}
		}

		// If post data is available to save/copy admin settings,
		// do the appropriate queries here
		// for each member group
		if($this->EE->input->post('members'))
		{
			foreach($this->EE->input->post('members') as $member_gr_id => $data)
			{
				
				if( in_array($member_gr_id, $member_group_rows) )
				{
					// Update
					$sql = $this->EE->db->update_string($this->addon_short_name . '_access_settings', $data, "group_id = ".$this->EE->db->escape_str($member_gr_id)." AND site_id = ".$this->EE->db->escape_str($this->site_id));
					$this->EE->db->query($sql);
				} else {

					$data['group_id']	= $member_gr_id;
					$data['site_id']	= $this->site_id;

					// Add new row
					$sql = $this->EE->db->insert_string($this->addon_short_name . '_access_settings', $data);
					$this->EE->db->query($sql);
				}
			}
		}

		/**
		 * For Super Admins, save data to enable the module for selected member groups.
		 * A lot handier than viting each member group settings and enabling the add-on.
		 */
		if($this->EE->input->post('enable_module'))
		{
			$module_id = array_search(HOKOKU_NAME, $installed_addons['modules']);

			// Remove all member_group module access settings for this module
			// Will add new settings based on submitted data later
			$this->EE->db->delete('exp_module_member_groups', array('module_id' => $module_id)); 

			foreach($this->EE->input->post('enable_module') as $key => $group_id)
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
			
		if($this->EE->input->post('members') || $this->EE->input->post('enable_module'))
		{
		// Display success message
		$this->EE->javascript->output('
    			$.ee_notice("'.$this->EE->lang->line("message_settings_saved").'", {"type" : "success"});
			');
		}
	} // END save_access_settings()

	// --------------------------------------------------------------------

	protected function record_progress($profile_id = 0, $total_results = 0, $perpage = 0, $hash = '')
	{
		
		//	----------------------------------------
		//	Determine progress
		//	----------------------------------------
		$progress = round((100 * $perpage) / $total_results);
		$progress = $progress > 100 ? 100 : $progress;

		if( empty($hash) )
		{
			$hash = FALSE;

			//	----------------------------------------
			//	Create progress row
			//	----------------------------------------
			$data['profile_id']			= $profile_id;
			$data['export_start_date']	= $this->EE->localize->now;
			$data['member_id']			= $this->member_id;
			$data['group_id']			= $this->group_id;
			$data['site_id']			= $this->site_id;
			$data['total_results']		= $total_results;
			$data['total_exported']		= $perpage;
			$data['progress']			= $progress;
			$data['hash']				= random_string('sha1', 16);

			$sql = $this->EE->db->insert_string('exp_hokoku_cache', $data);
			$this->EE->db->query($sql);

			//	----------------------------------------
			//	Cache the created row
			//	----------------------------------------
			$this->EE->db->from('exp_hokoku_cache');
			$this->EE->db->where('profile_id', $profile_id);
			$this->EE->db->where('hash', $data['hash']);
			$this->EE->db->where('export_start_date', $data['export_start_date']);
			$this->EE->db->where('member_id', $this->member_id);
			$this->EE->db->where('group_id', $this->group_id);
			$this->EE->db->where('site_id', $this->site_id);
			$sql = $this->EE->db->get();
			
			if($sql->num_rows() > 0)
			{
				foreach($sql->result_array() as $row)
				{
					$hash	= $row['hash'];
				}
			}

		} else {

			$data['progress']			= $progress;
			$data['total_exported']		= $perpage;

			$sql = $this->EE->db->update('exp_hokoku_cache', $data, "hash = '" . $hash . "'");
		}

		return $hash;
		
	} // END record_progress()

	// --------------------------------------------------------------------


	protected function purge_old_progress_records()
	{
		$old_date = $this->EE->localize->now - 7200; // 7200 = 2 hours ago
		$this->EE->db->query("DELETE FROM exp_hokoku_cache WHERE export_start_date < " . $old_date);
	} // END purge_old_progress_records()

	// --------------------------------------------------------------------

}