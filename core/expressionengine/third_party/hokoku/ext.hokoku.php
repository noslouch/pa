<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Hokoku Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Nicolas Bottari
 * @link		http://nicolasbottari.com
 */

if( ! defined('PATH_THIRD')) { define('PATH_THIRD', APPPATH . 'third_party'); };
require_once PATH_THIRD . 'hokoku/config.php';

class Hokoku_ext {
	
	public $settings 		= array();
	public $description		= 'Zenbu Add-On - Export entry data from Zenbu';
	public $docs_url		= 'http://zenbustudio.com/software/hokoku';
	public $name			= HOKOKU_NAME;
	public $settings_exist	= 'n';
	public $version			= HOKOKU_VER;
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('hokoku');
		$this->settings		= $settings;

		//	----------------------------------------
		//	Load Session Libraries if not available 
		//	(eg. in cp_js_end hook) - EE 2.6
		//	----------------------------------------

		// Get the old last_call first, just to be sure we have it
		$old_last_call = $this->EE->extensions->last_call;

		if ( ! isset($this->EE->session) || ! isset($this->EE->session->userdata) )
        {

            if (file_exists(APPPATH . 'libraries/Localize.php'))
            {
                $this->EE->load->library('localize');
            }

            if (file_exists(APPPATH . 'libraries/Remember.php'))
            {
                $this->EE->load->library('remember');
            }

            if (file_exists(APPPATH.'libraries/Session.php'))
            {
                $this->EE->load->library('session');
            }
        }

        // Restore last_call
        $this->EE->extensions->last_call = $old_last_call;
        
		$this->site_id		= $this->EE->session->userdata['site_id'];
		$this->member_id	= $this->EE->session->userdata['member_id'];
		$this->group_id		= $this->EE->session->userdata['group_id'];
		$this->_base_url	= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hokoku';
		$this->cp_call		= (REQ == 'CP') ? TRUE : FALSE;
	}
	
	// ----------------------------------------------------------------------
	
	private function get_settings()
	{
		// Alias to the already-existing _get_export_profiles() method
		
		require_once PATH_THIRD . 'hokoku/mcp.hokoku.php';

		$this->EE->load->model('hokoku_get');
		
		$output['profiles'] = $this->EE->hokoku_get->_get_export_profiles('all');
		$output['access_settings'] = $this->EE->hokoku_get->_get_access_settings();

		return $output;
	}

	/**
	 * ==============================================
	 * function zenbu_add_export_button
	 * ==============================================
	 * Adds an "Export this search" button in Zenbu,
	 * next to the "Save this search" button
	 * @return $output 	string 	The markup to be added after the save search button
	 */
	public function zenbu_add_export_button()
	{
		$output = '';

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$output = $this->EE->extensions->last_call;
		}

		// Leave if you're not in the CP. It can happen.
		if($this->cp_call === FALSE)
		{
			return '';
		}
		
		$settings = $this->get_settings();

		//zenbu_after_save_search
		$url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$url = str_replace('module=zenbu', 'module=hokoku', $url);
		$has_profiles = count($settings['profiles']) > 0 ? 'id="openprofiles"' : '';

		$output .= '<button id="hokoku-export-button" class="submit" ' . $has_profiles . ' style="margin-left: 5px;"><span>' .$this->EE->lang->line('export_this_search') . '</span></button>
				<div class="clear"></div>';

		$output .= '<div id="hokoku-modal" style="display: none">
					<div id="exportoptions" data-title="'.lang('export_profiles_select').'">';

		$output .= '<ul>';

		if( ! empty($settings['profiles']) && isset($settings['profiles']['by_group'][$this->group_id]) )
		{
			
			//	----------------------------------------
			//	Indiviual profiles
			//	----------------------------------------

			$c = 0;
			
			foreach($settings['profiles']['by_group'][$this->group_id] as $export_profile)
			{
				if( ! empty($settings['access_settings']) && 
					isset($settings['access_settings'][$this->group_id]['can_admin_own_profiles']) && 
					$settings['access_settings'][$this->group_id]['can_admin_own_profiles'] == 'y' )
				{
					if($c == 0)
					{
						$output .= '<li class="clear header"><h3><i class="icon-user"></i> '.lang('your_profiles').'</h3></li>';
					}

					$c++;

					if(isset($export_profile['profile_type']) && $export_profile['profile_type'] == 'single')
					{
						$output .= '<li class="clear exportprofile">
							<span class="exportingmessage invisible">' . lang('exporting') . NBS . '[HOKOKU_EXPORT_PROGRESS]' . lang('percent_complete') . NBS . '<i class="icon-spinner icon-spin icon-large"></i></span>
							<button class="export" name="profile_id" value="' . $export_profile['id'] . '" data-label="'.$export_profile['label'].'">' . $export_profile['label'] . '</button>
						</li>';
					}
				}
			}

			//	----------------------------------------
			//	Group profiles
			//	----------------------------------------

			$c = 0;

			foreach($settings['profiles']['by_group'][$this->group_id] as $export_profile)
			{
				if( ! empty($settings['access_settings']) && 
					isset($settings['access_settings'][$this->group_id]['can_view_group_profiles']) && 
					$settings['access_settings'][$this->group_id]['can_view_group_profiles'] == 'y' )
				{
					if(isset($export_profile['profile_type']) && $export_profile['profile_type'] == 'group')
					{
						if($c == 0)
						{
							$output .= '<li class="clear header"><br /><h3><i class="icon-group"></i> '.lang('group_profiles').'</h3></li>';
						}

						$c++;

						$output .= '<li class="clear">
							<span class="exportingmessage invisible">' . lang('exporting') . '</span>
							<button class="export" name="profile_id" value="' . $export_profile['id'] . '">' . $export_profile['label'] . '</button>
						</li>';
					}
				}
			}

		}

		$output .= '<li class="hokoku_controls invisible">
					<div class="progressbar"><div></div></div>
					<button class="exportcancel" data-label="'.$this->EE->lang->line('cancelling').'">'.$this->EE->lang->line('cancel').'</button></li>';

		//	----------------------------------------
		//	Give access to Profile Manager
		//	----------------------------------------
		if( (isset($settings['access_settings'][$this->group_id]['can_admin_own_profiles']) && 
			isset($settings['access_settings'][$this->group_id]['can_admin_own_profiles']) ) && 
			($settings['access_settings'][$this->group_id]['can_admin_own_profiles'] == 'y' 
			|| $settings['access_settings'][$this->group_id]['can_admin_group_profiles'] == 'y') )
		{
			$output .= '<li class="manage-profiles"><a class="" href="'. $this->_base_url . '&method=manage_profiles">&raquo; ' . $this->EE->lang->line('manage_profiles') . '</a></li>';
		}
		$output .= '</ul>';
		$output .= '</div></div>';
	
		$this->EE->load->library('javascript');
		$this->EE->cp->load_package_js('hokoku_export_button');
		$this->EE->cp->load_package_css('hokoku_ext');
		
		if($this->EE->input->get('export'))
		{
			$output .= '
				<script>
					$(document).ready(function () {
						$.ee_notice("' . $this->EE->input->get('export') . '", {"type" : "' . $this->EE->input->get('export') . '"});
					});
				</script>';
		}

		return $output;
	}

	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
	public function settings()
	{
		return array(
			
		);
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$data[] = array(
		        'class'      => __CLASS__,
		        'method'    => "zenbu_add_export_button",
		        'hook'      => "zenbu_after_save_search",
		        'settings'    => serialize($this->settings),
		        'priority'    => 100,
		        'version'    => $this->version,
		        'enabled'    => "y"
		     );
	
		// insert in database
		foreach($data as $key => $data) 
		{
			$this->EE->db->insert('exp_extensions', $data);
		}

		// No hooks selected, add in your own hooks installation code here.
	}	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.hokoku.php */
/* Location: /system/expressionengine/third_party/hokoku/ext.hokoku.php */