<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if( ! defined('PATH_THIRD')) { define('PATH_THIRD', APPPATH . 'third_party'); };
require_once PATH_THIRD . 'zenbu/config.php';

class Zenbu_upd {

	var $version = ZENBU_VER;
	var $addon_short_name = 'zenbu';
	var $standard_fields = array(
		'show_id',
		'show_title',
		'show_url_title',
		'show_channel',
		'show_categories',
		'show_status',
		'show_sticky',
		'show_entry_date',
		'show_author',
		'show_comments',
		'show_view',
		);
	var $permissions = array(
		'can_admin', 
		'can_copy_profile', 
		'can_access_settings', 
		'edit_replace', 
		'can_view_group_searches', 
		'can_admin_group_searches'
		);
	
	function Zenbu_upd()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->dbprefix = $this->EE->db->dbprefix;
	}
	

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */	
	function install()
	{
		$this->EE->load->dbforge();

		$data = array(
			'module_name' => ucfirst($this->addon_short_name),
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);

		$this->EE->db->insert('modules', $data);
		
		unset($data['module_name']);
		unset($data['module_version']);
		unset($data['has_cp_backend']);
		
		/**
		* ============================
		* exp_zenbu table
		* ============================
		*/
		
		$fields = array(
		'id'						=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
		'member_group_id'			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
		'site_id'					=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
		'general_settings'			=> array('type'	=> 'text'),
		'show_fields'				=> array('type'	=> 'mediumtext'),
		'show_custom_fields'		=> array('type'	=> 'text'),
		'field_order'				=> array('type'	=> 'mediumtext'),
		'extra_options'				=> array('type'	=> 'mediumtext'),
		'can_admin'					=> array('type'	=> 'varchar', 'constraint'	=> '1', 'default' => 'n'),
		'can_copy_profile'			=> array('type'	=> 'varchar', 'constraint'	=> '1', 'default' => 'n'),
		'can_access_settings'		=> array('type'	=> 'varchar', 'constraint'	=> '1', 'default' => 'n'),
		'edit_replace'				=> array('type'	=> 'varchar', 'constraint'	=> '1', 'default' => 'y'),
		'can_view_group_searches'	=> array('type'	=> 'varchar', 'constraint'	=> '1', 'default' => 'n'),
		'can_admin_group_searches'	=> array('type'	=> 'varchar', 'constraint'	=> '1', 'default' => 'n'),
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		
		$this->EE->dbforge->create_table($this->addon_short_name);
		
		/**
		*  Insert default data
		*/

		// -----------------------------------------------------------------
		// Data to be inserted for multi-channel listings (i.e. channel "0")
		// -----------------------------------------------------------------
		$data_show_fields['0'] = array(
			'show_id'			=> 'y',
			'show_title'		=> 'y',
			'show_url_title'	=> 'y',
			'show_channel'		=> 'y',
			'show_categories'	=> 'y',
			'show_status'		=> 'y',
			'show_sticky'		=> 'y',
			'show_entry_date'	=> 'y',
			'show_author'		=> 'y',
			'show_comments'		=> 'y',
			'show_view'			=> 'y',
		);
		$data_show_custom_fields['0'] = array(
			'show_custom_fields'	=> "",
		);
		
		// Create a basic order for standard fields
		$field_order = array();
		foreach($this->standard_fields as $key => $value)
		{
			$field_order[] = $value;
		}
		// Serialize to pack in database columns
		$field_order = array_flip($field_order);
		
		$data_field_order['0'] = array(
			'field_order' 		=> $field_order,
		);
		
		$data_extra_options['0'] = array(
		
			'extra_options' 	=> array(
			'text_option_1' 	=> '',
			'matrix_option_1'	=> '',
			),
		);
		
		// -----------------------------------------------------------------
		// Data to be inserted for other channels
		// -----------------------------------------------------------------
		$channels = $this->EE->db->query("SELECT channel_id, site_id FROM exp_channels");
		if($channels->num_rows() > 0)
		{
			
			foreach($channels->result_array() as $row)
			{
				$channel_id = $row['channel_id'];
				$site_id = $row['site_id'];
			
				// GET channel-specific custom fields
				$custom_fields = array();
				
				$this->EE->db->select("channel_fields.field_id");
				$this->EE->db->from("channel_fields");
				$this->EE->db->where("channels.channel_id", $channel_id);
				$this->EE->db->where("channels.site_id", $site_id);
				$this->EE->db->join("channels", "channel_fields.group_id = channels.field_group");
				$this->EE->db->order_by("field_order", "asc");
				$custom_field_query = $this->EE->db->get();
				if($custom_field_query->num_rows() > 0)
				{
					foreach($custom_field_query->result_array() as $row_field)
					{
						$custom_fields[] = 'field_'.$row_field['field_id'];
					}
				}
			
				// Create a basic order for standard fields
				$field_order = array();
				foreach($this->standard_fields as $key => $value)
				{
					$field_order[] = $value;
				}
				// Create a basic order for custom fields
				foreach($custom_fields as $key => $value)
				{
					$field_order[] = $value;
				}
				// Serialize to pack in database columns
				$field_order = array_flip($field_order);
			
				$data_show_fields[$channel_id] = array(
				'show_id'			=> 'y',
				'show_title'		=> 'y',
				'show_url_title'	=> 'y',
				'show_channel'		=> 'y',
				'show_categories'	=> 'y',
				'show_status'		=> 'y',
				'show_sticky'		=> 'y',
				'show_entry_date'	=> 'y',
				'show_author'		=> 'y',
				'show_comments'		=> 'y',
				'show_view'			=> 'y',
				);
				
				$data_show_custom_fields[$channel_id] = array(
					'show_custom_fields'	=> '',
				);
				
				$data_field_order[$channel_id] = array(
				
				'field_order' 		=> $field_order,
				);
				
				$data_extra_options[$channel_id] = array(
				
				'extra_options' 	=> array(
					'text_option_1' 	=> '',
					'matrix_option_1'	=> '',
					),
				);
				
					
			}
			$db_data['member_group_id'] = 0;										// Default settings (everything turned on)
			$db_data['site_id'] = 0;
			$db_data['general_settings'] = serialize(array());										
			$db_data['show_fields'] = serialize($data_show_fields);					// Default "show everything" settings
			$db_data['show_custom_fields'] = serialize($data_show_custom_fields);	// Default settings for "fields to show"
			$db_data['field_order'] = serialize($data_field_order);					// Default "field order" settings
			$db_data['extra_options'] = serialize($data_extra_options);				// Default settings for extra field options

			$sql = $this->EE->db->insert_string($this->addon_short_name, $db_data);
			$this->EE->db->query($sql);
			
			// Copy default settings to al present member groups
			$query_set_up_members = $this->EE->db->get("member_groups"); 
			foreach($query_set_up_members->result_array() as $row)
			{
				$db_data['member_group_id'] = $row['group_id'];
				$db_data['site_id'] = $row['site_id'];

				//	----------------------------------------
				//	Enabling it all for Super Admins
				//	----------------------------------------
				if($row['group_id'] == 1)
				{
					foreach($this->permissions as $permission)
					{
						$db_data[$permission] = 'y';
					}
				}
			
				$sql = $this->EE->db->insert_string($this->addon_short_name, $db_data);
				$this->EE->db->query($sql);

				// Unset Super Admin settings
				if($row['group_id'] == 1)
				{
					foreach($this->permissions as $permission)
					{
						unset($db_data[$permission]);
					}
				}
			}
			
			
			
			
		} else {
		
			//	------------------------------------------
			//	Data to insert if no channels are present
			//	------------------------------------------
			$db_data['member_group_id'] = 0;										// Default settings (everything turned on)
			$db_data['site_id'] = 0;
			$db_data['general_settings'] = serialize(array());										
			$db_data['show_fields'] = serialize($data_show_fields);					// Default "show everything" settings
			$db_data['show_custom_fields'] = serialize($data_show_custom_fields);	// Default settings for "fields to show"
			$db_data['field_order'] = serialize($data_field_order);					// Default "field order" settings
			$db_data['extra_options'] = serialize($data_extra_options);				// Default settings for extra field options
			
			$query_set_up_members = $this->EE->db->get("member_groups"); 
			foreach($query_set_up_members->result_array() as $row)
			{
				$db_data['member_group_id'] = $row['group_id'];
				$db_data['site_id'] = $row['site_id'];
				
				//	----------------------------------------
				//	Enabling it all for Super Admins
				//	----------------------------------------
				if($row['group_id'] == 1)
				{
					foreach($this->permissions as $permission)
					{
						$db_data[$permission] = 'y';
					}
				}

				$sql = $this->EE->db->insert_string($this->addon_short_name, $db_data);
				$this->EE->db->query($sql);

				// Unset Super Admin settings
				if($row['group_id'] == 1)
				{
					foreach($this->permissions as $permission)
					{
						unset($db_data[$permission]);
					}
				}
			}
		}
		
		/**
		* ==============================
		* exp_zenbu_saved_searches table
		* ==============================
		*/
		$fields = array(
			'rule_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'member_group_id'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE, 'default' => 0),
			'rule_order'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE, 'default' => 0),
			'site_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'rule_label'			=> array('type'	=> 'text'),
			'rules'					=> array('type'	=> 'mediumtext'),
		);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('rule_id', TRUE);
		
		$this->EE->dbforge->create_table($this->addon_short_name.'_saved_searches');

		/**
		* ===============================
		* exp_zenbu_member_settings table
		* ===============================
		*/
		
		$fields = array(
		'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
		'site_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
		'general_settings'		=> array('type'	=> 'text'),
		'show_fields'			=> array('type'	=> 'mediumtext'),
		'show_custom_fields'	=> array('type'	=> 'text'),
		'field_order'			=> array('type'	=> 'mediumtext'),
		'extra_options'			=> array('type'	=> 'mediumtext'),
		'can_admin'				=> array('type'	=> 'varchar', 'constraint'	=> '1'),
		'can_copy_profile'		=> array('type'	=> 'varchar', 'constraint'	=> '1'),
		'can_access_settings'	=> array('type'	=> 'varchar', 'constraint'	=> '1'),
		'edit_replace'			=> array('type'	=> 'varchar', 'constraint'	=> '1'),
		);

		$this->EE->dbforge->add_field($fields);
		
		$this->EE->dbforge->create_table($this->addon_short_name . '_member_settings');
		

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
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->addon_short_name));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', $this->addon_short_name);
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', $this->addon_short_name);
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table($this->addon_short_name);
		$this->EE->dbforge->drop_table($this->addon_short_name.'_saved_searches');
		$this->EE->dbforge->drop_table($this->addon_short_name.'_member_settings');
		
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
		//echo $current;
		if ($current == $this->version)
		{
			return FALSE;
		}
		
		/**
		* Version 1.0 => 1.1 Update script
		* --------------------------------
		* Ditch primary keys for member_group_id, add site_id column
		* and add incrementing id column (might come in handy).
		* First site_id is used to update what is already available,
		* then data is copied for other site_ids
		**/
		if (version_compare($current, "1.1", '<')) 
		{
			$this->EE->load->dbforge();
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." DROP PRIMARY KEY");
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." ADD COLUMN site_id INT(10) NOT NULL AFTER member_group_id");
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." ADD COLUMN id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
			
			$sites = $this->EE->db->query("SELECT site_id FROM exp_sites ORDER BY site_id");
			if($sites->num_rows() > 0)
			{
				foreach($sites->result_array() as $row)
				{
					$site_id[] = $row['site_id'];
				}
			}
			
			$this->EE->db->where_not_in('member_group_id', array(0));
			$table_query = $this->EE->db->get('exp_'.$this->addon_short_name);
			
			foreach($site_id as $key => $site_id)
			{
				
				if($key == 0)
				{
					// Set first site_id to newly created columns
					foreach($table_query->result_array() as $data)
					{
						$data_update['site_id'] = $site_id;
						$this->EE->db->update($this->addon_short_name, $data_update);
					}
				} else {
					
					// Copy settings to other site_ids
					foreach($table_query->result_array() as $data)
					{
						unset($data['id']);
						$data['site_id'] = $site_id;
						$this->EE->db->query($this->EE->db->insert_string($this->addon_short_name, $data, TRUE));
					}
				}
			}
			
		} 
		
		if (version_compare($current, "1.2.1", '<'))
		{
			$zenbu_query = $this->EE->db->query("SELECT * FROM exp_zenbu");
			if($zenbu_query->num_rows() > 0)
			{
				foreach($zenbu_query->result_array() as $key => $row)
				{
					$show_fields_settings = unserialize($row['show_fields']);
					foreach($show_fields_settings as $channel_id => $settings_array)
					{
						$show_fields_settings[$channel_id]['show_title'] = 'y';	
					}
					
					$show_fields_settings = serialize($show_fields_settings);
					$data['show_fields'] = $show_fields_settings;
					
					$this->EE->db->where('id', $row['id']);
					$this->EE->db->update($this->addon_short_name, $data);
					
				}
			}
		}
		
		if (version_compare($current, "1.3", '<'))
		{
			/**
			* ==============================
			* exp_zenbu_saved_searches table
			* ==============================
			*/
			$this->EE->load->dbforge();
			
			$fields = array(
				'rule_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
				'rule_label'			=> array('type'	=> 'text'),
				'rules'					=> array('type'	=> 'mediumtext'),
			);
			
			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('rule_id', TRUE);
			
			$this->EE->dbforge->create_table($this->addon_short_name.'_saved_searches');
			
			
			/**
			* ================================
			* Adding multi-channel preferences
			* ================================
			*/
			$zenbu_query = $this->EE->db->query("SELECT * FROM exp_zenbu");
			if($zenbu_query->num_rows() > 0)
			{
				foreach($zenbu_query->result_array() as $key => $row)
				{
					$data_show_fields = unserialize($row['show_fields']);
					$data_show_custom_fields = unserialize($row['show_custom_fields']);
					$data_field_order = unserialize($row['field_order']);
					$data_extra_options = unserialize($row['extra_options']);
					
					// -----------------------------------------------------------------
					// Data to be inserted for multi-channel listings (i.e. channel "0")
					// -----------------------------------------------------------------
					$data_show_fields['0'] = array(
						'show_id'			=> 'y',
						'show_title'		=> 'y',
						'show_url_title'	=> 'y',
						'show_channel'		=> 'y',
						'show_categories'	=> 'y',
						'show_status'		=> 'y',
						'show_sticky'		=> 'y',
						'show_entry_date'	=> 'y',
						'show_author'		=> 'y',
						'show_comments'		=> 'y',
						'show_view'			=> 'y',
					);
					$data_show_custom_fields['0'] = array(
						'show_custom_fields'	=> "",
					);
					
					// Create a basic order for standard fields
					$field_order = array();
					foreach($this->standard_fields as $key => $value)
					{
						$field_order[] = $value;
					}
					// Serialize to pack in database columns
					$field_order = array_flip($field_order);
					
					$data_field_order['0'] = array(
						'field_order' 		=> $field_order,
					);
					
					$data_extra_options['0'] = array(
					
						'extra_options' 	=> array(
						'text_option_1' 	=> '',
						'matrix_option_1'	=> '',
						),
					);
					
					
					$db_data['member_group_id'] = $row['member_group_id'];					// Default settings (everything turned on)
					$db_data['site_id'] = $row['site_id'];										
					$db_data['show_fields'] = serialize($data_show_fields);					// Default "show everything" settings
					$db_data['show_custom_fields'] = serialize($data_show_custom_fields);	// Default settings for "fields to show"
					$db_data['field_order'] = serialize($data_field_order);					// Default "field order" settings
					$db_data['extra_options'] = serialize($data_extra_options);				// Default settings for extra field options
					$db_data['can_admin'] = $row['can_admin'];								// Can access the member permissions
					$db_data['can_copy_profile'] = $row['can_copy_profile'];				// Can save own profile to other members
					$db_data['can_access_settings'] = $row['can_access_settings'];			// Can see the "Settings" tab in addon
					$db_data['edit_replace'] = $row['edit_replace'];						// Enables extension to replace Edit link in Content => Edit for these members
	
					$sql = $this->EE->db->insert_string($this->addon_short_name, $db_data);
					$this->EE->db->query($sql);
					
					
				}
			}
			
		}
		
		if (version_compare($current, "1.4.0", '<'))
		{
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." ADD COLUMN general_settings TEXT NOT NULL AFTER site_id");
			$db_data['general_settings'] = serialize(array());
			
			$zenbu_query = $this->EE->db->query("SELECT id FROM exp_zenbu");
			if($zenbu_query->num_rows() > 0)
			{
				foreach($zenbu_query->result_array() as $key => $row)
				{
					$id[] = $row['id'];
					
				}
			}
			
			$zenbu_query->free_result();
			
			
			foreach($id as $key => $val)
			{
				$this->EE->db->where('id', $val);
				$this->EE->db->update($this->addon_short_name, $db_data);
			}
			
		}

		if (version_compare($current, "1.5.1", '<'))
		{
			$this->EE->load->dbforge();
			/**
			* ===============================
			* exp_zenbu_member_settings table
			* ===============================
			*/
			
			$fields = array(
			'member_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'site_id'				=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => FALSE),
			'general_settings'		=> array('type'	=> 'text'),
			'show_fields'			=> array('type'	=> 'mediumtext'),
			'show_custom_fields'	=> array('type'	=> 'text'),
			'field_order'			=> array('type'	=> 'mediumtext'),
			'extra_options'			=> array('type'	=> 'mediumtext'),
			'can_admin'				=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			'can_copy_profile'		=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			'can_access_settings'	=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			'edit_replace'			=> array('type'	=> 'varchar', 'constraint'	=> '1'),
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('member_id', TRUE);
			
			$this->EE->dbforge->create_table($this->addon_short_name . '_member_settings');
		}
		

		if (version_compare($current, "1.5.4", '<'))
		{
			/**
			 * Removing the primary key/auto increment from exp_zenbu_member_settings
			 * since this cannot be done in MSM setups
			 */
			$check_primary = $this->EE->db->query("SHOW index FROM exp_zenbu_member_settings WHERE Key_name = 'PRIMARY'");
			if($check_primary->num_rows() > 0)
			{
				$sql = array(	'ALTER TABLE exp_zenbu_member_settings MODIFY member_id INT(10) NOT NULL',
								'ALTER TABLE exp_zenbu_member_settings DROP PRIMARY KEY',);
				foreach($sql as $key => $query)
				{
					$this->EE->db->query($query);
				}
			}
		}

		if (version_compare($current, "1.5.5", '<'))
		{
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name."_saved_searches ADD COLUMN site_id INT(10) DEFAULT " . $this->EE->session->userdata['site_id'] . " AFTER member_id");
		}

		if (version_compare($current, "1.8.0b1", '<'))
		{
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name."_saved_searches ADD COLUMN member_group_id INT(10) DEFAULT 0 AFTER member_id");
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name."_saved_searches ADD COLUMN rule_order INT(10) DEFAULT 0 AFTER member_group_id");
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." ADD COLUMN can_view_group_searches VARCHAR(1) DEFAULT 'n'");
			$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." ADD COLUMN can_admin_group_searches VARCHAR(1) DEFAULT 'n'");

			//	----------------------------------------
			//	Set new defaults for access settings
			//	----------------------------------------
			$alt_cols = array(
				'can_access_settings'	=> 'n',
				'can_admin'				=> 'n',
				'can_copy_profile'		=> 'n',
				'edit_replace'			=> 'y'
				);

			foreach($alt_cols as $col => $val)
			{
				$this->EE->db->query("ALTER TABLE exp_".$this->addon_short_name." ALTER COLUMN " . $col . " SET DEFAULT '" . $val . "'");
			}

		}
		
	return TRUE; 

	}
	
}
/* END Class */

/* End of file upd.zenbu.php */
/* Location: ./system/expressionengine/third_party/modules/zenbu/upd.zenbu.php */