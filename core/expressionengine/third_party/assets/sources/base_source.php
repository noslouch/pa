<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets 2.0 source abstract class
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */

abstract class Assets_base_source
{
	/**
	 * @var EE
	 */
	public $EE;

	/**
	 * @var string
	 */
	protected $_source_id = '';

	/**
	 * @var string
	 */
	protected $_source_type = '';

	/**
	 * @var array of Assets_base_file
	 */
	private $files = array();

	/**
	 * @var null|StdClass
	 */
	protected $_source_settings = null;

	/**
	 * @var null|StdClass
	 */
	protected $_source_row = null;

	/**
	 * Store index data for batch inserts
	 * @var array
	 */
	protected $_index_batch_entries = array();

	public function __construct()
	{
		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		$this->EE = get_instance();

		if ( ! in_array(PATH_THIRD.'assets/', $this->EE->load->get_package_paths()))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'assets/');
		}
		
		$this->EE->load->library('assets_lib');

		if (! isset($this->EE->session->cache['assets']))
		{
			$this->EE->session->cache['assets'] = array();
		}

		$this->cache =& $this->EE->session->cache['assets'];


	}

	/**
	 * Returns TRUE if source is capable from performing the file move from another source as if the file being moved was
	 * inside the new source already. For example - all EE operations and S3 operations, that take place on the same AWS account
	 * @param Assets_base_source $source
	 * @return boolean
	 */
	abstract public function can_move_files_from(Assets_base_source $source);

	/**
	 * Create a folder at designated path for source
	 * @abstract
	 * @param $server_path
	 * @return array
	 */
	abstract protected function _create_source_folder($server_path);

	/**
	 * Rename a folder
	 * @abstract
	 * @param $old_path
	 * @param $new_path
	 * @return bool|mixed
	 */
	abstract protected function _rename_source_folder($old_path, $new_path);

	/**
	 * Check if a source file exists
	 * @abstract
	 * @param $server_path
	 * @return bool|mixed
	 */
	abstract protected function _source_file_exists($server_path);

	/**
	 * Delete a folder
	 * @abstract
	 * @param $server_path
	 * @return mixed
	 */
	abstract protected function _delete_source_folder($server_path);

	/**
	 * Delete a file
	 * @abstract
	 * @param $server_path
	 * @param $source_data
	 * @return mixed
	 */
	abstract protected function _delete_source_file($server_path, $source_data = array());

	/**
	 * Check if folder exists on source
	 * @abstract
	 * @param $server_path
	 * @return array
	 */
	abstract protected function _source_folder_exists($server_path);

	/**
	 * Perform upload in the designated folder
	 * @abstract
	 * @param stdclass $folder_data holding the folder row data
	 * @param string $temp_file_path file path on disk
	 * @param string $original_name file original name (type checks need this)
	 * @return mixed
	 */
	abstract protected function _do_upload_in_folder($folder_data, $temp_file_path, $original_name);

	/**
	 * Move a source file
	 * @abstract
 	 * @param Assets_base_file $file
	 * @param $previous_folder_row
	 * @param $folder_row
	 * @param $new_file_name
	 * @param bool $overwrite if set to TRUE will always overwrite the target file
	 * @return mixed
	 */
	abstract protected function _move_source_file(Assets_base_file $file, $previous_folder_row, $folder_row, $new_file_name = '', $overwrite = FALSE);

	/**
	 * Starts an indexing session
	 * @param $session_id
	 * @return array
	 */
	abstract public function start_index($session_id);

	/**
	 * Starts a folder indexing session
	 * @param $session_id
	 * @param StdClass $folder_row
	 * @return array
	 */
	abstract public function start_folder_index($session_id, $folder_row);


	/**
	 * Perform indexing
	 * @param $session_id int
	 * @param $offset
	 * @abstract
	 * @return boolean
	 */
	abstract public function process_index($session_id, $offset);

	/**
	 * Perform some actions that should be done after image upload
	 * @abstract
	 * @param $file_id
	 * @param $image_path
	 * @return mixed
	 */
	abstract public function post_upload_image_actions($file_id, $image_path);

	/**
	 * Get a replacement name
	 * @abstract
	 * @param $folder_row
	 * @param $file_name
	 * @return mixed
	 */
	abstract public function get_name_replacement($folder_row, $file_name);

	/**
	 * Get a files server path
	 * @abstract
	 * @param $folder_row
	 * @param $file_name
	 * @return mixed
	 */
	abstract protected function _get_file_server_path($folder_row, $file_name);

	/**
	 * Return setting fields for this source
	 * @return array
	 */
	abstract public static function get_settings_field_list();

	/**
	 * Get folder name for a path
	 * @param $path
	 * @return string
	 */
	public function get_folder_name($path)
	{
		$components = explode('/', Assets_helper::normalize_path($path));
		return array_pop($components);
	}

	/**
	 * Return source type
	 * @return string
	 */
	public function get_source_type()
	{
		return $this->_source_type;
	}

	/**
	 * Return source id
	 * @return string
	 */
	public function get_source_id()
	{
		return $this->_source_id;
	}

	/**
	 * Return settings for source
	 * @return mixed
	 */
	public function settings()
	{
		return $this->_source_settings;
	}

	/**
	 * Clear file cache
	 */
	public function clear_file_cache()
	{
		$this->files = array();
	}

	/**
	 * Create Folder at path, which consists is in form of "parent_id/folder_name"
	 * @param $path
	 * @throws Exception
	 * @return array
	 */
	public function create_folder($path)
	{
		if (substr_count($path, '/') !== 1)
		{
			throw new Exception(lang('invalid_folder_path'));
		}

		list ($parent_id, $folder_name) = explode('/', $path);

		$row = $this->EE->assets_lib->get_folder_row_by_id($parent_id);

		// swap whitespace with underscores
		$folder_name = preg_replace('/\s+/', '_', $folder_name);

		if (!Assets_helper::is_allowed_folder_name($folder_name))
		{
			throw new Exception(lang('invalid_folder_name'));
		}

		$parent_path = Assets_helper::normalize_path($row->full_path);
		$new_path = $parent_path . $folder_name;

		// attempt to resolve, check if exists and create.
		if (! ($server_path = $this->get_folder_server_path($new_path)))
		{
			throw new Exception(lang('invalid_folder_path'));
		}
		if ($this->_source_folder_exists($server_path) OR $this->EE->assets_lib->get_folder_id_by_parent_and_name($parent_id, $folder_name))
		{
			throw new Exception(lang('file_already_exists'));
		}
		if ( ! $this->_create_source_folder($server_path))
		{
			throw new Exception(lang('invalid_folder_path'));
		}

		// created, insert in DB
		$data = array(
			'source_type' => $this->get_source_type(),
			'folder_name' => $folder_name,
			'full_path' => ltrim($new_path, '/') . '/',
			'parent_id' => $parent_id,
			'source_id' => $row->source_id,
			'filedir_id' => $row->filedir_id
		);

		$this->EE->db->insert('assets_folders', $data);

		$folder_id = $this->EE->db->insert_id();
		$this->EE->assets_lib->call_extension('assets_create_folder', array($this->EE->assets_lib->get_folder_row_by_id($folder_id)));

		return array('success' => TRUE,
			'folder_id' => $folder_id,
			'parent_id' => $parent_id,
			'folder_name' => $folder_name);
	}

	/**
	 * Get the folder server path
	 *
	 * @param $folder
	 * @return string
	 */
	public function get_folder_server_path($folder)
	{
		return trim($folder, '/') . '/';
	}

	/**
	 * Rename folder
	 * @param $folder_id
	 * @param $new_title
	 * @throws Exception
	 * @return array
	 */
	public function rename_folder($folder_id, $new_title)
	{
		if (substr_count($new_title, '/') > 0 OR !Assets_helper::is_allowed_folder_name($new_title))
		{
			throw new Exception(lang('invalid_folder_path'));
		}

		// swap whitespace with underscores
		$new_title = preg_replace('/\s+/', '_', $new_title);

		$source_folder = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		$parent_row = $this->EE->assets_lib->get_folder_row_by_id($source_folder->parent_id);
		$source_path = Assets_helper::normalize_path(substr($this->get_folder_server_path($source_folder->full_path, $source_folder), 0, -1));

		$base = pathinfo($source_path, PATHINFO_DIRNAME);
		if ($base == '.')
		{
			$base = "";
		}
		else
		{
			$base .= "/";
		}

		$target = $base . $new_title;

		if (strtolower($new_title) == strtolower($source_folder->folder_name) && $source_folder->folder_name != $new_title)
		{
			$temp_folder = $new_title . uniqid('assets');
			while ($this->_source_folder_exists($base.$temp_folder))
			{
				$temp_folder = $new_title . uniqid('assets');
			}

			$this->_rename_source_folder($source_path, $base.$temp_folder);
			$this->_rename_source_folder($base.$temp_folder, $target);

			$source_folder->folder_name = $new_title;
			$this->_update_folder_info($source_folder, $parent_row);

			return array('success' => TRUE, 'new_name' => $new_title);
		}

		if ( $this->_source_folder_exists($target) )
		{
			throw new Exception(lang('invalid_folder_path'));
		}

		if ( ! $this->_rename_source_folder($source_path, $target))
		{
			throw new Exception(lang('invalid_folder_path'));
		}

		$this->EE->assets_lib->call_extension('assets_rename_folder', array($source_folder, $new_title));

		$source_folder->folder_name = $new_title;
		$this->_update_folder_info($source_folder, $parent_row);

		return array('success' => TRUE, 'new_name' => $new_title);
	}

	/**
	 * Move folder from one path to another
	 * @param $folder_id
	 * @param $new_parent
	 * @param boolean $overwrite_target if TRUE will overwrite target folder
	 * @return array
	 */
	public function move_folder($folder_id, $new_parent, $overwrite_target = FALSE)
	{
		// why not.
		if ($folder_id == $new_parent)
		{
			return array('success' => TRUE);
		}

		$source_row = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		if ($source_row->parent_id == $new_parent)
		{
			return array('success' => TRUE);
		}

		$new_parent_row = $this->EE->assets_lib->get_folder_row_by_id($new_parent);
		$new_parent_path = $this->get_folder_server_path($new_parent_row->full_path, $new_parent_row);
		$target = $new_parent_path . $source_row->folder_name;

		// if the folder exists, we see if the user has taken an action already
		$remove_from_tree = '';
		if ($this->_source_folder_exists(rtrim($target, '/') . '/') OR $this->EE->assets_lib->get_folder_id_by_parent_and_name($new_parent, $source_row->folder_name))
		{
			if ($overwrite_target)
			{
				if ( ! $this->EE->assets_lib->get_folder_id_by_parent_and_name($new_parent_row->folder_id, $source_row->folder_name))
				{
					$this->_delete_source_folder(
						$this->get_folder_server_path($new_parent_row->full_path . $source_row->full_path, $new_parent_row),
						$new_parent_row);
				}
				else
				{
					// pass this along as well, since this is a conflicting folder that must be removed from the tree
					$remove_from_tree = $this->EE->assets_lib->get_folder_id_by_parent_and_name($new_parent_row->folder_id, $source_row->folder_name);
					$this->delete_folder($remove_from_tree);
				}
			}
			else
			{
				return $this->_folder_prompt_result_array($source_row->folder_name, $folder_id);
			}
		}

		$this->EE->assets_lib->call_extension('assets_move_folder', array($source_row, $new_parent_row));

		// NOTE: this is needed, so we can create a progress bar - we need to split all the tasks in chunks
		//
		// transfer_list: array that describes file transfers needed
		// delete_list: list of folders to delete after move
		// changed_folder_ids: list of folder id changes
		$return = array(
			'success' => TRUE,
			'transfer_list' => array(),
			'delete_list' => array($folder_id),
			'changed_folder_ids' => array(),
			'remove_from_tree' => $remove_from_tree
		);

		$mirroring_data = array(
			'changed_folder_ids' => array(),
		);

		$this->_mirror_structure($new_parent_row, $source_row, $mirroring_data);

		$return['changed_folder_ids'] = $mirroring_data['changed_folder_ids'];

		$folder = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		$result = $this->EE->db->select('folder_id')->like('full_path', $folder->full_path, 'after')->get('assets_folders')->result();

		$folder_ids = array();

		foreach ($result as $row)
		{
			$folder_ids[] = $row->folder_id;
		}

		$this->EE->db->where_in('folder_id', $folder_ids);
		$result = $this->EE->db->get('assets_files')->result();

		foreach ($result as $row)
		{
			$return['transfer_list'][] = array(
				'old_id' => $row->file_id,
				'folder_id' => $return['changed_folder_ids'][$row->folder_id]['new_id'],
				'file_name' => $row->file_name);
		}

		return $return;
	}

	/**
	 * Mirrors a subset of folder tree from one location to other
	 * @param $target_row
	 * @param $source_row
	 * @param $changed_data
	 * @throws Exception
	 */
	private function _mirror_structure($target_row, $source_row, &$changed_data)
	{
		$result = $this->create_folder($target_row->folder_id . '/' . $source_row->folder_name);

		if (isset($result['success']))
		{
			$new_id = $result['folder_id'];
			$parent_id = $result['parent_id'];

			$changed_data['changed_folder_ids'][$source_row->folder_id] = array(
				'new_id' => $new_id,
				'new_parent_id' => $parent_id
			);

			$new_target_row = $this->EE->assets_lib->get_folder_row_by_id($new_id);

			$children = $this->EE->db->get_where('assets_folders', array('parent_id' => $source_row->folder_id));
			$children = $children->result();
			foreach ($children as $child)
			{
				$this->_mirror_structure($new_target_row, $child, $changed_data);
			}
		}
		else
		{
			throw new Exception(lang('exception_error'));
		}
	}

	/**
	 * Delete folder
	 * @param $folder_id
	 * @throws Exception
	 * @return array
	 */
	public function delete_folder($folder_id)
	{
		$db = $this->EE->db;

		$source = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		$source_path = $this->get_folder_server_path($source->full_path);

		// delete all files in this folder
		$files_to_delete = $db->get_where('assets_files', array('folder_id' => $folder_id));
		$rows = $files_to_delete->result();

		foreach ($rows as $file)
		{
			$this->delete_file($file->file_id, TRUE);
		}

		// delete all subfolders
		$folders_to_delete = $db->get_where('assets_folders', array('parent_id' => $folder_id));
		$rows = $folders_to_delete->result();

		foreach ($rows as $folder)
		{
			$this->delete_folder($folder->folder_id);
		}

		$this->EE->assets_lib->call_extension('assets_delete_folder', array($source));

		if ( ! $this->_delete_source_folder($source_path, $source))
		{
			throw new Exception(lang('invalid_source_path'));
		}

		$db->delete('assets_folders', array('folder_id' => $folder_id));

		return array('success' => TRUE);
	}

	/**
	 * Upload a file into the folder with the id
	 * @param $folder_id
	 * @return array
	 */
	public function upload_file($folder_id)
	{
		try
		{
			$folder_row = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		}
		catch (Exception $error)
		{
			return array('error' => $error->getMessage());
		}

		$server_path = $this->get_folder_server_path($folder_row->full_path, $folder_row);

		if ( ! $server_path)
		{
			return array('error' => lang('invalid_filedir_path'));
		}

		// upload the file and drop it in the temporary folder
		$uploader = new qqFileUploader();

		// make sure a file was uploaded
		if (! $uploader->file)
		{
			return array('error' => lang('no_files'));
		}

		$size = $uploader->file->getSize();

		// make sure the file isn't empty
		if (! $size)
		{
			return array('error' => lang('empty_file'));
		}

		if (!Assets_helper::is_allowed_file_name($uploader->file->getName()))
		{
			throw new Exception(lang('invalid_file_name'));
		}

		$file_path = Assets_helper::get_temp_file(pathinfo($uploader->file->getName(), PATHINFO_EXTENSION));
		$uploader->file->save($file_path);

		// the file is being saved in a temporary location, so that the workflow here is manageable
		// if we didn't do this, that would mean that all sources must implement their own uploader as well
		// which would have been an overkill.
		$result = $this->_do_upload_in_folder($folder_row, $file_path, $uploader->file->getName());

		$return_prompt = FALSE;

		// naming conflict. create the new filename and ask user what to do
		if (isset($result['prompt']))
		{
			$new_file_name = $this->get_name_replacement($folder_row, $uploader->file->getName());
			$return_prompt = $result;
			$result = $this->_do_upload_in_folder($folder_row, $file_path, $new_file_name);
		}

		if (isset($result['success']))
		{
			$filename = pathinfo($result['path'], PATHINFO_BASENAME);

			$data = array(
				'folder_id' => $folder_id,
				'source_type' => $folder_row->source_type,
				'source_id' => $folder_row->source_id,
				'filedir_id' => $folder_row->filedir_id,
				'file_name' => $filename,
				'kind' => Assets_helper::get_kind($filename)
			);

			$this->EE->db->insert('assets_files', $data);

			$file_id = $this->EE->db->insert_id();

			$file = $this->get_file($file_id);

			// For EE files, the dimensions might have changed due to filedir restrictions, so we have to use the returned path
			if ($file instanceof Assets_ee_file)
			{
				$this->update_file_info($file, $result['path']);
			}
			else
			{
				$this->update_file_info($file, $file_path);
			}

			@unlink($file_path);

			$this->EE->assets_lib->update_file_search_keywords($file_id);
			if ( ! $return_prompt)
			{
				return array('success' => TRUE, 'file_id' => $file_id);
			}
			else
			{
				$return_prompt['additional_info'] = $folder_id . ':' . $file_id;
				$return_prompt['new_file_id'] = $file_id;

				return $return_prompt;
			}
		}
		else
		{
			@unlink($file_path);
			return $result;
		}
	}

	/**
	 * Updates file info in DB (width/height/size/date_modified) and performs additional actions for image files
	 * @param Assets_base_file $file
	 * @param string $file_path file location
	 */
	public function update_file_info(Assets_base_file $file, $file_path = '')
	{
		$unlink_path = FALSE;
		if (empty($file_path))
		{
			$file_path = $file->get_local_copy();
			$unlink_path = TRUE;
		}

		if ($file->kind()  == 'image')
		{
			$this->post_upload_image_actions($file->file_id(), $file_path);
		}

		$time_modified = $file->date_modified(TRUE);
		$data = array(
			'date_modified' => $time_modified,
			'date' => $time_modified,
			'size' => $file->size('', TRUE)
		);

		$this->EE->db->update('assets_files', $data, array('file_id' => $file->file_id()));
		if ($unlink_path)
		{
			@unlink($file_path);
		}

		return $file->file_id();
	}

	/**
	 * Recursively updates folder of given id - sets the full path to the provided base + folder name
	 * @param stdclass $target to update
	 * @param stdclass $parent for source info
	 */
	private function _update_folder_info($target, $parent)
	{
		$new_full_path = $parent->full_path . $target->folder_name . '/';

		$data = array(
			'folder_name' => $target->folder_name,
			'full_path' => $new_full_path,
			'source_id' => $parent->source_id,
			'filedir_id' => $parent->filedir_id
		);

		$this->EE->db->update('assets_folders', $data, array('folder_id' => $target->folder_id));

		$rows = $this->EE->db->get_where('assets_folders', array('parent_id' => $target->folder_id))->result();

		// have to do this so we an just pass along this object instead of selecting the data again
		$target->source_type = $parent->source_type;
		$target->source_id = $parent->source_id;
		$target->filedir_id = $parent->filedir_id;
		$target->full_path = $new_full_path;

		foreach ($rows as $row)
		{
			$this->_update_folder_info($row, $target);
		}
	}

	/**
	 * Get a file by asset id
	 * @param int $file_id
	 * @param bool $return_missing if true return object even if file is missing
	 * @param array $file_row with prefetched data
	 * @return Assets_base_file
	 */
	public function get_file($file_id, $return_missing = FALSE, $file_row = null)
	{
		if (! isset($this->files[$file_id]))
		{
			$class_name = 'Assets_' . $this->get_source_type()  .'_file';

			$file = new $class_name($file_id, $this, $file_row);
			$this->files[$file_id] = $file;
		}

		return ($this->files[$file_id] && ($return_missing OR $this->files[$file_id]->exists())) ? $this->files[$file_id] : FALSE;
	}

	/**
	 * Move file from one path to another, if possible. Return false if not possible
	 * @param Assets_base_source $previous_source
	 * @param        $file_id
	 * @param        $folder_id
	 * @param        $file_name
	 * @param string $action action to take in case of naming conflict
	 * @throws Exception
	 * @return array
	 */
	public function move_file_inside_source(Assets_base_source $previous_source, $file_id, $folder_id, $file_name, $action)
	{
		if (!$this->can_move_files_from($previous_source))
		{
			return FALSE;
		}

		$file = $this->get_file($file_id);

		if (! $file)
		{
			throw new Exception(lang('invalid_file_path'));
		}

		if ($file->row_field('folder_id') == $folder_id && $file->filename() == $file_name)
		{
			return array('success' => TRUE, 'file_id' => $file_id);
		}

		$folder_row = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		$previous_folder_row = $this->EE->assets_lib->get_folder_row_by_id($file->row_field('folder_id'));

		// if this is not empty, we have a revisited conflict with some plan of action
		if ( ! empty($action))
		{
			switch ($action)
			{
				case Assets_helper::ACTIONS_REPLACE:
					$server_path = $this->_get_file_server_path($folder_row, $file_name);
					if ( ! $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_id, $file_name))
					{
						$this->_delete_source_file($server_path);
						$this->_purge_cached_source_file($server_path);

					}
					else
					{
						$this->delete_file($this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_id, $file_name));
						$this->_purge_cached_source_file($server_path);
					}

					break;
				case Assets_helper::ACTIONS_KEEP_BOTH:
					$file_name = $this->get_name_replacement($folder_row, $file_name);
					break;
			}
		}

		// Special case if renaming a file and just changing the case - if we don't handle this, it comes into conflict with itself.
		// Division by zero is child's play compared to this.
		if ($previous_folder_row->folder_id == $folder_row->folder_id && strtolower($file->filename()) == strtolower($file_name))
		{
			$overwrite = TRUE;
		}
		else
		{
			$overwrite = FALSE;
		}

		// now the source specific function has enough data to get to work
		$result = $this->_move_source_file($file, $previous_folder_row, $folder_row, $file_name, $overwrite);

		if (isset($result['success']))
		{
			$data = $file->row();
			$data['folder_id'] = $folder_id;
			$data['file_name'] = $result['new_file_name'];
			$data['source_id'] = $folder_row->source_id;
			$data['filedir_id'] = $folder_row->filedir_id;

			$this->EE->db->update('assets_files', $data, array('file_id' => $file_id));
		}

		return $result;
	}

	/**
	 * Delete file with the id
	 * @param $file_id
	 * @param $delete_missing boolean if TRUE will delete all records even if physical file cannot be found
	 * @throws Exception
	 * @return array
	 */
	public function delete_file($file_id, $delete_missing = FALSE)
	{
		$file = $this->get_file($file_id, $delete_missing);

		if ( !$file)
		{
			throw new Exception(lang('invalid_file_path'));
		}

		$this->EE->assets_lib->call_extension('assets_delete_file', array($file));

		$this->_delete_source_file($file->server_path());

		$this->EE->db->where('file_id', $file_id)
			->delete('assets_files');

		$this->EE->db->where('file_id', $file_id)
			->delete('assets_selections');

		$this->_delete_generated_thumbs($file_id);

		return array('success' => TRUE);

	}

	/**
	 * Transfers a file into this source from a file.
	 *
	 * @param string           $source_location
	 * @param string           $folder_id
	 * @param Assets_base_file $file
	 * @param                  $action
	 * @throws Exception
	 * @return array
	 */
	public function transfer_file_into_source($source_location, $folder_id, $file, $action)
	{

		$folder_row = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		$file_name = $file->filename();

		// swap whitespace with underscores
		$file_name = preg_replace('/\s+/', '_', $file_name);

		// if this is not empty, we have a revisited conflict with some plan of action
		if ( ! empty($action))
		{
			switch ($action)
			{
				case Assets_helper::ACTIONS_REPLACE:
				{
					$server_path = $this->_get_file_server_path($folder_row, $file_name);
					if ( ! $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_id, $file->filename()))
					{
						$this->_delete_source_file($server_path);
					}
					else
					{
						$this->delete_file($this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_id, $file_name), TRUE);
					}

					break;
				}

				case Assets_helper::ACTIONS_KEEP_BOTH:
				{
					$file_name = $this->get_name_replacement($folder_row, $file_name);
					break;
				}
			}
		}

		$result = $this->_do_upload_in_folder($folder_row, $source_location, $file_name, $action);

		if (isset($result['success']))
		{
			$file_row = $file->row();
			$file_row['source_type'] = $this->get_source_type();
			$file_row['source_id'] = $folder_row->source_id;
			$file_row['filedir_id'] = $folder_row->filedir_id;
			$file_row['folder_id'] = $folder_id;
			$file_row['file_name'] = $file_name;
			$this->EE->db->update('assets_files', $file_row, array('file_id' => $file->file_id()));
			return $result;
		}

		return $result;
	}

	/**
	 * Finalize file transfer out of this source
	 * @param Assets_base_file $file
	 * @return array
	 */
	public function finalize_outgoing_transfer(Assets_base_file $file)
	{
		return $this->_delete_source_file($file->server_path());
	}

	/**
	 * Prepare a source folder for an incoming file transfer from another source
	 * @param $parent_id
	 * @param $folder_id
	 * @throws Exception
	 * @internal param $folder_name
	 * @return array
	 */
	public function prepare_folder_for_transfer($parent_id, $folder_id)
	{
		$folder_data = $this->EE->assets_lib->get_folder_row_by_id($folder_id);
		$result = $this->create_folder($parent_id . '/' . $folder_data->folder_name);

		if ( isset($result['error']))
		{
			throw new Exception($result['error']);
		}

		return $result;
	}

	/**
	 * Finalize a folder transfer by deleting the source folder
	 * @param $folder_id
	 */
	public function finalize_folder_transfer($folder_id)
	{
		$this->delete_folder($folder_id);
		$folder_data = $this->EE->assets_lib->get_folder_row_by_id($folder_id);

		$this->_delete_source_folder(
			$this->get_folder_server_path($folder_data->full_path, $folder_data), $folder_data);
	}

	/**
	 * Return a result array for prompting the user about filename conflicts
	 * @param string $file_name the cause of all trouble
	 * @return array
	 */
	protected function _prompt_result_array($file_name)
	{
		return array(
			'prompt' => $this->EE->functions->var_swap(lang('file_already_exists__title'), array('file' => $file_name)),
			'file_name' => $file_name,
			'choices' => array(
				array('value' => Assets_helper::ACTIONS_KEEP_BOTH, 'title' => lang('file_already_exists__keep_both')),
				array('value' => Assets_helper::ACTIONS_REPLACE, 'title' => lang('file_already_exists__replace')),
				array('value' => Assets_helper::ACTIONS_CANCEL, 'title' => lang('file_already_exists__cancel'))
			)
		);
	}

	/**
	 * Return a result array for prompting the user about folder conflicts
	 * @param string $folder_name the caused of all trouble
	 * @param int $folder_id
	 * @return array
	 */
	protected function _folder_prompt_result_array($folder_name, $folder_id)
	{
		return array(
			'prompt' => $this->EE->functions->var_swap(lang('folder_already_exists__title'), array('folder' => $folder_name)),
			'file_name' => $folder_id,
			'choices' => array(
				array('value' => Assets_helper::ACTIONS_REPLACE, 'title' => lang('folder_already_exists__replace')),
				array('value' => Assets_helper::ACTIONS_CANCEL, 'title' => lang('folder_already_exists__cancel'))
			)
		);
	}

	/**
	 * Replace physical file
	 * @param Assets_base_file $old_file
	 * @param Assets_base_file $replace_with
	 */
	public function replace_file(Assets_base_file $old_file, Assets_base_file $replace_with)
	{
		if ($old_file->kind() == 'image')
		{
			// we'll need this if replacing images
			$local_copy = $replace_with->get_local_copy();

		}

		$this->_delete_source_file($old_file->server_path());
		$this->_purge_cached_source_file($old_file->server_path());
		$this->_delete_generated_thumbs($old_file->file_id());

		$this->_move_source_file($replace_with, $replace_with->folder_row(), $old_file->folder_row(), $old_file->filename());

		if ($old_file->kind() == 'image')
		{
			$this->post_upload_image_actions($old_file->file_id(), $local_copy);
		}

		$data = array(
			'width' => (int) $replace_with->width(),
			'height' => (int) $replace_with->height(),
			'size' => $replace_with->size(),
			'date_modified' => $replace_with->date_modified()
		);

		$this->EE->db->update('assets_files', $data, array('file_id' => $old_file->file_id()));

	}

	/**
	 * Return source row
	 * @return null|StdClass
	 */
	public function get_source_row()
	{
		return $this->_source_row;
	}

	/**
	 * Get folder preferences
	 * @param $parameters
	 * @return mixed
	 */
	protected function _get_sources($parameters)
	{
		$result = $this->EE->db->get_where('assets_sources', $parameters)->result();

		foreach ($result as &$row)
		{
			$row->settings = json_encode(Assets_helper::apply_source_overrides($row->source_id, json_decode($row->settings)));
		}

		return $result;
	}

	/**
	 * Store folder data
	 * @param $data
	 * @return $insert_id
	 */
	protected function _store_folder($data)
	{
		$this->EE->db->insert('assets_folders', $data);
		return $this->EE->db->insert_id();
	}

	/**
	 * Finds folder row by parameters
	 * @param $parameters
	 * @return mixed
	 */
	protected function _find_folder($parameters)
	{
		$result = $this->EE->db->get_where('assets_folders', $parameters)->result();
		if (empty($result))
		{
			return FALSE;
		}
		return $result[0];
	}

	/**
	 * Delete thumbnails generated for this file by assets
	 * @param $file_id
	 */
	protected function _delete_generated_thumbs($file_id)
	{
		$thumb_path = Assets_helper::ensure_cache_path('assets/thumbs');
		$files = glob($thumb_path . '/' . $file_id . '/*');
		if (is_array($files))
		{
			foreach ($files as $path)
			{
				@unlink($path);
			}
		}
	}

	/**
	 * Returns TRUE if extension is allowed by configurations
	 * @param $extension
	 * @return bool
	 */
	protected function _is_extension_allowed($extension)
	{
		// check if file is valid according to config/mimes.php
		$valid_mime = TRUE;

		global $mimes;

		if (! is_array($mimes))
		{
			require_once(APPPATH.'config/mimes.php');
		}

		if (is_array($mimes) && ! isset($mimes[strtolower($extension)]))
		{
			$valid_mime = FALSE;
		}

		return $valid_mime;
	}

	/**
	 * Store file data
	 * @param $data
	 * @return $insert_id
	 */
	protected function _store_file($data)
	{
		$this->EE->db->insert('assets_files', $data);
		return $this->EE->db->insert_id();
	}

	/**
	 * Update file data
	 * @param $data
	 * @param $file_id
	 */
	protected function _update_file($data, $file_id)
	{
		$this->EE->db->where('file_id', $file_id);
		$this->EE->db->update('assets_files', $data);
		$this->EE->assets_lib->update_file_search_keywords($file_id);
	}

	/**
	 * Store a index entry
	 * @param $session_id
	 * @param $source_type
	 * @param $source_id
	 * @param $offset
	 * @param $uri
	 * @param int $size
	 */
	protected function _store_index_entry($session_id, $source_type, $source_id, $offset, $uri, $size = 0)
	{
		$this->_index_batch_entries[] = array(
				'session_id' => $session_id,
				'source_type' => $source_type,
				'source_id' => $source_id,
				'offset' => $offset,
				'uri' => $uri,
				'filesize' => $size
			);

		if (count($this->_index_batch_entries) == 100)
		{
			$this->_execute_index_batch();
		}
	}

	/**
	 * Do a multi-row insert of index entries
	 */
	protected function _execute_index_batch()
	{
		$query = "INSERT INTO " . $this->EE->db->dbprefix . 'assets_index_data (session_id, source_type, source_id, offset, uri, filesize) VALUES ';
		foreach ($this->_index_batch_entries as $row)
		{
			$row_insert = '(';
			foreach ($row as $value)
			{
				$row_insert .=  $this->EE->db->escape($value) . ",";
			}
			$row_insert = rtrim($row_insert, ',') . ')';

			$query .= $row_insert . ',';
		}
		$query = rtrim($query, ',');
		if (!empty($this->_index_batch_entries))
		{
			$this->EE->db->query($query);
		}

		$this->_index_batch_entries = array();
	}

	/**
	 * @param $parameters
	 * @return bool
	 */
	protected function _get_index_entry($parameters)
	{
		$result = $this->EE->db->get_where('assets_index_data', $parameters)->result();
		if (empty($result))
		{
			return FALSE;
		}
		return $result[0];
	}

	/**
	 * Return a prefix for sources that have subfolder set
	 *
	 * @param $source_data
	 * @return string
	 */
	protected function _get_path_prefix($source_data = '')
	{
		$settings = empty($source_data) ? $this->settings() : $source_data;

		if (!empty($settings->subfolder))
		{
			return rtrim($settings->subfolder, '/').'/';
		}

		return "";
	}

	/**
	 * Purge a file from the cache. Sources that need this should override this method.
	 *
	 * @param $server_path
	 */
	protected function _purge_cached_source_file($server_path)
	{
		return;
	}

	/**
	 * Check if a file exists on the server.
	 *
	 * @param $folder
	 * @param $file_name
	 * @return bool|mixed
	 */
	public function source_file_exists($folder, $file_name)
	{
		return $this->_source_file_exists($this->_get_file_server_path($folder, $file_name));
	}

	/**
	 * Return true if a folder path is allowed according to settings.
	 *
	 * @param $path
	 * @return bool
	 */
	protected function _is_allowed_folder_path($path)
	{
		$parts = explode('/', rtrim($path, '/'));

		// The folder or one of it's parent folders is no good. Throw it out.
		foreach ($parts as $path_part)
		{
			if (!Assets_helper::is_allowed_folder_name($path_part))
			{
				// Continue the outer loop
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Return true if a file path is allowed according to settings.
	 *
	 * @param $path
	 * @return bool
	 */
	protected function _is_allowed_file_path($path)
	{

		$filename = pathinfo($path, PATHINFO_BASENAME);
		$path_to = pathinfo($path, PATHINFO_DIRNAME);

		// Check if folder is allowed
		if (!empty($path_to) && $path_to != '.' && !$this->_is_allowed_folder_path($path_to))
		{
			return FALSE;
		}

		return Assets_helper::is_allowed_file_name($filename);
	}
}
