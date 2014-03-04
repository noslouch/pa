<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets Rackspace Cloud source
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_rs_source extends Assets_base_source
{
	protected $_source_id = 0;
	protected $_source_type = 'rs';

	// images of this size will be saved to be used as sources for thumbnail generation
	const IMAGE_SOURCE_SIZE = '400x400';

	const RACKSPACE_US_AUTH_HOST = 'https://identity.api.rackspacecloud.com/v1.0';
	const RACKSPACE_UK_AUTH_HOST = 'https://lon.identity.api.rackspacecloud.com/v1.0';

	const RACKSPACE_STORAGE_OPERATION = 'storage';
	const RACKSPACE_CDN_OPERATION = 'cdn';

	/**
	 * Stores access information.
	 *
	 * @var array
	 */
	private static $_access_store = array();


	/**
	 * Constructor
	 */
	public function __construct($source_id, $settings, $ignore_restrictions)
	{

		parent::__construct();

		$this->_source_settings = $settings;
		$this->_source_id = $source_id;

		if ($source_id)
		{
			$this->_source_row = $this->EE->assets_lib->get_source_row_by_id($this->get_source_id());
		}
	}

	/**
	 * Returns TRUE if source is capable from performing the file move from another source as if the file being moved was
	 * inside the new source already. For example - all EE operations and S3 operations, that take place on the same AWS account.
	 *
	 * @param Assets_base_source $source
	 * @return boolean
	 */
	public function can_move_files_from(Assets_base_source $source)
	{
		return ($source instanceof Assets_rs_source
			&& $this->settings()->username == $source->settings()->username
			&& $this->settings()->api_key == $source->settings()->api_key);
	}

	/**
	 * Create a folder at designated path for source.
	 *
	 * @param $server_path
	 * @return array
	 */
	protected function _create_source_folder($server_path)
	{
		$headers = array(
			'Content-type: application/directory',
			'Content-length: 0'
		);

		$targetUri = $this->_prepare_request_uri($this->settings()->container, $server_path);

		$this->_do_authenticated_request(self::RACKSPACE_STORAGE_OPERATION,  $targetUri, 'PUT', $headers);
		return TRUE;
	}

	/**
	 * Rename a folder
	 * @param $old_path
	 * @param $new_path
	 * @return bool|mixed
	 */
	protected function _rename_source_folder($old_path, $new_path)
	{
		$old_folder = trim($old_path, '/') . '/';

		$file_list = $this->_get_file_list($old_path);
		$files_to_move = array();

		foreach ($file_list as $file)
		{
			if ($file->name != $old_path)
			{
				$files_to_move[] = $file->name;
			}
		}

		rsort($files_to_move);

		foreach ($files_to_move as $file)
		{
			$file_path = substr($file, strlen($old_folder));

			$source_uri = $this->_prepare_request_uri($this->settings()->container, $file);
			$target_uri = $this->_prepare_request_uri($this->settings()->container, ltrim($new_path . '/' . $file_path, '/'));
			$this->_copy_file($source_uri, $target_uri);
			$this->_delete_object($source_uri);
		}

		$this->_delete_object($this->_prepare_request_uri($this->settings()->container, $old_folder));

		return TRUE;
	}

	/**
	 * Delete a folder
	 * @param $server_path
	 * @return mixed
	 */
	protected function _delete_source_folder($server_path)
	{
		$objects_to_delete = $this->_get_file_list($server_path);
		foreach ($objects_to_delete as $object)
		{
			$this->_delete_object($this->_prepare_request_uri($this->settings()->container, $object->name));
		}

		$server_path = rtrim($server_path, '/');

		$this->_delete_object($this->_prepare_request_uri($this->settings()->container, $server_path));

		return TRUE;
	}

	/**
	 * Delete a file.
	 *
	 * @param $server_path
	 * @param $source_data
	 * @return bool
	 */
	protected function _delete_source_file($server_path, $source_data = array())
	{
		if (empty($source_data))
		{
			$source_data = $this->settings();
		}
		$uri = $this->_prepare_request_uri($source_data->container,$server_path, $source_data);
		$this->_delete_object($uri);
		return TRUE;
	}

	/**
	 * Purge a file from the CDN by server path.
	 *
	 * @param $server_path
	 */
	protected function _purge_cached_source_file($server_path)
	{
		$uri = $this->_prepare_request_uri($this->settings()->container,$server_path);
		$this->_purge_object($uri);
	}

	/**
	 * Check if file exists on source
	 *
	 * @param $server_path
	 * @return array
	 */
	protected function _source_file_exists($server_path)
	{
		return is_object($this->get_object_info($server_path));
	}

	/**
	 * Check if folder exists on source.
	 *
	 * @param $server_path
	 * @return bool
	 */
	protected function _source_folder_exists($server_path)
	{
		$info = $this->get_object_info(rtrim($server_path, '/'));
		if (is_object($info) && $info->content_type == 'application/directory')
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Perform upload in the designated folder
	 * @param stdClass $folder_data holding the folder row data
	 * @param string $temp_file_path file path on disk
	 * @param string $original_name file original name (type checks need this)
	 * @return mixed
	 */
	protected function _do_upload_in_folder($folder_data, $temp_file_path, $original_name )
	{

		$folder_path = $folder_data->full_path;

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

		if ($this->_upload_file($folder_path . $original_name, $temp_file_path))
		{
			return array('success' => TRUE, 'path' => $file_path);
		}
		else
		{
			return array('error'=> lang('couldnt_save'));
		}
	}

	/**
	 * Move a source file.
	 *
	 * @param Assets_base_file $file
	 * @param $previous_folder_row
	 * @param $folder_row
	 * @param $new_file_name
	 * @param bool $overwrite
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


		$previous_settings = $this->get_source_settings($previous_folder_row->source_id);
		$new_settings = $this->get_source_settings($folder_row->source_id);

		$this->_copy_file($this->_prepare_request_uri($previous_settings->container, ltrim($old_path, '/') . $file->filename(), $previous_settings),
			$this->_prepare_request_uri($new_settings->container, ltrim($new_path, '/') . $new_file_name), $new_settings);

		$this->_delete_source_file($file->server_path(), $this->get_source_settings($previous_folder_row->source_id));
		return array(
			'success' => TRUE,
			'file_id' => $file->file_id(),
			'new_file_name' => $new_file_name);
	}

	/**
	 * Start indexing
	 * @param $session_id
	 * @return array
	 */
	public function start_index($session_id)
	{
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
		$file_list = $this->_get_file_list($prefix);

		// Let's assume that we'll need more memory if we hit an arbitrary amount of entries
		if (count($file_list) > 2000)
		{
			ini_set('memory_limit', '64M');
		}

		$container_folders = array();

		// Check if we should bother at all.
		foreach ($file_list as $file)
		{
			$file->name = substr($file->name, strlen($prefix));

			if ($file->content_type == 'application/directory')
			{
				if (!$this->_is_allowed_folder_path($file->name))
				{
					continue;
				}
			}
			else
			{
				if (!$this->_is_allowed_file_path($file->name))
				{
					continue;
				}
			}

			// So in RackSpace a folder may or may not exist. For path a/path/to/file.jpg, any of those folders may
			// or may not exist. So we have to add all the segments to $containerFolders to make sure we index them

			// Matches all paths with folders, except if there if no folder at all.
			if (preg_match('/(.*\/).+$/', $file->name, $matches))
			{
				$folders = explode('/', rtrim($matches[1], '/'));
				$base_path = '';

				foreach ($folders as $folder)
				{
					$base_path .= $folder;

					// This is exactly the case referred to above
					if ( ! isset($container_folders[$base_path]))
					{
						$container_folders[$base_path] = true;
						$this->_store_rs_folder($base_path, $indexed_folder_ids);
					}

					$base_path .= '/';
				}
			}

			if ($file->content_type == 'application/directory')
			{
				$this->_store_rs_folder($file->name, $indexed_folder_ids);
				$container_folders[$file->name] = true;
			}
			else
			{
				$this->_store_index_entry($session_id, $this->get_source_type(), $this->get_source_id(), $offset++, $file->name, $file->bytes);
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
	 * Starts a folder indexing session
	 * @param $session_id
	 * @param StdClass $folder_row
	 * @return array
	 */
	public function start_folder_index($session_id, $folder_row)
	{

		$file_list = $this->_get_file_list($this->_get_path_prefix().$folder_row->full_path);

		$offset = 0;
		$count = 0;
		foreach ($file_list as $file)
		{
			$file->name = substr($file->name, strlen($this->_get_path_prefix()));

			// Only allow files directly in this folder
			if (strpos(substr($file->name, strlen($folder_row->full_path)), '/') === FALSE)
			{
				if (!$this->_is_allowed_file_path($file->name))
				{
					continue;
				}

				$count++;
				$this->_store_index_entry($session_id, $this->get_source_type(), $this->get_source_id(), $offset++, $file->name);
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

			$object = $this->get_object_info($file);

			$data = array(
				'size' => $object->size
			);

			if (is_object($object))
			{
				$time = new DateTime($object->last_modified, new DateTimeZone('GMT'));
				$data['date_modified'] = $time->format('U');
			}

			$file_row = $this->EE->assets_lib->get_file_row_by_id($file_id);
			if (!$file_row->date)
			{
				$data['date'] = $file_row->date_modified ? $file_row->date_modified : $data['date_modified'];
			}

			if ($file_row->kind == 'image' && $object->size != $file_row->size)
			{
				$this->_perform_image_actions($file, $file_id, true);
			}
			$this->_update_file($data, $file_id);
		}

		return TRUE;
	}

	/**
	 * Perform image actions - resize and save dimensions. If no bucket name provided, $uri is treated as filesystem path
	 *
	 * @param $uri
	 * @param $file_id
	 * @param mixed $download_copy if set to true will download a new copy
	 * @return bool
	 */
	private function _perform_image_actions($uri, $file_id, $download_copy = null)
	{
		$this->EE->load->library('filemanager');

		$cache_path = 'assets/rs_sources';

		$cache_path = Assets_helper::ensure_cache_path($cache_path);

		if ($download_copy)
		{
			$target = Assets_helper::get_temp_file();
			$this->download_file($uri, $target);
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
	 * Store a RackSpace folder in database
	 * @param $path
	 * @param $indexed_folder_ids array of indexed folder ids by reference
	 */
	private function _store_rs_folder($path, &$indexed_folder_ids)
	{
		$path = rtrim($path, '/') . '/';

		$folder_search = array(
			'source_type' => $this->get_source_type(),
			'source_id' => $this->get_source_id(),
			'full_path' => $path
		);

		$folder_row = $this->_find_folder($folder_search);

		// new folder?
		if ( empty($folder_row))
		{
			$parts = explode('/', rtrim($path, '/'));
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
				'full_path' =>  $path,
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
	 * Perform post-delete actions
	 *
	 * @param $file_id
	 * @return void
	 */
	public function post_delete_actions($file_id)
	{
		$cache_path = $this->EE->config->item('cache_path');

		if (empty($cache_path))
		{
			$cache_path = APPPATH.'cache/';
		}

		@unlink($cache_path . 'assets/thumbs/' . $file_id . '/*');
		@rmdir($cache_path . 'assets/thumbs/' . $file_id);

		return;
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
		$file_list = $this->_get_file_list($folder_row->full_path);

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
	 * Get a files server path
	 * @param $folder_row
	 * @param $file_name
	 * @return mixed
	 */
	protected function _get_file_server_path($folder_row, $file_name)
	{
		return $folder_row->full_path . $file_name;
	}

	/**
	 * Return setting fields for this source
	 * @return array
	 */
	public static function get_settings_field_list()
	{
		return array(
			'username', 'api_key', 'location', 'subfolder'
		);

	}

	/**
	* Get container list for credentials.
	 *
	* @return array
	* @throws Exception
	*/
	public function get_container_list()
	{
		$response = $this->_do_authenticated_request(self::RACKSPACE_CDN_OPERATION, '?format=json');

		$extracted_response = self::_extract_request_response($response);
		$data = json_decode($extracted_response);

		$return_data = array();
		if (is_array($data))
		{
			foreach ($data as $container)
			{
				$return_data[$container->name] = rtrim($container->cdn_uri, '/').'/';
			}
		}
		else
		{
			self::_log_unexpected_response($response);
		}

		return $return_data;
	}

	/**
	 * Return container settings
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
	 * Refresh a connection information and return authorization token.
	 *
	 * @throws Exception
	 */
	private function _refresh_connection_information()
	{
		$settings = $this->_source_settings;
		$username = $settings->username;
		$api_key = $settings->api_key;
		$location = $settings->location;

		$headers = array(
			'X-Auth-User: '.$username,
			'X-Auth-Key: '.$api_key
		);

		$target_url = self::_make_authorization_request_url($location);
		$response = self::_do_request($target_url, 'GET', $headers);

		// Extract the values
		$token = self::_extract_header($response, 'X-Auth-Token');
		$storage_url = rtrim(self::_extract_header($response, 'X-Storage-Url'), '/').'/';
		$cdn_url = rtrim(self::_extract_header($response, 'X-CDN-Management-Url'), '/').'/';

		if (!($token && $storage_url && $cdn_url))
		{
			throw new Exception(lang('wrong_credentials'));
		}

		$connection_key = $username.$api_key;

		$data = array('token' => $token, 'storage_url' => $storage_url, 'cdn_url' => $cdn_url);

		// Store this in the access store
		self::$_access_store[$connection_key] = $data;

		// And update DB information.
		$this->_update_access_data($connection_key, $data);
	}


	/**
	 * Create the authorization request URL by location
	 *
	 * @param string $location
	 * @return string
	 */
	private static function _make_authorization_request_url($location = '')
	{
		if ($location == 'uk')
		{
			return self::RACKSPACE_UK_AUTH_HOST;
		}

		return self::RACKSPACE_US_AUTH_HOST;
	}

	/**
	 * Make a request and return the response.
	 *
	 * @param $url
	 * @param $method
	 * @param $headers
	 * @param $curl_options
	 * @return string
	 */
	private static function _do_request($url, $method = 'GET', $headers = array(), $curl_options = array())
	{
		$ch = curl_init($url);
		if ($method == 'HEAD')
		{
			curl_setopt($ch, CURLOPT_NOBODY, 1);
		}
		else
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		foreach ($curl_options as $option => $value)
		{
			curl_setopt($ch, $option, $value);
		}

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	/**
	 * Get object information by path
	 * @param $path
	 * @return bool|object
	 */
	public function get_object_info($path)
	{

		$target = $this->_prepare_request_uri($this->_source_settings->container, $path);
		$response = $this->_do_authenticated_request(self::RACKSPACE_STORAGE_OPERATION, $target, 'HEAD');

		$last_modified = self::_extract_header($response, 'Last-Modified');
		$size = self::_extract_header($response, 'Content-Length');

		if (!$last_modified)
		{
			return false;
		}

		return (object) array('last_modified' => $last_modified, 'size' => $size);
	}

	/**
	 * Do an authenticated request against Rackspace severs.
	 *
	 * @param string $operation_type operation type so we know which server to target
	 * @param string $target URI target on the Rackspace server
	 * @param string $method GET/POST/PUT/DELETE
	 * @param array $headers array of headers. Authorization token will be appended to this before request.
	 * @param array $curl_options additional curl options to set.
	 * @return string full response including headers.
	 * @throws Exception
	 */
	private function _do_authenticated_request($operation_type, $target = '', $method = 'GET', $headers = array(), $curl_options = array())
	{
		$settings = $this->_source_settings;

		$username = $settings->username;
		$api_key = $settings->api_key;

		$connection_key = $username.$api_key;

		// If we don't have the access information, load it from DB
		if (empty(self::$_access_store[$connection_key]))
		{
			$this->_load_access_data();
		}

		// If we still don't have it, fetch it using username and api key.
		if (empty(self::$_access_store[$connection_key]))
		{
			$this->_refresh_connection_information();
		}

		// If we still don't have it, then we're all out of luck.
		if (empty(self::$_access_store[$connection_key]))
		{
			throw new Exception(lang('connection_information_missing'));
		}

		$connectionInformation = self::$_access_store[$connection_key];

		$headers[] = 'X-Auth-Token: ' . $connectionInformation['token'];

		switch ($operation_type)
		{
			case self::RACKSPACE_STORAGE_OPERATION:
			{
				$url = $connectionInformation['storage_url'].$target;
				break;
			}

			case self::RACKSPACE_CDN_OPERATION:
			{
				$url = $connectionInformation['cdn_url'].$target;
				break;
			}

			default:
				{
				throw new Exception(lang('unrecognized_operation_type'));
				}
		}

		$response = self::_do_request($url, $method, $headers, $curl_options);

		preg_match('/HTTP\/1.1 (?P<http_status>[0-9]{3})/', $response, $matches);

		if (!empty($matches['http_status']))
		{
			// Error checking
			switch ($matches['http_status'])
			{
				// Invalid token - try to renew it once.
				case '401':
				{
					static $token_failure = 0;
					if (++$token_failure == 1)
					{
						$this->_refresh_connection_information();

						// Remove token header.
						$new_headers = array();
						foreach ($headers as $header)
						{
							if (strpos($header, 'X-Auth-Token') === false)
							{
								$new_headers[] = $header;
							}
						}

						return $this->_do_authenticated_request($operation_type, $target, $method, $new_headers);
					}
					throw new Exception("Token has expired and the attempt to renew it failed. Please check the source settings.");
					break;
				}

			}
		}

		return $response;
	}

	/**
	 * Load Rackspace access data from DB.
	 */
	private function _load_access_data()
	{
		$rows = $this->EE->db->select('connection_key, token, storage_url, cdn_url')->get('assets_rackspace_access')->result_array();

		foreach ($rows as $row)
		{
			self::$_access_store[$row['connection_key']] = array(
				'token' => $row['token'],
				'storage_url' => $row['storage_url'],
				'cdn_url' => $row['cdn_url']);
		}
	}

	/**
	 * Update or insert access data for a connection key.
	 *
	 * @param $connection_key
	 * @param $data
	 */
	private function _update_access_data($connection_key, $data)
	{
		$row = $this->EE->db->select('connection_key')->where('connection_key', $connection_key)->get('assets_rackspace_access')->row();
		if (!empty($row))
		{
			$this->EE->db->update('assets_rackspace_access', $data, array('connection_key' => $connection_key));
		}
		else
		{
			$data['connection_key'] = $connection_key;
			$this->EE->db->insert('assets_rackspace_access', $data);
		}
	}

	/**
	 * Extract a header from a response.
	 *
	 * @param $response
	 * @param $header
	 * @return mixed
	 */
	private static function _extract_header($response, $header)
	{
		preg_match('/.*'.$header.': (?P<value>.+)\r/', $response, $matches);
		return isset($matches['value']) ? $matches['value'] : false;
	}



	/**
	 * Extract the response form a response that has headers.
	 *
	 * @param $response
	 * @return string
	 */
	private static function _extract_request_response($response)
	{
		return rtrim(substr($response, strpos($response, "\r\n\r\n") + 4));
	}

	/**
	 * Log an unexpected response.
	 *
	 * @param $response
	 */
	private static function _log_unexpected_response($response)
	{
		$EE =& get_instance();
		$EE->load->library('logger');
		$EE->logger->developer("RACKSPACE: Received unexpected response: " . $response);
	}

	/**
	 * Download a file to the target location. The file will be downloaded using the public URL, instead of cURL.
	 *
	 * @param $path
	 * @param $target_file
	 * @return bool
	 */
	public function download_file($path, $target_file)
	{
		$prefix = !empty($this->settings()->subfolder) ? rtrim($this->settings()->subfolder).'/' : '';
		$path = $this->_source_settings->url_prefix.$prefix.$path;

		$ch = curl_init($path);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);

		file_put_contents($target_file, $response);

		return true;
	}

	/**
	 * Upload a file to Rackspace.
	 *
	 * @param $target_uri
	 * @param $source_file
	 * @return bool
	 */
	public function _upload_file($target_uri, $source_file)
	{

		$file_size = filesize($source_file);
		$fp = fopen($source_file, "r");

		$headers = array(
			'Content-type: ' . self::_get_mime_type($source_file),
			'Content-length: ' . $file_size
		);

		$curl_options = array(
			CURLOPT_UPLOAD => true,
			CURLOPT_INFILE => $fp,
			CURLOPT_INFILESIZE => $file_size
		);

		$target_uri = $this->_prepare_request_uri($this->_source_settings->container, $target_uri);
		$this->_do_authenticated_request(self::RACKSPACE_STORAGE_OPERATION, $target_uri, 'PUT', $headers, $curl_options);
		fclose($fp);
		return true;
	}

	/**
	 * Get file list from Rackspace.
	 *
	 * @param $prefix
	 * @return mixed
	 * @throws Exception
	 */
	private function _get_file_list($prefix = '')
	{
		$target_uri = $this->_prepare_request_uri($this->_source_settings->container).'?prefix='.$prefix.'&format=json';
		$response = $this->_do_authenticated_request(self::RACKSPACE_STORAGE_OPERATION, $target_uri);

		$extractedResponse = self::_extract_request_response($response);
		$file_list = json_decode($extractedResponse);

		if (!is_array($file_list))
		{
			self::_log_unexpected_response($response);
			throw new Exception(lang('unexpected_response'));
		}

		return $file_list;
	}

	/**
	 * Delete a file on Rackspace.
	 *
	 * @param $uri_path
	 */
	private function _delete_object($uri_path)
	{
		$this->_do_authenticated_request(self::RACKSPACE_STORAGE_OPERATION, $uri_path, 'DELETE');
	}

	/**
	 * Purge a file from Akamai CDN
	 *
	 * @param $uri_path
	 */
	private function _purge_object($uri_path)
	{
		$this->_do_authenticated_request(self::RACKSPACE_CDN_OPERATION, $uri_path, 'DELETE');
	}

	/**
	 * Copy a file on Rackspace.
	 * @param $source_uri
	 * @param $target_uri
	 */
	private function _copy_file($source_uri, $target_uri)
	{
		$target_uri = '/'.ltrim($target_uri, '/');
		$this->_do_authenticated_request(self::RACKSPACE_STORAGE_OPERATION, $source_uri, 'COPY', array('Destination: '.$target_uri));
	}

	/**
	 * Prepare a request URI by container and target path.
	 *
	 * @param $container
	 * @param $uri
	 * @return string
	 */
	private function _prepare_request_uri($container, $uri = '', $source_data = array())
	{
		return rawurlencode($container).(!empty($uri) ? '/'.rawurlencode($this->_get_path_prefix($source_data).$uri) : '');
	}

	/**
	 * Get MIME type for file
	 *
	 * @internal Used to get mime types
	 * @param string &$file File path
	 * @return string
	 */
	private static function _get_mime_type(&$file)
	{
		$type = false;
		// Fileinfo documentation says fileinfo_open() will use the
		// MAGIC env var for the magic file
		if (extension_loaded('fileinfo') && isset($_ENV['MAGIC']) &&
			($finfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC'])) !== false)
		{
			if (($type = finfo_file($finfo, $file)) !== false)
			{
				// Remove the charset and grab the last content-type
				$type = explode(' ', str_replace('; charset=', ';charset=', $type));
				$type = array_pop($type);
				$type = explode(';', $type);
				$type = trim(array_shift($type));
			}
			finfo_close($finfo);

			// If anyone is still using mime_content_type()
		} elseif (function_exists('mime_content_type'))
			$type = trim(mime_content_type($file));

		if ($type !== false && strlen($type) > 0) return $type;

		// Otherwise do it the old fashioned way
		global $mimes;

		$ext = strtolower(pathInfo($file, PATHINFO_EXTENSION));
		if ($ext == 'png')
		{
			return 'image/png';
		}

		$mime_type = isset($mimes[$ext]) ? $mimes[$ext] : 'application/octet-stream';
		if (is_array($mime_type))
		{
			$mime_type = end($mime_type);
		}
		return $mime_type;
	}
}
