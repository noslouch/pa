<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets Google Cloud source
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_gc_source extends Assets_base_source
{
	protected $_source_id = 0;
	protected $_source_type = 'gc';

	private static $_predefined_endpoints = array(
		'US' => 'storage.googleapis.com',
		'EU' => 'storage.googleapis.com'
	);

	// images of this size will be saved to be used as sources for thumbnail generation
	const IMAGE_SOURCE_SIZE = '400x400';

	/**
	 * @var Assets_GC
	 */
	public $GC = null;

	/**
	 * Constructor
	 */
	public function __construct($source_id, $settings, $ignore_restrictions)
	{
		parent::__construct();

		require_once PATH_THIRD . 'assets/sources/gc/lib/Assets_GC.php';
		$this->GC = new Assets_GC($settings->access_key_id, $settings->secret_access_key);

		$this->_source_settings = $settings;
		$this->_source_id = $source_id;

		$this->_source_row = $this->EE->assets_lib->get_source_row_by_id($this->get_source_id());
	}

	/**
	 * Returns TRUE if the other source is a Google Cloud source and shares the same credentials
	 * @param Assets_base_source $source
	 * @return mixed
	 */
	public function can_move_files_from(Assets_base_source $source)
	{
		return ($source instanceof Assets_gc_source
			&& $this->settings()->access_key_id == $source->settings()->access_key_id
			&& $this->settings()->secret_access_key == $source->settings()->secret_access_key);
	}

	/**
	 * @return array
	 */
	public static function get_settings_field_list()
	{
		return array(
			'access_key_id', 'secret_access_key', 'subfolder'
		);
	}

	/**
	 * Get bucket list for credential
	 * @param $key_id
	 * @param $secret_key
	 * @return array
	 * @throws Exception
	 */
	public static function get_bucket_list($key_id, $secret_key)
	{
		require_once PATH_THIRD . 'assets/sources/gc/lib/Assets_GC.php';
		$gc = new Assets_GC($key_id, $secret_key);
		$buckets = @$gc->listBuckets();
		if (empty($buckets))
		{
			throw new Exception(lang('wrong_credentials'));
		}

		$bucket_list = array();
		foreach ($buckets as $bucket)
		{
			$location = $gc->getBucketLocation($bucket);
			$bucket_item = array(
				'bucket' => $bucket,
				'location' => $location,
				'url_prefix' => 'http://' . self::get_endpoint_by_location($location) . '/' . $bucket . '/'
			);

			$bucket_list[$bucket] = (object) $bucket_item;

		}
		return $bucket_list;
	}

	/**
	 * Create a folder at designated path for source
	 *
	 * @param $server_path
	 * @return array
	 */
	protected function _create_source_folder($server_path)
	{
		$bucket_data = $this->get_source_settings();

		return $this->GC->putObject('', $bucket_data->bucket, $this->_get_path_prefix().rtrim($server_path, '/') . '/', Assets_GC::ACL_PUBLIC_READ);
	}

	/**
	 * Rename a folder
	 *
	 * @param $old_path
	 * @param $new_path
	 * @return mixed
	 */
	protected function _rename_source_folder($old_folder_path, $new_path)
	{
		$bucket_data = $this->get_source_settings();

		$old_folder = trim($old_folder_path, '/') . '/';
		$files_to_move = $this->GC->getBucket($bucket_data->bucket, $this->_get_path_prefix().$old_folder);

		rsort($files_to_move);
		foreach ($files_to_move as $file)
		{
			$file_path = substr($file['name'], strlen($old_folder));

			$this->GC->copyObject($bucket_data->bucket, str_replace('//', '/', $file['name']),
				$bucket_data->bucket, $this->_get_path_prefix().ltrim(str_replace('//', '/', $new_path . '/' . $file_path), '/'),
				Assets_GC::ACL_PUBLIC_READ);

			$this->GC->deleteObject($bucket_data->bucket, $file['name']);
		}

		return TRUE;
	}

	/**
	 * Delete a folder
	 *
	 * @param $server_path
	 * @return mixed
	 */
	protected function _delete_source_folder($server_path)
	{
		$bucket_data = $this->get_source_settings();
		$this->_gc_set_creds($bucket_data->access_key_id, $bucket_data->secret_access_key);
		@$this->GC->deleteObject($bucket_data->bucket, $this->_get_path_prefix().$server_path);
		return true;
	}

	/**
	 * Delete a file
	 *
	 * @param $server_path
	 * @param $source_data
	 * @return mixed
	 */
	protected function _delete_source_file($server_path, $source_data = array())
	{
		if (!empty($source_data))
		{
			$bucket_data = $source_data;
		}
		else
		{
			$bucket_data = $this->get_source_settings();
		}

		$this->_gc_set_creds($bucket_data->access_key_id, $bucket_data->secret_access_key);
		@$this->GC->deleteObject($bucket_data->bucket, $this->_get_path_prefix($source_data).$server_path);
		return true;
	}

	/**
	 * Check if file exists on source
	 *
	 * @param $server_path
	 * @return array
	 */
	protected function _source_file_exists($server_path)
	{
		return $this->_source_folder_exists($this->_get_path_prefix().$server_path);
	}

	/**
	 * Check if folder exists on source
	 *
	 * @param $server_path
	 * @return array
	 */
	protected function _source_folder_exists($server_path)
	{
		$bucket_data = $this->get_source_settings();

		$this->_gc_set_creds($bucket_data->access_key_id, $bucket_data->secret_access_key);
		return (bool) $this->GC->getObjectinfo($bucket_data->bucket, $this->_get_path_prefix().$server_path);
	}

	/**
	 * Upload File
	 */
	protected function _do_upload_in_folder($folder_data, $temp_file_path, $original_name )
	{
		$folder_path = $this->get_folder_server_path($folder_data->full_path);

		// make sure this is a valid upload directory path
		if (! $folder_path)
		{
			return array('error' => lang('invalid_filedir_path'));
		}

		if ($folder_path == '/')
		{
			$folder_path = '';
		}

		// swap whitespace with underscores
		$original_name = $this->EE->assets_lib->clean_filename($original_name);

		$file_path = $folder_path . $original_name;

		if ($this->_source_file_exists($folder_path . $original_name)
			OR (empty($this->cache['merge_in_progress']) && $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_data->folder_id, $original_name))
		)
		{
			return $this->_prompt_result_array($original_name);
		}

		$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

		if (! $this->_is_extension_allowed($ext))
		{
			throw new Exception(lang('filetype_not_allowed'));
		}

		$bucket_data = $this->get_source_settings();

		$this->_gc_set_creds($bucket_data->access_key_id, $bucket_data->secret_access_key);

		if ($this->GC->putObject(array('file' => $temp_file_path), $bucket_data->bucket, $this->_get_path_prefix().$file_path, Assets_GC::ACL_PUBLIC_READ))
		{
			return array('success' => TRUE, 'path' => $file_path);
		}
		else
		{
			return array('error'=> lang('couldnt_save'));
		}
	}

	/**
	 * Move a source file
	 *
	 * @param Assets_base_file $file
	 * @param                  $previous_folder_row
	 * @param                  $folder_row
	 * @param string           $new_file_name
	 * @param bool             $overwrite
	 * @throws Exception
	 * @return mixed
	 */
	protected function _move_source_file(Assets_base_file $file, $previous_folder_row, $folder_row, $new_file_name = '', $overwrite = FALSE)
	{
		if (empty($new_file_name))
		{
			$new_file_name = $file->filename();
		}

		$old_path = $this->get_folder_server_path($previous_folder_row->full_path);
		$new_path = $this->get_folder_server_path($folder_row->full_path);

		// can we place the file there?
		if (!$overwrite && ($this->_source_file_exists($new_path . $new_file_name)
			OR (empty($this->cache['merge_in_progress']) && $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_row->folder_id, $new_file_name))))
		{
			return $this->_prompt_result_array($new_file_name);
		}

		$old_bucket_data = $this->get_source_settings($previous_folder_row->source_id);
		$new_bucket_data = $this->get_source_settings($folder_row->source_id);

		$old_prefix = !empty($old_bucket_data->subfolder) ? rtrim($old_bucket_data->subfolder, '/').'/' : '';
		$this->_gc_set_creds($new_bucket_data->access_key_id, $new_bucket_data->secret_access_key);
		if ($this->GC->copyObject($old_bucket_data->bucket, $old_prefix.ltrim($old_path, '/') . $file->filename(), $new_bucket_data->bucket, $this->_get_path_prefix().ltrim($new_path, '/') . $new_file_name,Assets_GC::ACL_PUBLIC_READ))
		{
			$this->_delete_source_file($file->server_path(), $old_bucket_data);
			return array(
				'success' => TRUE,
				'file_id' => $file->file_id(),
				'new_file_name' => $new_file_name);
		}

		throw new Exception(lang('invalid_path'));
	}

	/**
	 * Return bucket info
	 *
	 * @param $source_id
	 * @return array|bool|mixed|null
	 */
	public function get_source_settings($source_id = 0)
	{
		if (empty($source_id) OR $source_id == $this->_source_id)
		{
			return $this->_source_settings;
		}

		if ( empty($this->cache['buckets'][$source_id]))
		{
			$bucket_rows = $this->_get_sources(array('source_type' => $this->get_source_type(), 'source_id' => $source_id));
			if (! empty($bucket_rows))
			{
				$this->cache['buckets'][$source_id] = $bucket_rows[0];
			}
		}

		$row = $this->cache['buckets'][$source_id];

		if ( ! $row )
		{
			return FALSE;
		}

		return json_decode($row->settings);
	}

	/**
	 * Start indexing
	 * @param $session_id
	 * @return array
	 */
	public function start_index($session_id)
	{
		$settings = $this->_source_settings;
		$offset = 0;
		$total_file_count = 0;
		$indexed_folder_ids = array();

		$folder_row = $this->_find_folder(array('source_type' => $this->get_source_type(), 'source_id' => $this->get_source_id(), 'parent_id' => NULL));

		if ( empty($folder_row))
		{
			$source_row = $this->_source_row;
			// this is a new folder - insert into DB
			$data = array (
				'source_type' => $this->get_source_type(),
				'folder_name' => $source_row->name,
				'full_path' => '',
				'source_id' => $this->get_source_id(),
			);

			$indexed_folder_ids[$this->_store_folder($data)] = TRUE;
		}
		else
		{
			$indexed_folder_ids[$folder_row->folder_id] = TRUE;
		}

		$prefix = $this->_get_path_prefix();
		$this->_gc_set_creds($settings->access_key_id, $settings->secret_access_key);

		$file_list = $this->GC->getBucket($settings->bucket, $prefix);

		// Let's assume that we'll need more memory if we hit an arbitrary amount of entries
		if (count($file_list) > 2000)
		{
			ini_set('memory_limit', '64M');
		}

		foreach ($file_list as $file)
		{
			$file['name'] = substr($file['name'], strlen($prefix));

			// Check if we should bother at all.
			if (substr($file['name'], -1) == '/')
			{
				if (!$this->_is_allowed_folder_path($file['name']))
				{
					continue;
				}
			}
			else
			{
				if (!$this->_is_allowed_file_path($file['name']))
				{
					continue;
				}

			}

			// in Google Cloud, it's possible to have files in folders that don't exist. E.g. - one/two/three.jpg.
			// if folder "one" is empty, except for folder "two", this won't show up in this list so we work around it
			// matches all paths with folders, except if folder is last or no folder at all
			if (preg_match('/(.*\/).+$/', $file['name'], $matches))
			{
				$folders = explode('/', rtrim($matches[1], '/'));
				$base_path = '';
				foreach ($folders as $folder)
				{
					$base_path .= $folder . '/';

					// this is exactly the case the above comment block reffers to
					if ( ! isset($existing_bucket_files[$base_path]))
					{
						$existing_bucket_files[$base_path] = TRUE;
						$this->_store_gc_folder($base_path, $indexed_folder_ids);
					}
				}
			}

			if (substr($file['name'], -1) == '/')
			{
				$this->_store_gc_folder($file['name'], $indexed_folder_ids);
				$existing_bucket_files[$file['name']] = TRUE;
			}
			else
			{
				$this->_store_index_entry($session_id, $this->get_source_type(), $this->get_source_id(), $offset++, $file['name'], $file['size']);
				$total_file_count++;
			}
		}

		$this->_execute_index_batch();

		// figure out the obsolete records for folders
		$missing_folder_ids = array();
		$all_folders = $this->EE->db->select('folder_id, full_path')
			->where('source_id', $this->get_source_id())
			->get('assets_folders')->result();

		foreach ($all_folders as $folder_row)
		{
			if (!isset($indexed_folder_ids[$folder_row->folder_id]))
			{
				$missing_folder_ids[$folder_row->folder_id] = $this->_source_row->name . '/' . $folder_row->full_path;
			}
		}

		return array(
			'source_type' => $this->get_source_type(),
			'source_id' => $this->get_source_id(),
			'total' => $total_file_count,
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
		$settings = $this->_source_settings;

		$this->_gc_set_creds($settings->access_key_id, $settings->secret_access_key);
		$file_list = $this->GC->getBucket($settings->bucket, $this->_get_path_prefix().$folder_row->full_path);

		$offset = 0;
		$count = 0;
		foreach ($file_list as $file)
		{
			$file['name'] = substr($file['name'], strlen($this->_get_path_prefix()));

			// Only allow files directly in this folder
			if (strpos(substr($file['name'], strlen($folder_row->full_path)), '/') === FALSE)
			{
				if (!$this->_is_allowed_file_path($file['name']))
				{
					continue;
				}

				$count++;
				$this->_store_index_entry($session_id, $this->get_source_type(), $this->get_source_id(), $offset++, $file['name'], $file['size']);
			}
		}
		$this->_execute_index_batch();

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

		$file = $index_entry->uri;
		$size = $index_entry->filesize;

		$file_indexed = FALSE;


		if ( $this->_is_extension_allowed(pathinfo($file, PATHINFO_EXTENSION)))
		{
			$parts = explode('/', $file);
			$file_name = array_pop($parts);

			$search_full_path = join('/', $parts) . '/';
			if ($search_full_path == '/')
			{
				$search_full_path = '';
			}

			$folder_search = array(
				'source_type' => $this->get_source_type(),
				'source_id' => $this->get_source_id(),
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
					'source_id' => $this->get_source_id(),
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
			$settings = $this->settings();
			$this->_gc_set_creds($settings->access_key_id, $settings->secret_access_key);
			$info = $this->GC->getObjectInfo($this->settings()->bucket, $this->_get_path_prefix().$file);

			$data = array(
				'size' => $size
			);

			if (is_array($info))
			{
				$data['date_modified'] = $info['time'];
			}

			$file_row = $this->EE->assets_lib->get_file_row_by_id($file_id);
			if (!$file_row->date)
			{
				$data['date'] = $file_row->date_modified ? $file_row->date_modified : $data['date_modified'];
			}

			if ($file_row->kind == 'image' && $size != $file_row->size)
			{
				$this->_perform_image_actions($file, $file_id, $this->settings()->bucket);
			}
			$this->_update_file($data, $file_id);
		}

		return TRUE;
	}

	/**
	 * Store an Google Cloud folder in database
	 * @param $file
	 * @param $indexed_folder_ids array of indexed forlder ids by reference
	 */
	private function _store_gc_folder($file, &$indexed_folder_ids)
	{
		$folder_search = array(
			'source_type' => $this->get_source_type(),
			'source_id' => $this->get_source_id(),
			'full_path' => $file
		);

		$folder_row = $this->_find_folder($folder_search);

		// new folder?
		if ( empty($folder_row))
		{
			$parts = explode('/', rtrim($file, '/'));
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
				'full_path' =>  $file,
				'source_id' => $this->get_source_id()
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

	/**
	 * @param $location
	 * @return string
	 */
	public static function get_endpoint_by_location($location)
	{
		if (isset(self::$_predefined_endpoints[$location]))
		{
			return self::$_predefined_endpoints[$location];
		}
		return 'storage.googleapis.com'; //'s3-' . $location . '.amazonaws.com';
	}

	/**
	 * Perform image actions - resize and save dimensions. If no bucket name provided, $uri is treated as filesystem path
	 *
	 * @param $uri
	 * @param $file_id
	 * @param mixed $bucket false for uri to be treated as a filesystem path
	 * @return bool
	 */
	private function _perform_image_actions($uri, $file_id, $bucket = FALSE)
	{
		$this->EE->load->library('filemanager');

		$cache_path = 'assets/gc_sources';

		$cache_path = Assets_helper::ensure_cache_path($cache_path);

		if ($bucket)
		{
			$target = Assets_helper::get_temp_file();
			$this->_gc_set_creds($this->settings()->access_key_id, $this->settings()->secret_access_key);
			$this->GC->getObject($bucket, $this->_get_path_prefix().$uri, $target);
			$uri = $target;
		}

		list ($width, $height) = getimagesize($uri);
		$data = array('width' => $width, 'height' => $height);
		$this->_update_file($data, $file_id);

		if (strtolower($this->EE->config->item('assets_cache_remote_images')) !== "no")
		{
			$target_path = $cache_path . $file_id . '.jpg';
			$this->EE->assets_lib->resize_image($uri, $target_path, self::IMAGE_SOURCE_SIZE);
		}

		@unlink($uri);
	}

	/**
	 * Width/Height/Thumbnail
	 *
	 * @param $file_id
	 * @param $file_path
	 * @return mixed|void
	 */
	public function post_upload_image_actions($file_id, $file_path)
	{
		// cook up the thumbnail while we're at it
		$this->_perform_image_actions($file_path, $file_id);
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
		$bucket_data = $this->get_source_settings($folder_row->source_id);

		$this->_gc_set_creds($bucket_data->access_key_id, $bucket_data->secret_access_key);
		$file_list = $this->GC->getBucket($bucket_data->bucket, $this->_get_path_prefix().$folder_row->full_path);

		$file_name_parts = explode(".", $file_name);

		$extension = array_pop($file_name_parts);

		$file_name_start = join(".", $file_name_parts) . '_';
		$index = 1;

		while (
			(isset($file_list[$folder_row->full_path . $file_name_start . $index . '.' . $extension]))
			|| $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_row->folder_id, $file_name_start . $index . '.' . $extension)
		)
		{
			$index++;
		}

		return $file_name_start . $index . '.' . $extension;
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
		return $folder_row->full_path . $file_name;
	}

	/**
	 * Set Google Cloud credentials
	 * @param $accessKey
	 * @param $secretKey
	 */
	private function _gc_set_creds($accessKey, $secretKey)
	{
		Assets_GC::setAuth($accessKey, $secretKey);
	}
}
