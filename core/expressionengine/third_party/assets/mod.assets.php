<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets Module
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets
{
	public function __construct()
	{
		$this->EE = get_instance();

		$this->EE->load->add_package_path(PATH_THIRD.'assets/');
		$this->EE->load->library('assets_lib');
	}

	/**
	 * Get files and parse them
	 */
	public function files()
	{
		$tagdata = $this->EE->TMPL->tagdata;

		// Ignore if there's no tagdata
		if (!$tagdata)
		{
			return '';
		}

		$parameters = $this->_gather_file_parameters();

		$files = $this->EE->assets_lib->get_files($parameters);

		if ($files)
		{
			// is there a var_prefix?
			if (($var_prefix = $this->EE->TMPL->fetch_param('var_prefix')) !== FALSE)
			{
				$var_prefix = rtrim($var_prefix, ':').':';
				$tagdata = str_replace($var_prefix, '', $tagdata);
			}

			return Assets_helper::parse_file_tag($files, $tagdata);
		}
		else
		{
			return $this->EE->TMPL->no_results();
		}
	}

	/**
	 * Get folders.
	 *
	 * @param array $passed_parameters if this is passed, will not bother looking up template parameters
	 * @param int $depth depth of this lookup
	 * @return string
	 */
	public function folders($passed_parameters = array(), $depth = 0)
	{
		$tagdata = $this->EE->TMPL->tagdata;

		// Ignore if there's no tagdata
		if (!$tagdata)
		{
			return '';
		}

		// Load template parameters, if none have been passed
		$parameters = empty($passed_parameters) ? $this->_gather_folder_parameters() : $passed_parameters;

		if (empty($parameters))
		{
			return '';
		}

		$folders = $this->EE->assets_lib->get_folders($parameters);

		// Avoid unnecessary actions
		$load_subfolders = FALSE;
		if (strpos($tagdata, '{subfolders}') !== FALSE)
		{
			$load_subfolders = TRUE;
		}

		// Make sure there are folders
		if ($folders)
		{
			$results = array();

			foreach ($folders as $folder)
			{
				$subfolders = '';
				// See if we have subfolders
				if ($load_subfolders && $this->EE->assets_lib->get_folder_id_by_params(array('parent_id' => $folder->folder_id)))
				{
					$subfolder_parameters['parent_id'] = $folder->folder_id;
					$subfolders = $this->folders($subfolder_parameters, $depth + 1);
				}
				$results[] = array(
					'folder_name' => $folder->folder_name,
					'folder_id' => $folder->folder_id,
					'subfolders' => $subfolders,
					'depth' => $depth,
					'total_subfolders' => $this->EE->assets_lib->get_subfolder_count($folder->folder_id)
				);
			}

			// is there a var_prefix?
			if (($var_prefix = $this->EE->TMPL->fetch_param('var_prefix')) !== FALSE)
			{
				$var_prefix = rtrim($var_prefix, ':').':';
				$tagdata = str_replace($var_prefix, '', $tagdata);
			}

			return $this->EE->TMPL->parse_variables($tagdata, $results);
		}
		else
		{
			return $this->EE->TMPL->no_results();
		}
	}

	/**
	 * Return the number of total folders by parameters.
	 *
	 * @return int
	 */
	public function total_folders()
	{
		$parameters = $this->_gather_folder_parameters();
		$folders = $this->EE->assets_lib->get_folders($parameters);
		return count($folders);
	}

	/**
	 * Return the number of total files by parameters.
	 *
	 * @return int
	 */
	public function total_files()
	{
		$parameters = $this->_gather_file_parameters();
		$files = $this->EE->assets_lib->get_files($parameters);
		return count($files);
	}

	/**
	 * Gather file parameters from the template.
	 *
	 * @return array
	 */
	private function _gather_file_parameters()
	{
		$folders = $this->EE->TMPL->fetch_param('folder');
		$folders = preg_split("/\|/", $folders);
		foreach ($folders as &$folder)
		{
			$folder = $this->_get_folder_id_by_tagpath($folder);
		}

		$folder_ids = $this->EE->TMPL->fetch_param('folder_id');
		$folder_ids = preg_split("/\|/", $folder_ids);

		$folders = array_merge($folders, $folder_ids);

		$file_ids = $this->EE->TMPL->fetch_param('file_id');
		$file_ids = preg_split("/\|/", $file_ids);

		// sort out required kinds if any
		$kinds = $this->EE->TMPL->fetch_param('kind', '');
		$kinds = empty($kinds) ? 'any' : $kinds;

		if ($kinds != 'any' && ! is_array($kinds))
		{
			$kinds = preg_split("/\||&&/", $kinds);
		}

		$orderby = $this->EE->TMPL->fetch_param('orderby', '');

		$fixed_order = $this->EE->TMPL->fetch_param('fixed_order');
		if (!empty($fixed_order))
		{
			$fixed_order = preg_split("/\|/", $fixed_order);
			$file_ids = $fixed_order;
			$orderby = 'fixed';
		}

		$parameters = array(
			'folders' => $folders,
			'keywords' => array_filter(preg_split("/\||&&/", (string) $this->EE->TMPL->fetch_param('keywords', ''))),
			'orderby' => $orderby,
			'sort' => $this->EE->TMPL->fetch_param('sort', ''),
			'offset' => $this->EE->TMPL->fetch_param('offset', 0),
			'limit' => $this->EE->TMPL->fetch_param('limit', 100),
			'kinds' => $kinds,
			'file_ids' => $file_ids,
		);

		return $parameters;
	}

	/**
	 * Gather folder parameters from the template.
	 *
	 * @return array
	 */
	private function _gather_folder_parameters()
	{
		$folder = $this->EE->TMPL->fetch_param('parent_folder');

		if (empty($folder) OR $folder == 'top')
		{
			$folder_id = 0;
		}
		else
		{
			$folder_id = $this->_get_folder_id_by_tagpath($folder);

			// If no folder found by designated parameter, return.
			if ( !$folder_id)
			{
				return array();
			}
		}

		$parameters = array(
			'parent_id' => $folder_id,
			'keywords' => array_filter(preg_split("/\||&&/", $this->EE->TMPL->fetch_param('keywords', ''))),
			'offset' => $this->EE->TMPL->fetch_param('offset', 0),
			'limit' => $this->EE->TMPL->fetch_param('limit', 100),
			'recursive' => $this->EE->TMPL->fetch_param('recursive', 'no'),
			'sort' => $this->EE->TMPL->fetch_param('sort', 'asc'),
		);

		return $parameters;
	}

	/**
	 * Get folder id by tag path
	 * @param $tagpath
	 * @return bool
	 */
	private function _get_folder_id_by_tagpath($tagpath)
	{
		$folder_id = FALSE;
		$pattern = '/\{(filedir|source)_([0-9]+)\}([a-z0-9_\- \/]+)?/i';

		if (preg_match($pattern, $tagpath, $matches))
		{
			try
			{
				$source_type = $matches[1];
				$source_id = $matches[2];
				$path = isset($matches[3]) ? $matches[3] : '';

				$field = 'source_id';
				switch($source_type)
				{
					case 'filedir':
						// check if filedir exists for this site
						if (!$this->EE->db->get_where('upload_prefs', array('id' => $source_id, 'site_id' => intval($this->EE->config->item('site_id'))))->row())
						{

							return FALSE;
						}
						$field = 'filedir_id';
						break;

					case 'source':
						if (!$this->EE->db->get_where('assets_sources', array('source_id' => $source_id)))
						{
							return FALSE;
						}
						break;
				}

				if (!empty($path))
				{
					$path = rtrim($path, '/') . '/';
				}

				$folder_row = $this->EE->db->get_where('assets_folders', array($field => $source_id, 'full_path' => $path))->row();
				if (empty($folder_row))
				{
					$folder_id = FALSE;
				}
				else
				{
					$folder_id = $folder_row->folder_id;
				}

			}
			catch (Exception $exception)
			{
				$folder_id = FALSE;
			}
		}

		return $folder_id;
	}
}
