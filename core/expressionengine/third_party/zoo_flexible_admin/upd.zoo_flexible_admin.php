<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Installer for Zoo Flexible Admin
 *
 * This file must be in your /system/third_party/zoo_flexible_admin directory of your ExpressionEngine installation
 *
 * @package             Zoo Flexible Admin for EE2
 * @author              Nico De Gols (nico@ee-zoo.com)
 * @copyright			Copyright (c) Nico De Gols
 * @link                http://www.ee-zoo.com
 */


class Zoo_flexible_admin_upd {

	var $version = 1.79;
	
	function Zoo_flexible_admin_upd()
	{

		$this->EE =& get_instance();
	}

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */	
	function install()
	{
		$this->EE->load->dbforge();

		//Module details
		$data = array(
			'module_name' => 'Zoo_flexible_admin' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);

		//Insert Actions
		$data = array(
			'class' => 'Zoo_flexible_admin',
			'method' => 'ajax_preview'
		);

		$this->EE->db->insert('actions', $data);

		$data = array(
			'class' => 'Zoo_flexible_admin',
			'method' => 'ajax_load_tree'
		);

		$this->EE->db->insert('actions', $data);
		
		$data = array(
			'class' => 'Zoo_flexible_admin',
			'method' => 'ajax_load_settings'
		);

		$this->EE->db->insert('actions', $data);
			
		$data = array(
			'class' => 'Zoo_flexible_admin',
			'method' => 'ajax_save_tree'
		);

		$this->EE->db->insert('actions', $data);
			
		$data = array(
			'class' => 'Zoo_flexible_admin',
			'method' => 'ajax_remove_tree'
		);

		$this->EE->db->insert('actions', $data);
			
		$data = array(
			'class' => 'Zoo_flexible_admin',
			'method' => 'ajax_copy_tree'
		);

		$this->EE->db->insert('actions', $data);
			
		$fields = array(
						'id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
						'site_id'		=> array('type' 		 => 'int',
											  	 'constraint'	=> '4',
												 'unsigned'		 => TRUE),
						'group_id'		=> array('type'			=> 'int',
												 'constraint'	=> '4'),
						'nav'			=> array('type' => 'text'),
						'autopopulate'	=> array('type' => 'boolean'),
						'hide_sidebar'	=> array('type' => 'boolean'),
						'startpage'		=> array('type' => 'tinytext')
						);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		
		$this->EE->dbforge->create_table('zoo_flexible_admin_menus');
		
		if($this->EE->db->table_exists('ndg_flexible_admin_menus'))
		{
			$existing = $this->EE->db->select('*')->get('ndg_flexible_admin_menus');
			
			if($existing->num_rows() > 0)
			{
				foreach($existing->result_array() as $row)
				{
					unset($row['id']);	
					$this->EE->db->insert('zoo_flexible_admin_menus', $row); 
				}
			}
		}
		
		return TRUE;
				
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Zoo_flexible_admin'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Zoo_flexible_admin');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Zoo_flexible_admin');
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table('zoo_flexible_admin_menus');


		return TRUE;
	}



	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */	
	
	function update($current='')
	{
	
		if($current < 1.1){

		}
		
		return TRUE;
	}
	
}
/* END Class */

/* End of file upd.zoo_flexible_admin.php */
/* Location: ./system/expressionengine/third_party/modules/zoo_flexible_admin/upd.zoo_flexible_admin.php */