<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

// load dependencies
if (! defined('PATH_THIRD')) define('PATH_THIRD', EE_APPPATH.'third_party/');
require_once PATH_THIRD.'assets/config.php';

/**
 * Assets Update
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_upd
{
	var $version = ASSETS_VER;

	/**
	 * Constructor
	 */
	function __construct($switch = TRUE)
	{
		// -------------------------------------------
		//  Make a local reference to the EE super object
		// -------------------------------------------
		$this->EE =& get_instance();
	}

	/**
	 * Install
	 */
	function install()
	{
		$this->EE->load->dbforge();

		// -------------------------------------------
		//  Add row to exp_modules
		// -------------------------------------------

		$this->EE->db->insert('modules', array(
			'module_name'        => ASSETS_NAME,
			'module_version'     => ASSETS_VER,
			'has_cp_backend'     => 'y',
			'has_publish_fields' => 'n'
		));

		// -------------------------------------------
		//  Add rows to exp_actions
		// -------------------------------------------

		// file manager actions
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'upload_file'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_files_view_by_folders'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_props'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'save_props'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_ordered_files_view'));

		// Indexing actions
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_session_id'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'start_index'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'perform_index'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'finish_index'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_s3_buckets'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_gc_buckets'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_rs_containers'));

		// folder/file CRUD actions
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'move_folder'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'rename_folder'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'create_folder'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'delete_folder'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'view_file'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'move_file'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'delete_file'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'view_thumbnail'));

		// field actions
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'build_sheet'));
		$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_selected_files'));

		// -------------------------------------------
		//  Create the exp_assets table
		// -------------------------------------------

		$fields = array(
			'file_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'folder_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'null' => FALSE),
			'source_type'		=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 'ee'),
			'source_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'filedir_id'		=> array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE),
			'file_name'			=> array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE),
			'title'				=> array('type' => 'varchar', 'constraint' => 100),
			'date'				=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'null' => TRUE),
			'alt_text'			=> array('type' => 'tinytext'),
			'caption'			=> array('type' => 'tinytext'),
			'author'			=> array('type' => 'tinytext'),
			'`desc`'			=> array('type' => 'text'),
			'location'			=> array('type' => 'tinytext'),
			'keywords'			=> array('type' => 'text'),
			'date_modified'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'kind'				=> array('type' => 'varchar', 'constraint' => 5),
			'width'				=> array('type' => 'int', 'constraint' => 2),
			'height'			=> array('type' => 'int', 'constraint' => 2),
			'size'				=> array('type' => 'int', 'constraint' => 3),
			'search_keywords'	=> array('type' => 'text')
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('file_id', TRUE);
		$this->EE->dbforge->create_table('assets_files');

		$this->EE->db->query('ALTER TABLE exp_assets_files ADD UNIQUE unq_folder_id__file_name (folder_id, file_name)');

		// -------------------------------------------
		//  Create the exp_assets_selections table
		// -------------------------------------------

		$fields = array(
			'file_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'entry_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'field_id'		=> array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
			'col_id'		=> array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
			'row_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'var_id'		=> array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
			'element_id'    => array('type' => 'varchar', 'constraint' => 255, 'null' => TRUE),
			'content_type'  => array('type' => 'varchar', 'constraint' => 255, 'null' => TRUE),
			'sort_order'	=> array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE),
			'is_draft'  	=> array('type' => 'TINYINT', 'constraint' => '1', 'unsigned' => TRUE, 'default' => 0)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('file_id');
		$this->EE->dbforge->add_key('entry_id');
		$this->EE->dbforge->add_key('field_id');
		$this->EE->dbforge->add_key('col_id');
		$this->EE->dbforge->add_key('row_id');
		$this->EE->dbforge->add_key('var_id');
		$this->EE->dbforge->create_table('assets_selections');

		// folder structure
		$fields = array(
			'folder_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'source_type'	=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 'ee'),
			'folder_name'	=> array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE),
			'full_path'		=> array('type' => 'varchar', 'constraint' => 255),
			'parent_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'null' => TRUE),
			'source_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'filedir_id'	=> array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE),
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('folder_id', true);
		$this->EE->dbforge->create_table('assets_folders');

		$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__filedir_id__parent_id__folder_name (`source_type`, `source_id`, `filedir_id`, `parent_id`, `folder_name`)');
		$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__filedir_id__full_path (`source_type`, `source_id`, `filedir_id`, `full_path`)');

		// source information
		$fields = array(
			'source_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'source_type'	=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 's3'),
			'name'			=> array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'default' => ''),
			'settings'		=> array('type' => 'text', 'null' => FALSE)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('source_id', true);
		$this->EE->dbforge->create_table('assets_sources');

		// table for temporary data during indexing
		$fields = array(
			'session_id'	=> array('type' => 'char', 'constraint' => 36),
			'source_type'	=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 'ee'),
			'source_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'offset'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'uri'			=> array('type' => 'varchar', 'constraint' => 255),
			'filesize'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'type'			=> array('type' => 'enum', 'constraint' => "'file','folder'"),
			'record_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->create_table('assets_index_data');
		$this->EE->db->query('ALTER TABLE `exp_assets_index_data` ADD UNIQUE unq__session_id__source_type__source_id__offset (`session_id`, `source_type`, `source_id`, `offset`)');

		$fields = array(
			'connection_key' => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE),
			'token'	         => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE),
			'storage_url'    => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE),
			'cdn_url'        => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('connection_key', true);
		$this->EE->dbforge->create_table('assets_rackspace_access');

		return TRUE;
	}

	/**
	 * Update
	 */
	function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}

		// -------------------------------------------
		//  Require Assets 1.x => 2.x to take place from the module unless DevDemon Updater is running the show
		// -------------------------------------------

		if (version_compare($current, '2.0b2', '<'))
		{
			// Prevent the EE update wizard from running this
			if (get_class($this->EE) == 'Wizard')
			{
				return FALSE;
			}

			// is this DevDemon Updater?
			if (
				$this->EE->input->get('C') == 'addons_modules' &&
				$this->EE->input->get('M') == 'show_module_cp' &&
				$this->EE->input->get('module') == 'updater' &&
				$this->EE->input->get('method') == 'ajax_router' &&
				$this->EE->input->get('task') == 'addon_install'
			)
			{
				// make sure they're running Updater 3.1.6 or later, which checks database_backup_required()
				$version = $this->EE->db->select('module_version')->where('module_name', 'Updater')->get('modules')->row('module_version');
				if (version_compare($version, '3.1.6', '<'))
				{
					$this->EE->lang->loadfile('assets');
					exit(lang('updater_316_required'));
				}
			}
			else
			{
				// is this an MCP index request?
				$mcp_index = (
					$this->EE->input->get('C') == 'addons_modules' &&
					$this->EE->input->get('M') == 'show_module_cp' &&
					$this->EE->input->get('module') == 'assets' &&
					(($method = $this->EE->input->get('method')) === FALSE || $method == 'index')
				);

				if (!$mcp_index || $this->EE->input->get('goforth') != 'y')
				{
					if ($mcp_index)
					{
						// let the MCP know to display the DB backup message
						$this->EE->session->cache['assets']['show_dbbackup'] = TRUE;
					}
					else
					{
						// redirect to the MCP index
						$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=assets');
					}

					// cancel the update
					return FALSE;
				}
			}
		}

		// -------------------------------------------
		//  Schema changes
		// -------------------------------------------

		if (version_compare($current, '0.2', '<'))
		{
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_subfolders'));
		}

		if (version_compare($current, '0.3', '<'))
		{
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'upload_file'));
		}

		if (version_compare($current, '0.4', '<'))
		{
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'move_folder'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'create_folder'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'delete_folder'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'move_file'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'delete_file'));
		}

		if (version_compare($current, '0.5', '<'))
		{
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'view_file'));
		}

		if (version_compare($current, '0.6', '<'))
		{
			// {filedir_x}/filename => {filedir_x}filename
			$this->EE->db->query('UPDATE exp_assets SET file_path = REPLACE(file_path, "}/", "}")');
		}

		if (version_compare($current, '0.7', '<'))
		{
			$this->EE->load->dbforge();

			// delete unused exp_assets columns
			$this->EE->dbforge->drop_column('assets', 'asset_kind');
			$this->EE->dbforge->drop_column('assets', 'file_dir');
			$this->EE->dbforge->drop_column('assets', 'file_name');
			$this->EE->dbforge->drop_column('assets', 'file_size');
			$this->EE->dbforge->drop_column('assets', 'sha1_hash');
			$this->EE->dbforge->drop_column('assets', 'img_width');
			$this->EE->dbforge->drop_column('assets', 'img_height');
			$this->EE->dbforge->drop_column('assets', 'date_added');
			$this->EE->dbforge->drop_column('assets', 'edit_date');

			// rename 'asset_date' to 'date', and move it after title
			$this->EE->db->query('ALTER TABLE exp_assets
			                      CHANGE COLUMN `asset_date` `date` INT(10) UNSIGNED NULL DEFAULT NULL  AFTER `title`');
		}

		if (version_compare($current, '0.8', '<'))
		{
			// build_file_manager => build_sheet
			$this->EE->db->where('method', 'build_file_manager')
				->update('actions', array('method' => 'build_sheet'));
		}

		if (version_compare($current, '1.0.1', '<'))
		{
			// tell EE about the fieldtype's global settings
			$this->EE->db->where('name', 'assets')
				->update('fieldtypes', array('has_global_settings' => 'y'));
		}

		if (version_compare($current, '1.1.5', '<'))
		{
			$this->EE->load->dbforge();

			// do we need to add the var_id column to exp_assets_entries?
			//  - the 1.1 update might have added this but then failed on another step, so the version wouldn't be updated
			$query = $this->EE->db->query('SHOW COLUMNS FROM `'.$this->EE->db->dbprefix.'assets_entries` LIKE "var_id"');
			if (! $query->num_rows())
			{
				$this->EE->db->query('ALTER TABLE exp_assets_entries ADD var_id INT(6) UNSIGNED AFTER row_id, ADD INDEX (var_id)');
			}
			else
			{
				// do we need to add its index?
				$query = $this->EE->db->query('SHOW INDEX FROM exp_assets_entries WHERE Key_name = "var_id"');
				if (! $query->num_rows())
				{
					$this->EE->db->query('ALTER TABLE exp_assets_entries ADD INDEX (var_id)');
				}
			}

			// do we need to add the unq_file_path index to exp_assets?
			//  - the 1.1 update used to attempt to add this, but it would fail if there was a duplicate file_path
			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets WHERE Key_name = "unq_file_path"');
			if (! $query->num_rows())
			{
				// are there any duplicate file_path's?
				$query = $this->EE->db->query('
					SELECT a.asset_id, a.file_path FROM exp_assets a
					INNER JOIN (
						SELECT file_path FROM exp_assets
						GROUP BY file_path HAVING count(asset_id) > 1
					) dup ON a.file_path = dup.file_path');

				if ($query->num_rows())
				{
					$duplicates = array();
					foreach ($query->result() as $asset)
					{
						$duplicates[$asset->file_path][] = $asset->asset_id;
					}

					foreach ($duplicates as $file_path => $asset_ids)
					{
						$first_asset_id = array_shift($asset_ids);

						if (count($asset_ids))
						{
							// point any entries that were using the duplicate IDs over to the first one
							$this->EE->db->where_in('asset_id', $asset_ids)
								->update('assets_entries', array('asset_id' => $first_asset_id));

							// delete the duplicates in exp_assets
							$this->EE->db->where_in('asset_id', $asset_ids)
								->delete('assets');
						}
					}
				}

				// now that there are no more unique file_path's, add the unique index,
				// and drop the old file_path index, since that would be redundant
				$this->EE->db->query('ALTER TABLE exp_assets ADD UNIQUE unq_file_path (file_path), DROP INDEX file_path');
			}
		}

		if (version_compare($current, '2.0b1', '<'))
		{
			$this->EE->load->dbforge();

			// Set file_path to NOT NULL
			$this->EE->db->query('ALTER TABLE exp_assets MODIFY COLUMN file_path VARCHAR(255) NOT NULL');

			// on a clean 1.2.1 install, this index might not exist
			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets WHERE Key_name = "unq_file_path"');
			if ($query->num_rows())
			{
				// Drop the unq_file_path index
				$this->EE->db->query('ALTER TABLE exp_assets DROP INDEX unq_file_path');
			}

			// Add all the fields to make exp_assets a functional index table
			$fields = array(
				'source_type'		=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 'ee'),
				'source_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'filedir_id'		=> array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE, 'null' => TRUE),
				'folder_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'date_modified'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'kind'				=> array('type' => 'varchar', 'constraint' => 5),
				'width'				=> array('type' => 'int', 'constraint' => 2),
				'height'			=> array('type' => 'int', 'constraint' => 2),
				'size'				=> array('type' => 'int', 'constraint' => 3),
				'search_keywords'	=> array('type' => 'text')
			);

			$this->EE->dbforge->add_column('assets', $fields);
			$this->EE->db->query('ALTER TABLE exp_assets CHANGE `file_path` `file_name` VARCHAR (255) NOT NULL');

			// table for storing folder structure
			$fields = array(
				'folder_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'source_type'	=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 'ee'),
				'folder_name'	=> array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE),
				'full_path'		=> array('type' => 'varchar', 'constraint' => 255),
				'parent_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'null' => TRUE),
				'source_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'filedir_id'	=> array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE),
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('folder_id', true);
			$this->EE->dbforge->create_table('assets_folders');


			// source information
			$fields = array(
				'source_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'source_type'	=> array('type' => 'varchar', 'constraint' => 2, 'null' => FALSE, 'default' => 's3'),
				'name'			=> array('type' => 'varchar', 'constraint' => 255),
				'settings'		=> array('type' => 'text', 'null' => FALSE)
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('source_id', TRUE);
			$this->EE->dbforge->create_table('assets_sources');

			// Add new actions
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'rename_folder'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'view_thumbnail'));

			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_session_id'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'start_index'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'perform_index'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'finish_index'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_s3_buckets'));

			// some more table structure changes
			$this->EE->db->query("ALTER TABLE exp_assets RENAME TO exp_assets_files");
			$this->EE->db->query("ALTER TABLE exp_assets_files CHANGE `asset_id` `file_id` INT(10) NOT NULL AUTO_INCREMENT");
			$this->EE->db->query("ALTER TABLE exp_assets_entries RENAME TO exp_assets_selections");
			$this->EE->db->query("ALTER TABLE exp_assets_selections CHANGE `asset_order` `sort_order` INT(4) UNSIGNED");
			$this->EE->db->query("ALTER TABLE exp_assets_selections CHANGE `asset_id` `file_id` INT(10)");

			// migrate the existing data
			$this->_migrate_data('<2 -> 2.0');

			// Add the unique indexes
			$this->EE->db->query('ALTER TABLE exp_assets_files ADD UNIQUE unq_folder_id__file_name (folder_id, file_name)');
			$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__parent_id__folder_name (`source_type`, `source_id`, `parent_id`, `folder_name`)');
			$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__full_path (`source_type`, `source_id`,  `full_path`)');
			$this->EE->db->query('ALTER TABLE exp_assets_sources ADD UNIQUE unq_source_type__source_id (`source_type`, `source_id`)');

			// table for temporary data during indexing
			$fields = array(
				'session_id'	=> array('type' => 'char', 'constraint' => 36),
				'source_type'	=> array('type' => 'varchar', 'constraint' => 2),
				'source_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'offset'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'uri'			=> array('type' => 'varchar', 'constraint' => 255),
				'filesize'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'type'			=> array('type' => 'enum', 'constraint' => "'file','folder'"),
				'record_id'		=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE)
			);
			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->create_table('assets_index_data');
			$this->EE->db->query('ALTER TABLE `exp_assets_index_data` ADD UNIQUE unq__session_id__source_type__source_id__offset (`session_id`, `source_type`, `source_id`, `offset`)');
		}
		elseif (version_compare($current, '2.0b2', '<'))
		{
			// add the new actions
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_session_id'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'start_index'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'perform_index'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'finish_index'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_s3_buckets'));

			// files
			$this->EE->db->query("ALTER TABLE exp_assets_files CHANGE COLUMN `connector` `source_type` VARCHAR(2) NOT NULL DEFAULT 'ee'");
			$this->EE->db->query("ALTER TABLE exp_assets_files ADD COLUMN `source_id` INT(10) UNSIGNED NULL AFTER `source_type`");
			$this->EE->db->query("ALTER TABLE exp_assets_files ADD COLUMN `filedir_id` INT(4) UNSIGNED NULL AFTER `source_id`");
			$this->EE->db->query("ALTER TABLE exp_assets_files CHANGE COLUMN `folder_id` `folder_id` INT(10) UNSIGNED NULL");

			// folders
			$this->EE->db->query("ALTER TABLE exp_assets_folders CHANGE COLUMN `connector` `source_type` VARCHAR(2) NOT NULL DEFAULT 'ee'");
			$this->EE->db->query("ALTER TABLE exp_assets_folders CHANGE COLUMN `pref_id` `source_id` INT(10) UNSIGNED NULL");
			$this->EE->db->query("ALTER TABLE exp_assets_folders ADD COLUMN `filedir_id` INT(4) UNSIGNED NULL AFTER `source_id`");
			$this->EE->db->query('ALTER TABLE exp_assets_folders DROP INDEX unq_connector__parent_id__folder_name');
			$this->EE->db->query('ALTER TABLE exp_assets_folders DROP INDEX unq_connector__pref_id__full_path');
			$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__filedir_id__parent_id__folder_name (`source_type`, `source_id`, `filedir_id`, `parent_id`, `folder_name`)');
			$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__filedir_id__full_path (`source_type`, `source_id`, `filedir_id`, `full_path`)');

			// index_data
			$this->EE->db->query("ALTER TABLE exp_assets_sync_data RENAME TO exp_assets_index_data");
			$this->EE->db->query("ALTER TABLE exp_assets_index_data CHANGE COLUMN `sync_session` `session_id` CHAR(36) NULL");
			$this->EE->db->query("ALTER TABLE exp_assets_index_data ADD COLUMN `source_type` VARCHAR(2) AFTER `session_id`");
			$this->EE->db->query("ALTER TABLE exp_assets_index_data CHANGE COLUMN `folder_id` `source_id` INT(10) UNSIGNED NULL");
			$this->EE->db->query('ALTER TABLE exp_assets_index_data DROP INDEX `sync_index`');
			$this->EE->db->query('ALTER TABLE exp_assets_index_data ADD UNIQUE unq__session_id__source_type__source_id__offset (`session_id`, `source_type`, `source_id`, `offset`)');

			// sources
			$this->EE->db->query("ALTER TABLE exp_assets_folder_prefs RENAME TO exp_assets_sources");
			$this->EE->db->query("ALTER TABLE exp_assets_sources CHANGE COLUMN `pref_id` `source_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
			$this->EE->db->query("ALTER TABLE exp_assets_sources CHANGE COLUMN `connector` `source_type` VARCHAR(2) NOT NULL DEFAULT 's3'");
			$this->EE->db->query("ALTER TABLE exp_assets_sources ADD COLUMN `name` VARCHAR(255) DEFAULT '' AFTER `source_type`");
			$this->EE->db->query("ALTER TABLE exp_assets_sources CHANGE COLUMN `data` `settings` TEXT NOT NULL DEFAULT ''");

			$this->_migrate_data('2.0b1 -> 2.0b2');
		}

		if (version_compare($current, '2.0b4', '<')){
			$this->EE->load->dbforge();
			$fields = array(
				'is_draft'  	=> array('type' => 'TINYINT', 'constraint' => '1', 'unsigned' => TRUE, 'default' => 0)
			);

			$this->EE->dbforge->add_column('assets_selections', $fields);

			$this->EE->db->query("ALTER TABLE exp_assets_files MODIFY COLUMN `folder_id` INT(10) NOT NULL AFTER `file_id`");
			$this->EE->db->query("ALTER TABLE exp_assets_files MODIFY COLUMN `source_type` VARCHAR(2) NOT NULL DEFAULT 'ee' AFTER `folder_id`");
			$this->EE->db->query("ALTER TABLE exp_assets_files MODIFY COLUMN `source_id` INT(10) UNSIGNED NULL AFTER `source_type`");
			$this->EE->db->query("ALTER TABLE exp_assets_files MODIFY COLUMN `filedir_id` INT(4) UNSIGNED NULL AFTER `source_id`");

			$this->EE->db->query('UPDATE exp_modules SET module_version = "2.0" WHERE module_name = "Assets"');
		}

		if (version_compare($current, '2.1', '<'))
		{
			$this->EE->load->dbforge();

			// Changes to file date
			$this->EE->db->query("UPDATE exp_assets_files SET `date` = `date_modified` WHERE `date` IS NULL OR `date` = ''");
			$this->EE->db->query("ALTER TABLE exp_assets_files MODIFY COLUMN `date` INT(10) UNSIGNED NOT NULL");

			// Adding Rackspace cloud and Google cloud actions
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_rs_containers'));
			$this->EE->db->insert('actions', array('class' => 'Assets_mcp', 'method' => 'get_gc_containers'));

			// Adding the rackspace connection table
			$fields = array(
				'connection_key' => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE),
				'token'	         => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE),
				'storage_url'    => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE),
				'cdn_url'        => array('type' => 'varchar', 'constraint' => 255, 'null' => FALSE, 'required' => TRUE)
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('connection_key', true);
			$this->EE->dbforge->create_table('assets_rackspace_access');
		}

		if (version_compare($current, '2.1.2', '<'))
		{
			// Clean up possible incorrect indexes
			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets_folders WHERE Key_name = "unq_source_type__source_id__full_path"');
			if ($query->num_rows())
			{
				// Drop the unq_file_path index
				$this->EE->db->query('ALTER TABLE exp_assets_folders DROP INDEX unq_source_type__source_id__full_path');
			}

			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets_folders WHERE Key_name = "unq_source_type__source_id__parent_id__folder_name"');
			if ($query->num_rows())
			{
				// Drop the unq_file_path index
				$this->EE->db->query('ALTER TABLE exp_assets_folders DROP INDEX unq_source_type__source_id__parent_id__folder_name');
			}

			// Add new indexes, if needed
			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets_folders WHERE Key_name = "unq_source_type__source_id__filedir_id__parent_id__folder_name"');
			if (!$query->num_rows())
			{
				$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__filedir_id__parent_id__folder_name (`source_type`, `source_id`, `filedir_id`, `parent_id`, `folder_name`)');
			}

			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets_folders WHERE Key_name = "unq_source_type__source_id__filedir_id__full_path"');
			if (!$query->num_rows())
			{
				$this->EE->db->query('ALTER TABLE exp_assets_folders ADD UNIQUE unq_source_type__source_id__filedir_id__full_path (`source_type`, `source_id`, `filedir_id`, `full_path`)');
			}


		}

		if (version_compare($current, '2.1.4', '<'))
		{
			$this->EE->db->query('ALTER TABLE exp_assets_files MODIFY COLUMN `date` INT(10) NULL');
		}

		if (version_compare($current, '2.2', '<'))
		{
			if (!$this->EE->db->field_exists('element_id', 'assets_selections'))
			{
				$this->EE->db->query('ALTER TABLE exp_assets_selections ADD COLUMN `element_id` VARCHAR(255) NULL AFTER `var_id`');
			}
			if (!$this->EE->db->field_exists('content_type', 'assets_selections'))
			{
				$this->EE->db->query('ALTER TABLE exp_assets_selections ADD COLUMN `content_type` VARCHAR(255) NULL AFTER `element_id`');
			}

			$query = $this->EE->db->query('SHOW INDEX FROM exp_assets_sources WHERE Key_name = "unq_source_type__source_id"');
			if ($query->num_rows())
			{
				// Drop the unq_file_path index
				$this->EE->db->query('ALTER TABLE exp_assets_sources DROP INDEX unq_source_type__source_id');
			}

		}

		if (version_compare($current, '2.2.2', '<'))
		{
			// Paranoia will destroy ya
			if (!$this->EE->db->field_exists('content_type', 'assets_selections') && !version_compare($current, '2.2', '<'))
			{
				$this->EE->db->query('ALTER TABLE exp_assets_selections ADD COLUMN `content_type` VARCHAR(255) NULL AFTER `element_id`');
			}
			$this->EE->db->query("UPDATE exp_assets_selections SET content_type = 'matrix' WHERE row_id > 0 AND (content_type = '' OR content_type IS NULL)");
		}

		// -------------------------------------------
		//  Update version number in exp_fieldtypes and exp_extensions
		// -------------------------------------------

		$this->EE->db->where('name', 'assets')
			->update('fieldtypes', array('version' => ASSETS_VER));

		$this->EE->db->where('class', 'Assets_ext')
			->update('extensions', array('version' => ASSETS_VER));

		return TRUE;
	}

	/**
	 * Uninstall
	 */
	function uninstall()
	{
		$this->EE->load->dbforge();

		// routine EE table cleanup

		$this->EE->db->select('module_id');
		$module_id = $this->EE->db->get_where('modules', array('module_name' => 'Assets'))->row('module_id');

		$this->EE->db->where('module_id', $module_id);
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Assets');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Assets');
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', 'Assets_mcp');
		$this->EE->db->delete('actions');

		// drop Assets tables
		$this->EE->dbforge->drop_table('assets_files');
		$this->EE->dbforge->drop_table('assets_selections');
		$this->EE->dbforge->drop_table('assets_sources');
		$this->EE->dbforge->drop_table('assets_folders');
		$this->EE->dbforge->drop_table('assets_index_data');
		$this->EE->dbforge->drop_table('assets_rackspace_access');

		$cache_path = $this->EE->config->item('cache_path');
		if (empty($cache_path))
		{
			$cache_path = APPPATH.'cache/';
		}

		$this->_delete_recursively($cache_path . 'assets');
		return TRUE;
	}

	/**
	 * Delete folder recursively
	 * @param $folder
	 */
	private function _delete_recursively($folder)
	{
		foreach(glob($folder . '/*') as $file)
		{
			if(is_dir($file))
			{
				$this->_delete_recursively($file);
			}
			else
			{
				@unlink($file);
			}
		}
		@rmdir($folder);
	}

	/**
	 * Migrate data according to a scenario
	 * @param $scenario
	 */
	private function _migrate_data($scenario)
	{
		$this->EE = get_instance();
		$db = $this->EE->db;
		$this->EE->load->library('assets_lib');

		clearstatcache();

		switch ($scenario)
		{
			case '<2 -> 2.0':
				require_once(PATH_THIRD . 'assets/sources/ee/source.ee.php');
				$filedirs = array();
				$folder_list = array();

				// load upload preferences and store them in table for Assets
				$rows = $db->get('upload_prefs')->result();
				foreach ($rows as $filedir)
				{
					$filedirs[$filedir->id] = Assets_ee_source::apply_filedir_overrides($filedir);
				}

				// load physical folder structure
				foreach ($filedirs as $id => $filedir)
				{
					$filedir->server_path = Assets_ee_source::resolve_server_path($filedir->server_path);

					$folder_list[$id][] = $filedir->server_path;
					$this->_load_folder_structure($filedir->server_path, $folder_list[$id]);
				}

				// store the folder structure in database
				$subfolders = array();
				foreach ($folder_list as $filedir_id => $folders)
				{
					$filedir = $filedirs[$filedir_id];
					foreach ($folders as $folder)
					{
						$subpath = substr($folder, strlen($filedir->server_path));
						if (empty($subpath))
						{
							$folder_name = $filedir->name;
							$parent_id = NULL;
						}
						else
						{
							$path_parts = explode('/', $subpath);
							$folder_name = array_pop($path_parts);
							$parent_key = $filedir_id . ':' . rtrim(join('/', $path_parts), '/');
							$parent_id = isset($subfolders[$parent_key]) ? $subfolders[$parent_key] : 0;
						}

						// in case false was returned earlier
						$subpath = $subpath ? rtrim($subpath, '/') . '/' : '';

						$folder_entry = array(
							'source_type' => 'ee',
							'filedir_id' => $filedir_id,
							'folder_name' => $folder_name,
							'full_path' => $subpath
						);

						if ( ! is_null($parent_id))
						{
							$folder_entry['parent_id'] = $parent_id;
						}

						$this->EE->db->insert('assets_folders', $folder_entry);
						$subfolders[$filedir_id . ':' . rtrim($subpath, '/')] = $this->EE->db->insert_id();
					}
				}

				// bring up the list of existing assets and update the entries
				$rows = $db->get('assets_files')->result();
				$pattern = '/\{filedir_(?P<filedir_id>[0-9]+)\}(?P<path>.*)/';
				foreach ($rows as $asset)
				{
					$asset->connector = 'ee';
					if (preg_match($pattern, $asset->file_name, $matches))
					{
						if (isset($filedirs[$matches['filedir_id']]))
						{
							$filedir = $filedirs[$matches['filedir_id']];

							$full_path = str_replace('{filedir_' . $filedir->id . '}', $filedir->server_path, $asset->file_name);
							$subpath = substr($full_path, strlen($filedir->server_path));
							$path_parts = explode('/', $subpath);
							$file = array_pop($path_parts);
							$subpath = join('/', $path_parts);

							$folder_key = $matches['filedir_id'] . ':' . $subpath;
							if (isset($subfolders[$folder_key]))
							{
								$folder_id = $subfolders[$folder_key];

								$kind = Assets_helper::get_kind($full_path);
								$data = array(
									'source_type' => 'ee',
									'filedir_id' => $filedir->id,
									'folder_id' => $folder_id,
									'file_name' => $file,
									'kind' => $kind,
								);

								if (file_exists($full_path))
								{
									$data['size'] = filesize($full_path);
									$data['date_modified'] = filemtime($full_path);
									if ($kind == 'image')
									{
										list ($width, $height) = getimagesize($full_path);
										$data['width'] = $width;
										$data['height'] = $height;
									}
								}

								$this->EE->db->update('assets_files', $data, array('file_id' => $asset->file_id));
								$this->EE->assets_lib->update_file_search_keywords($asset->file_id);
							}
						}
					}
				}

				// celebrate
				break;
			case '2.0b1 -> 2.0b2':

				// get S3 credentials if any
				$query = $this->EE->db->select('settings')
					->where('name', 'assets')
					->get('fieldtypes');

				$settings = unserialize(base64_decode($query->row('settings')));
				$settings = array_merge(array('license_key' => '', 's3_access_key_id' => '', 's3_secret_access_key' => ''), $settings);

				//if we have s3 settings, let's convert the "folder_prefs" way to "sources" way
				if (!empty($settings['s3_access_key_id']) && !empty($settings['s3_secret_access_key']))
				{
					$old_sources = $this->EE->db->get('assets_sources')->result();
					foreach ($old_sources as $source)
					{
						$previous_settings = json_decode($source->settings);
						$new_settings = (object) array(
							'access_key_id' => $settings['s3_access_key_id'],
							'secret_access_key' => $settings['s3_secret_access_key'],
							'bucket' => $previous_settings->name,
							'url_prefix' => $previous_settings->url_prefix,
							'location' => $previous_settings->location
						);
						$data = array(
							'name' => $previous_settings->name,
							'settings' => Assets_helper::get_json($new_settings)
						);

						$this->EE->db->update('assets_sources', $data, array('source_id' => $source->source_id));
					}
				}

				// modify folder data and also keep a list of who's who
				$folders = $this->EE->db->get('assets_folders')->result();
				$folder_sources = array();
				foreach ($folders as $row)
				{
					if ($row->source_type == 'ee')
					{
						$row->filedir_id = $row->source_id;
						$row->source_id = NULL;
						$this->EE->db->update('assets_folders', $row, array('folder_id' => $row->folder_id));
						$folder_sources[$row->folder_id] = $row->filedir_id;
					}
					else
					{
						$folder_sources[$row->folder_id] = $row->source_id;
					}
				}

				// add some data for file entries and we're done!
				$files = $this->EE->db->get('assets_files')->result();
				foreach ($files as $row)
				{
					if ($row->source_type == 'ee' && isset($folder_sources[$row->folder_id]))
					{
						$row->source_id = NULL;
						$row->filedir_id = $folder_sources[$row->folder_id];
						$this->EE->db->update('assets_files', $row, array('file_id' => $row->file_id));
					}
					else if (isset($folder_sources[$row->folder_id]))
					{
						$row->source_id = $folder_sources[$row->folder_id];
						$row->filedir_id = NULL;
						$this->EE->db->update('assets_files', $row, array('file_id' => $row->file_id));
					}
				}

				// party!
				break;
		}
	}

	/**
	 * Load the folder structure for data migration
	 *
	 * @param $path
	 * @param $folder_structure
	 */
	private function _load_folder_structure($path, &$folder_structure)
	{
		// starting with underscore or dot gets ignored
		$list = glob($path . '[!_.]*', GLOB_MARK);

		if (is_array($list) && count($list) > 0)
		{
			foreach ($list as $item)
			{
				// parse folders and add files
				$item = Assets_helper::normalize_path($item);
				if (substr($item, -1) == '/')
				{
					// add with dropped slash and parse
					$folder_structure[] = substr($item, 0, -1);
					$this->_load_folder_structure($item, $folder_structure);
				}
			}
		}
	}

	/**
	 * Return true if updating from the $current version requires a DB backup
	 * @param $current
	 * @return bool
	 */
	public function database_backup_required($current)
	{
		if (version_compare($current, '2.0', '<'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
