<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


require_once PATH_THIRD.'playa/config.php';


/**
 * Playa Fieldtype Class for ExpressionEngine 2
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa_ft extends EE_Fieldtype {

	var $drop_panes_size = 10;                          // number of items to be shown at once in Drop Panes UI
	var $limit_options = array(25,50,100,250,500,1000); // options for the "Limit" setting

	// --------------------------------------------------------------------

	var $info = array(
		'name'    => PLAYA_NAME,
		'version' => PLAYA_VER
	);

	var $has_array_data = TRUE;

	var $row_id; // Set by Matrix
	var $var_id; // Set by Low Variables
	var $is_draft = FALSE; // Set by Better Workflow

	/**
	 * Fieldtype Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['playa']))
		{
			$this->EE->session->cache['playa'] = array();
		}

		$this->cache =& $this->EE->session->cache['playa'];

		// -------------------------------------------
		//  Load the helper
		// -------------------------------------------

		if (! class_exists('Playa_Helper'))
		{
			require_once PATH_THIRD.'playa/helper.php';
		}

		$this->helper = new Playa_Helper();

		// -------------------------------------------
		//  Need to call update()?
		// -------------------------------------------

		if (! $this->EE->db->table_exists('playa_relationships'))
		{
			// was Playa 3 installed?
			$query = $this->EE->db->select('fieldtype_id, version')
			                      ->where('name', 'playa')
			                      ->get('fieldtypes');

			if ($query->num_rows())
			{
				// call update()
				$this->update($query->row('version'));

				// update the version # in exp_fieldtypes
				$this->EE->db->where('fieldtype_id', $query->row('fieldtype_id'))
				             ->update('fieldtypes', array('version' => PLAYA_VER));
			}
		}

	}

	// --------------------------------------------------------------------

	/**
	 * Update Global Settings
	 */
	private function _update_global_settings()
	{
		$this->settings = array_merge(array(
			'license_key'    => '',
			'filter_min'  => 20
		), $this->settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		// -------------------------------------------
		//  EE1 Conversion
		// -------------------------------------------

		$ee1_version = '';
		$global_settings = array();

		// include FF2EE2
		if (! class_exists('FF2EE2')) require_once PATH_THIRD.'playa/includes/ff2ee2/ff2ee2.php';

		// run the conversion script
		$converter = new FF2EE2('playa');

		// was Playa 2 or 3 for EE1 installed?
		if ($converter->version)
		{
			// get the old version number
			$ee1_version = $converter->version;

			// get the old global settings
			$global_settings = $converter->global_settings;
		}
		else
		{
			// was Playa 1 for EE1 installed?
			$query = $this->EE->db->select('field_id, field_list_items')
			                      ->where('field_type', 'playa')
			                      ->where('field_list_items !=', '')
			                      ->get('channel_fields');

			if ($query->num_rows())
			{
				$ee1_version = '1.3.3';

				foreach ($query->result() as $field)
				{
					// assemble the new field settings
					$field_settings = array(
						'channels' => explode(',', $field->field_list_items)
					);

					// update the row in exp_channel_fields
					$data = array(
						'field_settings'        => base64_encode(serialize($field_settings)),
						'field_maxl'            => '',
						'field_ta_rows'         => '',
						'field_list_items'      => '',
					);

					// 2.6 dropped these.
					if (version_compare(APP_VER, '2.6', '<'))
					{
						$data['field_related_orderby'] = '';
						$data['field_related_max'] = '';
					}

					$this->EE->db->where('field_id', $field->field_id)
					             ->update('channel_fields', $data);
				}
			}
		}

		if ($ee1_version)
		{
			// run the update script
			$this->update($ee1_version);
		}
		else
		{
			// just create the new table
			$this->_create_table();
		}

		// return the global settings
		return $global_settings;
	}

	/**
	 * Create exp_relationships table
	 */
	private function _create_table()
	{
		if (! $this->EE->db->table_exists('playa_relationships'))
		{
			$this->EE->load->dbforge();

			$this->EE->dbforge->add_field(array(
				'rel_id'          => array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'parent_entry_id' => array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'parent_field_id' => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
				'parent_col_id'   => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
				'parent_row_id'   => array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'parent_var_id'   => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
				'parent_is_draft' => array('type' => 'int', 'constraint' => 1, 'unsigned' => TRUE, 'default' => 0),
				'child_entry_id'  => array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'rel_order'       => array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE)
			));

			$this->EE->dbforge->add_key('rel_id', TRUE);
			$this->EE->dbforge->add_key('parent_entry_id');
			$this->EE->dbforge->add_key('parent_field_id');
			$this->EE->dbforge->add_key('parent_col_id');
			$this->EE->dbforge->add_key('parent_row_id');
			$this->EE->dbforge->add_key('parent_var_id');
			$this->EE->dbforge->add_key('child_entry_id');

			$this->EE->dbforge->create_table('playa_relationships');

			// add it to the table names cache
			$this->EE->db->data_cache['table_names'][] = $this->EE->db->dbprefix.'playa_relationships';

			return TRUE;
		}
	}

	/**
	 * Update
	 */
	function update($from)
	{
		if ($from)
		{
			if (version_compare($from, '4.0', '<'))
			{
				if ($this->_create_table())
				{
					$rel_data = array();
					$old_rel_ids = array();

					// -------------------------------------------
					//  Migrate Playa relationships
					// -------------------------------------------

					// get the Playa fields
					$fields = $this->EE->db->select('field_id')
						->where('field_type', 'playa')
						->get('channel_fields');


					foreach ($fields->result() as $field)
					{
						set_time_limit(30);

						$field_name = 'field_id_'.$field->field_id;

						// get the old Playa data
						$entries = $this->EE->db->select('entry_id, '.$field_name)
												->where($field_name.' !=', '')
												->get('channel_data');

						foreach ($entries->result() as $entry)
						{
							// get this field's rel_id's
							$rel_ids = $this->_parse_legacy_rel_ids($entry->$field_name);

							if ($rel_ids)
							{
								// Accomodate the schema changes
								if (version_compare(APP_VER, '2.6', '<'))
								{
									// get the old relationships
									$rels = $this->EE->db->select('rel_id, rel_child_id')
														 ->where_in('rel_id', $rel_ids)
														 ->get('relationships');

									foreach ($rels->result() as $rel)
									{
										$rel_order = array_search($rel->rel_id, $rel_ids);

										$rel_data[] = array(
											'parent_entry_id' => $entry->entry_id,
											'parent_field_id' => $field->field_id,
											'parent_col_id'   => null,
											'parent_row_id'   => null,
											'child_entry_id'  => $rel->rel_child_id,
											'rel_order'       => $rel_order
										);
									}
								}
								else
								{
									$entries = $this->EE->db->select('relationship_id, parent_id, child_id, field_id, order')
										->where_in('field_id', $field->field_id)
										->get('relationships');

									// If we get no matches, try again with relationship Ids
									if ($entries->num_rows() == 0)
									{
										$entries = $this->EE->db->select('relationship_id, parent_id, child_id, field_id, order')
											->where_in('relationship_id', $rel_ids)
											->get('relationships');

									}


									foreach ($entries->result() as $entry)
									{
										$rel_data[] = array(
											'parent_entry_id' => $entry->parent_id,
											'parent_field_id' => $field->field_id,
											'parent_col_id'   => null,
											'parent_row_id'   => null,
											'child_entry_id'  => $entry->child_id,
											'rel_order'       => $entry->order
										);
									}
								}

								// mark the old rel IDs for deletion
								$old_rel_ids = array_merge($old_rel_ids, $rel_ids);
							}
						}
					}

					// -------------------------------------------
					//  Migrate Matrix-Playa relationships
					// -------------------------------------------

					if ($this->EE->db->table_exists('matrix_cols'))
					{
						$cols = $this->EE->db->select('col_id')
						                     ->get_where('matrix_cols', array('col_type' => 'playa'));

						foreach ($cols->result() as $col)
						{
							$col_id = 'col_id_'.$col->col_id;

							$rows = $this->EE->db->select('row_id, entry_id, field_id, '.$col_id)
							                     ->get_where('matrix_data', array($col_id.' !=' => ''));

							foreach ($rows->result() as $row)
							{
								$rel_ids = $this->_parse_legacy_rel_ids($row->$col_id);

								if ($rel_ids)
								{
									// Accomodate the schema changes
									if (version_compare(APP_VER, '2.6', '<'))
									{

										// get the old relationships
										$rels = $this->EE->db->select('rel_id, rel_child_id')
															 ->where_in('rel_id', $rel_ids)
															 ->get('relationships');

										foreach ($rels->result() as $rel)
										{
											$rel_order = array_search($rel->rel_id, $rel_ids);

											$rel_data[] = array(
												'parent_entry_id' => $row->entry_id,
												'parent_field_id' => $row->field_id,
												'parent_col_id'   => $col->col_id,
												'parent_row_id'   => $row->row_id,
												'child_entry_id'  => $rel->rel_child_id,
												'rel_order'       => $rel_order
											);
										}
									}
									else
									{
										$entries = $this->EE->db->select('relationship_id, parent_id, child_id, field_id, order')
											->where_in('relationship_id', $rel_ids)
											->get('relationships');

										foreach ($entries->result() as $entry)
										{
											$rel_data[] = array(
												'parent_entry_id' => $row->entry_id,
												'parent_field_id' => $row->field_id,
												'parent_col_id'   => $col->col_id,
												'parent_row_id'   => $row->row_id,
												'child_entry_id'  => $entry->child_id,
												'rel_order'       => $entry->order
											);
										}
									}

									// mark the old rel IDs for deletion
									$old_rel_ids = array_merge($old_rel_ids, $rel_ids);
								}
							}
						}
					}

					// -------------------------------------------
					//  Create the new relationships
					// -------------------------------------------

					if (!empty($rel_data))
					{
						$this->EE->db->insert_batch('playa_relationships', $rel_data);
					}

					// -------------------------------------------
					//  Delete the old relationships
					// -------------------------------------------

					if (!empty($old_rel_ids))
					{
						// Accomodate the schema changes
						if (version_compare(APP_VER, '2.6', '<'))
						{
							$this->EE->db->where_in('rel_id', $old_rel_ids)
								->delete('relationships');
						}
						else
						{
							$this->EE->db->where_in('relationship_id', $old_rel_ids)
								->delete('relationships');
						}
					}
				}
			}

			else if (version_compare($from, '4.2', '<'))
			{
				// Just add the var_id column
				$this->EE->db->query('ALTER TABLE exp_playa_relationships ADD parent_var_id INT(6) UNSIGNED AFTER parent_row_id, ADD INDEX (parent_var_id)');
			}

			if (version_compare($from, '4.3.1', '<'))
			{
				if ($this->EE->db->table_exists('matrix_data'))
				{
					// Delete any orphaned relationships created in now-deleted Matrix rows
					// due to our delete_rows() function now working correctly until 4.3.1.
					$this->EE->db->query('DELETE rel FROM exp_playa_relationships AS rel
					                      WHERE rel.parent_row_id IS NOT NULL
					                      AND (SELECT COUNT(mrow.row_id) FROM exp_matrix_data AS mrow WHERE mrow.row_id = rel.parent_row_id) = 0');
				}
			}

			if (version_compare($from, '4.4', '<'))
			{
				if (!$this->EE->db->field_exists('parent_is_draft', 'playa_relationships'))
				{
					// Just add the parent_is_draft column
					$this->EE->db->query('ALTER TABLE exp_playa_relationships ADD parent_is_draft INT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER parent_var_id');
				}
			}


			if (version_compare($from, '4.4.1', '<'))
			{
				$result = $this->EE->db->query("SHOW FIELDS FROM exp_playa_relationships")->result();
				$has_column = FALSE;

				foreach ($result as $row)
				{
					if ($row->Field == "parent_is_draft")
					{
						$has_column = TRUE;
						break;
					}
				}

				if (!$has_column)
				{
					// Add it already.
					$this->EE->db->query('ALTER TABLE exp_playa_relationships ADD parent_is_draft INT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER parent_var_id');
				}

			}

			if (version_compare($from, '4.4.4', '<'))
			{
				if (!$this->EE->db->field_exists('parent_is_draft', 'playa_relationships'))
				{
					$this->EE->db->query('ALTER TABLE exp_playa_relationships ADD parent_is_draft INT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER parent_var_id');
				}
				else
				{
					$this->EE->db->query('ALTER TABLE exp_playa_relationships MODIFY parent_is_draft INT(1) UNSIGNED NOT NULL DEFAULT 0');
				}
				$this->EE->db->query('UPDATE exp_playa_relationships SET parent_is_draft = 0 WHERE parent_is_draft <> 1');
			}

				// update the module version number
			$this->EE->db->where('module_name', 'Playa')
			             ->update('modules', array('module_version' => PLAYA_VER));

			// update the extension version number
			$this->EE->db->where('class', 'Playa_ext')
			             ->update('extensions', array('version' => PLAYA_VER));
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Theme URL
	 */
	private function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url.'playa/';
		}

		return $this->cache['theme_url'];
	}

	/**
	 * Include Theme CSS
	 */
	private function _include_theme_css($file)
	{
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'" />');
	}

	/**
	 * Include Theme JS
	 */
	private function _include_theme_js($file)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'"></script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Insert CSS
	 */
	private function _insert_css($css)
	{
		$this->EE->cp->add_to_head('<style type="text/css">'.$css.'</style>');
	}

	/**
	 * Insert JS
	 */
	private function _insert_js($js)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Relationship IDs
	 */
	private function _parse_legacy_rel_ids($data = '', $ignore_closed = FALSE)
	{
		$rel_ids = array();

		$lines = array_filter(preg_split("/[\r\n]+/", $data));
		if (count($lines))
		{
			foreach ($lines as $line)
			{
				if (preg_match('/\[(\!)?(\d+)\]/', $line, $matches))
				{
					if (! $ignore_closed OR ! $matches[1])
					{
						$rel_id = $matches[2];
					}
					else
					{
						continue;
					}
				}
				else
				{
					$rel_id = $line;
				}

				if (is_numeric($rel_id))
				{
					$rel_ids[] = $rel_id;
				}
			}
		}

		return $rel_ids;
	}

	// --------------------------------------------------------------------

	/**
	 * Prepare Params
	 */
	private function _prep_params(&$params)
	{
		// defaults
		$params = array_merge(array(
			'author_id'           => '',
			'backspace'           => '0',
			'category'            => '',
			'category_group'      => '',
			'delimiter'           => '|',
			'dynamic_parameters'  => '',
			'entry_id'            => '',
			'fixed_order'         => '',
			'group_id'            => '',
			'limit'               => '100',
			'offset'              => '0',
			'orderby'             => '',
			'show_expired'        => 'no',
			'show_future_entries' => 'no',
			'sort'                => '',
			'status'              => 'not closed',
			'start_on'            => '',
			'stop_before'         => '',
			'url_title'           => '',
			'weblog'              => ''
		), $params);

		// dynamic params
		if ($params['dynamic_parameters'])
		{
			$dynamic_parameters = explode('|', $params['dynamic_parameters']);
			foreach ($dynamic_parameters as $param)
			{
				if (($val = $this->EE->input->post($param)) !== FALSE)
				{
					$params[$param] = $this->EE->db->escape_str($val);
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Select Options
	 */
	private function _select_options($value, $options)
	{
		$r = '';
		foreach ($options as $option_value => $option_line)
		{
			if (is_array($option_line))
			{
				$r .= '<optgroup label="'.$option_value.'">'."\n"
				    .   $this->_select_options($value, $option_line)
				    . '</optgroup>'."\n";
			}
			else
			{
				$selected = is_array($value) ? in_array($option_value, $value) : ($option_value == $value);
				$r .= '<option value="'.$option_value.'"'.($selected ? ' selected="selected"' : '').'>'.$option_line.'</option>';
			}
		}
		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Global Settings
	 */
	function display_global_settings()
	{
		$this->_update_global_settings();

		// load the language file
		$this->EE->lang->loadfile('playa');

		// load the table lib
		$this->EE->load->library('table');

		// load the CSS
		$this->_include_theme_css('styles/global_settings.css');

		// use the default template known as
		// $cp_pad_table_template in the views
		$this->EE->table->set_template(array(
			'table_open'    => '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">',
			'row_start'     => '<tr class="even">',
			'row_alt_start' => '<tr class="odd">'
		));

		// "Preference" and "Setting" table headings
		$this->EE->table->set_heading(array('data' => lang('preference'), 'style' => 'width: 50%'), lang('setting'));

		// -------------------------------------------
		//  License Key
		// -------------------------------------------

		$this->EE->table->add_row(
			lang('license_key', 'license_key'),
			form_input('license_key', $this->settings['license_key'], 'id="license_key" size="40"')
		);

		// -------------------------------------------
		//  Filter Min
		// -------------------------------------------

		$this->EE->table->add_row(
			lang('filter_min', 'filter_min').'<br/>'.lang('filter_min_desc'),
			form_input('filter_min', $this->settings['filter_min'], 'id="filter_min" size="3" style="width: 3em;"')
		);

		// -------------------------------------------
		//  Relationship field conversion
		// -------------------------------------------

		// Accomodate the schema changes
		if (version_compare(APP_VER, '2.6', '<'))
		{
			$field_types = array('"rel"', '"mrel"');
		}
		else
		{
			$field_types = array('"relationship"');
		}

		$field_types = implode(",", $field_types);

		$query = $this->EE->db->query('SELECT cf.field_id, cf.field_label, fg.group_name
		                               FROM exp_channel_fields AS cf
		                               INNER JOIN exp_field_groups AS fg ON cf.group_id = fg.group_id
		                               WHERE cf.field_type IN (' . $field_types . ')');

		if ($query->num_rows())
		{
			// load the Admin Content lang file
			$this->EE->lang->loadfile('admin_content');

			// initialize a new Table object
			$table = new CI_Table();

			// set the template
			$table->set_template(array(
				'table_open' => '<table class="playaConvertRel" border="0" cellspacing="0" cellpadding="0"'
			));

			// "Field Group", "Field Name", and "Convert?" table headings
			$table->set_heading(lang('field_group'), lang('field_name'), lang('convert'));

			// add each of the Rel fields
			foreach ($query->result() as $field)
			{
				$table->add_row(
					$field->group_name,
					$field->field_label,
					form_radio('convert_rel_field['.$field->field_id.']', 'y', FALSE, 'id="convert_rel_field_'.$field->field_id.'_y"') . NL
						. lang('yes', 'convert_rel_field_'.$field->field_id.'_y') . NBS.NBS.NBS.NBS.NBS . NL
						. form_radio('convert_rel_field['.$field->field_id.']', 'n', TRUE, 'id="convert_rel_field_'.$field->field_id.'_n"') . NL
						. lang('no', 'convert_rel_field_'.$field->field_id.'_n')
				);
			}

			// add the row to the main table
			$this->EE->table->add_row(
				lang('convert_rel_fields', 'convert_rel_fields').'<br/>'
					. lang('convert_rel_fields_info'),
				$table->generate()
			);
		}

		// -------------------------------------------
		//  Related Entries conversion
		// -------------------------------------------

		if ($this->EE->db->table_exists('related_entries'))
		{
			// Convert Related Entries?
			$this->EE->table->add_row(
				lang('convert_related_entries', 'convert_related_entries_y').'<br/>'
					. lang('convert_related_entries_info'),
				form_radio('convert_related_entries', 'y', FALSE, 'id="convert_related_entries_y"') . NL
					. lang('yes', 'convert_related_entries_y') . NBS.NBS.NBS.NBS.NBS . NL
					. form_radio('convert_related_entries', 'n', TRUE, 'id="convert_related_entries_n"') . NL
					. lang('no', 'convert_related_entries_n')
			);
		}

		return $this->EE->table->generate();
	}

	/**
	 * Save Global Settings
	 */
	function save_global_settings()
	{
		$rel_data = array();
		$old_rel_ids = array();

		// -------------------------------------------
		//  Relationship field conversion
		// -------------------------------------------

		if ($fields = $this->EE->input->post('convert_rel_field'))
		{
			$field_ids = array();

			foreach ($fields as $field_id => $convert)
			{
				if ($convert == 'y') $field_ids[] = $field_id;
			}

			if ($field_ids)
			{
				// get the rel fields marked for conversion
				$fields = $this->EE->db->select('*')
				                       ->where_in('field_id', $field_ids)
				                       ->get('channel_fields');

				foreach ($fields->result() as $field)
				{
					// -------------------------------------------
					//  Update the field settings
					// -------------------------------------------

					if (version_compare(APP_VER, '2.6', '<'))
					{
						$field_settings = array(
							'multi'    => ($field->field_type == 'mrel' ? 'y' : 'n'),
							'channels' => array($field->field_related_id),
							'limit'    => $field->field_related_max,
							'orderby'  => $field->field_related_orderby,
							'sort'     => strtoupper($field->field_related_sort)
						);
					}
					else
					{
						$current_settings = unserialize(base64_decode($field->field_settings));
						$field_settings = array(
							'multi'    => $current_settings['allow_multiple'] ? 'y' : 'n',
							'channels' => $current_settings['channels'],
							'limit'    => $current_settings['limit'],
							'orderby'  => $current_settings['order_field'],
							'sort'     => strtoupper($current_settings['order_dir'])
						);
					}

					$data = array(
						'field_type' => 'playa',
						'field_settings' => base64_encode(serialize($field_settings))
					);

					$this->EE->db->where('field_id', $field->field_id)
					             ->update('channel_fields', $data);

					// -------------------------------------------
					//  Convert the relationships
					// -------------------------------------------

					$field_name = 'field_id_'.$field->field_id;

					// is this a Multi Relationship field?
					if ($field->field_type == 'mrel')
					{
						// first we need to get all of the entry data
						$entries = $this->EE->db->select("entry_id, {$field_name}")
						                        ->where("{$field_name} !=", '')
						                        ->get('channel_data');

						$rel_ids = array();

						foreach ($entries->result() as $entry)
						{
							// get the rel_id's
							$rel_ids = array_merge($rel_ids, array_filter(preg_split("/[\r\n]+/", $entry->$field_name)));
						}

						if ($rel_ids)
						{
							if (version_compare(APP_VER, '2.6', '<'))
							{
								// get the old relationships
								$rels = $this->EE->db->select('rel_id, rel_parent_id, rel_child_id')
													 ->where_in('rel_id', $rel_ids)
													 ->get('relationships');
							}
							else
							{
								// get the old relationships
								$rels = $this->EE->db->select('relationship_id, parent_id, child_id')
									->where_in('relationship_id', $rel_ids)
									->get('relationships');
							}
						}
					}
					else
					{
						// get the old relationships
						if (version_compare(APP_VER, '2.6', '<'))
						{
							$rels = $this->EE->db->query('SELECT r.rel_id, r.rel_parent_id, r.rel_child_id
						                              FROM exp_relationships AS r
						                              INNER JOIN exp_channel_data AS cd ON cd.'.$field_name.' = r.rel_id');
						}
						else
						{
							$rels = $this->EE->db->query('SELECT r.relationship_id, r.parent_id, r.child_id
						                              FROM exp_relationships AS r
						                              WHERE r.field_id = '.$field->field_id);

						}
					}

					if (isset($rels) && $rels)
					{
						foreach ($rels->result() as $rel)
						{

							if (version_compare(APP_VER, '2.6', '<'))
							{
								$rel_data[] = array(
									'parent_entry_id' => $rel->rel_parent_id,
									'parent_field_id' => $field->field_id,
									'child_entry_id'  => $rel->rel_child_id
								);
								$old_rel_ids[] = $rel->rel_id;
							}
							else
							{
								$rel_data[] = array(
									'parent_entry_id' => $rel->parent_id,
									'parent_field_id' => $field->field_id,
									'child_entry_id'  => $rel->child_id
								);
								$old_rel_ids[] = $rel->relationship_id;
							}

							// mark the rel_id for deletion

						}
					}
				}
			}
		}

		// -------------------------------------------
		//  Related Entries Conversion
		// -------------------------------------------

		if ($this->EE->db->table_exists('related_entries') && $this->EE->input->post('convert_related_entries') == 'y')
		{
			// get the field groups
			$query = $this->EE->db->query('SELECT fg.group_id, fg.site_id, fg.group_name,
			                                 (SELECT MAX(cf.field_order) FROM exp_channel_fields AS cf WHERE group_id = fg.group_id) AS field_order
			                               FROM exp_field_groups AS fg');

			if ($query->num_rows())
			{
				$groups = array();

				foreach ($query->result_array() as $group)
				{
					$groups[$group['group_id']] = $group;
				}

				// get all of the related entries
				$query = $this->EE->db->query('SELECT re.entry_id, re.related_entry_id, re.main, c.field_group
				                               FROM exp_related_entries AS re
				                               INNER JOIN exp_channel_titles AS ct ON re.entry_id = ct.entry_id
				                               INNER JOIN exp_channels AS c ON ct.channel_id = c.channel_id');

				foreach ($query->result() as $rel)
				{
					$group =& $groups[$rel->field_group];

					// -------------------------------------------
					//  Create the Playa field
					// -------------------------------------------

					if (! isset($group['playa_field_id']))
					{
						// come up with a unique field name
						$base_field_name = trim(preg_replace('/([^\w]|_)+/', '_', strtolower($group['group_name'])), '_') . '_related_entries';

						for ($i = 0; $i < 50; $i++)
						{
							$field_name = $base_field_name . ($i ? $i : '');

							$count = $this->EE->db->select('COUNT(*) AS count')
							                      ->where('field_name', $field_name)
							                      ->where('site_id', $group['site_id'])
							                      ->get('channel_fields')
							                      ->row('count');

							if (! $count) break;
						}

						// add the field
						$data = array(
							'site_id'     => $group['site_id'],
							'group_id'    => $rel->field_group,
							'field_name'  => $field_name,
							'field_label' => trim($group['group_name']) . ' Related Entries',
							'field_type'  => 'playa',
							'field_order' => ($group['field_order'] + 1)
						);

						$this->EE->db->insert('channel_fields', $data);

						// get the new field_id
						$field_id = $this->EE->db->insert_id();

						// add the field formatting
						$this->EE->db->insert('field_formatting', array(
							'field_id'  => $field_id,
							'field_fmt' => 'none'
						));

						// add the columns
						$this->EE->load->dbforge();
						$fields = $this->settings_modify_column(array_merge($data, array(
							'field_id' => $field_id,
							'ee_action' => 'add'
						)));
						$this->EE->dbforge->add_column('channel_data', $fields);

						// save the field id
						$group['playa_field_id'] = $field_id;
					}

					// -------------------------------------------
					//  Add this relationship
					// -------------------------------------------

				 	$rel_data[] = array(
						'parent_entry_id' => $rel->entry_id,
						'parent_field_id' => $group['playa_field_id'],
						'child_entry_id'  => $rel->related_entry_id,
						'rel_order'       => ($rel->main ? 0 : 1)
					);
				}
			}
		}

		// -------------------------------------------
		//  Create the new relationships
		// -------------------------------------------

		if ($rel_data)
		{
			$this->EE->db->insert_batch('playa_relationships', $rel_data);
		}

		// -------------------------------------------
		//  Delete the old relationships
		// -------------------------------------------

		if ($old_rel_ids)
		{
			if (version_compare(APP_VER, '2.6', '<'))
			{
				$this->EE->db->where_in('rel_id', $old_rel_ids)
					->delete('relationships');
			}
			else
			{
				$this->EE->db->where_in('relationship_id', $old_rel_ids)
					->delete('relationships');
			}
		}

		// -------------------------------------------
		//  Return the actual global settings
		// -------------------------------------------

		return array(
			'license_key'   => $this->EE->input->post('license_key'),
			'filter_min' => $this->EE->input->post('filter_min')
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Update settings
	 */
	private function _update_settings(&$settings)
	{
		if (isset($settings['show_filters']))
		{
			unset($settings['show_filters']);
		}

		if (isset($settings['blogs']))
		{
			$settings['channels'] = $settings['blogs'];
			unset($settings['blogs']);
		}

		if (isset($settings['ui_mode']))
		{
			$settings['multi'] = ($settings['ui_mode'] != 'select') ? 'y' : 'n';
			unset($settings['ui_mode']);
		}

		// merge in the default settings
		$settings = array_merge(array(
			'multi'			=> 'y',
			'sites'			=> array(),
			'channels'		=> array(),
			'cats'			=> array(),
			'member_groups'	=> array(),
			'authors'		=> array(),
			'statuses'		=> array(),
			'limit'			=> '0',
			'limitby'		=> '',
			'orderby'		=> 'title',
			'sort'			=> 'ASC',
			'expired'		=> 'n',
			'future'		=> 'y',
			'editable'      => 'n',
		), $settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		$rows = $this->_field_settings($data);

		foreach ($rows as $row)
		{
			$this->EE->table->add_row($row[0], $row[1]);
		}
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		return $this->_field_settings($data, TRUE);
	}

	/**
	 * Display Variable Settings
	 * @param array $data
	 */
	function display_var_settings($data)
	{
		if (! $this->var_id)
		{
			return array(
				array('', 'Playa requires Low Variables 1.3.7 or later.')
			);
		}

		return $this->_field_settings($data);
	}

	/**
	 * Field Settings
	 */
	private function _field_settings($data, $is_cell = FALSE)
	{
		// update the settings
		$this->_update_settings($data);

		// load the language file
		$this->EE->lang->loadfile('playa');

		// is this an MSM install?
		$this->msm = ($this->EE->config->item('multiple_sites_enabled') == 'y');

		// get the limit options
		$limit_options['0'] = lang('all');
		foreach ($this->limit_options as $limit)
		{
			$limit_options[(string)$limit] = $limit;
		}

		// Allow multiple selections?
		$r[] = array(
			lang('allow_multiple_selections', 'playa_multi') . ($is_cell ? '' : '<br/>'.lang('multi_info')),
			form_radio('playa[multi]', 'y', ($data['multi'] == 'y'), 'id="playa_multi_y"') . NL
				. lang('yes', 'playa_multi_y') . NBS.NBS.NBS.NBS.NBS . NL
				. form_radio('playa[multi]', 'n', ($data['multi'] != 'y'), 'id="playa_multi_n"') . NL
				. lang('no', 'playa_multi_n')
		);

		// Show expired entries?
		$r[] = array(
			lang('show_expired_entries', 'playa_expired'),
			form_radio('playa[expired]', 'y', ($data['expired'] == 'y'), 'id="playa_expired_y"') . NL
				. lang('yes', 'playa_expired_y') . NBS.NBS.NBS.NBS.NBS . NL
				. form_radio('playa[expired]', 'n', ($data['expired'] != 'y'), 'id="playa_expired_n"') . NL
				. lang('no', 'playa_expired_n')
		);

		// Show future entries?
		$r[] = array(
			lang('show_future_entries', 'playa_future'),
			form_radio('playa[future]', 'y', ($data['future'] == 'y'), 'id="playa_future_y"') . NL
				. lang('yes', 'playa_future_y') . NBS.NBS.NBS.NBS.NBS . NL
				. form_radio('playa[future]', 'n', ($data['future'] != 'y'), 'id="playa_future_n"') . NL
				. lang('no', 'playa_future_n')
		);

		// Only show editable entries?
		$r[] = array(
			lang('only_show_editable_entries', 'playa_editable') . ($is_cell ? '' : '<br/>'.lang('only_show_editable_entries_info')),
			form_radio('playa[editable]', 'y', ($data['editable'] == 'y'), 'id="playa_editable_y"') . NL
				. lang('yes', 'playa_editable_y') . NBS.NBS.NBS.NBS.NBS . NL
				. form_radio('playa[editable]', 'n', ($data['editable'] != 'y'), 'id="playa_editable_n"') . NL
				. lang('no', 'playa_editable_n')
		);

		// Sites
		if ($this->msm)
		{
			$r[] = array(
				lang('sites', 'playa_sites'),
				$this->_sites_select($data['sites'])
			);
		}

		// Channels
		$r[] = array(
			lang('channels', 'playa_channels'),
			$this->_channels_select($data['channels'])
		);

		// Categories
		$r[] = array(
			lang('cats', 'playa_cats'),
			$this->_cats_select($data['cats'])
		);

		// Member groups
		$r[] = array(
			lang('member_groups', 'playa_member_groups'),
			$this->_member_groups_select($data['member_groups'])
		);

		// Authors
		$r[] = array(
			lang('authors', 'playa_authors'),
			$this->_authors_select($data['authors'])
		);

		// Statuses
		$r[] = array(
			lang('statuses', 'playa_statuses'),
			$this->_statuses_select($data['statuses'])
		);

		// Limit entries to
		$r[] = array(
			lang('limit_entries_to', 'playa_limit'),
			'<select name="playa[limit]" onchange="this.nextSibling.style.visibility=this.value==\'0\'?\'hidden\':\'visible\';">'
			.   $this->_select_options($data['limit'], $limit_options)
			. '</select>'
			. '<select name="playa[limitby]" style="margin-left: 4px;'.($data['limitby'] ? '' : ' visibility:hidden;').'">'
			.   $this->_select_options($data['limitby'], array('newest'=>lang('newest_entries'), 'oldest'=>lang('oldest_entries')))
			. '</select>'
		);

		// Order entries by
		$r[] = array(
			lang('order_entries_by', 'playa_order'),
			'<select name="playa[orderby]">'
			.   $this->_select_options($data['orderby'], array('title'=>lang('entry_title'), 'entry_date'=>lang('entry_date')))
			. '</select>'
			. ' in '
			. '<select name="playa[sort]">'
			.   $this->_select_options($data['sort'], array('ASC'=>lang('asc_order'), 'DESC'=>lang('desc_order')))
			. '</select>'
		);

		unset($this->msm);

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Sites Multi-select
	 */
	private function _sites_select($selected_sites)
	{
		$sites = $this->EE->db->query('SELECT site_id AS `id`, site_label AS `title`
		                               FROM exp_sites
		                               ORDER BY site_label')
		                       ->result_array();

		// add Current option
		array_unshift($sites, array('id' => 'current', 'title' => '&mdash; '.lang('current').' &mdash;'));

		return $this->_field_settings_select('sites', $sites, $selected_sites, TRUE);
	}

	/**
	 * Channels Multi-select
	 */
	private function _channels_select($selected_channels)
	{
		$site_id = $this->EE->config->item('site_id');

		$channels = $this->EE->db->query('SELECT c.channel_id AS `id`, c.channel_title AS `title`, s.site_label AS `group`
		                                  FROM exp_channels c, exp_sites s
		                                  WHERE s.site_id = c.site_id
		                                        '.($this->msm ? '' : 'AND s.site_id = "'.$site_id.'"').'
		                                  ORDER BY s.site_label, c.channel_title ASC')
		                         ->result_array();

		// add Current option
		array_unshift($channels, array('id' => 'current', 'title' => '&mdash; '.lang('current').' &mdash;'));

		return $this->_field_settings_select('channels', $channels, $selected_channels, TRUE, $this->msm);
	}

	/**
	 * Categories Select
	 */
	private function _cats_select($selected_cats)
	{
		$site_id = $this->EE->config->item('site_id');

		$cats = $this->EE->db->query('SELECT c.cat_id AS `id`, c.cat_name AS `title`, c.parent_id, cg.group_name AS `group`
		                              FROM exp_categories c, exp_category_groups cg
		                              WHERE c.group_id = cg.group_id
		                                    '.($this->msm ? '' : 'AND c.site_id = "'.$site_id.'"').'
		                              ORDER BY cg.group_name, c.cat_order');

		if ($cats->num_rows())
		{
			// group cats by parent_id
			$cats_by_parent = $this->_cats_by_parent($cats->result_array());

			// flatten into sorted and indented options
			$this->_cats_select_options($cats_options, $cats_by_parent);

			return $this->_field_settings_select('cats', $cats_options, $selected_cats);
		}

		return lang('no_cats');
	}

		/**
		 * Group categories by parent_id
		 */
		private function _cats_by_parent($cats)
		{
			$cats_by_parent = array();

			foreach ($cats as $cat)
			{
				if (! isset($cats_by_parent[$cat['parent_id']]))
				{
					$cats_by_parent[$cat['parent_id']] = array();
				}

				$cats_by_parent[$cat['parent_id']][] = $cat;
			}

			return $cats_by_parent;
		}

		/**
		 * Category Options
		 */
		private function _cats_select_options(&$cats=array(), &$cats_by_parent, $parent_id='0', $indent='')
		{
			foreach ($cats_by_parent[$parent_id] as $cat)
			{
				$cat['title'] = $indent.$cat['title'];
				$cats[] = $cat;
				if (isset($cats_by_parent[$cat['id']]))
				{
					$this->_cats_select_options($cats, $cats_by_parent, (string)$cat['id'], $indent.NBS.NBS.NBS.NBS);
				}
			}
		}

	/**
	 * Authors Select
	 */
	private function _authors_select($selected_authors)
	{
		$site_id = $this->EE->config->item('site_id');

		$authors = $this->EE->db->query('SELECT m.member_id AS `id`, m.screen_name AS `title`, mg.group_title AS `group`
		                                 FROM exp_members m, exp_member_groups mg
		                                 WHERE m.group_id = mg.group_id
		                                       AND mg.can_access_publish = "y"
		                                       '.($this->msm ? '' : 'AND mg.site_id = "'.$site_id.'"').'
		                                 GROUP BY m.member_id
		                                 ORDER BY mg.group_title, m.screen_name')
		                        ->result_array();

		// add Current option
		array_unshift($authors, array('id' => 'current', 'title' => '&mdash; '.lang('current').' &mdash;'));

		return $this->_field_settings_select('authors', $authors, $selected_authors);
	}

	/**
	 * Member groups Select
	 */
	private function _member_groups_select($selected_groups)
	{
		$site_id = $this->EE->config->item('site_id');

		$groups = $this->EE->db->query('SELECT DISTINCT(mg.group_id) AS `id`, mg.group_title AS `title`
		                                 FROM exp_member_groups AS mg
		                                 WHERE mg.can_access_publish = "y"
		                                       '.($this->msm ? '' : 'AND mg.site_id = "'.$site_id.'"').'
		                                 ORDER BY mg.group_title')
			->result_array();

		// add Current option
		array_unshift($groups, array('id' => 'current', 'title' => '&mdash; '.lang('current').' &mdash;'));

		return $this->_field_settings_select('member_groups', $groups, $selected_groups);
	}


	/**
	 * Statuses Select
	 */
	private function _statuses_select($selected_statuses)
	{
		$site_id = $this->EE->config->item('site_id');

		$statuses = $this->EE->db->query('SELECT s.status AS `id`, s.status AS `title`, sg.group_name AS `group`
		                                  FROM exp_statuses s, exp_status_groups sg
		                                  WHERE s.group_id = sg.group_id
		                                        AND s.status NOT IN ("open", "closed")
		                                        '.($this->msm ? '' : 'AND s.site_id = "'.$site_id.'"').'
		                                  ORDER BY sg.group_name, s.status_order');

		$rows = array_merge(array(
			array('id' => 'open', 'title' => 'Open'),
			array('id' => 'closed', 'title' => 'Closed')
		), $statuses->result_array());

		return $this->_field_settings_select('statuses', $rows, $selected_statuses);
	}

	/**
	 * Field Settings Select
	 */
	private function _field_settings_select($name, $rows, $selected_ids, $multi = TRUE, $optgroups = TRUE)
	{
		$options = $this->_field_settings_select_options($rows, $selected_ids, $optgroups, $row_count);

		return '<select name="playa['.$name.'][]" multiple="multiple" class="multiselect" size="'.($row_count < 10 ? $row_count : 10).'" style="width: 230px">'
		       . $options
		       . '</select>';
	}

	/**
	 * Select Options
	 */
	private function _field_settings_select_options($rows, $selected_ids = array(), $optgroups = TRUE, &$row_count = 0)
	{
		if ($optgroups) $optgroup = '';
		$options = '<option value="any"'.($selected_ids ? '' : ' selected="selected"').'>&mdash; '.lang('any').' &mdash;</option>';
		$row_count = 1;

		foreach ($rows as $row)
		{
			if ($optgroups && isset($row['group']) && $row['group'] != $optgroup)
			{
				if ($optgroup) $options .= '</optgroup>';
				$options .= '<optgroup label="'.$row['group'].'">';
				$optgroup = $row['group'];
				$row_count++;
			}

			$selected = in_array($row['id'], $selected_ids) ? 1 : 0;
			$options .= '<option value="'.$row['id'].'"'.($selected ? ' selected="selected"' : '').'>'.$row['title'].'</option>';
			$row_count++;
		}

		if ($optgroups && $optgroup) $options .= '</optgroup>';

		return $options;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($data)
	{
		$settings = $this->EE->input->post('playa');

		$this->_validate_settings($settings);

		// cross the T's
		$settings['field_fmt'] = 'none';
		$settings['field_show_fmt'] = 'n';
		$settings['field_type'] = 'playa';

		return $settings;
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		$settings = $settings['playa'];
		$this->_validate_settings($settings);

		return $settings;
	}

	/**
	 * Save Variable Settings
	 */
	function save_var_settings()
	{
		$settings = $this->EE->input->post('playa');
		$this->_validate_settings($settings);

		return $settings;
	}

	/**
	 * Validate Field Settings
	 */
	private function _validate_settings(&$settings)
	{
		// remove any filters that have "Any" selected
		$filters = array('sites', 'channels', 'cats', 'member_groups', 'authors', 'statuses');

		foreach ($filters as $filter)
		{
			if (isset($settings[$filter]) && in_array('any', $settings[$filter]))
			{
				unset($settings[$filter]);
			}
		}

		// remove Limit if set to "All"
		if (isset($settings['limit']) && ! $settings['limit'])
		{
			unset($settings['limit']);
		}

		// remove Limit By if there's no Limit
		if (isset($settings['limitby']) && ! isset($settings['limit']))
		{
			unset($settings['limitby']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Status CSS Snippet
	 */
	private function _status_css($status, $highlight)
	{
		$highlight = ltrim($highlight, '#');
		return '  .playa-entry a span.'.$status.' { color: #'.$highlight.' !important; }' . NL;
	}

	/**
	 * Display Field
	 */
	function display_field($data)
	{
		$this->_update_settings($this->settings);

		// -------------------------------------------
		//  Include Shared Resources
		// -------------------------------------------

		if (! isset($this->cache['included_shared_resources']))
		{
			// CSS
			$css = $this->_status_css('open', '093')
			     . $this->_status_css('closed', '900');

			$statuses = $this->EE->db->query('SELECT status, highlight FROM exp_statuses
			                                  WHERE status NOT IN ("open", "closed")');
			foreach ($statuses->result_array() as $status)
			{
				$css .= $this->_status_css(str_replace(' ', '_', $status['status']), $status['highlight']);
			}

			$this->_insert_css($css);
			$this->_include_theme_css('styles/field.css');

			// Playa Filter Resources
			$query = $this->EE->db->query('SELECT action_id FROM exp_actions WHERE class = "Playa_mcp" AND method = "filter_entries"');
			$this->cache['filter_action_id'] = $query->num_rows() ? $query->row('action_id') : FALSE;

			if ($this->cache['filter_action_id'])
			{
				$this->EE->lang->loadfile('playa');

				if (($site_index = $this->EE->config->item('playa_site_index')) === FALSE) $site_index = $this->EE->functions->fetch_site_index(0, 0);

				$js = 'PlayaFilterResources = {' . NL
				    . '  filterUrl: "'.$site_index.'",' . NL
				    . '  ACT: '.$this->cache['filter_action_id'].',' . NL
				    . '  lang: { is: "'.lang('is').'" }' . NL
				    . '};';

				$this->_insert_js($js);
			}

			$this->cache['included_shared_resources'] = TRUE;

			// View path
			$this->EE->load->add_package_path(PATH_THIRD.'playa');
		}

		// is this a Matrix cell?
		$is_cell = isset($this->cell_name);

		if (! $this->var_id)
		{
			// is this an existing entry?
			$entry_id = $this->EE->input->get('entry_id');
		}

		// have we included the Matrix single select script?
		if ($is_cell && ! isset($this->cache['included_cell_resources']))
		{
			$this->_include_theme_js('scripts/matrix.js');

			$this->cache['included_cell_resources'] = TRUE;
		}

		// -------------------------------------------
		//  Get the selected entry IDs
		// -------------------------------------------

		// autosave data?
		if (is_array($data) && isset($data['selections']))
		{
			$vars['selected_entry_ids'] = array_merge(array_filter($data['selections']));
		}
		else if (! $this->var_id && isset($_POST[$this->field_name]) && isset($_POST[$this->field_name]['selections']) && $_POST[$this->field_name]['selections'])
		{
			$vars['selected_entry_ids'] = $_POST[$this->field_name]['selections'];
		}
		else
		{
			$vars['selected_entry_ids'] = array();

			if (($this->var_id || $entry_id) && (! $is_cell || $this->row_id))
			{
				if ($this->var_id)
				{
					$where = array(
						'parent_var_id' => $this->var_id
					);
				}
				else
				{
					$where = array(
						'parent_entry_id' => $entry_id,
						'parent_field_id' => $this->field_id
					);
				}

				// Matrix?
				if (isset($this->cell_name))
				{
					$where['parent_col_id'] = $this->col_id;
					$where['parent_row_id'] = $this->row_id;
				}

				// -------------------------------------------
				//  'playa_field_selections_query' hook
				//   - Override which entries should appear selected
				//
					if ($this->EE->extensions->active_hook('playa_field_selections_query'))
					{
						$rels = $this->EE->extensions->call('playa_field_selections_query', $this, $where);
					}
					else
					{
						$rels = $this->EE->db->select('child_entry_id')
						                     ->where($where)
						                     ->order_by('rel_order')
						                     ->get('playa_relationships');
					}
				//
				// -------------------------------------------

				foreach ($rels->result() as $rel)
				{
					$vars['selected_entry_ids'][] = $rel->child_entry_id;
				}
			}
		}

		// -------------------------------------------
		//  Selections list
		//   - Since the selections aren't necessarily in the options list,
		//     we need to run an additional query here
		// -------------------------------------------

		$vars['selected_entries'] = array();

		if ($vars['selected_entry_ids']
			&& ($query = $this->EE->db->query('SELECT * FROM exp_channel_titles
		                                       WHERE entry_id '.$this->helper->param2sql($vars['selected_entry_ids'])))
			&& $query->num_rows()
		)
		{
			foreach ($query->result() as $entry)
			{
				$key = array_search($entry->entry_id, $vars['selected_entry_ids']);
				$vars['selected_entries'][$key] = $entry;
			}

			ksort($vars['selected_entries']);
		}

		// -------------------------------------------
		//  Is this a cloned entry?
		// -------------------------------------------

		if ($this->EE->input->get('clone') == 'y')
		{
			$old_data = '';
		}

		// -------------------------------------------
		//  Current channel?
		// -------------------------------------------

		if (($key = array_search('current', $this->settings['channels'])) !== FALSE)
		{
			array_splice($this->settings['channels'], $key, 1);

			// get the current channel's ID
			$channel_id = $this->EE->input->get('channel_id');

			// add the ID if it's not already there
			if ($channel_id && ! in_array($channel_id, $this->settings['channels']))
			{
				$this->settings['channels'][] = $channel_id;
			}
		}

		// -------------------------------------------
		//  Editable channel?
		// -------------------------------------------

		if ($this->settings['editable'] == 'y')
		{
			$group_id = $this->EE->session->userdata('group_id');

			if ($group_id != 1)
			{
				$query = $this->EE->db->query('SELECT channel_id FROM exp_channel_member_groups
					                           WHERE group_id = '.$group_id);

				if ($query->num_rows())
				{
					foreach ($query->result() as $channel)
					{
						$group_channels[] = $channel->channel_id;
					}

					if ($this->settings['channels'])
					{
						$this->settings['channels'] = array_merge(array_intersect($this->settings['channels'], $group_channels));
					}
					else
					{
						$this->settings['channels'] = $group_channels;
					}
				}

				if (empty($this->settings['channels']))
				{
					$this->settings['channels'] = array(0);
				}
			}
		}

		// -------------------------------------------
		//  Current group?
		// -------------------------------------------

		if (($key = array_search('current', $this->settings['member_groups'])) !== FALSE)
		{
			// just show all authors if this is a super admin and config['playa_sa_all_authors'] is set to TRUE
			if ($this->EE->session->userdata('group_id') == 1 && $this->EE->config->item('playa_sa_all_authors'))
			{
				$this->settings['member_groups'] = array();
			}
			else
			{
				// remove the 'current' value
				array_splice($this->settings['member_groups'], $key, 1);

				// if this is an existing entry, get the author's group id
				if ($entry_id)
				{
					$group_id = $this->EE->db->select('group_id')
						->from('channel_titles')
						->where('entry_id', $entry_id)
						->join('members', 'channel_titles.author_id = members.member_id')
						->limit(1)
						->get()
						->row('group_id');
				}
				// otherwise use the current member's group ID
				else
				{
					$group_id = $this->EE->session->userdata('group_id');
				}

				// get all groups available to this user

				// add the ID if it's not already there
				if (! in_array($group_id, $this->settings['member_groups']))
				{
					$this->settings['member_groups'][] = $group_id;
				}
			}
		}

		// -------------------------------------------
		//  Current author?
		// -------------------------------------------

		if (($key = array_search('current', $this->settings['authors'])) !== FALSE)
		{
			// just show all authors if this is a super admin and config['playa_sa_all_authors'] is set to TRUE
			if ($this->EE->session->userdata('group_id') == 1 && $this->EE->config->item('playa_sa_all_authors'))
			{
				$this->settings['authors'] = array();
			}
			else
			{
				// remove the 'current' value
				array_splice($this->settings['authors'], $key, 1);

				// if this is an existing entry, use the entry's author
				if ($entry_id)
				{
					$author_id = $this->EE->db->select('author_id')
					                          ->where('entry_id', $entry_id)
					                          ->limit(1)
					                          ->get('channel_titles')
					                          ->row('author_id');
				}
				// otherwise use the current member's ID
				else
				{
					$author_id = $this->EE->session->userdata('member_id');
				}

				// add the ID if it's not already there
				if (! in_array($author_id, $this->settings['authors']))
				{
					$this->settings['authors'][] = $author_id;
				}
			}
		}

		// -------------------------------------------
		//  Get Total Possible Entries
		// -------------------------------------------

		// flatten the array settings
		$flat_channels		= implode('|', $this->settings['channels']);
		$flat_cats			= implode('|', $this->settings['cats']);
		$flat_member_gorups	= implode('|', $this->settings['authors']);
		$flat_authors		= implode('|', $this->settings['member_groups']);
		$flat_statuses		= implode('|', $this->settings['statuses']);

		// cached?
		$cache_key = $this->settings['expired']
		           . $this->settings['future']
		           . $this->settings['editable']
		           . $flat_channels . ','
		           . $flat_cats          . ','
				   . $flat_member_gorups . ','
		           . $flat_authors       . ','
		           . $flat_statuses;

		if (isset($this->cache['total_entries'][$cache_key]))
		{
			$vars['total_entries'] = $this->cache['total_entries'][$cache_key];
		}
		else
		{
			$vars['total_entries'] = $this->helper->entries_query(array(
				'count'               	     => TRUE,
				'show_expired'        	     => ($this->settings['expired'] == 'y' ? 'yes' : ''),
				'show_future_entries'        => ($this->settings['future'] == 'y' ? 'yes' : ''),
				'only_show_editable_entries' => ($this->settings['editable'] == 'y' ? 'yes' : ''),
				'channel_id'          	     => $this->settings['channels'],
				'category'                   => $this->settings['cats'],
				'member_groups'              => $this->settings['member_groups'],
				'author_id'           	     => $this->settings['authors'],
				'status'              	     => $this->settings['statuses'],
				'site_id'                    => (isset($this->settings['sites']) ? $this->settings['sites'] : null)
			));

			// cache it for later
			$this->cache['total_entries'][$cache_key] = $vars['total_entries'];
		}

		// no entries?
		if (! $vars['total_entries'])
		{
			$this->EE->lang->loadfile('content');
			return lang('no_related_entries');
		}

		// -------------------------------------------
		//  Field config stuff
		// -------------------------------------------

		$cache_key .= $this->settings['limit']   . ','
		            . $this->settings['limitby'] . ','
		            . $this->settings['orderby'] . ','
		            . $this->settings['sort'];

		if (isset($this->cache['configs'][$cache_key]))
		{
			$vars['entries'] =& $this->cache['configs'][$cache_key]['entries'];
			$opts['defaults'] =& $this->cache['configs'][$cache_key]['default_opts'];
		}
		else
		{
			// -------------------------------------------
			//  Get the initial set of entries
			// -------------------------------------------

			$params = array(
				'show_expired'        	     => ($this->settings['expired'] == 'y' ? 'yes' : ''),
				'show_future_entries' 	     => ($this->settings['future'] == 'y' ? 'yes' : ''),
				'only_show_editable_entries' => ($this->settings['editable'] == 'y' ? 'yes' : ''),
				'channel_id'          	     => $this->settings['channels'],
				'category'            	     => $this->settings['cats'],
				'member_groups'              => $this->settings['member_groups'],
				'author_id'           	     => $this->settings['authors'],
				'status'              	     => $this->settings['statuses'],
				'site_id'                    => (isset($this->settings['sites']) ? $this->settings['sites'] : null)
			);

			if ($this->settings['limit'])
			{
				$params['orderby'] = 'entry_date';
				$params['sort'] = $this->settings['limitby'] == 'newest' ? 'DESC' : 'ASC';
				$params['limit'] = $this->settings['limit'];
			}
			else
			{
				$params['orderby'] = $this->settings['orderby'];
				$params['sort'] = $this->settings['sort'];
			}

			// run the query
			$vars['entries'] = $this->helper->entries_query($params);

			// if we used ORDER BY for initial limiting,
			// manually sort the entries here
			if ($this->settings['limitby'])
			{
				$this->helper->sort_entries($vars['entries'], $this->settings['sort'], $this->settings['orderby']);
			}

			// cache it for later
			$this->cache['configs'][$cache_key]['entries'] =& $vars['entries'];

			// -------------------------------------------
			//  Put together the default JS options
			// -------------------------------------------

			$opts['defaults']['expired'] = $this->settings['expired'];
			$opts['defaults']['future']  = $this->settings['future'];
			$opts['defaults']['editable']  = $this->settings['future'];

			if ($flat_channels) $opts['defaults']['channel']  = $flat_channels;
			if ($flat_cats)     $opts['defaults']['category'] = $flat_cats;
			if ($flat_authors)  $opts['defaults']['author']   = $flat_authors;
			if ($flat_statuses) $opts['defaults']['status']   = $flat_statuses;

			$opts['defaults']['limit']   = $this->settings['limit'];
			$opts['defaults']['limitby'] = $this->settings['limitby'];
			$opts['defaults']['orderby'] = $this->settings['orderby'];
			$opts['defaults']['sort']    = $this->settings['sort'];

			// cache it for later
			$this->cache['configs'][$cache_key]['default_opts'] =& $opts['defaults'];
		}

		// -------------------------------------------
		//  Pass everything off to the UI function
		// -------------------------------------------

		// use the appropriate field name
		$vars['field_name'] = $is_cell ? $this->cell_name : $this->field_name;
		$vars['field_id']   = str_replace(array('[', ']'), array('_', ''), $vars['field_name']);

		if (! $is_cell)
		{
			$opts['fieldName'] = $vars['field_name'];
		}

		if ($is_cell)
		{
			$vars['margin'] = '3px 0';
		}
		else if ($this->var_id)
		{
			$vars['margin'] = '0';
		}
		else
		{
			$vars['margin'] = '11px 0 1px';
		}

		// return the appropriate display function
		$func = '_display_field_' . ($this->settings['multi'] == 'y' ? 'droppanes' : 'singleselect');
		return $this->$func($data, $vars, $opts, $is_cell);
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		return $this->display_field($data);
	}

	/**
	 * Display Variable Field
	 * @param string $data
	 * @return string
	 */
	function display_var_field($data)
	{
		if (! $this->var_id) return;

		return $this->display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field - Drop panes
	 */
	private function _display_field_droppanes($data, $vars, $opts, $is_cell)
	{
		$this->_update_global_settings();

		// -------------------------------------------
		//  Include Drop Panes Resources
		// -------------------------------------------

		if (! isset($this->cache['included_droppanes_resources']))
		{
			// load the CSS and JS
			$this->_include_theme_css('styles/droppanes.css');
			$this->_include_theme_js('scripts/droppanes.js');

			$this->cache['included_droppanes_resources'] = TRUE;
		}

		// -------------------------------------------
		//  Filter Bar
		// -------------------------------------------

		// should we display the filters?
		if ($vars['total_entries'] >= $this->settings['filter_min'] && $this->cache['filter_action_id'])
		{
			if (! $is_cell || ! isset($this->cache['initialized_cols'][$this->col_id]))
			{
				// MSM?
				$msm = ($this->EE->config->item('multiple_sites_enabled') == 'y');
				$site_id = $this->EE->config->item('site_id');

				$cat_groups = array();
				$status_groups = array();

				// -------------------------------------------
				//  Sites
				// -------------------------------------------

				if ($msm)
				{
					$sites = $this->EE->db->query('SELECT site_id AS `id`, site_label AS `title`
					                              FROM exp_sites
					                              ORDER BY site_label')->result_array();

					if (count($sites) > 1)
					{
						$opts['filters']['site'] = array(lang('site'), $this->_field_settings_select_options($sites));
					}
				}

				// -------------------------------------------
				//  Channels
				// -------------------------------------------

				$channels = $this->EE->db->query('SELECT c.channel_id AS `id`, c.channel_title AS `title`, c.cat_group, c.status_group, s.site_label AS `group`
				                                  FROM exp_channels c, exp_sites s
				                                  WHERE s.site_id = c.site_id
				                                        '.($msm ? '' : 'AND s.site_id = "'.$site_id.'"').'
				                                        '.($this->settings['channels'] ? 'AND c.channel_id '.$this->helper->param2sql($this->settings['channels']) : '').'
				                                  ORDER BY s.site_label, c.channel_title ASC')->result_array();

				// remember channel's category groups and status group for later
				foreach ($channels as &$channel)
				{
					if ($channel['cat_group'])    $cat_groups    = array_merge($cat_groups,    explode('|', $channel['cat_group']));
					if ($channel['status_group']) $status_groups = array_merge($status_groups, array($channel['status_group']));

					unset($channel['cat_group']);
					unset($channel['status_group']);
				}

				if (count($channels) > 1)
				{
					$opts['filters']['channel'] = array(lang('channel'), $this->_field_settings_select_options($channels));
				}

				// -------------------------------------------
				//  Categories
				// -------------------------------------------

				if ($cat_groups)
				{
					$cats = $this->EE->db->query('SELECT c.cat_id AS `id`, c.cat_name AS `title`, c.parent_id, cg.group_name AS `group`
					                              FROM exp_categories c, exp_category_groups cg
					                              WHERE c.group_id = cg.group_id
					                                    AND cg.group_id '.$this->helper->param2sql($cat_groups).'
					                              ORDER BY cg.group_name, c.cat_order');

					if ($cats->num_rows())
					{
						// group cats by parent_id
						$cats_by_parent = $this->_cats_by_parent($cats->result_array());

						// flatten into sorted and indented options
						$this->_cats_select_options($cats_options, $cats_by_parent);

						$opts['filters']['category'] = array(lang('category'), $this->_field_settings_select_options($cats_options));
					}
				}

				// -------------------------------------------
				// Member groups
				// -------------------------------------------
				$groups = $this->EE->db->query('SELECT DISTINCT(mg.group_id) AS `id`, mg.group_title AS `title`
		                                 FROM exp_member_groups AS mg
		                                 WHERE mg.can_access_publish = "y"
		                                       '.($msm ? '' : 'AND mg.site_id = "'.$site_id.'"').'
		                                 ORDER BY mg.group_title');
				if ($groups->num_rows())
				{
					$opts['filters']['member_groups'] = array(lang('member_group'), $this->_field_settings_select_options($groups->result_array()));
				}

				// -------------------------------------------
				//  Authors
				// -------------------------------------------

				$authors = $this->EE->db->query('SELECT m.member_id AS `id`, m.screen_name AS `title`, mg.group_title AS `group`
				                                 FROM exp_members m, exp_member_groups mg
				                                 WHERE m.group_id = mg.group_id
				                                       AND mg.can_access_publish = "y"
				                                       '.($msm ? '' : 'AND mg.site_id = "'.$site_id.'"').'
				                                       '.($this->settings['authors'] ? 'AND m.member_id '.$this->helper->param2sql($this->settings['authors']) : '').'
				                                 GROUP BY m.member_id
				                                 ORDER BY mg.group_title, m.screen_name');

				if ($authors->num_rows())
				{
					$opts['filters']['author'] = array(lang('author'), $this->_field_settings_select_options($authors->result_array()));
				}

				// -------------------------------------------
				//  Statuses
				// -------------------------------------------

				$statuses = $this->EE->db->query('SELECT s.status AS `id`, s.status AS `title`, sg.group_name AS `group`
				                                  FROM exp_statuses s, exp_status_groups sg
				                                  WHERE s.group_id = sg.group_id
				                                        AND s.status NOT IN ("open", "closed")
				                                        AND s.group_id '.$this->helper->param2sql($status_groups).'
				                                        '.($msm ? '' : 'AND s.site_id = "'.$site_id.'"').'
				                                  ORDER BY sg.group_name, s.status_order');

				$statuses = array_merge(array(
					array('id' => 'open', 'title' => 'Open'),
					array('id' => 'closed', 'title' => 'Closed')
				), $statuses->result_array());

				$opts['filters']['status'] = array(lang('status'), $this->_field_settings_select_options($statuses));


				$opts_json = $this->helper->get_json($opts);

				if ($is_cell)
				{
					$this->_insert_js('PlayaColOpts.col_id_'.$this->col_id.' = '.$opts_json.';');

					// remember that we've already gone through all of this for this column
					$this->cache['initialized_cols'][$this->col_id] = TRUE;
				}

			}

			$vars['show_filters'] = TRUE;
		}
		else
		{
			$opts_json = '';
			$vars['show_filters'] = FALSE;
		}

		// -------------------------------------------
		//  Insert the JS
		// -------------------------------------------

		if (! $is_cell)
		{
			$this->_insert_js('new PlayaDropPanes(jQuery("#'.$vars['field_id'].'")'.($opts_json ? ', '.$opts_json : '').');');
		}

		// -------------------------------------------
		//  Prepare HTML
		// -------------------------------------------

		$size = (($vars['total_entries'] > $this->drop_panes_size) ? $this->drop_panes_size : $vars['total_entries']);
		$height = $size * 19 + 9;
		if ($height < 50) $height = 50;
		$vars['options_height'] = $vars['selections_height'] = $height;
		if ($vars['show_filters']) $vars['selections_height'] += 34;

		$r = $this->EE->load->view('droppanes', $vars, TRUE);
		return $this->helper->strip_whitespace($r);
	}

	/**
	 * Category Filter Snippet
	 */
	private function _cats_f(&$cats=array(), &$cats_by_parent, $parent_id='0', $indent='')
	{
		foreach ($cats_by_parent[$parent_id] as $cat_id => $cat)
		{
			$cats[$cat_id] = $indent.$cat['cat_name'];
			if (isset($cats_by_parent[$cat_id]))
			{
				$this->_cats_f($cats, $cats_by_parent, "$cat_id", $indent.'    ');
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field - Select
	 */
	private function _display_field_singleselect($data, $vars, $opts, $is_cell)
	{
		// -------------------------------------------
		//  Include Single Select Resources
		// -------------------------------------------

		if (! isset($this->cache['included_singleselect_resources']))
		{
			// load the CSS and JS
			$this->_include_theme_css('styles/singleselect.css');
			$this->_include_theme_js('scripts/singleselect.js');

			$this->cache['included_singleselect_resources'] = TRUE;
		}

		// -------------------------------------------
		//  Insert the JS
		// -------------------------------------------

		$opts_json = $this->helper->get_json($opts);

		if (! $is_cell)
		{
			$this->_insert_js('new PlayaSingleSelect(jQuery("#'.$vars['field_id'].'")'.($opts_json ? ', '.$opts_json : '').');');
		}
		else if (! isset($this->cache['initialized_cols'][$this->col_id]))
		{
			$this->_insert_js('PlayaColOpts.col_id_'.$this->col_id.' = '.$opts_json.';');

			// remember that we've already gone through all of this for this column
			$this->cache['initialized_cols'][$this->col_id] = TRUE;
		}

		// -------------------------------------------
		//  Prepare HTML
		// -------------------------------------------

		$vars['theme_url'] = $this->_theme_url();
		$vars['selected_entry'] = isset($vars['selected_entries'][0]) ? $vars['selected_entries'][0] : NULL;

		$r = $this->EE->load->view('singleselect', $vars, TRUE);
		return $this->helper->strip_whitespace($r);
	}

	// --------------------------------------------------------------------

	/**
	 * Validate
	 */
	function validate($data)
	{
		// is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			// make sure there are selections
			if (! isset($data['selections']) || ! array_filter($data['selections']))
			{
				return lang('required');
			}
		}

		return TRUE;
	}

	/**
	 * Validate Cell
	 */
	function validate_cell($data)
	{
		// is this a required cell?
		if ($this->settings['col_required'] == 'y')
		{
			// make sure there are selections
			if (! isset($data['selections']) || ! array_filter($data['selections']))
			{
				return lang('col_required');
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field
	 */
	function save($data)
	{
		// ignore everything but the selections
		$selections = isset($data['selections']) ? array_merge(array_filter($data['selections'])) : array();

		// save the post data for later
		$this->cache['selections'][$this->field_id] = $selections;

		// return the rel keywords
		$keywords = $this->_get_rel_keywords($selections);
		return $keywords;
	}

	/**
	 * Save Cell
	 */
	function save_cell($data)
	{
		// ignore everything but the selections
		$selections = isset($data['selections']) ? array_merge(array_filter($data['selections'])) : array();

		// save the post data for later
		if ($this->var_id)
		{
			$this->cache['selections'][$this->var_id][$this->settings['col_id']][$this->settings['row_name']] = $selections;
		}
		else
		{
			$this->cache['selections'][$this->field_id][$this->settings['col_id']][$this->settings['row_name']] = $selections;
		}

		// return the rel keywords
		$keywords = $this->_get_rel_keywords($selections);
		return $keywords;
	}

	/**
	 * Discard a BWF draft.
	 */
	public function draft_discard()
	{
		$where = array(
			'parent_entry_id' => $this->settings['entry_id'],
			'parent_is_draft' => 1
		);

		$this->EE->db->where($where)->delete('playa_relationships');
	}

	/**
	 * Publish a BWF draft.
	 */
	public function draft_publish()
	{
		$where = array(
			'parent_entry_id' => $this->settings['entry_id'],
			'parent_is_draft' => 0
		);
		$this->EE->db->where($where)->delete('playa_relationships');

		$where['parent_is_draft'] = 1;
		$update = array('parent_is_draft' => 0);
		$this->EE->db->where($where)->update('playa_relationships', $update);

		return;
	}

	/**
	 * Save a BWF draft.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function draft_save($data)
	{
		$this->field_id = $this->settings['field_id'];
		$this->is_draft = 1;
		$this->post_save($data);
		return $data;
	}

	/**
	 * Post Save
	 */
	function post_save($data)
	{
		// make sure this should have been called in the first place
		if (! isset($this->cache['selections'][$this->field_id])) return;

		// get the selections from the cache
		$selections = $this->cache['selections'][$this->field_id];

		$data = array(
			'parent_entry_id' => $this->settings['entry_id'],
			'parent_field_id' => $this->field_id,
			'parent_is_draft' => $this->is_draft
		);

		// save the changes
		$this->_save_rels($selections, $data);
	}

	/**
	 * Post Save Cell
	 */
	function post_save_cell($data)
	{
		// get the selections from the cache
		if ($this->var_id)
		{
			$selections = $this->cache['selections'][$this->var_id][$this->settings['col_id']][$this->settings['row_name']];
		}
		else
		{
			$selections = $this->cache['selections'][$this->field_id][$this->settings['col_id']][$this->settings['row_name']];
		}

		$data = array(
			'parent_col_id'   => $this->settings['col_id'],
			'parent_row_id'   => $this->settings['row_id'],
			'parent_is_draft' => $this->is_draft
		);

		if ($this->var_id)
		{
			$data['parent_var_id'] = $this->var_id;
			$data['parent_is_draft'] = 0;
		}
		else
		{
			$data['parent_entry_id'] = $this->settings['entry_id'];
			$data['parent_field_id'] = $this->field_id;
		}

		// save the changes
		$this->_save_rels($selections, $data);
	}

	/**
	 * Save Variable Field
	 */
	function save_var_field($data)
	{
		if (! $this->var_id) return;

		// ignore everything but the selections
		$selections = isset($data['selections']) ? array_merge(array_filter($data['selections'])) : array();

		$data = array(
			'parent_var_id' => $this->var_id
		);

		// save the changes
		$this->_save_rels($selections, $data);

		// return the rel keywords
		$keywords = $this->_get_rel_keywords($selections);
		return $keywords;
	}

	/**
	 * Returns the relationship keywords in the format "[EntryId] [UrlTitle] EntryTitle"
	 * which will get saved into exp_channel_data, exp_matrix_data, or exp_global_variables
	 *
	 * @param array $entry_ids The selected entry IDs
	 * @return string
	 */
	private function _get_rel_keywords($entry_ids)
	{
		$keywords = '';

		if ($entry_ids)
		{
			$entries = $this->EE->db->select('entry_id, url_title, title')
			                        ->where_in('entry_id', $entry_ids)
			                        ->get('channel_titles')
			                        ->result();

			foreach ($entries as $entry)
			{
				$keywords .= ($keywords ? "\n" : '') . "[{$entry->entry_id}] [{$entry->url_title}] ".str_replace('\'', '', $entry->title);
			}
		}

		return $keywords;
	}

	/**
	 * Save Relationships
	 */
	private function _save_rels($selections, $data)
	{
		// -------------------------------------------
		//  'playa_save' hook
		//   - Update the $data array before the deletion and insert
		//
			if ($this->EE->extensions->active_hook('playa_save_rels'))
			{
				$data = $this->EE->extensions->call('playa_save_rels', $this, $selections, $data);
			}
		//
		// -------------------------------------------

		// Delete existing relationships
		$this->EE->db->where($data)
		             ->delete('playa_relationships');


		if ($selections)
		{
			foreach ($selections as $rel_order => $child_entry_id)
			{
				$batch_rel_data[] = array_merge($data, array(
					'child_entry_id' => $child_entry_id,
					'rel_order'      => $rel_order
				));
			}

			$this->EE->db->insert_batch('playa_relationships', $batch_rel_data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Modify exp_channel_data Column Settings
	 */
	function settings_modify_column($data)
	{
		if ($data['ee_action'] == 'delete')
		{
			// delete any relationships created by this field
			$this->EE->db->where('parent_field_id', $data['field_id'])
			             ->delete('playa_relationships');
		}

		// just return the default column settings
		return parent::settings_modify_column($data);
	}

	/**
	 * Modify exp_matrix_data Column Settings
	 */
	function settings_modify_matrix_column($data)
	{
		if ($data['matrix_action'] == 'delete')
		{
			// delete any relationships created by this column
			$this->EE->db->where('parent_col_id', $data['col_id'])
			             ->delete('playa_relationships');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Entries
	 */
	function delete($entry_ids)
	{
		// ignore if there are no entries to delete
		if (empty($entry_ids)) return;

		$this->EE->db->where_in('parent_entry_id', $entry_ids)
		             ->or_where_in('child_entry_id', $entry_ids)
		             ->delete('playa_relationships');
	}

	/**
	 * Delete Rows
	 */
	function delete_rows($row_ids)
	{
		// ignore if there are no rows to delete
		if (empty($row_ids)) return;

		$this->EE->db->where_in('parent_row_id', $row_ids)
		             ->delete('playa_relationships');
	}

	/**
	 * Delete Variable
	 */
	function delete_var($var_id)
	{
		$this->EE->db->where('parent_var_id', $var_id)
		             ->delete('playa_relationships');
	}

	// --------------------------------------------------------------------

	/**
	 * Module Tag Alias
	 */
	private function _create_module_tag($params, $tagdata, $func = 'children')
	{
		if ($this->var_id)
		{
			$params['var_id'] = $this->var_id;
		}
		else
		{
			$params['entry_id'] = $this->row['entry_id'];
			$params['field_id'] = $this->field_id;

			// cache the row data
			if (! isset($this->cache['entry_rows'][$this->row['entry_id']]))
			{
				$this->cache['entry_rows'][$this->row['entry_id']] =& $this->row;
			}
		}

		if ($this->row_id)
		{
			$params['col_id'] = $this->col_id;
			$params['row_id'] = $this->row_id;
		}

		// flatten the params
		$params_str = '';

		foreach ($params as $param => $val)
		{
			$params_str .= ' '.$param.'="'.$val.'"';
		}

		return $this->helper->create_module_tag($params_str, $tagdata, $func);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Variable Tag
	 */
	function display_var_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (! $this->var_id) return;

		unset($params['var']);
		return $this->_create_module_tag($params, $tagdata);
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if ($data)
		{
			return $this->_create_module_tag($params, $tagdata);
		}
	}

	/**
	 * Unordered List
	 */
	function replace_ul($data, $params = array())
	{
		if ($data)
		{
			return "<ul>\n"
			     .   $this->_create_module_tag($params, "  <li>{title}</li>\n")
			     . '</ul>';
		}
	}

	/**
	 * Ordered List
	 */
	function replace_ol($data, $params = array())
	{
		if ($data)
		{
			return "<ol>\n"
			     .   $this->_create_module_tag($params, "  <li>{title}</li>\n")
			     . '</ol>';
		}
	}

	/**
	 * Total Children
	 */
	function replace_total_children($data, $params = array(), $tagdata = FALSE)
	{
		if ($data)
		{
			return $this->_create_module_tag($params, $tagdata, 'total_children');
		}
		else
			return '0';
	}

	/**
	 * Child IDs
	 */
	function replace_child_ids($data, $params = array(), $tagdata = FALSE)
	{
		if ($data)
		{
			return $this->_create_module_tag($params, $tagdata, 'child_ids');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Total Related Entries (deprecated)
	 */
	function replace_total_related_entries($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_total_children($data, $params, $tagdata);
	}

	/**
	 * Replace Entry IDs (deprecated)
	 */
	function replace_entry_ids($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_child_ids($data, $params, $tagdata);
	}

}
