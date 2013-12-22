<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hokoku_get extends Hokoku_mcp {

	function Hokoku_get()
	{
		parent::Hokoku_mcp();
	}

	/**
	 * function set_head_stylesheets
	 */
	public function set_head_stylesheets()
	{
		if(defined('URL_THIRD_THEMES'))
		{
			$this->EE->cp->add_to_foot('<link type="text/css" rel="stylesheet" href="'.URL_THIRD_THEMES.'zenbu/fontawesome/fontawesome.css" />');
		} else {	
			$this->EE->cp->add_to_foot('<link type="text/css" rel="stylesheet" href="'.$this->EE->config->item('theme_folder_url').'zenbu/third_party/'.$this->addon_short_name.'/fontawesome/fontawesome.css" />');
		}
	}


	/**
	* function _get_access_settings
	* Retrieve settings for member group
	* @return	array User member group settings 
	*/
	public function _get_access_settings()
	{
		// Return data if already cached
		if($this->EE->session->cache('hokoku', 'access_settings'))
		{
			return $this->EE->session->cache('hokoku', 'access_settings');
		}

		$results = $this->EE->db->query("/* Hokoku _get_access_settings */ \n SELECT * FROM exp_hokoku_access_settings WHERE site_id = " . $this->EE->db->escape_str($this->site_id));

		$output = array();

		$permissions = array('can_admin_own_profiles', 'can_view_group_profiles', 'can_admin_group_profiles', 'can_access_access_settings');
				
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				
				foreach($permissions as $perm)
				{
					$output[$row['group_id']][$perm] = $row[$perm];
				}

			}
		} else {

			//	----------------------------------------
			//	If there are no results, likely means a
			//	site has been added AFTER Hokoku was installed
			//	Give at least Super Admins access to everything
			//	----------------------------------------
			foreach($permissions as $perm)
			{
				$output[1][$perm] = 'y';
			}
		}
		
		$results->free_result();
		
		$this->EE->session->set_cache('hokoku', 'access_settings', $output);

		return $output;
		
	}


	/**
	* Function _get_file_upload_prefs
	* @return	array upload preferences
	*/
	function _get_file_upload_prefs()
	{
		$result = array();
		if (version_compare(APP_VER, '2.4', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			$result['general_data'] = $this->EE->file_upload_preferences_model->get_file_upload_preferences($this->group_id);
			foreach($result['general_data'] as $id => $data)
			{
				$result['destination_dropdown'][$id] = $data['name'];
			}
			
			return $result;
		}

		if (version_compare(APP_VER, '2.1.5', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			$result = $this->EE->file_upload_preferences_model->get_upload_preferences($this->group_id);
		} else {
			$this->EE->load->model('tools_model');
			$result = $this->EE->tools_model->get_upload_preferences($this->group_id);
		}

		// Use upload destination ID as key for row for easy traversing
		$output = array();
		if( ! empty($result) && $result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				// $output[$row['id']]['url'] = $row['url'];
				// $output[$row['id']]['server_path'] = $row['server_path'];

				$output['destination_dropdown'][$row['id']] = $row['name'];

				$output['general_data'][$row['id']]['id'] = $row['id'];
				$output['general_data'][$row['id']]['url'] = $row['url'];
				$output['general_data'][$row['id']]['server_path'] = $row['server_path'];
				$output['general_data'][$row['id']]['name'] = $row['name'];
			}
		}

		return $output;
	} // END function _get_file_upload_prefs

	function _get_save_destination($type = 'path')
	{
		$path = $_SERVER['DOCUMENT_ROOT'];
		/**
		*	Set up save destination
		*/
		$upload_dir			= $this->EE->input->get_post('upload_dir') ? $this->EE->input->get_post('upload_dir') : '';
		$upload_prefs 		= $this->_get_file_upload_prefs();
		switch ($type)
		{
			case 'path':
				$output = $upload_prefs['general_data'][$upload_dir]['server_path'];
			break;
			case 'url':
				$output = $upload_prefs['general_data'][$upload_dir]['url'];
			break;
		}
		
		return $output;
	}


	function _get_cache_destination()
	{
		
		// Get the cache path from config or make one yourself.
		if( $this->EE->config->item('cache_path') != '' )
		{
			$cache_path = rtrim($this->EE->config->item('cache_path'), '/') . '/hokoku/';
		} else {
			$cache_path = rtrim(APPPATH, '/') . '/cache/hokoku/'; 
		}

		// Create the hokoku directory if it doesn't exist
		if( ! is_dir($cache_path))
		{
			mkdir($cache_path);
		}

		return $cache_path;
	}


	/**
	* Function _get_export_options
	* @return	array upload preferences
	*/
	function _get_export_options()
	{
		$output 			= array();
		//$upload_prefs		= $this->_get_file_upload_prefs();
		$profile_data		= $this->_get_export_profiles();
		$profile_data		= isset($profile_data['by_profile_id'][$this->EE->input->get_post('profile_id')]) ? $profile_data['by_profile_id'][$this->EE->input->get_post('profile_id')] : array();
		$default_format		= isset($profile_data['export_format']) ? $profile_data['export_format'] : 'csv';
		
		if ( isset($profile_data['export_filename']) && ! in_array($this->EE->input->get('method'), array('edit_profiles')) )
		{

			// Parse the filename if you're not in the edit profiles section, 
			// eg. the index page, ready to export with a single name 
			$default_filename = parse_filename($profile_data['export_filename']);
		
		} elseif ( isset($profile_data['export_filename']) ) {

			//  Don't parse when you're on the edit profiles section 
			$default_filename = $profile_data['export_filename'];
		
		} else {
			
			$default_filename = 'export_%Y%m%d_%H%i';
		
		}

		//$default_upload_dir	= isset($profile_data['export_dir']) ? $profile_data['export_dir'] : '';

		/**
		*	CSV
		*/
		$ext				= 'csv';
		$disabled			= ($ext == $default_format) ? '' : ' disabled="disabled"';
		$default_delimiter	= isset($profile_data['export_settings']['delimiter']) ? $profile_data['export_settings']['delimiter'] : ',';
		$default_enclosure	= isset($profile_data['export_settings']['enclosure']) ? $profile_data['export_settings']['enclosure'] : '"';
		$check_excel_compat = $default_delimiter == 'TAB' && $default_enclosure == '"' ? TRUE : FALSE;
		
		$output[$ext][form_label($this->EE->lang->line('delimiter'), 'delimiter')]		= form_input('settings[delimiter]', $default_delimiter, 'size="1" maxlength="5" id="delimiter "' . $disabled) . nbs(3) . form_checkbox('excelcompat', '', $check_excel_compat) . nbs(1) . $this->EE->lang->line('excelcompat');
		$output[$ext][form_label($this->EE->lang->line('enclosure'), 'enclosure')]		= form_input('settings[enclosure]', $default_enclosure, 'size="1" maxlength="2" id="enclosure"' . $disabled);
		$output[$ext][form_label($this->EE->lang->line('filename'), 'filename')]		= form_input('filename', $default_filename, 'size="25" id="filename"' . $disabled) . ' .' . $ext;
		//$output[$ext][form_label($this->EE->lang->line('upload_pref'), 'upload_dir')]	= form_dropdown('upload_dir', isset($upload_prefs['destination_dropdown']) ? $upload_prefs['destination_dropdown'] : array(), $default_upload_dir, 'id="upload_dir"' . $disabled);

		/**
		*	HTML
		*/
		$ext = 'html';
		$disabled = ($ext == $default_format) ? '' : ' disabled="disabled"';
		$output[$ext][form_label($this->EE->lang->line('filename'), 'filename_html')] = form_input('filename', $default_filename, 'size="25" id="filename_html"' . $disabled) . ' .' . $ext;
		//$output[$ext][form_label($this->EE->lang->line('upload_pref'), 'upload_dir_html')] = form_dropdown('upload_dir', isset($upload_prefs['destination_dropdown']) ? $upload_prefs['destination_dropdown'] : array(), $default_upload_dir, 'id="upload_dir_html"' . $disabled);

		/**
		*	JSON
		*/
		$ext = 'json';
		$disabled = ($ext == $default_format) ? '' : ' disabled="disabled"';
		$output[$ext][form_label($this->EE->lang->line('filename'), 'filename_json')] = form_input('filename', $default_filename, 'size="25" id="filename_json"' . $disabled) . ' .' . $ext;
		//$output[$ext][form_label($this->EE->lang->line('upload_pref'), 'upload_dir_json')] = form_dropdown('upload_dir', isset($upload_prefs['destination_dropdown']) ? $upload_prefs['destination_dropdown'] : array(), $default_upload_dir, 'id="upload_dir_json"' . $disabled);
		
		return $output;
	} // END function _get_export_options



	/**
	* function _get_saved_searches
	* Retrieve member's saved search data
	* @return	array Rule ID and label 
	*/
	function _get_saved_searches()
	{
		$this->EE->db->from('zenbu_saved_searches');
		$this->EE->db->where('member_id', $this->member_id);
		$this->EE->db->order_by('rule_id', 'asc');
		$results = $this->EE->db->get();
		
		$output = array();
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output[$row['rule_id']]['rule_label'] 	= $row['rule_label'];
				$output[$row['rule_id']]['rule_id'] 	= $row['rule_id'];
				$output[$row['rule_id']]['rule_url']	= anchor(BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=hokoku".AMP."rule_id=".$row['rule_id'], $row['rule_label']);
			}
		}
		
		$results->free_result();
		
		return $output;
	}

	function _get_export_profiles($profile_id = '')
	{
		$output = array();

		$profile_id = $this->EE->input->get_post('profile_id') ? $this->EE->input->get_post('profile_id') : $profile_id;

		// Get out of here if we're on edit_profiles page and no profile_id is passed
		if( empty($profile_id) )
		{
			$output[] = array();
			return $output;	
		}

		$access_settings = $this->_get_access_settings();
		$acs = isset($access_settings[$this->group_id]) ? $access_settings[$this->group_id] : array();

		$profile_id_query	= $profile_id != 'all' ? " AND hp.profile_id = " . $this->EE->db->escape_str($profile_id) : '';
		$member_id_query	= $this->EE->session->userdata['group_id'] != 3 ?  " AND (hp.member_id = " . $this->EE->db->escape_str($this->member_id) : '';
		$group_id_query		= isset($acs['can_view_group_profiles']) && $acs['can_view_group_profiles'] == 'y' ? " OR hp.group_id = " . $this->EE->db->escape_str($this->group_id) . ")" : ")";
		$group_id_query		= isset($acs['can_admin_group_profiles']) && $acs['can_admin_group_profiles'] == 'y' ? $group_id_query . " OR hp.site_id = " . $this->EE->db->escape_str($this->site_id) . "" : $group_id_query;

		$results = $this->EE->db->query("/* Hokoku _get_export_profiles */ \nSELECT * FROM exp_hokoku_profiles hp
			LEFT JOIN exp_member_groups mg ON mg.group_id = hp.group_id 
			WHERE hp.site_id = " . $this->EE->db->escape_str($this->site_id) . $member_id_query . $group_id_query . $profile_id_query);
		
		if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$output['by_profile_id'][$row['profile_id']] = array(
					'id'				=> $row['profile_id'],
					'member_id'			=> $row['member_id'],
					'group_id'			=> $row['group_id'],
					'label'				=> $row['profile_label'],
					'export_format'		=> $row['export_format'],
					'export_filename'	=> $row['export_filename'],
					//'export_dir'		=> $row['export_dir'],
					'export_settings'	=> unserialize($row['export_settings']),
					'profile_type'		=> $row['member_id'] != '0' ? 'single' : 'group',
				);

				$output['group_name'][$row['group_id']] = $row['group_title'];
				
				$output['by_group'][$row['group_id']][$row['profile_id']] = $output['by_profile_id'][$row['profile_id']];

			}
		}
		
		return $output;
	}


	/**
	* Gets a list of installed and accessible modules
	* @return array	Simple array of installed modules
	*/
	function _get_installed_addons()
	{
		// Return data if already cached
		if($this->EE->session->cache('hokoku', 'installed_addons'))
		{
			return $this->EE->session->cache('hokoku', 'installed_addons');
		}

		$output = array();
				
		$query = $this->EE->db->query("/* Hokoku _get_installed_addons */ \n SELECT m.module_id, m.module_name, m.module_version, e.extension_id, e.class, e.version 
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

		$this->EE->session->set_cache('hokoku', 'installed_addons', $output);
		
		return $output;
	}

	/**
	 * Retrieve the progress id
	 */
	function get_progress($hash = '')
	{
		$sql = $this->EE->db->query("SELECT * FROM exp_hokoku_cache WHERE hash = '".$this->EE->db->escape_str($hash)."'");

		$output = array();

		if($sql->num_rows() > 0)
		{
			foreach($sql->result_array() as $row)
			{
				$output['total_exported']	= $row['total_exported'];
				$output['progress']			= $row['progress'];
				$output['id']				= $row['id'];
				$output['hash']				= $row['hash'];
			}
		}

		return $output;
	}



}
?>