<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

// load dependencies
require_once PATH_THIRD.'assets/config.php';

/**
 * Assets Extension
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_ext
{
	var $name           = ASSETS_NAME;
	var $version        = ASSETS_VER;
	var $description    = ASSETS_DESC;
	var $docs_url       = ASSETS_DOCS;
	var $settings_exist = 'n';

	/**
	 * Constructor
	 */
	function __construct()
	{
		// -------------------------------------------
		//  Make a local reference to the EE super object
		// -------------------------------------------

		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.'assets/');
		$this->EE->load->library('assets_lib');

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['assets']))
		{
			$this->EE->session->cache['assets'] = array();
		}

		$this->cache =& $this->EE->session->cache['assets'];

		if (!isset($this->cache['registered_files']))
		{
			$this->cache['registered_files'] = array();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		// -------------------------------------------
		//  Add the row to exp_extensions
		// -------------------------------------------

		$this->EE->db->insert('extensions', array(
			'class'    => 'Assets_ext',
			'method'   => 'channel_entries_query_result',
			'hook'     => 'channel_entries_query_result',
			'settings' => '',
			'priority' => 10,
			'version'  => ASSETS_VER,
			'enabled'  => 'y'
		));

		$this->EE->db->insert('extensions', array(
			'class'    => 'Assets_ext',
			'method'   => 'file_after_save',
			'hook'     => 'file_after_save',
			'settings' => '',
			'priority' => 9,
			'version'  => ASSETS_VER,
			'enabled'  => 'y'
		));

		$this->EE->db->insert('extensions', array(
			'class'    => 'Assets_ext',
			'method'   => 'files_after_delete',
			'hook'     => 'files_after_delete',
			'settings' => '',
			'priority' => 8,
			'version'  => ASSETS_VER,
			'enabled'  => 'y'
		));
	}

	/**
	 * Update Extension
	 */
	function update_extension($current = NULL)
	{
		// All updates are handled by the module,
		// so there's nothing to change here
		return FALSE;
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		// -------------------------------------------
		//  Remove the row from exp_extensions
		// -------------------------------------------

		$this->EE->db->where('class', 'Assets_ext')
			->delete('extensions');
	}

	// --------------------------------------------------------------------

	/**
	 * channel_entries_query_result
	 */
	function channel_entries_query_result($Channel, $query_result)
	{
		// -------------------------------------------
		//  Get the latest version of $query_result
		// -------------------------------------------

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$query_result = $this->EE->extensions->last_call;
		}

		if ($query_result)
		{
			// -------------------------------------------
			//  Get all of the Assets fields that belong to entries' sites
			// -------------------------------------------

			$all_assets_fields = array();

			foreach ($this->EE->TMPL->site_ids as $site_id)
			{
				if (isset($Channel->pfields[$site_id]))
				{
					foreach ($Channel->pfields[$site_id] as $field_id => $field_type)
					{
						if ($field_type == 'assets')
						{
							// Now get the field name
							if (($field_name = array_search($field_id, $Channel->cfields[$site_id])) !== FALSE)
							{
								$all_assets_fields[$field_id] = $field_name;
							}
						}
					}
				}
			}

			if ($all_assets_fields)
			{
				// -------------------------------------------
				//  Figure out which of those fields are being used in this template
				// -------------------------------------------

				$tmpl_fields = array_merge(
					array_keys($this->EE->TMPL->var_single),
					array_keys($this->EE->TMPL->var_pair)
				);

				$tmpl_assets_fields = array();

				foreach ($tmpl_fields as $field)
				{
					// Get the actual field name, sans tag func name and parameters
					preg_match('/^[\w\d-]*/', $field, $m);
					$field_name = $m[0];

					$field_ids = array_keys($all_assets_fields, $field_name);
					foreach ($field_ids as $field_id)
					{
						$tmpl_assets_fields[] = $field_id;
					}

				}

				if ($tmpl_assets_fields)
				{
					// -------------------------------------------
					//  Get each of the entry IDs
					// -------------------------------------------

					$entry_ids = array();

					foreach ($query_result as $entry)
					{
						if (! empty($entry['entry_id']))
						{
							$entry_ids[] = $entry['entry_id'];
						}
					}

					// -------------------------------------------
					//  Get all of the exp_assets_selections rows that will be needed
					// -------------------------------------------

					// Set it first so that if there are simply no files selected,
					// the fieldtype still knows the extension was called
					$this->cache['assets_selections_rows'] = array();

					// Set draft only to true if EP BWF is targeting exactly this entry in preview
					$draft_status = (int) (
						isset($this->EE->session->cache['ep_better_workflow']['is_draft'])
							&& $this->EE->session->cache['ep_better_workflow']['is_draft']
							&& count($entry_ids) == 1
							&& isset($this->EE->session->cache['ep_better_workflow']['preview_entry_data'])
							&& $this->EE->session->cache['ep_better_workflow']['preview_entry_data']->entry_id == $entry_ids[0]);

					if ($entry_ids)
					{
						$sql = 'SELECT DISTINCT a.file_id, a.*, ae.* FROM exp_assets_files a
								   INNER JOIN exp_assets_selections ae ON ae.file_id = a.file_id
								   WHERE ae.entry_id IN ('.implode(',', $entry_ids).')
									 AND ae.field_id IN ('.implode(',', $tmpl_assets_fields).')
									 AND is_draft = ' . $draft_status . '
								   ORDER BY ae.sort_order';

						//  'assets_data_query' hook
						//   - Modify the row data before it gets saved to exp_assets_selections
						//
						if ($this->EE->extensions->active_hook('assets_data_query'))
						{
							$query = $this->EE->extensions->call('assets_data_query', $this, $sql);
						}
						else
						{
							$query = $this->EE->db->query($sql);
						}

						foreach ($query->result_array() as $row)
						{
							$this->cache['assets_selections_rows'][$row['entry_id']][$row['field_id']][] = $row;
						}
					}
				}
			}
		}

		return $query_result;
	}

	/**
	 * Register the file in Assets tables
	 * @param $file_id
	 * @param $data
	 */
	public function file_after_save($file_id, $data)
	{
		if (empty($data['upload_location_id']) OR isset($this->cache['filemanager_extension_ignore_files'][$data['upload_location_id'].$data['file_name']]))
		{
			return;
		}

		$this->EE->assets_lib->register_ee_file($data['upload_location_id'], $data['title']);
	}

	/**
	 * Unregister the file from Assets tables
	 * @param $file_rows
	 */
	public function files_after_delete($file_rows)
	{
		foreach ($file_rows as $file_row)
		{
			$row = $this->EE->db->get_where('assets_files', array('filedir_id' => $file_row->upload_location_id, 'file_name' => $file_row->title))->row();
			if ($row)
			{
				$this->EE->assets_lib->unregister_file($row->file_id);
			}
		}
	}

}
