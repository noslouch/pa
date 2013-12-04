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
 * Hokoku Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Nicolas Bottari
 * @link		http://nicolasbottari.com
 */

if( ! defined('PATH_THIRD')) { define('PATH_THIRD', APPPATH . 'third_party'); };
require_once PATH_THIRD . 'hokoku/config.php';

class Hokoku_upd {
	
	public $version = HOKOKU_VER;
	public $addon_short_name = 'hokoku';
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{
		$mod_data = array(
			'module_name'			=> 'Hokoku',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);

		$this->EE->load->dbforge();

		/**
		* ============================
		* exp_hokoku_cache table
		* ============================
		*/
		
		$fields = array(
			'id'					=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'profile_id' 			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'export_start_date'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'group_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'site_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'total_results'			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'total_exported'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'progress'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'hash'				=> array('type' => 'varchar', 'constraint' => '64', 'auto_increment' => FALSE),
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		
		$this->EE->dbforge->create_table($this->addon_short_name . '_cache');

		/**
		* ============================
		* exp_hokoku_access_settings table
		* ============================
		*/
		
		$fields = array(
			'group_id'						=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'site_id'						=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'can_admin_own_profiles'		=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			'can_view_group_profiles'		=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			'can_admin_group_profiles'		=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			'can_access_access_settings'	=> array('type'	=> 'varchar', 'constraint'	=> '1'),
		);

		$this->EE->dbforge->add_field($fields);
		
		$this->EE->dbforge->create_table($this->addon_short_name . '_access_settings');

		/**
		* ============================
		* exp_hokoku_profiles table
		* ============================
		*/
		
		$fields = array(
			'profile_id' 			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'group_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'site_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'profile_label'			=> array('type'	=> 'varchar', 'constraint'	=> '250'),
			'export_format'			=> array('type'	=> 'varchar', 'constraint'	=> '10'),
			'export_filename'		=> array('type'	=> 'varchar', 'constraint'	=> '250'),
			//'export_dir'			=> array('type'	=> 'int', 'constraint'	=> '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'export_settings'		=> array('type'	=> 'text'),
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('profile_id', TRUE);
		
		$this->EE->dbforge->create_table($this->addon_short_name . '_profiles');

		/**
		*	=============================
		*	Add some basic data
		*	=============================
		*/

		$query = $this->EE->db->query('SELECT group_id, site_id FROM exp_member_groups ORDER BY site_id ASC, group_id ASC');

		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$yesno = $row['group_id'] == 1 ? 'y' : 'n';

				$db_data = array(
					'group_id'						=> $row['group_id'],	// Super Admin with Default settings (everything turned on)
					'site_id'						=> $row['site_id'],
					'can_admin_own_profiles'		=> $yesno,				// Can administrate own profiles
					'can_view_group_profiles'		=> $yesno,				// Can view member group profiles
					'can_admin_group_profiles'		=> $yesno,				// Can administrate member group profiles
					'can_access_access_settings'	=> $yesno,				// Controls access to access settings
				);

				$sql = $this->EE->db->insert_string($this->addon_short_name . '_access_settings', $db_data);
				
				$this->EE->db->query($sql);
			}
		}
		
		return TRUE;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		$this->EE->load->dbforge();
		
		$mod_id = $this->EE->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Hokoku'
								))->row('module_id');
		
		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->EE->db->where('module_name', 'Hokoku')
					 ->delete('modules');

		$this->EE->dbforge->drop_table($this->addon_short_name . '_access_settings');
		$this->EE->dbforge->drop_table($this->addon_short_name . '_profiles');
		$this->EE->dbforge->drop_table($this->addon_short_name . '_cache');
		
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		if(version_compare($this->version, '1.0.1.1', '<='))
		{
			$this->EE->load->dbforge();

			/**
			* ============================
			* exp_hokoku_cache table
			* ============================
			*/
			
			$fields = array(
				'id'					=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'profile_id' 			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'export_start_date'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'group_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'site_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'total_results'			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'total_exported'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'progress'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'hash'					=> array('type' => 'varchar', 'constraint' => '64', 'auto_increment' => FALSE),
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('id', TRUE);
			
			$this->EE->dbforge->create_table($this->addon_short_name . '_cache');
		}

		return TRUE;
	}
	
}
/* End of file upd.hokoku.php */
/* Location: /system/expressionengine/third_party/hokoku/upd.hokoku.php */