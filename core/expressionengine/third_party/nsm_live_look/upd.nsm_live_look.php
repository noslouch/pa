<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Install / Uninstall / Update Module
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2013 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */
class Nsm_live_look_upd
{
	/**
	 * The module version
	 *
	 * @var string
	 */
	public $version = '1.2.4';

	/**
	 * Does this module have a control panel?
	 *
	 * @var boolean
	 */
	private $has_cp_backend = FALSE;

	/**
	 * Does this module have publish fields?
	 *
	 * @var boolean
	 */
	private $has_publish_fields = TRUE;

	/**
	 * Does this module have tabs?
	 *
	 * @var boolean
	 */
	private $has_tabs = TRUE;


	/**
	 * Constructor
	 *
	 * @access public
	 * @author Leevi Graham
	 */
	public function __construct() 
	{
		$this->addon_id = strtolower(substr(__CLASS__, 0, -4));
	}


	/**
	 * The tabs for the module
	 *
	 * @access private
	 * @author Leevi Graham
	 * @return Array The tabs array
	 **/
	private function tabs()
	{
		return array
		(
			$this->addon_id => array
			(
				"preview" => array(
					'visible'		=> 'true',
					'collapse'		=> 'false',
					'htmlbuttons'	=> 'false',
					'width'			=> '100%'
				)
			)
		);
	}

	/**
	 * Installs the module
	 * 
	 * Installs the module, adding a record to the exp_modules table, creates and populates and necessary database tables, adds any necessary records to the exp_actions table, and if custom tabs are to be used, adds those fields to any saved publish layouts
	 *
	 * @access public
	 * @author Leevi Graham
	 * @return boolean
	 **/
	public function install()
	{
		$EE =& get_instance();
		$data = array(
			'module_name' => ucfirst($this->addon_id),
			'module_version' => $this->version,
			'has_cp_backend' => ($this->has_cp_backend) ? "y" : "n",
			'has_publish_fields' => ($this->has_publish_fields) ? "y" : "n"
		);

		$EE->db->insert('modules', $data);

		if(isset($this->actions) && is_array($this->actions))
		{
			foreach ($this->actions as $action)
			{
				$parts = explode("::", $action);
				$EE->db->insert('actions', array(
					"class" => $parts[0],
					"method" => $parts[1]
				));
			}
		}

		if($this->has_publish_fields) {
			$EE->load->library('layout');
			$EE->layout->add_layout_tabs($this->tabs(), strtolower($data['module_name']));
		}
		return TRUE;
	}

	/**
	 * Updates the module
	 * 
	 * This function is checked on any visit to the module's control panel, and compares the current version number in the file to the recorded version in the database. This allows you to easily make database or other changes as new versions of the module come out.
	 *
	 * @access public
	 * @author Leevi Graham
	 * @return Boolean FALSE if no update is necessary, TRUE if it is.
	 **/
	public function update($current = FALSE)
	{
		$EE =& get_instance();
		if($current < "1.0.1")
		{
			foreach($query = $EE->db->get('layout_publish')->result_array() as $layout)
			{
				$field_layout = unserialize($layout['field_layout']);
				foreach ($field_layout as $tab => $value)
				{
					if($tab == "Live Look" || $tab == "nsm_live_look")
						unset($field_layout[$tab]);
				}
				$data = array('field_layout' => serialize($field_layout));
				$EE->db->where('layout_id', $layout['layout_id']);
				$EE->db->update('layout_publish', $data);
			}

			if($this->has_publish_fields) {
				$EE->load->library('layout');
				$EE->layout->add_layout_tabs(self::tabs(), $this->addon_id);
			}
		}
		
		if($current < "1.2.0")
		{
			if ( ! function_exists('json_decode'))
				$EE->load->library('Services_json');

			$query = $EE->db->query("SELECT * FROM `exp_nsm_addon_settings` WHERE `addon_id` = '{$this->addon_id}'");
			foreach($query->result_array() as $site)
			{
				// decode the settings
				$site["settings"] = json_decode($site["settings"], true);

				// Update sitemap include to use y/n
				foreach ($site["settings"]["channels"] as $channel => $urls) {
					foreach ($site["settings"]["channels"][$channel]["urls"] as &$url) {
						$url["height"] = (isset($url["height"]) ? $url["height"] : false);
					}
				}

				// encode the json and save back to DB
				$site["settings"] = $EE->javascript->generate_json($site["settings"], true);

				$query = $EE->db->update(
							'exp_nsm_addon_settings',
							array('settings' => $site["settings"]),
							array(
								'addon_id' => $this->addon_id,
								'site_id' => $site['site_id']
							));
			}
		}

		// Update the extension
		$EE->db
			->where('module_name', ucfirst($this->addon_id))
			->update('modules', array('module_version' => $this->version));

		return false;
	}

	/**
	 * Uninstalls the module
	 *
	 * @access public
	 * @author Leevi Graham
	 * @return Boolean FALSE if uninstall failed, TRUE if it was successful
	 **/
	public function uninstall()
	{

		$EE =& get_instance();
		$module_name = substr(__CLASS__, 0, -4);

		$EE->db->select('module_id');
		$query = $EE->db->get_where('modules', array('module_name' => $module_name));

		$EE->db->where('module_id', $query->row('module_id'));
		$EE->db->delete('module_member_groups');

		$EE->db->where('module_name', $module_name);
		$EE->db->delete('modules');

		$EE->db->where('class', $module_name);
		$EE->db->delete('actions');

		$EE->db->where('class', $module_name . "_mcp");
		$EE->db->delete('actions');
		
		if($this->has_publish_fields) {
			$EE->load->library('layout');
			$EE->layout->delete_layout_tabs($this->tabs(), $module_name);
		}

		return TRUE;
	}
}
