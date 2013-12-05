<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/export_it/
 */
 
 /**
 * Export It - Extension Class
 *
 * Extension class
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/ext.export_it.php
 */
class Export_it_ext 
{

	public $settings = array();
	
	public $name = 'Export It';
	
	public $version = '1.3.1';
	
	public $description	= 'Extension for modifying how exporting works';
	
	public $settings_exist	= 'y';
	
	public $docs_url = ''; 
	
	public $required_by = array('module');	
		
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('email');
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->description = $config['description'];
		$this->docs_url = $config['docs_url'];
		$this->class = $this->name = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->version = $config['version'];
		$this->mod_name = $config['mod_url_name'];
		$this->ext_class_name = $config['ext_class_name'];

		$this->EE->lang->loadfile('export_it');
		
		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
		$this->url_base = BASE.AMP.$this->query_base;
		//$this->EE->export_it_lib->set_url_base($this->url_base);		
	}
	
	public function settings_form()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=export_it'.AMP.'method=settings');
	}
	
	public function cp_menu_array($menu)
	{
		$menu = ($this->EE->extensions->last_call != '' ? $this->EE->extensions->last_call : $menu);

		//the members export menu
		if($this->EE->session->userdata('can_access_members') == 'y')
		{
			$menu['members']['export_members'] = $this->url_base.'members';
		}
		
		//setup channel entries menu
		if($this->EE->session->userdata('can_access_content') == 'y')
		{
			$channels = array();
			$this->EE->load->model('channel_model');
			$channels = $this->EE->channel_model->get_channels();
			if(!$channels)
			{
				return $menu;
			}
			
			$channels = $channels->result_array();
			$export_menu = array();
			$new_menu = array();
			foreach($channels AS $key => $value)
			{
				$this->EE->lang->language['nav_'.$value['channel_title']] = $value['channel_title'];			
				$export_menu[$value['channel_title']] = $this->url_base.'channel_entries'.AMP.'channel_id='.$value['channel_id'];
			}
			
			$new_menu['publish'] = $menu['content']['publish']; unset($menu['content']['publish']);
			$new_menu['edit'] = $menu['content']['edit']; unset($menu['content']['edit']);
			$new_menu['files'] = $menu['content']['files']; unset($menu['content']['files']);
			$new_menu['export_entries'] = $export_menu;
			$new_menu['0'] = $menu['content']['0']; unset($menu['content']['0']);
			$new_menu['overview'] = $menu['content']['overview']; unset($menu['content']['overview']);
			$menu['content'] = $new_menu;
		}
		
		//setup the Tools menu
		if($this->EE->session->userdata('can_access_tools') == 'y')
		{
			$new_menu = array();
			if(!empty($menu['tools']['tools_communicate']))
			{
				$new_menu['tools_communicate'] = $menu['tools']['tools_communicate']; 
				unset($menu['tools']['tools_communicate']);
			}
			
			if(!empty($menu['tools']['0']))
			{
				$new_menu['0'] = $menu['tools']['0']; 
				unset($menu['tools']['0']);
			}
			
			if(!empty($menu['tools']['tools_utilities']))
			{
				$new_menu['tools_utilities'] = $menu['tools']['tools_utilities']; 
				unset($menu['tools']['tools_utilities']);
			}
			
			if(!empty($menu['tools']['tools_data']))
			{
				$new_menu['tools_data'] = $menu['tools']['tools_data']; 
				unset($menu['tools']['tools_data']);
			}
			
			if($this->EE->session->userdata('can_access_modules') == 'y')
			{
				$new_menu['export'] = array(
						'members' => $this->url_base.'members', 
						'channel_entries' => $this->url_base.'channel_entries', 
						'comments' => $this->url_base.'comments'
				);
				
				$this->EE->load->library('Export_it_lib');
				if($this->EE->export_it_lib->is_installed_module('Mailinglist'))
				{
					$new_menu['export']['mailing_list'] = $this->url_base.'mailing_list';
				}
		
				$new_menu['export']['0'] = '----';
				$new_menu['export']['settings'] = $this->url_base.'settings';
			}
			
			if(!empty($menu['tools']['tools_data']))
			{
				$new_menu['tools_logs'] = $menu['tools']['tools_logs']; 
				unset($menu['tools']['tools_logs']);
			}
			
			$new_menu = array_merge($new_menu, $menu['tools']);
			
			if(!empty($menu['tools']['1']))
			{
				$new_menu['1'] = $menu['tools']['1']; 
				unset($menu['tools']['1']);
			}
			
			if(!empty($menu['tools']['overview']))
			{
				$new_menu['overview'] = $menu['tools']['overview']; 
				unset($menu['tools']['overview']);
			}
			
			$menu['tools'] = $new_menu;
		
		}
		
		return $menu;
	}
	
	public function activate_extension() 
	{
		return TRUE;

	}
	
	public function update_extension($current = '')
	{
		return TRUE;
	}

	public function disable_extension()
	{
		return TRUE;

	}
}