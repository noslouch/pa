<?php

/*
=====================================================

RogEE "Show Me My Assets!"
an extension for ExpressionEngine 2
by Michael Rog
version 1.1.1

Contact Michael with questions, feedback, suggestions, bugs, etc.
>> http://rog.ee/show_me_my_assets
>> http://devot-ee.com/add-ons/show-me-my-assets

This extension is compatible with NSM Addon Updater:
>> http://ee-garage.com/nsm-addon-updater

Changelog:
>> http://rog.ee/versions/show_me_my_assets

=====================================================
*/


if (!defined('APP_VER') || !defined('BASEPATH')) { exit('No direct script access allowed'); }

// -----------------------------------------
//	Here goes nothin...
// -----------------------------------------

if (! defined('ROGEE_SMMA_VERSION'))
{
	// get the version from config.php
	require PATH_THIRD.'show_me_my_assets/config.php';
	define('ROGEE_SMMA_VERSION', $config['version']);
}

/**
 * Show Me My Assets class, for ExpressionEngine 2
 *
 * @package RogEE Show Me My Assets
 * @author Michael Rog <michael@michaelrog.com>
 * @copyright 2012 Michael Rog
 * @see http://rog.ee/show_me_my_assets
 */
class Show_me_my_assets_ext
{

	var $settings = array();
    	
	var $name = "RogEE Show Me My Assets" ;
	var $version = ROGEE_SMMA_VERSION ;
	var $description = "Redirects the File Manager CP link to the Assets file browser" ;
	var $settings_exist = "y" ;
	var $docs_url = "http://rog.ee/show_me_my_assets" ;
	
	
	/**
	* ==============================================
	* Constructor
	* ==============================================
	*
	* @param mixed: Settings array or empty string if none exist.
	*/
	function Show_me_my_assets_ext($settings='')
	{
	
		$default_settings = array(
			'replace_file_browser' => 'y',
			'expand_subfolders' => 'y'
		);
		
		$this->settings = is_array($settings) ? $settings : $default_settings;
	
		// ---------------------------------------------
		//	Get a local EE object reference
		// ---------------------------------------------
		
		$this->EE =& get_instance();
				
		// ---------------------------------------------
		//	Localize extension info
		// ---------------------------------------------
		
		$this->EE->lang->loadfile('show_me_my_assets');
		$this->name = $this->EE->lang->line('show_me_my_assets_extension_name');
		$this->description = $this->EE->lang->line('show_me_my_assets_extension_description');
	
	} // END Constructor


	/**
	* ==============================================
	* Settings
	* ==============================================
	*/
	function settings()
	{
	
		$settings_setup = array();
		
		// ---------------------------------------------
		//	Want to replace EE's stupid File Manager with the Assets module?
		// ---------------------------------------------
	
		$settings_setup['replace_file_browser'] = array('r', array('y' => "Yup!", 'n' => "Nope."), 'y');
		
		// ---------------------------------------------
		//	Want to add some jQuery gobbledigook to try to expand subfolders in the File Browser?
		// ---------------------------------------------
	
		$settings_setup['expand_subfolders'] = array('r', array('y' => "Yup!", 'n' => "Nope."), 'y');
		
		return $settings_setup;
	
	} // END settings()


	/**
	* ==============================================
	* Activate Extension
	* ==============================================
	*
	* Registers the extension into the exp_extensions table
	*
	* @see http://expressionengine.com/user_guide/development/extensions.html#enable
	*
	* @return void
	*
	*/
	function activate_extension()
	{

		// ---------------------------------------------
		//	Register the hooks
		// ---------------------------------------------
		
		$hook = array(
			'class'		=> __CLASS__,
			'method'	=> 'i_dont_want_no_ee_file_browser',
			'hook'		=> 'cp_menu_array',
			'settings'	=> serialize($this->settings),
			'priority'	=> 1,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		$this->EE->db->insert('extensions', $hook);

		$hook = array(
			'class'		=> __CLASS__,
			'method'	=> 'expand_my_subfolders',
			'hook'		=> 'cp_js_end',
			'settings'	=> serialize($this->settings),
			'priority'	=> 9,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		$this->EE->db->insert('extensions', $hook);
		
	} // END activate_extension()



	/**
	* ==============================================
	* Update Extension
	* ==============================================
	*
	* Performs any necessary database updates; runs each time the extension page is visited.
	* 
	* @see http://expressionengine.com/user_guide/development/extensions.html#enable
	*
	* @param string: current version
	* @return mixed: void on update / FALSE if none
	*
	*/
	function update_extension($current = '')
	{
	
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		elseif (version_compare($current, $this->version, '<'))
		{
	
			// ---------------------------------------------
			//	Un-register the hooks
			// ---------------------------------------------
			
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->delete('extensions');
			
			// ---------------------------------------------
			//	Re-register the hooks by running the Activate Extension function
			// ---------------------------------------------
			
			$this->activate_extension();
		
		}
	
	} // END update_extension()



	/**
	* ==============================================
	* Disable Extension
	* ==============================================
	*
	* Disables extension by removing its references from the exp_extensions table.
	*
	* @see http://expressionengine.com/user_guide/development/extensions.html#disable
	*
	* @return void
	*
	*/
	function disable_extension()
	{
		
		// ---------------------------------------------
		//	Un-register the hooks
		// ---------------------------------------------
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
		
	} // END disable_extension()



	/**
	* ==============================================
	* I Dont Want No EE File Browser!
	* ==============================================
	*
	* The magic happens here.
	* Confirms that Assets is installed and that the user has permissions to see it.
	* If so, replaces the File Manager CP link with a link to Assets.
	*
	* Fires on the "cp_menu_array" hook.
	* @see http://blog.adamfairholm.com/expressionengine-cp-menu-manipulation/
	*
	* @param Array: menu items, from EE or from the previously-called extension
	* @return Array: modified menu items
	*
	*/
	function i_dont_want_no_ee_file_browser($menu)
	{
	
		// ---------------------------------------------
		//	Make sure we play nice with the other kids.
		// ---------------------------------------------

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$menu = $this->EE->extensions->last_call;
		}

		// ---------------------------------------------
		//	Bail now if the admin has this component turned off.
		// ---------------------------------------------
		
		if (isset($this->settings['replace_file_browser']) && $this->settings['replace_file_browser'] == 'n')
		{
			return $menu;
		}
		
		// ---------------------------------------------
		//	We won't bother making changes unless we can find Assets in the exp_modules table.
		//	(We need the module_id anyway.)
		// ---------------------------------------------
		
		$this->EE->db->select('module_id')->from('modules')->where('module_name', "Assets")->limit(1);
		$query = $this->EE->db->get();
		
		if ($query->num_rows() > 0)
		{
			
			// ---------------------------------------------
			//	Does this user have access to Assets?
			//	Like Adam says... don't want to tease them.
			// ---------------------------------------------

			$assets_id = $query->row('module_id');
		
			$assigned = $this->EE->session->userdata('assigned_modules');

			if
			(
				$this->EE->cp->allowed_group('can_access_modules') and
				(
					$this->EE->session->userdata('group_id') == 1 or
					(isset($assigned[$assets_id]) and $assigned[$assets_id] == 'yes')
				)
			)
			{
				
				// ---------------------------------------------
				//	Everything checks out. Somebody's gonna get some Pixely, Tonicy Goodness.
				// ---------------------------------------------
				
				$assets_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=assets';
				$menu['content']['files']["file_manager"] = $assets_url;
				
			}
			
		}
		
		// ---------------------------------------------
		//	Returning the modified menu items Array to EE
		// ---------------------------------------------
		
		return $menu;
						
	} // END i_dont_want_no_ee_file_browser()


	/**
	* ==============================================
	* Expand My Subfolders!
	* ==============================================
	*
	* Here's an extra little nicety: A bit of jQuery to virtually click (expand) the first-level subfolders.
	*
	* Fires on the "cp_js_end" hook.
	*
	* @param String: JS code from EE
	* @return String: JS code with my [super-ghetto] jQuery pixie dust appended
	*
	*/
	function expand_my_subfolders($js)
	{
		
		// ---------------------------------------------
		//	Make sure we play nice with the other kids.
		// ---------------------------------------------

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$js = $this->EE->extensions->last_call;
		}
		
		// ---------------------------------------------
		//	Bail now if the admin has this component turned off.
		// ---------------------------------------------
		
		if (isset($this->settings['expand_subfolders']) && $this->settings['expand_subfolders'] == 'n')
		{
			return $js;
		}
		
		// ---------------------------------------------
		//	Sprinkle on some [super-ghetto] pixie dust...
		// ---------------------------------------------
			
		$super_ghetto_pixie_dust = <<<DOIT
		;
		$(function(){
		
			assets_fm_view = function(){
				$('.assets-fm-folder a').not('.assets-fm-expanded').find('.assets-fm-toggle').click();
			}
			
			do_it_onload = setTimeout('assets_fm_view()', 500);
		
			$('.assets-add').live('click',function(){
				do_it_onclick = setTimeout('assets_fm_view()', 1000);
			});
			
		});
DOIT;

		$js .= NL . $super_ghetto_pixie_dust;
		
		// ---------------------------------------------
		//	Returning the modified menu items Array to EE
		// ---------------------------------------------
		
		return $js;
						
	} // END expand_my_subfolders()


} // END CLASS

/* End of file ext.show_me_my_assets.php */
/* Location: ./system/expressionengine/third_party/show_me_my_assets/ext.show_me_my_assets.php */