<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Low_reorder_base'))
{
	require_once(PATH_THIRD.'low_reorder/base.low_reorder.php');
}

/**
 * Low Reorder UPD class
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */
class Low_reorder_upd extends Low_reorder_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Extension hooks
	 *
	 * @var        array
	 * @access     private
	 */
	private $hooks = array(
		'entry_submission_end',
		'channel_entries_query_result'
	);

	/**
	 * Class name shortcut
	 *
	 * @access     private
	 * @var        string
	 */
	private $class_name;

	/**
	 * Are we updating from v1?
	 *
	 * @access     private
	 * @var        book
	 */
	private $_from_v1 = FALSE;

	// --------------------------------------------------------------------
	// PUBLIC METHODS
	// --------------------------------------------------------------------

	/**
	* Constructor
	*
	* @access      public
	* @return      bool
	*/
	public function __construct()
	{
		parent::__construct();

		$this->class_name = ucfirst($this->package);
	}

	// --------------------------------------------------------------------

	/**
	* Install the module
	*
	* @access      public
	* @return      bool
	*/
	public function install()
	{
		// --------------------------------------
		// Install tables
		// --------------------------------------

		$this->EE->low_reorder_set_model->install();
		$this->EE->low_reorder_order_model->install();

		// --------------------------------------
		// Add row to modules table
		// --------------------------------------

		$this->EE->db->insert('modules', array(
			'module_name'    => $this->class_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		));

		// --------------------------------------
		// Add rows to extensions table
		// --------------------------------------

		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Uninstall the module
	*
	* @access      public
	* @return      bool
	*/
	public function uninstall()
	{
		// --------------------------------------
		// get module id
		// --------------------------------------

		$query = $this->EE->db->select('module_id')
		       ->from('modules')
		       ->where('module_name', $this->class_name)
		       ->get();

		// --------------------------------------
		// remove references from module_member_groups
		// --------------------------------------

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		// --------------------------------------
		// remove references from modules
		// --------------------------------------

		$this->EE->db->where('module_name', $this->class_name);
		$this->EE->db->delete('modules');

		// --------------------------------------
		// remove references from extensions
		// --------------------------------------

		$this->EE->db->where('class', $this->class_name.'_ext');
		$this->EE->db->delete('extensions');

		// --------------------------------------
		// Uninstall tables
		// --------------------------------------

		$this->EE->low_reorder_set_model->uninstall();
		$this->EE->low_reorder_order_model->uninstall();

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update the module
	 *
	 * @access      public
	 * @param       string    Current version of the module
	 * @return      bool
	 */
	public function update($current = '')
	{
		// --------------------------------------
		// Same version? A-okay, daddy-o!
		// --------------------------------------

		if ($current == '' OR version_compare($current, LOW_REORDER_VERSION) === 0)
		{
			return FALSE;
		}

		// --------------------------------------
		// Update to 1.0.5
		// --------------------------------------

		if (version_compare($current, '1.0.5', '<'))
		{
			$this->_v105();
		}

		// --------------------------------------
		// Update to 1.2.0
		// --------------------------------------

		if (version_compare($current, '1.2.0', '<'))
		{
			$this->_v120();
		}

		// --------------------------------------
		// Update to 2.0.0
		// --------------------------------------

		if (version_compare($current, '2.0b1', '<'))
		{
			$this->_from_v1 = TRUE;
			$this->_v20b1();
		}

		if (version_compare($current, '2.0b2', '<'))
		{
			$this->_v20b2();
		}

		// --------------------------------------
		// Update to 2.1.0
		// --------------------------------------

		if (version_compare($current, '2.1.0', '<'))
		{
			$this->_v210();
		}

		// --------------------------------------
		// All done updating
		// --------------------------------------

		return TRUE;

	}

	// --------------------------------------------------------------------
	// PRIVATE METHODS
	// --------------------------------------------------------------------

	/**
	 * Update routines for version 1.0.5
	 *
	 * @access      private
	 * @return      void
	 */
	private function _v105()
	{
		// Adds sorting order as setting
		$this->EE->db->query("ALTER TABLE `exp_low_reorder_settings` ADD `sort_order` ENUM('asc','desc') NOT NULL DEFAULT 'asc'");
	}

	/**
	 * Update routines for version 1.2.0
	 *
	 * @access      private
	 * @return      void
	 */
	private function _v120()
	{
		// Old attributes
		$oldies = array('statuses', 'categories', 'show_expired', 'show_future', 'sort_order');

		// New attribute to store settings
		$this->EE->db->query("ALTER TABLE `exp_low_reorder_settings` ADD `settings` TEXT NOT NULL");

		// Get all current settings and store in new settings attribute
		$query = $this->EE->db->get('exp_low_reorder_settings');

		foreach ($query->result_array() AS $row)
		{
			// Store oldies in their own array
			foreach ($oldies AS $attr)
			{
				$data[$attr] = $row[$attr];
			}

			// Save encoded array in new attribute
			$this->EE->db->where('channel_id', $row['channel_id']);
			$this->EE->db->where('field_id',   $row['field_id']);
			$this->EE->db->update('low_reorder_settings', array('settings' => base64_encode(serialize($data))));
		}

		// Get rid of old attributes
		foreach ($oldies AS $attr)
		{
			$this->EE->db->query("ALTER TABLE `exp_low_reorder_settings` DROP `{$attr}`");
		}
	}

	/**
	 * Update routines for version 2.0b1
	 *
	 * @access      private
	 * @return      void
	 */
	private function _v20b1()
	{
		// --------------------------------------
		// Install new tables
		// --------------------------------------

		$this->EE->low_reorder_set_model->install();
		$this->EE->low_reorder_order_model->install();

		// --------------------------------------
		// Get all current records from settings
		// --------------------------------------

		$query = $this->EE->db->get('low_reorder_settings');
		$rows  = $query->result_array();

		// Return if no settings exist
		if ( ! empty($rows))
		{
			// --------------------------------------
			// Get Field, Channel and Status details
			// --------------------------------------

			// Fields
			$query = $this->EE->db->select('field_id, site_id, field_name, field_label, field_instructions')
			       ->from('channel_fields')
			       ->where_in('field_id', low_flatten_results($rows, 'field_id'))
			       ->get();
			$fields = low_associate_results($query->result_array(), 'field_id');

			// Channels
			$query = $this->EE->db->select('channel_id, channel_name, channel_title, cat_group')
			       ->from('channels')
			       ->where_in('channel_id', low_flatten_results($rows, 'channel_id'))
			       ->get();
			$channels = low_associate_results($query->result_array(), 'channel_id');

			// Statuses
			$query = $this->EE->db->select('status_id, status')
			       ->from('statuses')
			       ->get();
			$statuses = low_flatten_results($query->result_array(), 'status', 'status_id');

			// --------------------------------------
			// Loop through rows and populate new table
			// --------------------------------------

			foreach ($rows AS $row)
			{
				// Skip non-existent channels or fields
				if ( ! (isset($channels[$row['channel_id']]) && isset($fields[$row['field_id']])) ) continue;

				// Shortcut to related channel and field
				$channel = $channels[$row['channel_id']];
				$field   = $fields[$row['field_id']];

				// Decode the settings
				$settings = decode_reorder_settings($row['settings']);

				// Initiate parameter array
				$params = array();

				// --------------------------------------
				// Set Channel parameter
				// --------------------------------------

				$params['channel'] = $channels[$row['channel_id']]['channel_name'];

				// --------------------------------------
				// Set Category parameter
				// --------------------------------------

				if ( ! empty($settings['categories']))
				{
					$params['category'] = implode('|', array_filter($settings['categories']));
				}

				// --------------------------------------
				// Set Status parameter
				// --------------------------------------

				if ( ! empty($settings['statuses']))
				{
					$tmp = array();

					foreach ($settings['statuses'] AS $status_id)
					{
						$tmp[] = $statuses[$status_id];
					}

					$params['status'] = implode('|', array_unique($tmp));
					unset($tmp);
				}

				// --------------------------------------
				// Set Show Expired parameter
				// --------------------------------------

				if ( ! empty($settings['show_expired']) && ($settings['show_expired'] == 'y' OR $settings['show_expired'] === TRUE))
				{
					$params['show_expired'] = 'yes';
				}

				// --------------------------------------
				// Set Show Future Entries parameter
				// --------------------------------------

				if ( ! empty($settings['show_future']) && ($settings['show_future'] == 'y' OR $settings['show_future'] === TRUE))
				{
					$params['show_future_entries'] = 'yes';
				}

				// --------------------------------------
				// Get permissions from settings
				// --------------------------------------

				$permissions = ( ! empty($settings['permissions'])) ? low_array_encode($settings['permissions']) : '';

				// --------------------------------------
				// Set Category Option value
				// --------------------------------------

				$cat_option = $settings['category_options'];

				// --------------------------------------
				// Set Category Groups value, if option is 'one'
				// --------------------------------------

				if ($cat_option == 'one' && $channel['cat_group'])
				{
					$cat_groups = low_linearize(explode('|', $channel['cat_group']));
				}
				else
				{
					$cat_groups = '';
				}

				// --------------------------------------
				// Set clear_cache value
				// --------------------------------------

				$clear_cache = ( ! empty($settings['clear_cache']) && $settings['clear_cache'] == 'n') ? 'n' : 'y';

				// --------------------------------------
				// Sort order setting
				// --------------------------------------

				$reverse = (@$settings['sort_order'] == 'desc');

				// --------------------------------------
				// Insert new row
				// --------------------------------------

				$set_id = $this->EE->low_reorder_set_model->insert(array(
					'site_id'     => $field['site_id'],
					'set_label'   => $channel['channel_title'].', '.$field['field_label'],
					'set_notes'   => $field['field_instructions'],
					'new_entries' => ($reverse) ? 'prepend' : 'append',
					'clear_cache' => $clear_cache,
					'channels'    => low_linearize(array($row['channel_id'])),
					'cat_option'  => $cat_option,
					'cat_groups'  => $cat_groups,
					'parameters'  => low_array_encode($params),
					'permissions' => $permissions
				));

				// --------------------------------------
				// Get current values
				// --------------------------------------

				$this->EE->db->select("GROUP_CONCAT(DISTINCT d.entry_id ORDER BY d.field_id_{$field['field_id']} ASC SEPARATOR '|') AS entries", FALSE)
					 ->from('channel_data d')
					 ->where('d.channel_id', $channel['channel_id'])
					 ->where("d.field_id_{$field['field_id']} !=", '');

				if ($cat_option != 'one')
				{
					$this->EE->db->select("'0' AS cat_id", FALSE);
				}
				else
				{
					$this->EE->db->select('cp.cat_id')
						->from('category_posts cp')
						->where('d.entry_id = cp.entry_id')
						->group_by('cat_id');
				}

				$query = $this->EE->db->get();

				foreach ($query->result() AS $row)
				{
					$entries = low_delinearize($row->entries);
					if ($reverse) $entries = array_reverse($entries);

					$this->EE->low_reorder_order_model->insert(array(
						'set_id' => $set_id,
						'cat_id' => $row->cat_id,
						'sort_order' => low_linearize($entries)
					));

				}
			}
		} // end if $rows

		// --------------------------------------
		// Change low_reorder fieldtype to text
		// --------------------------------------

		$this->EE->db->where('field_type', $this->package);
		$this->EE->db->update('channel_fields', array(
			'field_type'     => 'text',
			'field_settings' => low_array_encode(array('field_content_type' => 'text'))
		));

		// --------------------------------------
		// Remove low_reorder fieldtype
		// --------------------------------------

		$this->EE->db->where('name', $this->package);
		$this->EE->db->delete('fieldtypes');

		// --------------------------------------
		// Drop old table
		// --------------------------------------

		$this->EE->db->query("DROP TABLE IF EXISTS `exp_low_reorder_settings`");

		// --------------------------------------
		// Enable extension
		// --------------------------------------

		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}
	}

	/**
	 * Update routines for version 2.0b2
	 *
	 * @access      private
	 * @return      void
	 */
	private function _v20b2()
	{
		// Check if new_entries attr already exits
		$table = $this->EE->low_reorder_set_model->table();
		$field = 'new_entries';
		$query = $this->EE->db->query("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'");

		// Already exists
		if ($query->num_rows()) return;

		// Add column
		$this->EE->db->query("ALTER TABLE {$table} ADD COLUMN `{$field}` enum('append','prepend') NOT NULL DEFAULT 'append' AFTER `set_notes`");

		// Migrate to column
		$sets = $this->EE->low_reorder_set_model->get_all();

		foreach ($sets AS $set)
		{
			$params = low_array_decode($set['parameters']);
			$pend   = (@$params['sort'] == 'desc') ? 'prepend' : 'append';

			unset($params['sort']);

			$data = array(
				$field       => $pend,
				'parameters' => low_array_encode($params)
			);

			$this->EE->low_reorder_set_model->update($set['set_id'], $data);
		}

		// --------------------------------------
		// Update extension
		// --------------------------------------

		$this->_add_hook('channel_entries_query_result');
	}

	/**
	 * Update to version 2.1.0
	 */
	private function _v210()
	{
		// Load JS lib
		$this->EE->load->library('javascript');

		// Shortcut to table
		$table = $this->EE->low_reorder_set_model->table();

		// Add set_name Change DB stuff, only not coming from v1
		if ($this->_from_v1 == FALSE)
		{
			$this->EE->db->query("ALTER TABLE `{$table}` ADD `set_name` VARCHAR(50) NOT NULL AFTER `site_id`");

			// Add 'none' as cat_option
			$this->EE->db->query("ALTER TABLE `{$table}` CHANGE `cat_option` `cat_option` ENUM('all', 'some', 'one', 'none') DEFAULT 'all' NOT NULL");

			// Add indexes to table
			$this->EE->db->query("ALTER TABLE {$table} ADD INDEX (`site_id`)");
			$this->EE->db->query("ALTER TABLE {$table} ADD INDEX (`set_name`)");
		}

		// Change base64/serialize to json_encode and populate set_name
		foreach ($this->EE->low_reorder_set_model->get_all() AS $row)
		{
			$set_id = $row['set_id'];
			unset($row['set_id']);

			$row['parameters'] = $this->EE->javascript->generate_json(low_array_decode($row['parameters']));
			$row['permissions'] = $this->EE->javascript->generate_json(low_array_decode($row['permissions']));
			$row['set_name'] = 'set_'.$set_id;

			$this->EE->low_reorder_set_model->update($set_id, $row);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add extension hook
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _add_hook($name)
	{
		$this->EE->db->insert('extensions',
			array(
				'class'    => $this->class_name.'_ext',
				'method'   => $name,
				'hook'     => $name,
				'settings' => '',
				'priority' => 5,
				'version'  => $this->version,
				'enabled'  => 'y'
			)
		);
	}

} // End class

/* End of file upd.low_reorder.php */