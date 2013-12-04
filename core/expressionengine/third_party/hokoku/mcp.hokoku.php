<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Hokoku Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Nicolas Bottari
 * @link		http://nicolasbottari.com
 */

class Hokoku_mcp {
	
	public $return_data;
	
	private $_base_url;

	
	
	/**
	 * Constructor
	 */
	function Hokoku_mcp()
	{
		$this->EE =& get_instance();
		$this->site_id			= $this->EE->session->userdata['site_id'];
		$this->member_id		= $this->EE->session->userdata['member_id'];
		$this->group_id			= $this->EE->session->userdata['group_id'];
		$this->_base_url		= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hokoku';
		$this->return_to_zenbu	= BASE.'&C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=zenbu'.AMP.'method=index' . AMP . 'return_to_zenbu=y';
		$this->perpage			= 100;
		$this->addon_short_name = 'hokoku';
		if($this->EE->input->get('cp'))
		{
			$this->EE->cp->set_right_nav(array());
		}
	}


	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		// Alias to Manage Profiles page.
		$output = $this->manage_profiles();

		return $output;	
	}

	/**
	 * function export
	 *
	 * @return 	
	 */
	public function export($limit = 100, $perpage = 0, $total_results = '')
	{	
		// Prevent timeout
		set_time_limit(0);

		$this->EE->load->library('javascript');
		$this->EE->load->helper('html');
		$this->EE->load->helper('file');

		if( ! $this->EE->session->cache('hokoku', 'profile_data') )
		{
			$this->EE->load->add_package_path(PATH_THIRD.'hokoku');
			$this->EE->load->model('hokoku_get');
			$profile_data = $this->EE->hokoku_get->_get_export_profiles();
			$profile_data = $profile_data['by_profile_id'][$this->EE->input->get_post('profile_id')];
			$this->EE->session->set_cache('hokoku', 'profile_data', $profile_data);
		} else {
			$profile_data = $this->EE->session->cache('hokoku', 'profile_data');
		}

		$this->EE->load->helper('file');
		$this->EE->load->helper('output');
		$this->EE->load->model('hokoku_pack');
		$this->EE->load->model('hokoku_get');
		$this->EE->load->model('hokoku_db');

		//	----------------------------------------
		//	Find out where we're at based on hash
		//	----------------------------------------
		$hash = ($this->EE->input->get_post('hash')) ? $this->EE->input->get_post('hash') : '';
		$progress_data = $this->EE->hokoku_get->get_progress($hash);
		$perpage = isset($progress_data['total_exported']) ? $progress_data['total_exported'] : $perpage;

		//	----------------------------------------
		//	Purge old exports
		//	----------------------------------------
		if(empty($hash))
		{
			$this->EE->hokoku_db->purge_old_progress_records();
		}


		/**
		*	Loading Zenbu index method
		* 	==============================
		*/
		$this->EE->load->add_package_path(PATH_THIRD.'zenbu');
		
		$zenbu_class = 'Zenbu_mcp';
		
		if( ! class_exists($zenbu_class))
		{
			
			if(read_file(PATH_THIRD.'zenbu/mcp.zenbu.php') !== FALSE){
				
				require_once PATH_THIRD.'zenbu/mcp.zenbu.php';
				
			}
		}
		
		if(class_exists($zenbu_class))
		{
			$zenbu 	= new $zenbu_class();

			// Reset category_list cache before calling Zenbu, 
			// since we need a fresh list at each export batch
			$this->EE->session->set_cache('zenbu', 'category_list', '');

			$vars = $zenbu->index($limit, $perpage);
		}

		if( ! isset($vars['entry']) || empty($vars['entry']))
		{
			if(AJAX_REQUEST)
			{
				$output['no_data'] = 'y';
				$output['message'] = $this->EE->lang->line('no_data');
				$output = json_encode($output);
				
				echo $output;
				exit;

			} else {
				return 'no_data';
			}
		}

		$total_results = $this->EE->session->cache('zenbu', 'total_results');

		$final_query = $limit + $perpage >= $total_results ? TRUE : FALSE;

		/**
		* 	==============================
		* 	END of Zenbu loading
		*/

		// We got what we needed from Zenbu, add Hokoku package
		$this->EE->load->add_package_path(PATH_THIRD.'hokoku');
		

		$export_format			= $profile_data['export_format'] ? '.' . $profile_data['export_format'] : '.txt';
		$export_filename 		= parse_filename($profile_data['export_filename']) . $export_format;
		$path_to_file			= $this->EE->hokoku_get->_get_cache_destination() . $export_filename;

		switch($export_format)
		{
			case '.csv':
				$vars = $this->EE->hokoku_pack->pack_csv($vars, $perpage, $final_query);
			break;
			case '.html':
				$vars = $this->EE->hokoku_pack->pack_html($vars, $perpage, $final_query);
			break;
			case '.json':
				$vars = $this->EE->hokoku_pack->pack_json($vars, $perpage);
			break;
		}
		
		$perpage = $limit + $perpage;
		
		if($perpage <= $total_results)
		{
			//	----------------------------------------
			// 	File progress
			//	----------------------------------------
			$hash = $this->EE->hokoku_db->record_progress($profile_data['id'], $total_results, $perpage, $hash);

			$output['hash'] = $hash;
			$output['progress'] = isset($progress_data['progress']) ? $progress_data['progress'] : 0;
			$output['continue']	= 'y';
			$output = json_encode($output);

			echo $output;
			exit;

			// Continue exporting
			$this->export($limit, $perpage, $total_results);

		} else {
			
			if(AJAX_REQUEST)
			{
			
				// Reponse for ajax request
				// Using echo to avoid wrapping quotation marks, 
				// which happens with send_ajax_response
				$output['message'] = base64_encode($export_filename);
				$output = json_encode($output);

				echo $output;
				exit;
			
			} else {
			
				// Response for non-ajax request
				$this->EE->load->helper('download');
				$filedata = read_file($path_to_file); // Read the file's contents
				force_download($export_filename, $filedata);
			
			}
		}
		
	}

	/**
	*	function download()
	*	==============================
	*	Sets up the output file to be sent to browser for download
	*	@param 	none
	*	@return 	(void)	The file, sent to browser
	*/
	function download()
	{
		
		$this->EE->load->model('hokoku_get');
		$this->EE->load->helper('file');
		$this->EE->load->helper('download');

		$encoded_filename	= $this->EE->input->get('filename', TRUE);
		$export_filename	= base64_decode($encoded_filename);
		$path_to_file		= $this->EE->hokoku_get->_get_cache_destination() . $export_filename;

		if( read_file($path_to_file) )
		{
			$filedata = read_file($path_to_file); // Read the file's contents

			force_download($export_filename, $filedata);
		}
	}


	/**
	 * function manage_profiles
	 * The export profile manager
	 * 
	 * @return string 	The view
	 */
	function manage_profiles()
	{
		$this->EE->load->model('hokoku_get');
		$this->EE->cp->load_package_js('hokoku_manage_profiles');

		if(version_compare(APP_VER, '2.6', '>'))
		{
			$this->EE->view->cp_page_title = lang('manage_profiles');
		} else {
			$this->EE->cp->set_variable('cp_page_title', lang('manage_profiles'));
		}
		
		$this->EE->hokoku_get->set_head_stylesheets();

		$access_settings = $this->EE->hokoku_get->_get_access_settings();
		
		$vars['out']					= $this->EE->hokoku_get->_get_export_profiles('all');
		$vars['access_settings']		= isset($access_settings[$this->group_id]) ? $access_settings[$this->group_id] : array();
		$vars['base_url']				= $this->_base_url;
		$vars['create_new_profile_url']	= $this->_base_url."&C=addons_modules".AMP."M=show_module_cp".AMP."module=hokoku" . AMP . "method=edit_profiles";
		$vars['return_to_zenbu_url']	= $this->return_to_zenbu;

		//	----------------------------------------
		//	Prevent access to section if the member can't
		//	view or edit profiles
		//	----------------------------------------
		if( (
			! empty($vars['access_settings']) 
			&& $vars['access_settings']['can_admin_own_profiles'] != 'y' 
			&& $vars['access_settings']['can_view_group_profiles'] != 'y'
			&& $vars['access_settings']['can_admin_group_profiles'] != 'y'
			) 
			|| empty($vars['access_settings'])
		)
		{
			show_error($this->EE->lang->line('cannot_access_profile_manager'));
		}

		//	----------------------------------------
		//	Add/Remove "Create new profile" link
		//	----------------------------------------
		if( ! empty($vars['access_settings']) 
			&& ($vars['access_settings']['can_admin_own_profiles'] == 'y' 
			|| $vars['access_settings']['can_admin_group_profiles'] == 'y') )
		{
			$nav_array["<i class='icon-edit'></i>&nbsp;".lang('create_new_profile')] = $this->_base_url."&method=edit_profiles";
			$this->EE->cp->set_right_nav($nav_array);
		}

		//	----------------------------------------
		//	Add/Remove "Create new profile" link
		//	----------------------------------------
		if( ! empty($vars['access_settings']) 
			&& ($vars['access_settings']['can_access_access_settings'] == 'y' || $this->group_id == 1) )
		{
			$nav_array["<i class='icon-group'></i>&nbsp;".lang('member_access_settings')] = $this->_base_url."&method=access_settings";
			$this->EE->cp->set_right_nav($nav_array);
		}
		
		return $this->EE->load->view('hokoku_manage_profiles', $vars, TRUE);

	}

	/**
	 * =======================
	 * function edit_profiles
	 * =======================
	 * The form used for updating/creating export profiles
	 * @return string The form
	 */
	public function edit_profiles()
	{
		$this->EE->load->library('javascript');
		$this->EE->cp->load_package_js('hokoku_index');
		
		$this->EE->load->helper('output');
		$this->EE->load->helper('html');
		$this->EE->load->model('hokoku_get');
		$this->EE->hokoku_get->set_head_stylesheets();

		$access_settings = $this->EE->hokoku_get->_get_access_settings();

		//	----------------------------------------
		//	Deny access to users that shouldn't have
		//	access to the create/edit profile page
		//	----------------------------------------
		if( isset($access_settings[$this->group_id]['can_admin_own_profiles']) &&
			isset($access_settings[$this->group_id]['can_view_group_profiles']) &&
			isset($access_settings[$this->group_id]['can_admin_group_profiles']) &&
			$access_settings[$this->group_id]['can_admin_own_profiles'] != 'y' &&
			$access_settings[$this->group_id]['can_view_group_profiles'] != 'y' &&
			$access_settings[$this->group_id]['can_admin_group_profiles'] != 'y'
		  )
		{
			show_error($this->EE->lang->line('cannot_access_edit_profiles'));
		}



		$profile_data = $this->EE->hokoku_get->_get_export_profiles($this->EE->input->get('profile_id', TRUE));
		$profile_data = isset($profile_data['by_profile_id'][$this->EE->input->get('profile_id', TRUE)]) ? $profile_data['by_profile_id'][$this->EE->input->get('profile_id', TRUE)] : array();
		$profile_id		= isset($profile_data['id']) ? $profile_data['id'] : '';

		// Reset top page title
		if(empty($profile_id))
		{
			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = lang('create_new_profile');
			} else {
				$this->EE->cp->set_variable('cp_page_title', lang('create_new_profile'));
			}
		} else {
			if(version_compare(APP_VER, '2.6', '>'))
			{
				$this->EE->view->cp_page_title = lang('edit_profiles');
			} else {
				$this->EE->cp->set_variable('cp_page_title', lang('edit_profiles'));
			}
		}

		$vars['format'] = isset($profile_data['export_format']) ? $profile_data['export_format'] : '';
		$vars['export_filename'] = isset($profile_data['export_filename']) ? $profile_data['export_filename'] : '';
		$vars['profile_label'] = isset($profile_data['label']) ? $profile_data['label'] : '';

		/**
		*	Export options
		*/
		$export_formats = array(
			'csv' 	=> 'csv',
			'html' 	=> 'html',
			'json' 	=> 'json',
		);
		$vars['export_formats']			= form_dropdown('export_format', $export_formats, $vars['format'], 'id="export_format"');
		$vars['export_options']			= $this->EE->hokoku_get->_get_export_options();

		$vars['update_profile']				= ! empty($profile_id) ? form_hidden('update_profile', 'y').form_hidden('profile_id', $profile_id) : '';
		$vars['return_to_manage_profiles']	= $this->_base_url."&C=addons_modules".AMP."M=show_module_cp".AMP."module=hokoku" . AMP . "method=manage_profiles";
		$vars['action_url']					= "C=addons_modules".AMP."M=show_module_cp".AMP."module=hokoku" . AMP . "method=save_profile&profile_id=".$profile_id;

		//	----------------------------------------
		//	Building member group data for those
		//	that have the permission
		//	----------------------------------------
		if( isset($access_settings[$this->group_id]['can_admin_group_profiles']) && $access_settings[$this->group_id]['can_admin_group_profiles'] == 'y' && empty($profile_id) )
		{
			// Get list of member groups
			$results = $this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0) AND site_id = ".$this->site_id . " ORDER BY group_id");

			if($results->num_rows() > 0) 
			{
				foreach($results->result_array() as $num => $row)
				{
					$vars['member_groups'][$row['group_id']] = $row['group_title'];
					$vars['checked'][$row['group_id']] = isset($profile_data['profile_type']) && $profile_data['profile_type'] == 'group' && $row['group_id'] == $profile_data['group_id'] ? TRUE : FALSE;
				}
			}
		}

		// Send everything through Hokoku's view
		$output = $this->EE->load->view('hokoku_edit_profile', $vars, TRUE);

		return $output;
	}


	/**
	 * ========================
	 * function delete_profile
	 * ========================
	 * Deletes a profile from the database and returns to the profile manager
	 * @return  void 	Redirects to the export profile manager
	 */
	function delete_profile()
	{
		$profile_id = $this->EE->input->get_post('profile_id', TRUE);
		$this->EE->load->model('hokoku_db');
		$this->EE->hokoku_db->delete_profile($profile_id);

		$this->EE->functions->redirect(str_replace('&amp;', '&', $this->_base_url.'&method=manage_profiles'));
	}


	/**
	 * ========================
	 * function save_profile
	 * ========================
	 * Saves or updates a profile in the database and returns to the profile manager
	 * @return  void 	Redirects to the export profile manager
	 */
	function save_profile()
	{
		$this->EE->load->model('hokoku_get');
		$access_settings = $this->EE->hokoku_get->_get_access_settings();

		//	----------------------------------------
		//	Deny access to users that shouldn't have
		//	access to the create/edit profile page
		//	----------------------------------------
		if( isset($access_settings[$this->group_id]['can_admin_own_profiles']) &&
			isset($access_settings[$this->group_id]['can_view_group_profiles']) &&
			isset($access_settings[$this->group_id]['can_admin_group_profiles']) &&
			$access_settings[$this->group_id]['can_admin_own_profiles'] != 'y' &&
			$access_settings[$this->group_id]['can_view_group_profiles'] != 'y' &&
			$access_settings[$this->group_id]['can_admin_group_profiles'] != 'y'
		  )
		{
			show_error($this->EE->lang->line('cannot_access_edit_profiles'));
		}

		// Set top page title
		if(version_compare(APP_VER, '2.6', '>'))
		{
			$this->EE->view->cp_page_title = lang('preset_saved');
		} else {
			$this->EE->cp->set_variable('cp_page_title', lang('preset_saved'));
		}

		$this->EE->load->model('hokoku_db');
		$this->EE->load->helper('url');
		$this->EE->hokoku_db->save_profile();
		
		$this->EE->functions->redirect(str_replace('&amp;', '&', $this->_base_url.'&method=manage_profiles'));
	}


	/**
	 * ========================
	 * function tag_builder
	 * ========================
	 * Shows an interface to help build the {exp:hokoku:export} tag
	 * @return  string 		The tag builder page 	
	 */
	function tag_builder()
	{
		$profile_id = $this->EE->input->get('profile_id', TRUE) ? $this->EE->input->get('profile_id', TRUE) : '';

		// Exit if no valid profile_id is provided 
		if(empty($profile_id) || ! is_numeric($profile_id))
		{
			show_error($this->EE->lang->line('provide_profile_id'));;
		}

		$this->EE->load->model('hokoku_get');
		$this->EE->load->helper('url');

		$vars = array(
			'profile_id' => $profile_id,
			'member_id'	=> $this->EE->session->userdata['member_id'],
			'searches'	=> $this->EE->hokoku_get->_get_saved_searches(),
		);

		$output = $this->EE->load->view('hokoku_tag_builder', $vars, TRUE);
		
		if(AJAX_REQUEST)
		{
			echo $output; 
			exit;
			//$this->EE->output->send_ajax_response($output);

		} else {
			return $output;
		}
	}


	/**
	*	function check_for_file()
	*	=========================
	*	Checks for duplicate files and throws warning if a file
	*	with the same name, extension and upload folder is present.
	*	@param 	none
	*	@return 	(string)	AJAX output string containing results message
	*/
	function check_for_file()
	{
		$this->EE->load->model('hokoku_get');
		$this->EE->load->helper('output');
		$this->EE->lang->loadfile('filemanager', 'content', 'cp');

		$export_format		= $this->EE->input->get_post('export_format') ? '.' . $this->EE->input->get_post('export_format') : '.txt';
		$output_filename	= $this->EE->input->get_post('filename') ? parse_filename($this->EE->input->get_post('filename')) : 'file';
		$path				= $this->EE->hokoku_get->_get_save_destination('path');
		$url				= $this->EE->hokoku_get->_get_save_destination('url');
		$filename_url		= $url . $output_filename . $export_format;
		$filename			= $path . $output_filename . $export_format;

		if( ! is_dir($path))
		{
			$output['response'] = 'alert';
			$output['message'] = lang('invalid_directory');
			$this->EE->output->send_ajax_response($output);
		}

		if( read_file($filename) )
		{
			$output['response'] = 'confirm';
			$output['message'] = lang('file_already_exists');
			$this->EE->output->send_ajax_response($output);
		} else {
			$this->EE->output->send_ajax_response('');
		}
	}



	/**
	 * Member access settings
	 *
	 * @access	public
	 */
	function access_settings()
	{
		$this->EE->load->model('hokoku_get');
		$this->EE->load->model('hokoku_db');
		$this->EE->hokoku_db->save_access_settings();
		$this->EE->hokoku_get->set_head_stylesheets();
		
		$this->EE->lang->loadfile('content', 'cp');	// We'll need a few string from there

		//$this->EE->load->add_package_path(PATH_THIRD.'zenbu');
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper(array('form'));

		$this->EE->cp->load_package_js('hokoku_script');
		$this->EE->cp->load_package_js('hokoku_index');
		
		//$this->EE->javascript->compile();  

		if(version_compare(APP_VER, '2.6', '>'))
		{
			$this->EE->view->cp_page_title = $this->EE->lang->line('hokoku_module_name').' - '.$this->EE->lang->line('member_access_settings');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('hokoku_module_name').' - '.$this->EE->lang->line('member_access_settings'));
		}
		
		// Get settings from the database
		$vars_member_data['access_settings'] = $this->EE->hokoku_get->_get_access_settings();
		$settings = &$vars_member_data['access_settings']; 
		
		// Build right nav links based on member group settings
		$nav_array["<i class='icon-list'></i>&nbsp;".lang('manage_profiles')] = $this->_base_url;
		
		// Kick 'em out if they are not authorized
		if( ! isset($settings[$this->group_id]['can_access_access_settings']) || ($settings[$this->group_id]['can_access_access_settings'] != 'y' &&  $this->group_id != 1) ) 
		{
			$this->EE->cp->set_right_nav($nav_array);
			return $this->EE->lang->line('unauthorized_access');
		}
		
		$this->EE->cp->set_right_nav($nav_array);

		// Get list of member groups
		$results = ($this->group_id != 1) ? 
			$this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0, 1) AND site_id = ".$this->site_id . " ORDER BY group_id") :
			$this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE group_id NOT IN(0) AND site_id = ".$this->site_id . " ORDER BY group_id");

		if($results->num_rows() > 0) {
			foreach($results->result_array() as $num => $row)
			{
				$members[$row['group_id']] = $row['group_title'];

				$vars_member_data['member_groups'][$num] = $row;

				// $settings_query = $this->EE->db->query("/* Hokoku get settings for a specific member group/site */ SELECT * FROM exp_".$this->addon_short_name." WHERE member_group_id = ".$row['group_id']." AND site_id = ".$this->site_id);
				// if($settings_query->num_rows() > 0) {
				// 	foreach($settings_query->result_array() as $num2 => $row2)
				// 	{
				// 		$vars_member_data['member_groups'][$num] = $row; // group_id and group_title
				// 		$vars_member_data['member_groups'][$num]['can_admin'] = $row2['can_admin'];
				// 		$vars_member_data['member_groups'][$num]['can_copy_profile'] = $row2['can_copy_profile'];
				// 		$vars_member_data['member_groups'][$num]['can_access_settings'] = $row2['can_access_settings'];
				// 		$vars_member_data['member_groups'][$num]['edit_replace'] = $row2['edit_replace'];
				// 	}
				// } else {
				// 	$vars_member_data['member_groups'][$num] = $this->EE->zenbu_db->_insert_default_settings($row['group_id'], $row['group_title']);

				// }
				
			}
		}
		
		if($this->group_id == 1)
		{
			$installed_addons	= $this->EE->hokoku_get->_get_installed_addons();
			$module_id			= array_search(HOKOKU_NAME, $installed_addons['modules']);
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

		$vars_urls['action_url'] = "C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=access_settings";
		//$vars_urls['settings_view_url'] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=settings";
		$vars_urls['settings_admin_url'] = BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=".$this->addon_short_name.AMP."method=access_settings";
		// Check if current member group can administrate member access
		//$vars_other['can_admin'] = $settings['can_admin'];
		$vars_other['current_member_group'] = $this->group_id;
		
		$vars = array_merge($vars_member_data, $vars_urls, $vars_other);
		
		return $this->EE->load->view('hokoku_access_settings', $vars, TRUE);

		
	} // END function access_settings

	// --------------------------------------------------------------------


}
/* End of file mcp.hokoku.php */
/* Location: /system/expressionengine/third_party/hokoku/mcp.hokoku.php */