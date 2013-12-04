<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if( ! defined('PATH_THIRD')) { define('PATH_THIRD', APPPATH . 'third_party'); };
require_once PATH_THIRD . 'zenbu/config.php';

class Zenbu_ext {
	
	var $name				= 'Zenbu';
	var $addon_short_name 	= 'zenbu';
	var $version 			= ZENBU_VER;
	var $description		= 'Extension companion to module of the same name';
	var $settings_exist		= 'y';
	var $docs_url			= 'http://nicolasbottari.com/expressionengine_cms/zenbu';
	var $settings        	= array();

	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function Zenbu_ext($settings='')
	{
		$this->EE				=& get_instance();
		$this->settings			= $settings;

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

		$this->member_group_id	= $this->EE->session->userdata['group_id'];
		$this->site_id			= $this->EE->session->userdata['site_id'];
	}
	
	/**
	 * send_to_addon_post_delete
	 * Hook: delete_entries_end
	 * @return void Redirection
	 */
	function send_to_addon_post_delete()
	{
		// return_to_zenbu attempts to fetch the latest rules saved in session if present
		// First, check if we're in the CP and that we're accessing the delete_entries method.
		if($_GET['D'] == 'cp' && $_GET['C'] == 'content_edit' && $_GET['M'] == 'delete_entries')
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=zenbu'.AMP."return_to_zenbu=y");
		}
	}
	
	/**
	 * send_to_addon_post_edit
	 * Hook: update_multi_entries_start
	 * @return void 	Set redirection POST variable
	 */
	function send_to_addon_post_edit()
	{
		// Taking over redirection
		// return_to_zenbu attempts to fetch the latest rules saved in session if present
		// First, check if we're in the CP and that we're accessing the update_multi_entries routine.
		if($_GET['D'] == 'cp' && $_GET['C'] == 'content_edit' && $_GET['M'] == 'update_multi_entries')
		{
			unset($_POST['redirect']);
			$_POST['redirect'] = base64_encode(BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=zenbu".AMP."return_to_zenbu=y");
		}
	}
	
	/**
	 * replace_edit_dropdown
	 * Hook: cp_js_end
	 * @return string $output The added JS.
	 */
	function replace_edit_dropdown()
	{
		$this->EE->lang->loadfile('zenbu');
		$this->EE->lang->loadfile('content', 'cp');
		$this->EE->load->library('javascript');
		
		//$system_url = $this->EE->config->item("site_url").str_replace("&amp;", "&", BASE);
		
		if(isset($_SERVER['HTTP_REFERER']))
		{
			parse_str(parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $get);
		} else {
			$get = array();
		}
		
		// Sorry I forgot to add this, devs:
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$output = $this->EE->extensions->last_call;
		} else {
			$output = '';
		}
		
		// Replaces the main CP nav with the addon
		if($this->EE->session->cache('zenbu', 'edit_replace') == 'y')
		{
			$vars['edit_replace'] = 'y';
			
			$output .= $this->EE->load->view('cp_js_end.js', $vars, TRUE);

		} else {

			$query = $this->EE->db->query("SELECT edit_replace FROM exp_zenbu WHERE member_group_id = ".$this->EE->db->escape_str($this->member_group_id)." AND site_id = ".$this->EE->db->escape_str($this->site_id) . " AND edit_replace = 'y'");

			if($query->num_rows() > 0)
			{

				$this->EE->session->set_cache('zenbu', 'edit_replace', 'y');

				$vars['edit_replace'] = 'y';
				
				$output .= $this->EE->load->view('cp_js_end.js', $vars, TRUE);
					
			} else {
				$output .= $this->EE->load->view('cp_js_end.js', array(), TRUE);;
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
		$output = array();
				
		$this->EE->db->from(array("exp_modules", "exp_extensions"));
		$query = $this->EE->db->get();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$installed['modules'][$row['module_id']] = $row['module_name'];	// Modules
				$installed['extensions'][$row['extension_id']] = $row['class']; // Extensions
			}
		}
		
		return $installed;
	}
	
	/**
	 * Settings Form
	 *
	 * @param	Array	Settings
	 * @return 	void
	 */
	function settings_form()
	{
		$this->EE->load->helper('form');
		$this->EE->load->library('table');
		
		$query = $this->EE->db->query("SELECT settings FROM exp_extensions WHERE class = '".__CLASS__."'");
		$license = '';
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $result)
			{
				$settings = unserialize($result['settings']);
				if(!empty($settings))
				{
					$license = $settings['license'];
				}
			}
		}
		
		$vars = array();
				
		$vars['settings'] = array(
			'license'	=> form_input('license', $license, "size='80'"),
			);
		
		
		return $this->EE->load->view('ext_settings', $vars, TRUE);			
	}
	
	/**
	* Save Settings
	*
	* This function provides a little extra processing and validation 
	* than the generic settings form.
	*
	* @return void
	*/
	function save_settings()
	{
		if (empty($_POST))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}
		
		unset($_POST['submit']);
		
		$settings = $_POST;
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($settings)));
		
		$this->EE->session->set_flashdata(
			'message_success',
		 	$this->EE->lang->line('preferences_updated')
		);
	}



	function activate_extension() {
	
	      $data[] = array(
		        'class'      => __CLASS__,
		        'method'    => "send_to_addon_post_edit",
		        'hook'      => "update_multi_entries_start",
		        'settings'    => serialize($this->settings),
		        'priority'    => 10,
		        'version'    => $this->version,
		        'enabled'    => "y"
		      );
		      
		  $data[] = array(
		        'class'      => __CLASS__,
		        'method'    => "send_to_addon_post_delete",
		        'hook'      => "delete_entries_end",
		        'settings'    => serialize($this->settings),
		        'priority'    => 10,
		        'version'    => $this->version,
		        'enabled'    => "y"
		      );
		      
		  $data[] = array(
		        'class'      => __CLASS__,
		        'method'    => "replace_edit_dropdown",
		        'hook'      => "cp_js_end",
		        'settings'    => serialize($this->settings),
		        'priority'    => 100,
		        'version'    => $this->version,
		        'enabled'    => "y"
		     );
	
	      // insert in database
	      foreach($data as $key => $data) {
	      $this->EE->db->insert('exp_extensions', $data);
	      }
	  }
	
	
	  function disable_extension() {
	
	      $this->EE->db->where('class', __CLASS__);
	      $this->EE->db->delete('exp_extensions');
	  } 
	  
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
		
		if ($current < $this->version)
		{
			// Update to version 1.0
		}
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update(
					'extensions', 
					array('version' => $this->version)
		);
	}

  
  

}
// END CLASS