<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include PATH_THIRD.'tagger/config'.EXT;

/**
 * Install / Uninstall and updates the modules
 *
 * @package			DevDemon_Tagger
 * @version			2.1.5
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#update_file
 */
class Tagger_upd
{
	/**
	 * Module version
	 *
	 * @var string
	 * @access public
	 */
	public $version		=	DDTAGGER_VERSION;

	/**
	 * Module Short Name
	 *
	 * @var string
	 * @access private
	 */
	private $module_name	=	DDTAGGER_CLASS_NAME;

	/**
	 * Has Control Panel Backend?
	 *
	 * @var string
	 * @access private
	 */
	private $has_cp_backend = 'y';

	/**
	 * Has Publish Fields?
	 *
	 * @var string
	 * @access private
	 */
	private $has_publish_fields = 'n';


	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	// ********************************************************************************* //

	/**
	 * Installs the module
	 *
	 * Installs the module, adding a record to the exp_modules table,
	 * creates and populates and necessary database tables,
	 * adds any necessary records to the exp_actions table,
	 * and if custom tabs are to be used, adds those fields to any saved publish layouts
	 *
	 * @access public
	 * @return boolean
	 **/
	public function install()
	{
		// Load dbforge
		$this->EE->load->dbforge();

		//----------------------------------------
		// EXP_MODULES
		//----------------------------------------
		$module = array(	'module_name' => ucfirst($this->module_name),
							'module_version' => $this->version,
							'has_cp_backend' => $this->has_cp_backend,
							'has_publish_fields' => $this->has_publish_fields );

		$this->EE->db->insert('modules', $module);

		//----------------------------------------
		// EXP_TAGGER
		//----------------------------------------
		$tagger = array(
			'tag_id' 		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'tag_name'		=> array('type' => 'VARCHAR',	'constraint' => 255),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE,	'default' => 1),
			'author_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
			'entry_date'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
			'edit_date'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
			'hits'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'total_entries'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
		);

		$this->EE->dbforge->add_field($tagger);
		$this->EE->dbforge->add_key('tag_id', TRUE);
		$this->EE->dbforge->add_key('tag_name');
		$this->EE->dbforge->create_table('tagger', TRUE);

		//----------------------------------------
		// EXP_TAGGER_LINKS
		//----------------------------------------
		$tagger = array(
			'rel_id' 		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'entry_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'default' => 0),
			'channel_id'	=> array('type' => 'SMALLINT',	'unsigned' => TRUE,	'default' => 0),
			'field_id'		=> array('type' => 'MEDIUMINT',	'unsigned' => TRUE,	'default' => 0),
//			'item_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'default' => 0),
			'tag_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'default' => 0),
			'author_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
			'type'			=> array('type' => 'SMALLINT',	'unsigned' => TRUE, 'default' => 1),
			'tag_order'		=> array('type' => 'SMALLINT',	'unsigned' => TRUE, 'default' => 0),
		);

		$this->EE->dbforge->add_field($tagger);
		$this->EE->dbforge->add_key('rel_id', TRUE);
		$this->EE->dbforge->add_key('tag_id');
		$this->EE->dbforge->add_key('entry_id');
		$this->EE->dbforge->create_table('tagger_links', TRUE);

		//----------------------------------------
		// EXP_TAGGER_GROUPS
		//----------------------------------------
		$tagger = array(
			'group_id' 		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'group_title'	=> array('type' => 'VARCHAR',	'constraint' => 255),
			'group_name'	=> array('type' => 'VARCHAR',	'constraint' => 255),
			'group_desc'	=> array('type' => 'VARCHAR',	'constraint' => 255),
			'parent_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'`order`'		=> array('type' => 'MEDIUMINT',	'unsigned' => TRUE, 'default' => 1),
		);

		$this->EE->dbforge->add_field($tagger);
		$this->EE->dbforge->add_key('group_id', TRUE);
		$this->EE->dbforge->create_table('tagger_groups', TRUE);

		//----------------------------------------
		// EXP_TAGGER_GROUPS_ENTRIES
		//----------------------------------------
		$tagger = array(
			'rel_id' 		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'tag_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'group_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'`order`'		=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 1),
		);

		$this->EE->dbforge->add_field($tagger);
		$this->EE->dbforge->add_key('rel_id', TRUE);
		$this->EE->dbforge->add_key('group_id');
		$this->EE->dbforge->add_key('tag_id');
		$this->EE->dbforge->create_table('tagger_groups_entries', TRUE);

		//----------------------------------------
		// EXP_ACTIONS
		//----------------------------------------
		$module = array(	'class' => ucfirst($this->module_name),
							'method' => $this->module_name . '_router' );

		$this->EE->db->insert('actions', $module);

		//----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}

		// Do we need to enable the extension
        //if ($this->uses_extension === TRUE) $this->extension_handler('enable');

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Uninstalls the module
	 *
	 * @access public
	 * @return Boolean FALSE if uninstall failed, TRUE if it was successful
	 **/
	function uninstall()
	{
		// Load dbforge
		$this->EE->load->dbforge();

		// Remove
		$this->EE->dbforge->drop_table('tagger');
		$this->EE->dbforge->drop_table('tagger_links');
		$this->EE->dbforge->drop_table('tagger_groups');
		$this->EE->dbforge->drop_table('tagger_groups_entries');

		$this->EE->db->where('module_name', ucfirst($this->module_name));
		$this->EE->db->delete('modules');
		$this->EE->db->where('class', ucfirst($this->module_name));
		$this->EE->db->delete('actions');

		// $this->EE->cp->delete_layout_tabs($this->tabs(), 'tagger');

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Updates the module
	 *
	 * This function is checked on any visit to the module's control panel,
	 * and compares the current version number in the file to
	 * the recorded version in the database.
	 * This allows you to easily make database or
	 * other changes as new versions of the module come out.
	 *
	 * @access public
	 * @return Boolean FALSE if no update is necessary, TRUE if it is.
	 **/
	public function update($current = '')
	{
		// Are they the same?
		if ($current >= $this->version)
		{
			return FALSE;
		}

		$current = str_replace('.', '', $current);

		// Two Digits? (needs to be 3)
		if (strlen($current) == 2) $current .= '0';

		$update_dir = PATH_THIRD.strtolower($this->module_name).'/updates/';

		// Does our folder exist?
		if (@is_dir($update_dir) === TRUE)
		{
			// Loop over all files
			$files = @scandir($update_dir);

			if (is_array($files) == TRUE)
			{
				foreach ($files as $file)
				{
					if ($file == '.' OR $file == '..' OR strtolower($file) == '.ds_store') continue;

					// Get the version number
					$ver = substr($file, 0, -4);

					// We only want greater ones
					if ($current >= $ver) continue;

					require $update_dir . $file;
					$class = 'TaggerUpdate_' . $ver;
					$UPD = new $class();
					$UPD->do_update();
				}
			}
		}

		// Upgrade The Module
		$this->EE->db->set('module_version', $this->version);
		$this->EE->db->where('module_name', ucfirst($this->module_name));
		$this->EE->db->update('exp_modules');

		return TRUE;
	}

} // END CLASS

/* End of file upd.tagger.php */
/* Location: ./system/expressionengine/third_party/tagger/upd.tagger.php */
