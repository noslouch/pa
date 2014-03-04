<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets file abstract class.
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */

abstract class Assets_base_file
{
	/**
	 * @var EE
	 */
	protected $EE;

	/**
	 * @var Assets_ee_source
	 */
	protected $source;
	protected $file_id;
	protected $path;
	protected $subpath;
	protected $server_path;
	protected $extension;
	protected $kind;
	protected $url;
	protected $subfolder;

	protected $folder_row = array();
	protected $row = array();

	var $selected = FALSE;

	/**
	 * Construct the asset file from asset id and source
	 *
	 * @abstract
	 * @param $file_id
	 * @param Assets_base_source $source
	 * @param $prefetched_row if passed, will be used instead of loading DB data
	 */
	public function __construct($file_id, Assets_base_source $source, $prefetched_row = null)
	{
		if (!empty($prefetched_row))
		{
			$this->row = $prefetched_row;
		}

		$this->source = $source;

		$this->EE = get_instance();
		$this->EE->load->library('assets_lib');

		if (! isset($this->EE->session->cache['assets']))
		{
			$this->EE->session->cache['assets'] = array();
		}

		$this->file_id = $file_id;

		$this->load_row();

		// load asset folder information
		$this->folder_row = $this->EE->assets_lib->get_folder_row_by_id($this->row_field('folder_id'));

		if (empty($this->row) OR empty($this->folder_row))
		{
			return;
		}
	}

	/**
	 * Does file exist?
	 *
	 * @abstract
	 * @return bool
	 */
	abstract public function exists();

	/**
	 * Return file folder
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function folder();

	/**
	 * Return the file URL
	 *
	 * @abstract
	 * @param string $manipulation_name
	 * @return string
	 */
	abstract public function url($manipulation_name = '');

	/**
	 * Returns the server path for the file
	 *
	 * @param $manipulation_name
	 * @return mixed
	 */
	abstract public function server_path($manipulation_name = '');

	/**
	 * Returns the subfolder for a file
	 *
	 * @abstract
	 * @param string $manipulation_name
	 * @return mixed
	 */
	abstract public function subfolder($manipulation_name = '');

	/**
	 * Return file size
	 *
	 * @abstract
	 * @param string $manipulation_name
	 * @param $fresh_data bool if TRUE will get fresh data from the file source
	 * @return mixed
	 */
	abstract public function size($manipulation_name = '', $fresh_data = FALSE);

	/**
	 * Return date modified
	 *
	 * @param $fresh_data bool if TRUE will get fresh data from the file source
	 * @return mixed
	 */
	abstract public function date_modified($fresh_data = FALSE);

	/**
	 * Returns a local copy of the file. For remote storages this means downloading the file
	 *
	 * @abstract
	 * @return mixed
	 */
	abstract public function get_local_copy();

	/**
	 * Returns a path for the thumbnail source
	 *
	 * @abstract
	 * @return mixed
	 */
	abstract public function get_thumbnail_source_path();

	/**
	 * Call
	 */
	function __call($name, $arguments)
	{
		return $this->row_field($name);
	}

	/**
	 * Return the file height
	 *
	 * @param string $manipulation_name
	 * @return mixed
	 */
	public function height($manipulation_name = '')
	{
		return $this->row_field('height');
	}

	/**
	 * Return the file width
	 *
	 * @param string $manipulation_name
	 * @return mixed
	 */
	public function width($manipulation_name = '')
	{
		return $this->row_field('width');
	}

	/**
	 * Return file id
	 * @return mixed|string
	 */
	public function file_id()
	{
		return $this->row_field('file_id');

	}
	/**
	 * Set Row
	 */
	function set_row($row)
	{
		$this->row = $row;
	}

	/**
	 * Load Row
	 */
	function load_row()
	{
		if ( empty($this->row))
		{
			$this->row = (array) $this->EE->assets_lib->get_file_row_by_id($this->file_id);
		}

		return ! empty($this->row);
	}

	/**
	 * Row
	 */
	function row()
	{
		// just return the whole row
		return $this->row;
	}

	/**
	 * @param string $key
	 * @return mixed|string
	 */
	function row_field($key)
	{
		return isset($this->row[$key]) ? $this->row[$key] : '';
	}

	/**
	 * File Path
	 */
	function path()
	{
		return $this->path;
	}

	/**
	 * Return subfolder path
	 *
	 * @return string
	 */
	public function subpath()
	{
		return $this->subpath;
	}

	/**
	 * Return folder row
	 *
	 * @return StdClass
	 */
	public function folder_row()
	{
		return $this->folder_row;
	}

	/**
	 * File Extension
	 */
	function extension()
	{
		if (! isset($this->extension))
		{
			$this->extension = strtolower(pathinfo($this->server_path, PATHINFO_EXTENSION));
		}

		return $this->extension;
	}

	/**
	 * File Kind
	 */
	function kind()
	{
		if (! isset($this->kind))
		{
			$this->kind = Assets_helper::get_kind($this->filename());
		}

		return $this->kind;
	}

	/*
	 * Filename
	 */
	function filename()
	{
		return $this->row_field('file_name');
	}

	/*
	 * Filename
	 */
	function filename_sans_extension()
	{
		return pathinfo($this->row_field('file_name'), PATHINFO_FILENAME);
	}


	/**
	 * Retuns thumbnail info for this file.
	 *
	 * @param int $max_width
	 * @param int $max_height
	 * @return object
	 */
	public function get_thumb_data($max_width, $max_height)
	{
		// ignore if this isn't an image, or there's no width/height
		if ($this->kind() != 'image' || !$this->width() || !$this->height())
		{
			return FALSE;
		}
		else
		{
			// treat the image as a horizontal?
			if (($this->height() / $this->width()) <= ($max_height / $max_width))
			{
				$thumb_width = $max_width;
				$thumb_height = round(($max_width / $this->width()) * $this->height());
			}
			else
			{
				$thumb_height = $max_height;
				$thumb_width = round(($max_height / $this->height()) * $this->width());
			}

			return (object) array(
				'url'     => $this->get_thumb_url($thumb_width, $thumb_height),
				'url_2x'  => $this->get_thumb_url($thumb_width*2, $thumb_height*2),
				'width'   => $thumb_width,
				'height'  => $thumb_height,
			);
		}
	}

	/**
	 * Return the generated URL that will give us the image
	 */
	public function get_thumb_url($width, $height)
	{
		// get the action IDs
		$this->EE->db->select('action_id, method')
			->where('class', 'Assets_mcp');
		$this->EE->db->where('method', 'view_thumbnail');

		$actions = $this->EE->db->get('actions')->result();
		if ($actions)
		{
			return rtrim(Assets_helper::get_site_url(), '?') . "?ACT={$actions[0]->action_id}&file_id={$this->file_id}&size={$width}x{$height}&hash=".$this->date_modified();
		}

		return '';
	}

	/**
	 * Return the path to the thumbnail
	 *
	 * @param $size
	 * @return string
	 */
	public function get_thumb_path($size)
	{
		$path = Assets_helper::ensure_cache_path('assets/thumbs/'.$this->file_id);

		return $path.$this->file_id.'_'.$size.'.'.$this->extension();
	}

	/**
	 * Return a file's source.
	 *
	 * @return Assets_base_source|Assets_ee_source
	 */
	public function source()
	{
		return $this->source;
	}

	/**
	 * Return a file source's subfolder setting
	 *
	 * @return string
	 */
	public function source_subfolder()
	{
		$settings = $this->source()->settings();
		return !empty($settings->subfolder) ? rtrim($settings->subfolder, '/').'/' : '';
	}
}
