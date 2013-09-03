<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include (PATH_THIRD.'ig_picpuller/config.php');
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
 * ig_picpuller Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		John Morton
 * @link		http://picpuller.com
 */

class Ig_picpuller_upd {

	public $version = PP_IG_VERSION;

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
			'module_name'			=> 'Ig_picpuller',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);

		$this->EE->db->insert('modules', $mod_data);

		$data = array(
			'class' => "Ig_picpuller",
			'method' => 'authorization'
		);

		$this->EE->db->insert('actions', $data);

		$data = array(
			'class' => "Ig_picpuller",
			'method' => 'deauthorization'
		);

		$this->EE->db->insert('actions', $data);

		$this->EE->load->dbforge();

		$fields = array(
			'app_id'			=> array('type' => 'INT',  'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
			'ig_site_id'		=> array('type' => 'INT', 'length' => '11', 'null' => TRUE),
			'ig_client_id'		=> array('type' => 'varchar', 'constraint' => '64', 'null' => TRUE, 'default' => NULL),
			'ig_client_secret' 	=> array('type' => 'varchar', 'constraint'=> '64', 'null' => TRUE, 'default' => NULL)
			,
			'ig_picpuller_prefix'	=> array('type' => 'varchar', 'constraint'=> '128', 'null' => TRUE, 'default' => NULL),
			'auth_url'			=> array('type' => 'varchar', 'constraint'=> '256', 'null' => TRUE, 'default' => NULL),
			'frontend_auth_url'	=> array('type' => 'varchar', 'constraint'=> '256', 'null' => TRUE, 'default' => NULL)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('app_id', TRUE);
		$this->EE->dbforge->create_table('ig_picpuller_credentials');

		unset($fields);

		$fields = array(
			'member_id' 		=> array('type' => 'varchar', 'constraint' => '64', 'null' => TRUE, 'default' => NULL),
			'instagram_id' 		=> array('type' => 'varchar', 'constraint' => '64', 'null' => TRUE, 'default' => NULL),
			'oauth' 			=> array('type' => 'varchar', 'constraint' => '255', 'null' => TRUE, 'default' => NULL),
			'app_id' 			=> array('type' => 'INT', 'length' => '9', 'auto_increment' => FALSE, 'null' => TRUE)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->create_table('ig_picpuller_oauths');

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
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array(
			'module_name'	=> 'Ig_picpuller'
		));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Ig_picpuller');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Ig_picpuller');
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table('ig_picpuller_credentials');
		$this->EE->dbforge->drop_table('ig_picpuller_oauths');

		// No publish fields in this version to remove
		//$this->EE->load->library('layout');
		//$this->EE->layout->delete_layout_tabs($this->tabs(), 'ig_picpuller');

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

		// What we need to do here is more complex than I wanted it to be
		// Since DBForge doesn't allow you to alter an existing table to
		// add a PRIMARY KEY, we make a new table with a PRIMARY KEY
		// we may need to create an entire new empty table with a PRIMARY KEY
		// with a temporary name, copy the old data to it, then delete the old
		// table, then rename the new table with the same name as the old table.

	$this->EE->load->dbforge();

	$default_prefix = 'ig_';

	// If the column 'ig_site_id' doesn't exist, we update the 'ig_picpuller_credentials'
	// table with that new column and then we insert the currently logged in site as
	// the owner of the current Pic Puller Instagram application

	if (!$this->EE->db->field_exists('ig_site_id', 'ig_picpuller_credentials'))
	{
		$fields = array(
			'app_id'			=> array('type' => 'INT',  'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
			'ig_site_id'		=> array('type' => 'INT', 'length' => '11', 'null' => TRUE),
			'ig_client_id'		=> array('type' => 'varchar', 'constraint' => '64', 'null' => TRUE, 'default' => NULL),
			'ig_client_secret' 	=> array('type' => 'varchar', 'constraint'=> '64', 'null' => TRUE, 'default' => NULL)
			,
			'auth_url'			=> array('type' => 'varchar', 'constraint'=> '256', 'null' => TRUE, 'default' => NULL),
			'frontend_auth_url'	=> array('type' => 'varchar', 'constraint'=> '256', 'null' => TRUE, 'default' => NULL)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('app_id', TRUE);
		$this->EE->dbforge->create_table('ig_picpuller_credentials_TEMP');

		// Now get all the data from the OLD data base
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
			$data = array(
				// ig_site_id is set to the site that the user is currently logged into in the EE CP
			'ig_site_id' => $this->EE->config->config['site_id'],
			'ig_client_id' => $row->ig_client_id,
			'ig_client_secret' => $row->ig_client_secret,
			'auth_url' => $row->auth_url,
			);
			$this->EE->db->insert('ig_picpuller_credentials_TEMP', $data);
		};

		$this->EE->dbforge->rename_table($this->EE->db->dbprefix .'ig_picpuller_credentials', $this->EE->db->dbprefix .'ig_picpuller_credentials_OLD');

		$this->EE->dbforge->rename_table($this->EE->db->dbprefix .'ig_picpuller_credentials_TEMP', $this->EE->db->dbprefix .'ig_picpuller_credentials');

		$this->EE->dbforge->drop_table('ig_picpuller_credentials_OLD');

		// Now update the current exp_ig_picpuller_oauths for the existing app so they can
		// be associated with the correct IG app within Pic Puller

		// First, we will add that column to the exp_ig_picpuller_oauths table in the database
		unset($fields);
		unset($query);
		$fields = array(
				'app_id' => array('type' => 'INT', 'length' => '9', 'auto_increment' => FALSE, 'null' => TRUE)
		);

		$this->EE->dbforge->add_column('ig_picpuller_oauths', $fields);

		// find out what the current id of the potentially existing app is and add this to all existing oAuths
		// in exp_ig_picpuller_oauths table

		$this->EE->db->limit(1);
		$this->EE->db->select('app_id');
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
    		$appID = $row->app_id;
		}

		if (isset($appID)){
			$data = array(
				'app_id' => $appID
			);
			$this->EE->db->where('app_id', NULL);
			$this->EE->db->update('ig_picpuller_oauths', $data);

		}
	}

	// Since the prefix option was added in a later version of Pic Puller, we need to do that as its own operation.
	// The previous instructions made sure we now have the column 'ig_site_id' required for the MSM compatibility.
	// Now we will add in the new column, ig+picpuller_prefix, to the ig_picpuller_credentials database. It will be
	// prepopulated with the value in the variable '$default_prefix' defined at the beginnning of this update function.

	if (!$this->EE->db->field_exists('ig_picpuller_prefix', 'ig_picpuller_credentials'))
	{
		$fields = array(
			'app_id'			=> array('type' => 'INT',  'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
			'ig_site_id'		=> array('type' => 'INT', 'length' => '11', 'null' => TRUE),
			'ig_client_id'		=> array('type' => 'varchar', 'constraint' => '64', 'null' => TRUE, 'default' => NULL),
			'ig_client_secret' 	=> array('type' => 'varchar', 'constraint'=> '64', 'null' => TRUE, 'default' => NULL)
			,
			'ig_picpuller_prefix'	=> array('type' => 'varchar', 'constraint'=> '128', 'null' => TRUE, 'default' => NULL),
			'auth_url'			=> array('type' => 'varchar', 'constraint'=> '256', 'null' => TRUE, 'default' => NULL),
			'frontend_auth_url'	=> array('type' => 'varchar', 'constraint'=> '256', 'null' => TRUE, 'default' => NULL)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('app_id', TRUE);
		$this->EE->dbforge->create_table('ig_picpuller_credentials_TEMP2');

		// Now get all the data from the OLD data base
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
			$data = array(
				// ig_site_id is set to the site that the user is currently logged into in the EE CP
			'ig_site_id' => $row->ig_site_id,
			'ig_client_id' => $row->ig_client_id,
			'ig_client_secret' => $row->ig_client_secret,
			'ig_picpuller_prefix' => $default_prefix,
			'auth_url' => $row->auth_url,
			);
			$this->EE->db->insert('ig_picpuller_credentials_TEMP2', $data);
		};

		$this->EE->dbforge->rename_table($this->EE->db->dbprefix .'ig_picpuller_credentials', $this->EE->db->dbprefix .'ig_picpuller_credentials_OLD2');

		$this->EE->dbforge->rename_table($this->EE->db->dbprefix .'ig_picpuller_credentials_TEMP2', $this->EE->db->dbprefix .'ig_picpuller_credentials');

		$this->EE->dbforge->drop_table('ig_picpuller_credentials_OLD2');

	}

    if (version_compare($current, '0.9.2', '<'))
    {
        // Update code here
    	//$this->EE->load->dbforge();
    	$data = array(
			'class' => "Ig_picpuller",
			'method' => 'deauthorization'
		);

		$this->EE->db->insert('actions', $data);

    }
		return TRUE;
	}

}
/* End of file upd.ig_picpuller.php */
/* Location: /system/expressionengine/third_party/ig_picpuller/upd.ig_picpuller.php */