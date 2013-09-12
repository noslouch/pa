<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TaggerUpdate_215
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		// Load dbforge
		$this->EE->load->dbforge();
	}

	// ********************************************************************************* //

	public function do_update()
	{
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
			'`order`'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
		);

		$this->EE->dbforge->add_field($tagger);
		$this->EE->dbforge->add_key('rel_id', TRUE);
		$this->EE->dbforge->add_key('group_id');
		$this->EE->dbforge->add_key('tag_id');
		$this->EE->dbforge->create_table('tagger_groups_entries', TRUE);
	}

	// ********************************************************************************* //

}

/* End of file 215.php */
/* Location: ./system/expressionengine/third_party/tagger/updates/215.php */