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
 * Export It - Update Class
 *
 * Update class
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/upd.export_it.php
 */
class Export_it_upd 
{ 

    public $class = '';
    
    public $settings_table = ''; 
    
    public $version = '1.3.1';
         
    public function __construct() 
    { 
		$this->EE =& get_instance();
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->class = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->version = $config['version'];
		$this->ext_class_name = $config['ext_class_name'];		
    } 
    
	public function install() 
	{
		$this->EE->load->dbforge();
	
		$data = array(
			'module_name' => $this->class,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
	
		$this->EE->db->insert('modules', $data);
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->class."', 'api')";
		$this->EE->db->query($sql);
		
		$this->add_settings_table();		
		$this->activate_extension();
		
		return TRUE;
	} 
	
	public function activate_extension()
	{
		$data = array(
				'class'     => $this->ext_class_name,
				'method'    => 'cp_menu_array',
				'hook'      => 'cp_menu_array',
				'settings'  => serialize(array()),
				'priority'  => 9789,
				'version'   => $this->version,
				'enabled'   => 'y'
		);
		
		$this->EE->db->insert('extensions', $data);		
		return TRUE;
	}

	public function uninstall()
	{
		$this->EE->load->dbforge();
	
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->class));
	
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', $this->class);
		$this->EE->db->delete('modules');
	
		$this->EE->db->where('class', $this->class);
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table($this->settings_table);
		
		$this->disable_extension();
	
		return TRUE;
	}
	
	public function disable_extension()
	{
		$this->EE->db->where('class', $this->ext_class_name);
		$this->EE->db->delete('extensions');
	}

	public function update($current = '')
	{
		
		if ($current == $this->version)
		{
			return FALSE;
		}	

		if (version_compare($current, '1.1', '<'))
		{
			$this->add_settings_table();		
		}
		
		if(version_compare($current, '1.2.1', '<'))
		{
			$data = array(
					'class'     => $this->ext_class_name,
					'method'    => 'cp_menu_array',
					'hook'      => 'cp_menu_array',
					'settings'  => serialize(array()),
					'priority'  => 9789,
					'version'   => $this->version,
					'enabled'   => 'y'
			);
			
			$this->EE->db->insert('extensions', $data);			
		}
		
	}	
	
	private function add_settings_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
						'id'	=> array(
											'type'			=> 'int',
											'constraint'	=> 10,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'=> TRUE
										),
						'setting_key'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '30',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'setting_value'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE
										),
						'serialized' => array(
											'type' => 'int',
											'constraint' => 1,
											'null' => TRUE,
											'default' => '0'
						)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->settings_table, TRUE);		
	}
    
}