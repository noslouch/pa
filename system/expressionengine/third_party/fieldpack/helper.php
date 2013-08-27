<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Fieldpack_helper
{

	/**
	 * @var EE
	 */
	public $EE = null;

	public function __construct()
	{
		$this->EE =& get_instance();
	}

	/**
	 * Convert Channel fiel, Matrix column and Low variable between field types.
	 *
	 * @param $from
	 * @param $to
	 */
	public function convert_types($from, $to)
	{
		// Convert regular channel fields
		$update = array('field_type' => $to);
		$condition = array('field_type' => $from);
		$this->EE->db->update('channel_fields', $update, $condition);

		// Convert Matrix columns
		if ($this->EE->db->table_exists('matrix_cols'))
		{
			$update = array('col_type' => $to);
			$condition = array('col_type' => $from);
			$this->EE->db->update('matrix_cols', $update, $condition);
		}
		
	}

	/**
	 * Uninstall a fieldtype by removing it's row from the DB.
	 * 
	 * @param $type
	 */
	public function uninstall_fieldtype($type)
	{
		// Remove the field type's row
		$this->EE->db->delete('fieldtypes', array('name' => $type));
	}

	/**
	 * Convert Low variables between types.
	 *
	 * @param $from
	 * @param $to
	 */
	public function convert_Low_variables($from, $to)
	{
		if ($this->EE->db->table_exists('low_variables'))
		{
			// Convert Low variables
			$update = array('variable_type' => $to);
			$condition = array('variable_type' => $from);
			$this->EE->db->update('low_variables', $update, $condition);
		}

		// Fetch Low Variable's extensions
		$rows = $this->EE->db->get_where('extensions', array('class' => 'Low_variables_ext'))->result();

		// For each entry, add the new type to enabled types IF the old type was enabled
		foreach ($rows as $row)
		{
			$settings = unserialize($row->settings);
			$perform_update = FALSE;
			if (is_array($settings))
			{
				$new_types = array();
				foreach ($settings['enabled_types'] as $type)
				{
					if ($type != $from)
					{
						$new_types[] = $type;
					}
					else
					{
						$perform_update = TRUE;
					}
				}

				// The original was not enabled, so we need not bother.
				if (!$perform_update)
				{
					continue;
				}
				$new_types[] = $to;
				$settings['enabled_types'] = $new_types;
			}

			$settings = serialize($settings);

			$this->EE->db->update('extensions', array('settings' => $settings), array('extension_id' => $row->extension_id));
		}

	}

	/**
	 * Disable extension.
	 */
	public function disable_extension()
	{
		$this->EE->db->where('class', 'Pt_field_pack_ext')->delete('extensions');
	}
}

