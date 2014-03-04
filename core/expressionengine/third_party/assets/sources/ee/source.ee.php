<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets EE Upload Directory source
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_ee_source extends Assets_base_source
{
	protected $_source_id = 0;
	protected $_source_type = 'ee';

	private $_ignore_restrictions = FALSE;

	public function __construct($source_id, $filedir_prefs, $ignore_restrictions)
	{
		parent::__construct();

		$filedir_prefs = self::apply_filedir_overrides($filedir_prefs);

		$banned_or_guest = !$this->EE->session->userdata('member_id') OR $this->EE->session->userdata('is_banned');
		$filedir_denied = in_array($source_id, self::get_denied_filedirs());
		$assets_action = in_array($this->EE->input->get_post('ACT'), Assets_helper::get_asset_action_ids());

		// Enforce restrictions, if needed
		if (!$ignore_restrictions && (REQ == 'ACTION' && $assets_action && ($banned_or_guest OR $filedir_denied)))
		{
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

		$this->_ignore_restrictions = $ignore_restrictions;

		$this->_source_id = $source_id;

		$filedir_prefs->server_path = self::resolve_server_path($filedir_prefs->server_path);
		$this->_source_settings = $filedir_prefs;
	}

	/**
	 * Returns TRUE if the other source is also an EE source.
	 * @param Assets_base_source $source
	 * @return mixed
	 */
	public function can_move_files_from(Assets_base_source $source)
	{
		return $source instanceof Assets_ee_source;
	}

	/**
	 * No settings for EE source
	 * @return array
	 */
	public static function get_settings_field_list()
	{
		return array();
	}

	/**
	 * Get all filedirs
	 * @return array|bool
	 */
	public static function get_all_filedirs()
	{
		$filedirs = get_instance()->db->get_where('upload_prefs')->result();
		foreach ($filedirs as &$filedir)
		{
			$filedir = self::apply_filedir_overrides($filedir);
		}

		return $filedirs;
	}

	// --------------------------------------------------------------------
	//  Internal methods
	// --------------------------------------------------------------------

	/**
	 * Get Upload Directory Preferences
	 */
	private function _get_filedir_prefs($filedirs = 'all', $site_id = NULL)
	{
		// -------------------------------------------
		//  Figure out what we already have cached
		// -------------------------------------------

		if ($filedirs == 'all')
		{
			$run_query = ! isset($this->cache['filedir_prefs']['all']);
		}
		else
		{
			if (($return_single = ! is_array($filedirs)))
			{
				$filedirs = array($filedirs);
			}

			// figure out which of these we don't already have cached
			foreach ($filedirs as $filedir)
			{
				if (! isset($this->cache['filedir_prefs'][$filedir]))
				{
					$not_cached[] = $filedir;
				}
			}

			$run_query = isset($not_cached);
		}

		// -------------------------------------------
		//  Query and cache the remaining filedirs
		// -------------------------------------------

		if ($run_query)
		{
			// enforce access permissions for non-Super Admins, except on front-end pages
			if (!$this->_ignore_restrictions && REQ != 'PAGE' && ($denied = self::get_denied_filedirs()))
			{
				$this->EE->db->where_not_in('id', $denied);
			}

			if ($filedirs != 'all')
			{
				// limit to specific upload directories
				$this->EE->db->where_in('id', $filedirs);
			}
			else
			{
				// limit to upload directories from the current site, except on front-end pages
				if (REQ != 'PAGE')
				{
					if (! $site_id)
					{
						$site_id = $this->EE->config->item('site_id');
					}

					// unless specified as "all", apply the restriction
					if ($site_id != 'all')
					{
						$this->EE->db->where('site_id', $site_id);
					}
				}

				// order by name
				$upload_prefs = $this->EE->db->order_by('name');
			}

			// run the query
			$query = $this->EE->db->get('upload_prefs')->result();

			// cache the results
			foreach ($query as $filedir)
			{

				$filedir = self::apply_filedir_overrides($filedir);

				if (REQ != 'CP')
				{
					// relative paths are usually relative to the system directory,
					// but Assets' AJAX functions are loaded via the site URL
					// so attempt to turn relative paths into absolute paths

					$filedir->server_path = Assets_helper::normalize_path($filedir->server_path);
					if (! preg_match('/^(\/|\\\|[a-zA-Z]+:)/', $filedir->server_path))
					{
						// if the CP is masked, there's no way for us to determine the path to the CP's entry point
						// so people with relative upload directory paths _and_ masked CPs will have to point us in the right direction
						if (($cp_path = $this->EE->config->item('assets_cp_path')) !== FALSE)
						{
 							$cp_path = Assets_helper::normalize_path($cp_path);
 							$filedir->server_path = rtrim($cp_path, '/').'/'.$filedir->server_path;
						}
						else
						{
							$filedir->server_path = SYSDIR.'/'.$filedir->server_path;
						}
					}
				}

				$this->cache['filedir_prefs'][$filedir->id] = $filedir;
			}

			if ($filedirs == 'all')
			{
				$this->cache['filedir_prefs']['all'] = $query;
			}
		}

		// -------------------------------------------
		//  Sort and return the upload prefs
		// -------------------------------------------

		if ($filedirs == 'all')
		{
			return $this->cache['filedir_prefs']['all'];
		}

		if ($return_single)
		{
			return isset($this->cache['filedir_prefs'][$filedirs[0]]) ? $this->cache['filedir_prefs'][$filedirs[0]] : FALSE;
		}

		$r = array();

		foreach ($filedirs as $filedir)
		{
			if (isset($this->cache['filedir_prefs'][$filedir]))
			{
				$r[] = $this->cache['filedir_prefs'][$filedir];
				$sort_names[] = strtolower($this->cache['filedir_prefs'][$filedir]->name);
			}
		}

		if ($r)
		{
			array_multisort($sort_names, SORT_ASC, SORT_STRING, $r);
		}

		return $r;
	}

	/**
	 * Get dened filedirs for the current user.
	 *
	 * @return array
	 */
	public static function get_denied_filedirs()
	{
		static $denied_filedirs = null;

		if (is_null($denied_filedirs))
		{
			$denied = array();

			$group = get_instance()->session->userdata('group_id');

			if ($group != 1)
			{
				$no_access = get_instance()->db->select('upload_id')
					->where('member_group', $group)
					->get('upload_no_access');

				if ($no_access->num_rows() > 0)
				{
					foreach ($no_access->result() as $result)
					{
						$denied[] = $result->upload_id;
					}
				}
			}

			$denied_filedirs = $denied;
		}

		return $denied_filedirs;
	}

	/**
	 * Parse Upload Directory Path
	 */
	private function _parse_filedir_path($path, &$filedir, &$subpath)
	{
		// is this actually a {filedir_x} path?
		if (preg_match('/^\{filedir_(\d+)\}?(.*)/', $path, $match))
		{
			// is this a valid file directory?
			if ($filedir = $this->get_filedir($match[1]))
			{
				$subpath = ltrim($match[2], '/');

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Is a Folder?
	 */
	private function _is_folder($path)
	{
		return (file_exists($path) && is_dir($path));
	}

	/**
	 * Add Trailing Slash
	 */
	private function _add_trailing_slash($path)
	{
		$path = Assets_helper::normalize_path($path);
		return rtrim($path, '/') . '/';
	}

	/**
	 * Delete Folder and all its contents
	 */
	protected function _delete_source_folder($server_path)
	{
		// delete all children (for example, hidden subfolders or hidden files)
		if ( ! is_dir($server_path))
		{
			return false;
		}

		$server_path = $this->_add_trailing_slash($server_path);
		$files = glob($server_path . '*', GLOB_MARK);

		if (is_array($files))
		{
			foreach ($files as $file)
			{
				if (is_dir($file))
				{
					$this->_delete_source_folder($file);
				}
				else
				{
					$this->_delete_source_file($file);
				}
			}
		}

		$ret = rmdir($server_path);
		return $ret;
	}

	/**
	 * Delete file.
	 *
	 * @param $server_path
	 * @param $source_data
	 */
	protected function _delete_source_file($server_path, $source_data = array())
	{
		if (@unlink($server_path))
		{
			// delete the exp_files record
			$this->EE->db
				->where('rel_path', $server_path)
				->delete('files');

			$this->_delete_thumbnails($server_path, $this->_source_id);

			return array('success' => TRUE);
		}
	}

	/**
	 * Prep Filename
	 * @param string $path
	 * @param string $original
	 * @param object $folder_row if not false, will also check for conflicts in DB
	 * @return boolean $result
	 */
	private function _prep_filename(&$path, $original = FALSE, $folder_row = FALSE)
	{
		// save a copy of the target path
		$_path = $path;

		$original = $original ? strtolower($original) : FALSE;

		$pathinfo = pathinfo($path);
		$folder = $pathinfo['dirname'].'/';
		if (isset($pathinfo['filename']))
		{
			$filename = $pathinfo['filename'];
		}
		else
		{
			// PHP < 5.2 compatibility
			$filename = str_replace(pathinfo($path, PATHINFO_DIRNAME).'/', '', $path);
			$parts = explode(".", $filename);
			array_pop($parts);
			$filename = join(".", $parts);
		}
		$ext = (isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '');

		$filename = $this->EE->assets_lib->clean_filename($filename);

		$path = $folder.$filename.$ext;

		// -------------------------------------------
		//  Make sure it's unique
		// -------------------------------------------

		$i = 1;

		$attempted_filename = $filename.$ext;

		while (
			(! $original || strtolower($path) != $original) &&
			(file_exists($path) || (is_object($folder_row) && $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_row->folder_id, $attempted_filename)))
		)
		{
			$attempted_filename = $filename.'_'.($i++).$ext;
			$path = $folder.$attempted_filename;

		}

		// -------------------------------------------
		//  Return whether the filename has changed
		// -------------------------------------------

		return ($path != $_path);
	}


	/**
	 * Get Folder's Server Path
	 */
	public function get_folder_server_path($path)
	{
		$filedir_prefs = $this->_source_settings;

		if ( ! is_object($filedir_prefs))
		{
			return FALSE;
		}

		return $this->_add_trailing_slash(self::resolve_server_path($filedir_prefs->server_path) . $path);
	}

	/**
	 * Upload File
	 */
	protected function _do_upload_in_folder($folder_data, $temp_file_path, $original_name)
	{
		$filedir = $this->get_filedir($folder_data->filedir_id);

		// make sure the file is under the Max File Size limit, if set
		if ($filedir->max_size && filesize($temp_file_path) > $filedir->max_size)
		{
			$error = $this->EE->functions->var_swap(lang('file_too_large'), array(
				'max_size' => Assets_helper::format_filesize($filedir->max_size)
			));

			return array('error' => $error);
		}

		$server_path = $this->get_folder_server_path($folder_data->full_path, $folder_data);

		// make sure this is a valid upload directory path
		if (! $server_path)
		{
			return array('error' => lang('invalid_filedir_path'));
		}

		// make sure the folder is writable
		if (! is_writable($server_path))
		{
			return array('error' => lang('filedir_not_writable'));
		}

		$original_name = $this->EE->assets_lib->clean_filename($original_name);

		$file_path = $server_path . $original_name;
		$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

		$file_kinds = Assets_helper::get_file_kinds();
		$is_image = in_array($ext, $file_kinds['image']);

		// make sure the file is an image, if Allowed Types is set to Images Only
		if ($filedir->allowed_types == 'img' && ! $is_image)
		{
			return array('error' => lang('images_only_allowed'));
		}

		if ( ! $this->_is_extension_allowed($ext))
		{
			return array('error' => lang('filetype_not_allowed'));
		}

		if (file_exists($file_path)
			OR (empty($this->cache['merge_in_progress']) && $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_data->folder_id, $original_name))
		)
		{
			return $this->_prompt_result_array($original_name);
		}

		// make sure the filename is clean and unique
		$this->_prep_filename($file_path);

		// copy here, since it will be unlinked later on
		if ( ! copy($temp_file_path, $file_path))
		{
			return array('error'=> lang('couldnt_save'));
		}

		@chmod($file_path, FILE_WRITE_MODE);

		$source_server_path = $this->get_filedir($folder_data->filedir_id)->server_path;

		// for top level folders, add it to the exp_files table.
		if (empty($folder_data->parent_id)
			&& substr($source_server_path, 0, 3) != '../'
			&& strpos($source_server_path, SYSDIR) === FALSE
		)
		{
			$this->_store_file_data($file_path, $filedir->id);
		}
		else if ($is_image)
		{
			$this->_create_thumbnails($file_path, $filedir->id);
		}

		return array('success' => TRUE, 'path' => $file_path);
	}

	/**
	 * @param Assets_base_file $file
	 * @param                  $previous_folder_row
	 * @param                  $folder_row
	 * @param string           $new_file_name
	 * @param bool             $overwrite
	 * @return array|mixed
	 */
	protected function _move_source_file(Assets_base_file $file, $previous_folder_row, $folder_row, $new_file_name = '', $overwrite = FALSE)
	{
		if (empty($new_file_name))
		{
			$new_file_name = $file->filename();
		}

		$new_server_path = $this->get_folder_server_path($folder_row->full_path, $folder_row) . $new_file_name;

		if (!$overwrite && (file_exists($new_server_path)
			OR (empty($this->cache['merge_in_progress']) && $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_row->folder_id, $new_file_name))))
		{
			return $this->_prompt_result_array($new_file_name);
		}

		if (!$overwrite)
		{
			// make sure the filename is clean and unique
			$this->_prep_filename($new_server_path, $file->server_path());
		}

		// attempt to rename the file
		if (! @rename($file->server_path(), $new_server_path))
		{
			return array('error' => lang('couldnt_save'));
		}

		$new_filename = pathinfo($new_server_path, PATHINFO_BASENAME);

		$is_image = $file->kind() == 'image';

		// moved from top level
		if ($previous_folder_row->parent_id == 0)
		{
			// to a different one - UPDATE
			if ($folder_row->parent_id == 0)
			{
				$filedir = $this->get_filedir($folder_row->filedir_id);
				$this->EE->db->where('upload_location_id', $previous_folder_row->filedir_id)
					->where('file_name', $file->filename())
					->update('files', array(
					'site_id' => $filedir->site_id,
					'upload_location_id' => $filedir->id,
					'rel_path' => $new_server_path,
					'file_name' => $new_filename
				));
			}
			// out of exp_files scope - DELETE
			else
			{
				$this->EE->db->where('upload_location_id', $previous_folder_row->filedir_id)
					->where('file_name', $file->filename())
					->delete('files');
			}
		}
		else
		{
			$source_server_path = $this->get_filedir($folder_row->filedir_id)->server_path;

			// to a top level one - INSERT
			// if we can do this without EE complaining
			if ($folder_row->parent_id == 0
				&& substr($source_server_path, 0, 3) != '../'
				&& strpos($source_server_path, SYSDIR) === FALSE
			)
			{
				$this->_store_file_data($new_server_path, $folder_row->filedir_id);
			}
		}

		if ($is_image)
		{
			$this->_delete_thumbnails($file->server_path(), $previous_folder_row->filedir_id);
			$this->_create_thumbnails($new_server_path, $folder_row->filedir_id);
		}

		return array(
			'success' => TRUE,
			'file_id' => $file->file_id(),
		 	'new_file_name' => $new_filename);
	}

	/**
	 * Stores file data however filemanager pleases
	 *
	 * @param $file_path string absolute path to file
	 * @param $upload_folder_id
	 */
	private function _store_file_data($file_path, $upload_folder_id)
	{
		$this->EE->load->library('filemanager');

		$file_path = Assets_helper::normalize_path($file_path);
		$file_name = substr($file_path, strrpos($file_path, '/') + 1);

		$preferences = array();
		$preferences['rel_path'] = $file_path;
		$preferences['file_name'] = $file_name;
		$preferences['file_size'] = filesize($file_path);
		$preferences['uploaded_by_member_id'] = $this->EE->session->userdata('member_id');

		$this->cache['filemanager_extension_ignore_files'][$upload_folder_id . $file_name] = TRUE;


		$file_size = @getimagesize($file_path);

		if ($file_size !== FALSE)
		{
			$preferences['file_hw_original'] = $file_size[1].' '.$file_size[0];
		}

		$filedir = $this->get_filedir($upload_folder_id);
		$site_id = $filedir->site_id;
		$this->EE->config->site_prefs('', $site_id);
		if (substr($filedir->server_path, 0, 3) != '../')
		{
			$this->EE->filemanager->save_file($file_path, $upload_folder_id, $preferences);
		}
	}

	/**
	 * Creates thumbnails for uploaded image according to image manipulations specified
	 *
	 * @param string $image_path
	 * @param int $upload_folder_id
	 * @return bool
	 */
	private function _create_thumbnails($image_path, $upload_folder_id)
	{
		$this->EE->load->library('filemanager');
		$this->EE->load->helper('file_helper');

		$filedir = $this->get_filedir($upload_folder_id);

		$this->EE->filemanager->max_hw_check($image_path, (array) $filedir);

		$site_id = $filedir->site_id;
		$this->EE->config->site_prefs('', $site_id);

		$image_path = Assets_helper::normalize_path($image_path);
		return Assets_helper::create_ee_thumbnails($image_path, $upload_folder_id);
	}

	/**
	 * Delete all thumbnails and images created by manipulations for provided image
	 * @param string $image_path
	 * @param int $upload_folder_id
	 */
	private function _delete_thumbnails($image_path, $upload_folder_id)
	{
		$this->EE->load->library('filemanager');

		$image_path = Assets_helper::normalize_path($image_path);
		$file_name = substr($image_path, strrpos($image_path, '/') + 1);

		@unlink(str_replace($file_name, '', $image_path) . '_thumbs/' . $file_name);

		// Then, delete the dimensions
		$this->EE->load->model('file_model');
		$file_dimensions = $this->EE->file_model->get_dimensions_by_dir_id($upload_folder_id);

		foreach ($file_dimensions->result() as $file_dimension)
		{
			@unlink(str_replace($file_name, '', $image_path) . '_' . $file_dimension->short_name . '/' . $file_name);
		}
	}

	/**
	 * Start indexing
	 * @param $session_id
	 * @return array
	 */
	public function start_index($session_id)
	{
		$filedir = $this->_source_settings;
		$filedir->server_path = Assets_helper::normalize_path($filedir->server_path);

		$file_list = array();

		$this->_load_folder_contents($filedir->server_path, $file_list);
		$offset = 0;


		// Let's assume that we'll need more memory if we hit an arbitrary amount of entries
		if (count($file_list) > 2000)
		{
			ini_set('memory_limit', '64M');
		}

		$indexed_folder_ids = array();

		$folder_row = $this->_find_folder(array('source_type' => $this->get_source_type(), 'filedir_id' => $this->get_source_id(), 'parent_id' => NULL));

		if ( empty($folder_row))
		{
			// this is a new folder - insert into DB
			$data = array (
				'source_type' => $this->get_source_type(),
				'folder_name' => $filedir->name,
				'full_path' => '',
				'filedir_id' => $this->get_source_id(),
			);

			$indexed_folder_ids[$this->_store_folder($data)] = TRUE;
		}
		else
		{
			if ($folder_row->folder_name != $filedir->name)
			{
				$this->EE->assets_lib->rename_source_folder($this->get_source_id(), 'ee', $filedir->name);
			}

			$indexed_folder_ids[$folder_row->folder_id] = TRUE;
		}

		foreach ($file_list as $file)
		{

			if (is_dir($file))
			{
				$full_path = rtrim(str_replace($filedir->server_path, '', $file), '/') . '/';
				$parts = explode('/', rtrim($full_path, '/'));

				if (!$this->_is_allowed_folder_path($full_path))
				{
					continue;
				}

				$folder_search = array(
					'source_type' => $this->get_source_type(),
					'filedir_id' => $this->get_source_id(),
					'full_path' => $full_path
				);

				$folder_row = $this->_find_folder($folder_search);

				// new folder
				if (empty($folder_row))
				{
					$folder_name = array_pop($parts);

					// check for parent by path segment in table
					$folder_search['full_path'] = join('/', $parts) . '/';

					if ($folder_search['full_path'] == '/')
					{
						$folder_search['full_path'] = '';
					}
					$parent_row = $this->_find_folder($folder_search);

					if (empty($parent_row))
					{
						$parent_id = NULL;
					}
					else
					{
						$parent_id = $parent_row->folder_id;
					}

					$data = array (
						'source_type' => $this->get_source_type(),
						'folder_name' => $folder_name,
						'full_path' =>  $full_path,
						'filedir_id' => $this->get_source_id()
					);
					if (! is_null($parent_id))
					{
						$data['parent_id'] = $parent_id;
					}
					$indexed_folder_ids[$this->_store_folder($data)] = TRUE;
				}
				else
				{
					$indexed_folder_ids[$folder_row->folder_id] = TRUE;
				}
			}
			else
			{
				$full_path = str_replace($filedir->server_path, '', $file);
				if (!$this->_is_allowed_file_path($full_path))
				{
					continue;
				}

				$this->_store_index_entry($session_id, $this->get_source_type(), $this->get_source_id(), $offset++, $file);
			}
		}

		$this->_execute_index_batch();

		// figure out the obsolete records for folders
		$missing_folder_ids = array();
		$all_folders = $this->EE->db->select('folder_id, full_path')
			->where('filedir_id', $filedir->id)
			->get('assets_folders')->result();

		foreach ($all_folders as $folder_row)
		{
			if (!isset($indexed_folder_ids[$folder_row->folder_id]))
			{
				$missing_folder_ids[$folder_row->folder_id] = $filedir->name . '/' . $folder_row->full_path;
			}
		}

		return array(
			'source_type' => $this->get_source_type(),
			'source_id' => $this->get_source_id(),
			'total' => count($file_list),
			'missing_folders' => $missing_folder_ids);
	}

	/**
	 * Start indexing a folder
	 * @param $session_id
	 * @param StdClass $folder_row
	 * @return array
	 */
	public function start_folder_index($session_id, $folder_row)
	{
		$filedir = $this->_source_settings;

		$resolvedPath = $filedir->server_path.$folder_row->full_path;
		$file_list = glob($resolvedPath.'[!_.]*', GLOB_MARK);

		$offset = 0;
		$count = 0;
		if (is_array($file_list))
		{
			foreach ($file_list as $file)
			{
				// parse folders and add files
				$file = Assets_helper::normalize_path($file);
				if (substr($file, -1) != '/' && Assets_helper::is_allowed_file_name(pathinfo($file, PATHINFO_BASENAME)))
				{
					$count++;
					$this->_store_index_entry($session_id, $this->get_source_type(), $this->get_source_id(), $offset++, $file);
				}
			}
			$this->_execute_index_batch();
		}

		return array(
			'total' => $count,
		);

	}

	/**
	 * Perform indexing
	 * @param $session_id int
	 * @param $offset
	 * @return boolean
	 */
	public function process_index($session_id, $offset)
	{
		$search_parameters = array(
			'session_id' => $session_id,
			'source_type' => $this->get_source_type(),
			'source_id' => $this->get_source_id(),
			'offset' => $offset
		);

		$index_entry = $this->_get_index_entry($search_parameters);

		// can't find the file. awkward. avoid eye contact and return next offset
		if (empty($index_entry))
		{
			return FALSE;
		}

		$filedir = $this->settings();

		$upload_folder_path = $filedir->server_path;

		$file = $index_entry->uri;

		// get the relevant path - the part that is not shared with the upload folder
		$relevant_path = Assets_helper::normalize_path(substr($file, strlen($upload_folder_path)));

		$file_indexed = FALSE;

		if ( $this->_is_extension_allowed(pathinfo($file, PATHINFO_EXTENSION)))
		{
			$parts = explode('/', $relevant_path);
			$file_name = array_pop($parts);

			$search_full_path = join('/', $parts) . '/';
			if ($search_full_path == '/')
			{
				$search_full_path = '';
			}
			$folder_search = array(
				'source_type' => $this->get_source_type(),
				'filedir_id' => $this->get_source_id(),
				'full_path' => $search_full_path
			);

			// check for parent by path segment in table
			$parent_row = $this->_find_folder($folder_search);

			if (empty($parent_row))
			{
				return FALSE;
			}
			$folder_id = $parent_row->folder_id;

			$file_id = $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_id, $file_name);

			// new file?
			if ( empty($file_id))
			{
				$data = array(
					'folder_id' => $folder_id,
					'source_type' => $this->get_source_type(),
					'filedir_id' => $this->get_source_id(),
					'file_name' => $file_name,
					'kind' => Assets_helper::get_kind($file)
				);

				$file_id = $this->_store_file($data);
				$this->EE->db->update('assets_index_data', array('record_id' => $file_id), $search_parameters);
			}
			else
			{
				$this->EE->db->update('assets_index_data', array('record_id' => $file_id), $search_parameters);
			}

			$file_indexed = $file_id;
		}

		// add image dimensions and size as well
		if ( $file_indexed)
		{
			$data = array(
				'size' => filesize($file),
				'date_modified' => filemtime($file)
			);

			$file_row = $this->EE->assets_lib->get_file_row_by_id($file_id);
			if (!$file_row->date)
			{
				$data['date'] = $file_row->date_modified ? $file_row->date_modified : $data['date_modified'];
			}

			if (Assets_helper::get_kind($file) == 'image')
			{
				list ($width, $height) = getimagesize($file);
				$data['width'] = $width;
				$data['height'] = $height;
				@$this->_create_thumbnails($file, $this->get_source_id());
			}

			$this->_update_file($data, $file_indexed);
		}

		return TRUE;
	}

	/**
	 * Recursively load folder contents for $path and store them in $folder_files
	 *
	 * @param $path
	 * @param $folder_files
	 */
	private function _load_folder_contents($path, &$folder_files)
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
					$folder_files[] = substr($item, 0, -1);
					$this->_load_folder_contents($item, $folder_files);
				}
				else
				{
					$folder_files[] = $item;
				}
			}
		}
	}

	/**
	 * Return filedir info
	 *
	 * @param $filedir_id
	 * @return array|bool|mixed|null
	 */
	public function get_filedir($filedir_id)
	{
		return $this->_get_filedir_prefs($filedir_id);
	}

	/**
	 * @param $path
	 * @return array|void
	 */
	protected function _create_source_folder($path)
	{
		return mkdir($path, DIR_WRITE_MODE);
	}

	/**
	 * @param $path
	 * @return array|void
	 */
	protected function _source_folder_exists($path)
	{
		return file_exists($path) && is_dir($path);
	}

	/**
	 * @param $path
	 * @return array|void
	 */
	protected function _source_file_exists($path)
	{
		return file_exists($path) && !is_dir($path);
	}

	/**
	 * @param StdClass $old_path
	 * @param StdClass $new_path
	 * @return bool
	 */
	protected function _rename_source_folder($old_path, $new_path)
	{
		return @rename($old_path, $new_path);
	}

	/**
	 * @param $file_id
	 * @param $file_path
	 * @return mixed|void
	 */
	public function post_upload_image_actions($file_id, $file_path)
	{
		clearstatcache();
		list ($width, $height) = getimagesize($file_path);
		$this->EE->db->update('assets_files', array('width' => $width, 'height' => $height), array('file_id' => $file_id));
	}

	/**
	 * Get name replacement for a filename
	 *
	 * @param $folder_row
	 * @param $file_name
	 * @return mixed|void
	 */
	public function get_name_replacement($folder_row, $file_name)
	{
		$filedir = $this->get_filedir($folder_row->filedir_id);
		$full_path = $filedir->server_path . $folder_row->full_path . $file_name;

		$this->_prep_filename($full_path, FALSE, $folder_row);

		return pathinfo($full_path, PATHINFO_BASENAME);
	}

	/**
	 * Get server path for a file
	 *
	 * @param $folder_row
	 * @param $file_name
	 * @return string
	 */
	protected function _get_file_server_path($folder_row, $file_name)
	{
		$filedir = $this->get_filedir($folder_row->filedir_id);
		return $filedir->server_path  . $folder_row->full_path . $file_name;
	}

	/**
	 * Resolves a server path, accounting for whether it's relative or not.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function resolve_server_path($path)
	{
		// Relative paths are always relative to the system directory,
		// but Assets' AJAX functions are loaded via the site URL
		// so attempt to turn relative paths into absolute paths
		if (REQ != 'CP' && !file_exists($path) && !preg_match('/^(\/|\\\|[a-zA-Z]+:)/', $path))
		{
			// Is $config['assets_cp_path'] set?
			if ($cp_path = get_instance()->config->item('assets_cp_path'))
			{
				$path = rtrim($cp_path, '/').'/'.$path;
			}
			else
			{
				// Take a shot in the dark...
				$test_path = SYSDIR.'/'.$path;

				if (file_exists($test_path))
				{
					$path = $test_path;
				}
			}
		}

		return $path;
	}

	/**
	 * Applies filedir overrides from config.php file.
	 * @param $filedir
	 * @return mixed
	 */
	static public function apply_filedir_overrides($filedir)
	{
		static $overrides = null;
		if (is_null($overrides))
		{
			$overrides = get_instance()->config->item('upload_preferences');;
		}

		if (isset($overrides[$filedir->id]))
		{
			foreach ($overrides[$filedir->id] as $property => $value)
			{
				$filedir->{$property} = $value;
			}
		}

		$filedir->server_path = rtrim(Assets_helper::normalize_path($filedir->server_path), '/') . '/';

		return $filedir;
	}
}
