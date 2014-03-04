<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets S3 Upload File
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_s3_file extends Assets_base_file
{
	/**
	 * @var Assets_s3_source
	 */
	protected $source;

	/**
	 * File information from the S3 servers
	 * @var StdClass
	 */
	private $_file_info;

	/**
	 * @var array|bool|mixed|null
	 */
	private $_source_settings;

	/**
	 * Construct the asset file from asset id and source
	 * @param $file_id
	 * @param Assets_s3_source $source
	 * @param $prefetched_row if passed, will be used instead of loading DB data
	 */
	public function __construct($file_id, Assets_s3_source $source, $prefetched_row = null)
	{
		parent::__construct($file_id, $source, $prefetched_row);

		$bucket_id = $this->folder_row->source_id;

		$this->_source_settings = $source->get_source_settings();
		$this->subpath = $this->folder_row->full_path . $this->row_field('file_name');
		$this->path = $this->_source_settings->bucket . '/' . $this->subpath;
		$this->server_path = trim($this->subpath, '/');
	}

	/**
	 * Does file exist?
	 *
	 * @return bool
	 */
	public function exists()
	{
		// checking would be too expensive, so we'll just be optimistic
		return TRUE;
	}

	/**
	 * Return file folder
	 *
	 * @return string
	 */
	public function folder()
	{
		$path = $this->_source_settings->bucket . ($this->subpath ? '/'.$this->subpath : '');
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * Return the file URL
	 *
	 * @param string $manipulation_name
	 * @return string
	 */
	public function url($manipulation_name = '')
	{
		if ( ! empty($manipulation_name))
		{
			return NULL;
		}

		$prefix = !empty($this->_source_settings->subfolder) ? rtrim($this->_source_settings->subfolder, '/').'/' : '';

		return $this->_source_settings->url_prefix . $prefix . $this->subpath;
	}

	/**
	 * Returns a local copy of the file
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function get_local_copy()
	{
		$location = Assets_helper::get_temp_file();

		$prefix = !empty($this->_source_settings->subfolder) ? rtrim($this->_source_settings->subfolder, '/').'/' : '';

		$this->source->s3->setEndpoint($this->source->get_endpoint_by_location($this->_source_settings->location));
		@$this->source->s3->getObject($this->_source_settings->bucket, $prefix.$this->subpath, $location);

		if (!filesize($location))
		{
			throw new Exception(lang('couldnt_download'));
		}
		return $location;
	}

	/**
	 * Returns the server path for the file
	 *
	 * @param $manipulation_name
	 * @return mixed
	 */
	public function server_path($manipulation_name = '')
	{
		if ( ! empty($manipulation_name))
		{
			return NULL;
		}

		return $this->server_path;
	}

	/**
	 * Subfolder
	 */
	public function subfolder($manipulation_name = '')
	{
		if ( ! empty($manipulation_name))
		{
			return NULL;
		}

		if (! isset($this->subfolder))
		{
			$this->subfolder = dirname($this->subpath);
			if ($this->subfolder == '.') $this->subfolder = '';
		}

		return $this->subfolder;
	}

	/**
	 * File Size
	 */
	function size($manipulation_name = '', $fresh_data = FALSE)
	{
		if ( ! empty($manipulation_name))
		{
			return NULL;
		}

		if ($fresh_data)
		{
			return $this->_get_file_info()->size;
		}
		return $this->row_field('size');
	}

	/**
	 * @param bool $fresh_data
	 * @return mixed|string
	 */
	public function date_modified($fresh_data = FALSE)
	{
		if ($fresh_data)
		{
			return $this->_get_file_info()->time;
		}
		return $this->row_field('date_modified');
	}


	/**
	 * Returns a path for the thumbnail source
	 * @return mixed
	 */
	public function get_thumbnail_source_path()
	{
		$path = Assets_helper::ensure_cache_path('assets/s3_sources') . $this->file_id . '.jpg';
		if (!file_exists($path))
		{
			$location = $this->get_local_copy();
			@rename($location, $path);
		}

		return Assets_helper::ensure_cache_path('assets/s3_sources') . $this->file_id . '.jpg';
	}

	private function _get_file_info()
	{
		if (empty($this->_file_info))
		{
			$prefix = !empty($this->_source_settings->subfolder) ? rtrim($this->_source_settings->subfolder, '/').'/' : '';
			$this->source->s3->setEndpoint($this->source->get_endpoint_by_location($this->_source_settings->location));
			$this->_file_info = (object) $this->source->s3->getObjectInfo($this->_source_settings->bucket, $prefix.$this->subpath);
		}
		return $this->_file_info;
	}
}
