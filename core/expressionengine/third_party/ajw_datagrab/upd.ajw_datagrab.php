<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DataGrab Update Class
 *
 * Handles installation, upgrading and removal of the DataGrab module
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Ajw_datagrab_upd { 

	var $version        = '1.8.0'; 

	function Ajw_datagrab_upd( $switch = TRUE ) 
	{ 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	} 

	function install() 
	{
		$this->EE->load->dbforge();

		// Add to modules table
		$data = array(
			'module_name' => 'Ajw_datagrab' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
			);

		$this->EE->db->insert('modules', $data); 

		// Add an action to call imports from templates
		$data = array(
			'class' 	=> 'Ajw_datagrab',
			'method' 	=> 'run_action'
		);
		$this->EE->db->insert('actions', $data);

		// Create table for saved imports
		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '6',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'name' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'description' => array(
				'type' => 'text',
				'null' => FALSE
			), 
			'passkey' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'default' => ''
			), 
			'settings' => array(
				'type' => 'text'
			), 
			'last_run' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'default' => 0,
			)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_datagrab');

		return TRUE;
	}

	function uninstall() 
	{ 

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Ajw_datagrab'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Ajw_datagrab');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Ajw_datagrab');
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', 'Ajw_datagrab_mcp');
		$this->EE->db->delete('actions');

		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('ajw_datagrab');
		
		return TRUE;
	}

	function update($current = '')
	{
		if ($current == $this->version) {
			return FALSE;
		}

		$this->EE->load->dbforge();

		if ( $current < "0.9.1" )  {

			// Add a site_id field for MSM sites
			
			$fields = array(
				'site_id' => array(
					'type' => 'INT',
					'constraint' => 4, 
					'unsigned' => TRUE,
					'default' => 1
				)
			);
			$this->EE->dbforge->add_column('ajw_datagrab', $fields, 'id');
			
		} 

		if ( $current < "1.7.0" )  {

			// Saved import format changed. Try to update
			$this->_update_saved_imports();
			
		}

		if ( $current < "1.7.2" )  {

			// Add a passkey field
			
			$fields = array(
				'passkey' => array(
					'type' => 'varchar',
					'constraint' => 255, 
					'default' => ''
				)
			);
			$this->EE->dbforge->add_column('ajw_datagrab', $fields, 'description');
			
		} 

		return TRUE;
	}

	/*
		HELPER FUNCTIONS
	*/
	
	function _update_saved_imports() {
		
		$this->EE->db->select( "id, settings" );
		$query = $this->EE->db->get( "exp_ajw_datagrab" );
		$results = $query->result_array();
		
		$datatype_settings = array(
			'filename',
			'delimiter',
			'encloser',
			'skip',
			'path',
			'filter',
			'query',
			'just_posts',
			'username',
			'password',
			'database',
			'server'
		);
		
		$config_settings = array(
			"type",
			"channel",
			"update",
			"unique",
			"author",
			"author_field",
			"author_check",
			"offset",
			"limit",
			"title",
			"url_title",
			"date",
			"expiry_date",
			"timestamp",
			"delete_old",
			"category_value",
			"cat_field",
			"cat_group",
			"cat_delimiter",
			"id",
			"status",
			"import_comments",
			"comment_author",
			"comment_email",
			"comment_date",
			"comment_url",
			"comment_body",
			"ajw_entry_id"
			);
		
		
		foreach( $results as $row ) {
			$old = unserialize( $row["settings"] );
			$new = array();
			
			$new["cf"] = $old;
			
			$new["import"]["type"] = $old["type"];
			$new["import"]["channel"] = $old["channel"];
			
			foreach( $datatype_settings as $s ) {
				if( isset($old[$s]) ) {
					$new["datatype"][$s] = $old[$s];
				}
			}

			foreach( $config_settings as $s ) {
				if( isset($old[$s]) ) {
					$new["config"][$s] = $old[$s];
				}
			}
			
			$data = array(
				"settings" => serialize( $new )
			);
			$this->EE->db->where('id', $row["id"]);
			$this->EE->db->update('exp_ajw_datagrab', $data);
			
		}
		
		
	}
	

}

/* End of file upd.ajw_datagrab.php */