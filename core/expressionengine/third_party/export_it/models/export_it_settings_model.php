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
 * Export It - Settings Model
 *
 * Settings Model
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/models/export_it_settings_model.php
 */
class Export_it_settings_model extends CI_Model
{
	/**
	 * The table we're modifying
	 * @param string $_table
	 */	
	private $_table = 'export_it_settings';
	
	/**
	 * The default configuration to use
	 * @param array $_defaults
	 */		
	public $_defaults = array(
						'license_number' => '',
						'enable_api' => '0',
						'api_key' => '',
						'members_list_limit' => '20',
						'mailing_list_limit' => '20',
						'comments_list_limit' => '20',
						'channel_entries_list_limit' => '20',
						'disable_accordions' => FALSE,
						'export_it_date_format' => '%M %d, %Y, %h:%i:%s%A'
	);
	
	/**
	 * The setting keys that should be serialized for storage
	 * @param array $_serialized
	 */		
	private $_serialized = array(
						'replace_me'
	);
	
	private $_val_numeric = array(
						'members_list_limit',
						'mailing_list_limit',
						'comments_list_limit',
						'channel_entries_list_limit'
	);
	
	/**
	 * The setting keys that should be encrypted for storage
	 * @param array $_serialized
	 */		
	private $_encrypted = array(
						'api_key'
	);	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Adds a setting to the databse
	 * @param string $setting
	 */
	public function add_setting($setting)
	{
		$data = array(
		   'setting_key' => $setting,
		   'setting_value' => ''
		);
		
		return $this->db->insert($this->_table, $data); 
	}	
	
	public function get_settings()
	{	
		$this->db->select('setting_key, setting_value, serialized')
				 ->from($this->_table);
		$query = $this->db->get();	
		$_settings = $query->result_array();
		$settings = array();	
		foreach($_settings AS $setting)
		{
			$settings[$setting['setting_key']] = ($setting['serialized'] == '1' ? unserialize($setting['setting_value']) : $setting['setting_value']);
		}
		
		//now check to make sure they're all there and set default values if not
		foreach ($this->_defaults as $key => $value)
		{	
			//setup the override check
			if(isset($this->config->config['export_it'][$key]))
			{
				$settings[$key] = $this->config->config['export_it'][$key];
			}
						
			if(!isset($settings[$key]))
			{
				$settings[$key] = $value;
			}
		}		

		return $settings;
	}
	
	/**
	 * Returns the value straigt from the database
	 * @param string $setting
	 */
	public function get_setting($setting)
	{
		$data = $this->db->get_where($this->_table, array('setting_key' => $setting))->result_array();
		if(isset($data['0']))
		{
			$data = $data['0'];
			if($data['serialized'] == '1')
			{
				$data['setting_value'] = unserialize($data['setting_value']);
				if(!$data['setting_value'])
				{
					$data['setting_value'] = array();
				}
			}
			return $data['setting_value'];
		}
	}	
	
	public function update_settings(array $data)
	{		

		foreach($data AS $key => $value)
		{
			
			if(in_array($key, $this->_val_numeric))
			{
				if(!is_numeric($value) || $value <= '0')	
				{
					$value = $this->_defaults[$key];
				}
			}
			
			if(in_array($key, $this->_serialized))
			{
				$value = explode("\n", $value);
			}
			
			if(in_array($key, $this->_encrypted) && $value != '')
			{
				$value = $this->encrypt->encode($value);
			}
			
			$this->update_setting($key, $value);
		}
		
		return TRUE;
	}
	
	/**
	 * Updates the value of a setting
	 * @param string $key
	 * @param string $value
	 */
	public function update_setting($key, $value)
	{
		if(!$this->_check_setting($key))
		{
			return FALSE;
		}

		$data = array();
		if(is_array($value))
		{
			$value = serialize($value);
			$data['serialized '] = '1';
		}
		
		$data['setting_value'] = $value;
		$this->db->where('setting_key', $key);
		return $this->db->update($this->_table, $data);
	}

	/**
	 * Verifies that a submitted setting is valid and exists. If it's valid but doesn't exist it is created.
	 * @param string $setting
	 */
	private function _check_setting($setting)
	{
		if(array_key_exists($setting, $this->_defaults))
		{
			$value = $this->get_setting($setting);
			if(!$value && $value !== '0' && !is_array($value))
			{
				$this->add_setting($setting);
			}
			
			return TRUE;
		}		
	}
}