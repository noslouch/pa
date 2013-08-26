<?php if (! defined('BASEPATH')) exit('Invalid file request');


if (! defined('PATH_THIRD')) define('PATH_THIRD', EE_APPPATH.'third_party/');
require_once PATH_THIRD.'wygwam/config.php';
require_once PATH_THIRD.'wygwam/helper.php';


/**
 * Wygwam Update Class for EE2
 *
 * @package   Wygwam
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Wygwam_upd {

	var $version = WYGWAM_VER;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		$this->EE->load->dbforge();

		$this->EE->db->insert('modules', array(
			'module_name'        => WYGWAM_NAME,
			'module_version'     => WYGWAM_VER,
			'has_cp_backend'     => 'y',
			'has_publish_fields' => 'n'
		));

		// -------------------------------------------
		//  Create the exp_wygwam_configs table
		// -------------------------------------------

		if (! $this->EE->db->table_exists('wygwam_configs'))
		{
			$this->EE->load->dbforge();

			$this->EE->dbforge->add_field(array(
				'config_id'   => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'config_name' => array('type' => 'varchar', 'constraint' => 32),
				'settings'    => array('type' => 'text')
			));

			$this->EE->dbforge->add_key('config_id', TRUE);
			$this->EE->dbforge->create_table('wygwam_configs');
		}

		// -------------------------------------------
		//  Populate it
		// -------------------------------------------

		// Do toolbars already exist in the fieldtype's global settings?

		$this->EE->db->select('settings');
		$query = $this->EE->db->get_where('fieldtypes', array('name' => 'wygwam'));

		if ($query->num_rows()
			&& ($ft_settings = unserialize(base64_decode($query->row('settings'))))
			&& is_array($ft_settings)
			&& isset($ft_settings['toolbars']) && is_array($ft_settings['toolbars']) && $ft_settings['toolbars']
		)
		{
			$toolbars = $ft_settings['toolbars'];

			foreach ($toolbars as &$toolbar)
			{
				// stylesCombo_stylesSet => stylesSet
				if (isset($toolbar['stylesCombo_stylesSet']))
				{
					$toolbar['stylesSet'] = $toolbar['stylesCombo_stylesSet'];
					unset($toolbar['stylesCombo_stylesSet']);
				}
			}
		}
		else
		{
			$toolbars = Wygwam_helper::default_toolbars();
		}

		foreach ($toolbars as $name => &$toolbar) // WTF PHP
		{
			$config_settings = array_merge(Wygwam_helper::default_config_settings(), array('toolbar' => $toolbar));

			$this->EE->db->insert('wygwam_configs', array(
				'config_name' => $name,
				'settings'    => base64_encode(serialize($config_settings))
			));
		}

		// -------------------------------------------
		//  Get Config IDs
		// -------------------------------------------

		$config_ids = array();

		$this->EE->db->select('config_id, config_name');
		$query = $this->EE->db->get('wygwam_configs');

		foreach ($query->result_array() as $config)
		{
			$config_ids[$config['config_name']] = $config['config_id'];
		}

		// -------------------------------------------
		//  Update fields
		// -------------------------------------------

		$this->EE->db->select('field_id, field_label, field_settings');
		$query = $this->EE->db->get_where('channel_fields', array('field_type' => 'wygwam'));

		foreach ($query->result_array() as $field)
		{
			$field_settings = unserialize(base64_decode($field['field_settings']));

			if (! isset($field_settings['toolbar']) || ! isset($toolbars[$field_settings['toolbar']]))
			{
				// wtf is wrong with this?
				continue;
			}

			// create a new config?
			if ((isset($field_settings['config']) && $field_settings['config'])
				|| (isset($field_settings['upload_dir']) || $field_settings['upload_dir'])
			)
			{
				$new_config_settings = array_merge(
					Wygwam_helper::default_config_settings(),
					array('toolbar' => $toolbars[$field_settings['toolbar']])
				);

				// merge in config settings
				if (isset($field_settings['config']) && $field_settings['config'])
				{
					$new_config_settings = array_merge($new_config_settings, $field_settings['config']);
					unset($field_settings['config']);
				}

				// merge in upload directory
				if (isset($field_settings['upload_dir']) && $field_settings['upload_dir'])
				{
					$new_config_settings['upload_dir'] = $field_settings['upload_dir'];
					unset($field_settings['upload_dir']);
				}

				// add the new config
				$this->EE->db->insert('wygwam_configs', array(
					'config_name' => $field_settings['toolbar'].' - '.$field['field_label'],
					'settings'    => base64_encode(serialize($new_config_settings))
				));

				// get its ID
				$this->EE->db->select_max('config_id');
				$query = $this->EE->db->get('wygwam_configs');
				$field_settings['config'] = $query->row('config_id');
			}
			else
			{
				$field_settings['config'] = $config_ids[$field_settings['toolbar']];
			}

			unset($field_settings['toolbar']);

			// update the field
			$data = array('field_settings' => base64_encode(serialize($field_settings)));
			$this->EE->db->update('channel_fields', $data, array('field_id' => $field['field_id']));
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update
	 */
	function update($current = '')
	{
		// necessary to get EE to update the version number
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall
	 */
	function uninstall()
	{
		// remove row from exp_modules
		$this->EE->db->delete('modules', array('module_name' => 'Wygwam'));

		// drop the exp_wygwam_configs table
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('wygwam_configs');

		return TRUE;
	}

}
