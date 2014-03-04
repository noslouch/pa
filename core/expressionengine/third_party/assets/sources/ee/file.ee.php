<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets EE Upload Directory File
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_ee_file extends Assets_base_file
{
	private $filedir;

	private $_manipulation_data;

	/**
	 * Constructor
	 */
	function __construct($file_id, Assets_ee_source $source, $prefetched_row = null)
	{
		parent::__construct($file_id, $source, $prefetched_row);

		$cache =& $this->EE->session->cache['assets'];
		$upload_filedir_id = $this->folder_row->filedir_id;

		if ( ! isset($cache['filedir_prefs'][$upload_filedir_id]))
		{
			$cache['filedir_prefs'][$upload_filedir_id] = $this->source->get_filedir($upload_filedir_id);
		}

		if (empty($cache['filedir_prefs'][$upload_filedir_id]))
		{
			throw new Exception(lang('exception_error'));
		}

		$this->filedir = $cache['filedir_prefs'][$upload_filedir_id];
		$this->subpath = $this->folder_row->full_path . $this->row_field('file_name');
		$this->path = $this->filedir->name . '/' . $this->subpath;
		$this->server_path = str_replace('//', '/', Assets_ee_source::resolve_server_path($this->filedir->server_path) . $this->subpath);
	}

	/**
	 * File Exists?
	 */
	function exists()
	{
		return (isset($this->server_path) && file_exists($this->server_path) && is_file($this->server_path));
	}

	/**
	 * EE Upload Directory ID
	 */
	function filedir_id()
	{
		return $this->filedir->id;
	}

	/**
	 * EE Upload Directory Path
	 */
	function filedir_path()
	{
		return $this->filedir->server_path;
	}

	/**
	 * EE Upload Directory URL
	 */
	function filedir_url()
	{
		return $this->filedir->url;
	}

	/**
	 * File Folder
	 */
	function folder()
	{
		$path = $this->filedir->name . ($this->subpath ? '/'.$this->subpath : '');
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * URL
	 */
	function url($manipulation_name = '')
	{
		if (! isset($this->url))
		{
			$this->url = $this->filedir->url . str_replace(' ', '%20', $this->subpath);;
		}

		$url = $this->url;
		if (! empty($manipulation_name))
		{
			$url = $this->_inject_manipulation_path($url, $manipulation_name);
		}

		return $url;
	}

	/**
	 * Returns a local copy of the file
	 *
	 * @return mixed
	 */
	public function get_local_copy()
	{
		$location = Assets_helper::get_temp_file();

		copy($this->server_path, $location);
		clearstatcache();

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
		$path = $this->server_path;

		if (! empty($manipulation_name))
		{
			$path = $this->_inject_manipulation_path($path, $manipulation_name);
		}

		return $path;
	}

	/**
	 * Subfolder
	 */
	public function subfolder($manipulation_name = '')
	{
		if (! isset($this->subfolder))
		{
			$this->subfolder = dirname($this->subpath);
			if ($this->subfolder == '.')
			{
				$this->subfolder = '';
			}
		}

		return $this->subfolder . (! empty($manipulation_name) ? '/_' . $manipulation_name : '');
	}

	/**
	 * Return image height
	 */
	function height($manipulation_name = '')
	{
		if ($manipulation_name)
		{
			$manipulation_data = $this->_get_manipulation_data($manipulation_name);

			if ($manipulation_data !== FALSE)
			{
				return $manipulation_data->height;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return parent::height();
		}
	}

	/**
	 * Image Width
	 */
	function width($manipulation_name = '')
	{
		if ($manipulation_name)
		{
			$manipulation_data = $this->_get_manipulation_data($manipulation_name);

			if ($manipulation_data !== FALSE)
			{
				return $manipulation_data->width;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return parent::width();
		}
	}

	/**
	 * File Size
	 */
	function size($manipulation_name = '', $fresh_data = FALSE)
	{
		if ($manipulation_name)
		{
			$manipulation_data = $this->_get_manipulation_data($manipulation_name);

			if ($manipulation_data !== FALSE)
			{
				return $manipulation_data->size;
			}
			else
			{
				return 0;
			}
		}
		else if ($fresh_data)
		{
			return filesize($this->server_path);
		}
		else
		{
			return $this->row_field('size');
		}
	}

	/**
	 * Inject a manipulation path into path
	 *
	 * @param $path
	 * @param $manipulation_name
	 * @return string
	 */
	private function _inject_manipulation_path($path, $manipulation_name)
	{
		$parts = explode("/", $path);
		$final_part = array_pop($parts);
		return join("/", $parts) . '/_' . $manipulation_name . '/' . $final_part;
	}

	/**
	 * Returns a path for the thumbnail source
	 * @return mixed
	 */
	public function get_thumbnail_source_path()
	{
		return $this->server_path();
	}

	public function date_modified($fresh_data = FALSE)
	{
		if ($fresh_data)
		{
			return filemtime($this->server_path);
		}
		return $this->row_field('date_modified');
	}

	/**
	 * Returns the data for an image manipulation, or FALSE if the file doesn't exist.
	 *
	 * @access private
	 * @param string $manipulation_name
	 * @return std_object|bool
	 */
	private function _get_manipulation_data($manipulation_name)
	{
		if (! isset($this->_manipulation_data[$manipulation_name]))
		{
			$path = $this->server_path($manipulation_name);

			if (file_exists($path))
			{
				list ($width, $height) = getimagesize($path);

				$this->_manipulation_data[$manipulation_name] = (object) array(
					'width'  => $width,
					'height' => $height,
					'size'   => filesize($path),
				);
			}
			else
			{
				$this->_manipulation_data[$manipulation_name] = FALSE;
			}
		}

		return $this->_manipulation_data[$manipulation_name];
	}
}
