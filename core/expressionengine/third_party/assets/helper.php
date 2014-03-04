<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

define('ASSETS_LIB_PATH', PATH_THIRD . 'assets/libraries/');

/**
 * Assets Helper
 *
 * @package   Assets
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Assets_helper
{
	private static $_garnish_version = '0.1';
	private static $_css = '';
	private static $_hires_css = '';
	private static $_filesize_units = array('B', 'KB', 'MB', 'GB');
	private static $_cache = array();

	const ACTIONS_REPLACE = 'replace';
	const ACTIONS_CANCEL = 'cancel';
	const ACTIONS_KEEP_BOTH = 'keep_both';

	private static $_skip_file_patterns = array('^Thumbs\.db$', '^\.DS_STORE$');
	private static $_skip_folder_patterns = array('^_');

	/**
	 * Constructor
	 */
	function __construct()
	{
		// Add Package Path
		self::_get_EE()->load->add_package_path(PATH_THIRD.'assets/');

		self::_get_EE()->load->library('assets_lib');
	}

	/**
	 * Emulate helper
	 *
	 * @param $method_name
	 * @param $arguments
	 * @return mixed
	 */
	public function __call($method_name, $arguments)
	{
		if (method_exists(self::_get_EE()->assets_lib, $method_name))
		{
			return call_user_func_array(array(self::_get_EE()->assets_lib, $method_name), $arguments);
		}

		if (method_exists('Assets_helper', $method_name))
		{
			return call_user_func_array(array('Assets_helper', $method_name), $arguments);
		}

		return NULL;
	}

	/**
	 * @static
	 * @return array
	 */
	public static function get_file_kinds()
	{
		return array(
			'access'      => array('adp','accdb','mdb'),
			'audio'       => array('wav','aif','aiff','aifc','m4a','wma','mp3','aac','oga'),
			'excel'       => array('xls', 'xlsx'),
			'flash'       => array('fla','swf'),
			'html'        => array('html','htm'),
			'illustrator' => array('ai'),
			'image'       => array('jpg','jpeg','jpe','tiff','tif','png','gif','bmp','webp'),
			'pdf'         => array('pdf'),
			'photoshop'   => array('psd','psb'),
			'php'         => array('php'),
			'text'        => array('txt','text'),
			'video'       => array('mov','m4v','wmv','avi','flv','mp4','ogg','ogv','rm'),
			'word'        => array('doc','docx')
		);
	}


	/**
	 * Make EE available in static context
	 *
	 * @static
	 * @return EE
	 */
	private static function _get_EE()
	{
		return get_instance();
	}


	/**
	 * Theme URL
	 */
	private static function _theme_url()
	{
		if (! isset(self::$_cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : self::_get_EE()->config->slash_item('theme_folder_url').'third_party/';
			self::$_cache['theme_url'] = $theme_folder_url.'assets/';
		}

		return self::$_cache['theme_url'];
	}

	/**
	 * Include Theme CSS
	 */
	public static function include_css()
	{
		foreach (func_get_args() as $file)
		{
			self::_get_EE()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.self::_theme_url().'styles/'.$file.'" />');
		}
	}

	/**
	 * Include Theme JS
	 */
	public static function include_js()
	{
		foreach (func_get_args() as $file)
		{
			if (self::_get_EE()->config->item('assets_use_uncompressed_js') !== TRUE)
			{
				$file = str_replace('.js', '.min.js', $file);
			}

			$file .= '?'.ASSETS_VER;

			self::_get_EE()->cp->add_to_foot('<script type="text/javascript" src="'.self::_theme_url().'scripts/'.$file.'"></script>');
		}
	}

	/**
	 * Include Garnish
	 */
	public static function include_garnish()
	{
		$EE = self::_get_EE();

		// Has Garnish not been imported yet, or was an older version imported?
		if (!isset($EE->session->cache['garnish']) || version_compare($EE->session->cache['garnish'], self::$_garnish_version, '<'))
		{
			if ($EE->config->item('assets_use_uncompressed_js'))
			{
				$filename = 'garnish-'.self::$_garnish_version.'.js';
			}
			else
			{
				$filename = 'garnish-'.self::$_garnish_version.'.min.js';
			}

			$EE->cp->add_to_foot('<script type="text/javascript" src="'.self::_theme_url().'lib/'.$filename.'"></script>');

			// Remember that we've imported Garnish
			$EE->session->cache['garnish'] = self::$_garnish_version;
		}
	}

	/**
	 * Insert CSS
	 */
	public static function insert_css($css)
	{
		self::_get_EE()->cp->add_to_head('<style type="text/css">'.$css.'</style>');
	}

	/**
	 * Insert JS
	 */
	public static function insert_js($js)
	{
		self::_get_EE()->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	/**
	 * Include Sheet Resources
	 */
	public static function include_sheet_resources()
	{
		if (! isset(self::$_cache['included_sheet_resources']))
		{
			self::_get_EE()->lang->loadfile('assets');

			self::include_garnish();
			self::include_css('assets.css');
			self::include_js('assets.js');
			self::insert_css('.content_elements_icon_assets { background: url('.self::_theme_url().'images/ce_icon.png) !important; background-size: 16px !important; }');

			$js = self::get_actions_js()."\n"
				. self::get_lang_js('upload_files', 'upload_status', 'showing', 'of', 'file', 'files', 'selected',
					'new_subfolder', 'rename', '_delete', 'view_file', 'edit_file',
					'remove_file', 'remove_files', 'confirm_delete_folder', 'confirm_delete_file', 'confirm_delete_files',
					'how_to_proceed', 'apply_to_remaining_conflicts', 'perform_selected', 'couldnt_upload')."\n"
				. 'Assets.siteId = '.self::_get_EE()->config->item('site_id').";\n"
				. 'Assets.siteUrl = "'.self::get_site_url().'";';

			self::insert_js($js);

			self::$_cache['included_sheet_resources'] = TRUE;
		}
	}

	/**
	 * Queues up some CSS for inclusion later.
	 *
	 * @static
	 * @param string $css
	 */
	public static function queue_css($css)
	{
		if (self::$_css)
		{
			self::$_css .= NL;
		}

		self::$_css .= trim($css);
	}

	/**
	 * Queues up some hi-res CSS for inclusion later.
	 *
	 * @static
	 * @param string $css
	 */
	public static function queue_hires_css($css)
	{
		if (self::$_hires_css)
		{
			self::$_hires_css .= NL;
		}

		self::$_hires_css .= trim($css);
	}

	/**
	 * Queues up the CSS for a given set of thumbnails.
	 *
	 * @static
	 * @param array $thumbs
	 * @param bool $hardcode_file_widths
	 */
	public static function queue_thumb_css($thumbs, $hardcode_file_widths = FALSE)
	{
		foreach ($thumbs as $file_class => $thumb)
		{
			if ($hardcode_file_widths)
			{
				$width = $thumb->width + 10;
				self::queue_css(".{$file_class} { width: {$width}px; }");
			}

			self::queue_css(".{$file_class} .assets-thumb-wrapper { margin: {$thumb->margin}; }");
			self::queue_css(".{$file_class} .assets-thumb { width: {$thumb->width}px; height: {$thumb->height}px; background-image: url({$thumb->url}); }");
			self::queue_hires_css(".{$file_class} .assets-thumb { background-image: url({$thumb->url_2x}) !important; background-size: {$thumb->width}px {$thumb->height}px !important; }");
		}
	}

	/**
	 * Returns all of the queued CSS.
	 *
	 * @static
	 * @return string
	 */
	public static function get_queued_css()
	{
		$css = self::$_css;

		if (self::$_hires_css)
		{
			if ($css)
			{
				$css .= NL;
			}

			$css .= '@media only screen and (-moz-min-device-pixel-ratio: 1.5),' . NL .
				'only screen and (-o-min-device-pixel-ratio: 3/2),'.  NL .
				'only screen and (-webkit-min-device-pixel-ratio: 1.5),'.  NL .
				'only screen and (min-device-pixel-ratio: 1.5),'.  NL .
				'only screen and (min-resolution: 1.5dppx) {'.  NL .
				self::$_hires_css . NL .
				'}';
		}

		// clear out the queue
		self::$_css = '';
		self::$_hires_css = '';

		return $css;
	}

	/**
	 * Inserts any queued CSS into the page <head>
	 *
	 * @static
	 */
	public static function insert_queued_css()
	{
		$css = self::get_queued_css();

		if ($css)
		{
			self::insert_css($css);
		}
	}

	/**
	 * Return a list of ACT ids used by Assets.
	 *
	 * @return array
	 */
	public static function get_asset_action_ids()
	{
		static $ids = NULL;

		if (is_null($ids))
		{
			$ids = array();

			// get the action IDs
			self::_get_EE()->db->select('action_id')->where('class', 'Assets_mcp');
			$rows = self::_get_EE()->db->get('actions')->result();

			foreach ($rows as $row)
			{
				$ids[] = $row->action_id;
			}
		}

		return $ids;
	}

	/**
	 * Get Actions JS
	 */
	public static function get_actions_js()
	{
		// get the action IDs
		self::_get_EE()->db->select('action_id, method')
			->where('class', 'Assets_mcp');

		if ($methods = func_get_args())
		{
			self::_get_EE()->db->where_in('method', $methods);
		}

		$actions = self::_get_EE()->db->get('actions');

		$json = array();

		foreach ($actions->result() as $act)
		{
			$json[$act->method] = $act->action_id;
		}

		return 'Assets.actions = '.Assets_helper::get_json($json).';';
	}


	/**
	 * Get Site URL
	 */
	public static function get_site_url()
	{
		if (! isset(self::$_cache['site_url']))
		{
			if (! ($site_url = self::_get_EE()->config->item('assets_site_url')))
			{
				$site_url = self::_get_EE()->functions->fetch_site_index(0, 0);

				if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
				{
					$site_url = str_replace('http://', 'https://', $site_url);
				}
			}

			self::$_cache['site_url'] = $site_url;
		}

		return self::$_cache['site_url'];
	}


	/**
	 * Get Language JS
	 */
	public static function get_lang_js()
	{
		$json = array();

		foreach (func_get_args() as $line)
		{
			$json[$line] = lang($line);
		}

		return 'Assets.lang = '. Assets_helper::get_json($json).';';
	}

	/**
	 * Get File Kind
	 */
	public static function get_kind($file)
	{
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		$file_kinds = self::get_file_kinds();
		foreach ($file_kinds as $kind => &$extensions)
		{
			if (in_array($ext, $extensions))
			{
				return $kind;
			}
		}

		return 'file';
	}

	/**
	 * Format Date
	 */
	public static function format_date($timestamp)
	{
		if ($timestamp)
		{
			return date('M j, Y g:i A', $timestamp);
		}
	}

	/**
	 * Format File Size
	 */
	public static function format_filesize($filesize)
	{
		// get the formatted size
		foreach (self::$_filesize_units as $i => $unit)
		{
			// round up to next unit at 0.95
			if (! isset(self::$_filesize_units[$i+1]) || $filesize < (pow(1000, $i+1) * 0.95))
			{
				return ($i ? round($filesize / pow(1000, $i)) : $filesize) . ' '.$unit;
			}
		}
	}

	/**
	 * Fix wonky directory separators in a given path.
	 */
	public static function normalize_path($path)
	{
		$path = str_replace('\\', '/', $path);
		return str_replace('//', '/', $path);
	}

	/**
	 * Get temporary file
	 *
	 * @param string $extension extension to use, defaults to "tmp"
	 * @return string
	 */
	public static function get_temp_file($extension = "tmp")
	{
		self::_get_EE()->load->library('filemanager');

		$cache_path = 'assets/tmp_storage';

		$cache_path = self::ensure_cache_path($cache_path);

		$file_name = uniqid('assets_tmp_', TRUE).'.'.$extension;

		$file = fopen($cache_path . $file_name, 'w');
		fclose($file);

		return $cache_path . $file_name;
	}

	/**
	 * Get ACT for viewing thumbnail
	 *
	 * @return mixed
	 */
	public static function get_thumbnail_action()
	{
		static $action;
		if (empty($action))
		{
			$result = self::_get_EE()->db->get_where('actions', array('class' => 'Assets_mcp', 'method' => 'view_thumbnail'))->result();
			$action = $result[0]->action_id;
		}
		return $action;
	}

	/**
	 * @param $files
	 * @param $tagdata
	 * @param $var_prefix
	 * @return string
	 */
	public static function parse_file_tag($files, $tagdata, $var_prefix = '')
	{

		if (empty($files))
		{
			return '';
		}

		$manipulatable_tags = array('url', 'server_path', 'subfolder', 'filename', 'extension', 'date_modified', 'kind',
			'width', 'height', 'size');

		$manipulation_tags = array();
		$pattern = '/\{' . $var_prefix . '(?P<tag_name>' . join('|', $manipulatable_tags) . '):(?P<manipulation>[a-z\-_0-9]+)\}/i';

		if (preg_match_all($pattern, $tagdata, $matches))
		{
			foreach ($matches['manipulation'] as $i => $manipulation_name)
			{
				$manipulation_tags[$manipulation_name][] = $matches['tag_name'][$i];
			}
		}

		$variables = array();
		$file_count = count($files);

		foreach ($files as $file)
		{
			$unformatted_size = $file->size();
			$size = self::format_filesize($unformatted_size);
			$size = ($size == '2 GB' ? '> 2 GB' : $size);

			$file_vars = array(
				$var_prefix.'file_id'                => $file->file_id(),
				$var_prefix.'asset_id'               => $file->file_id(),
				$var_prefix.'url'                    => $file->url(),
				$var_prefix.'server_path'            => $file->server_path(),
				$var_prefix.'subfolder'              => $file->subfolder(),
				$var_prefix.'filename'               => $file->filename_sans_extension(),
				$var_prefix.'extension'              => $file->extension(),
				$var_prefix.'date_modified'          => $file->date_modified(),
				$var_prefix.'kind'                   => $file->kind(),
				$var_prefix.'width'                  => $file->width(),
				$var_prefix.'height'                 => $file->height(),
				$var_prefix.'size'                   => $size,
				$var_prefix.'size unformatted="yes"' => $unformatted_size,
				$var_prefix.'total_files'            => $file_count,
				$var_prefix.'source_id'              => $file->source()->get_source_id(),
				$var_prefix.'folder_id'              => $file->folder_id(),
				$var_prefix.'source_subfolder'       => $file->source_subfolder(),
			);

			// add additional image sizes.
			foreach ($manipulation_tags as $manipulation_name => $mtags)
			{
				// add info for all tags that are using this manipulation
				foreach ($mtags as $tag)
				{
					try
					{
						if (method_exists($file, $tag))
						{
							switch ($tag)
							{
								case 'size':
									$val = self::format_filesize($file->size($manipulation_name));
									$val = ($val == '2 GB' ? '> 2 GB' : $val);
									break;
								default:
									$val = $file->$tag($manipulation_name);
							}
						}
						else
						{
							$val = '';
						}
					}
					catch (Exception $e)
					{
						$val = $e->getMessage();
					}

					$file_vars[$var_prefix.$tag.':'.$manipulation_name] = $val;
				}
			}

			// load in file_id, title, date, etc.
			$row = $file->row();
			foreach ($row as $key => $val)
			{
				if (empty($file_vars[$var_prefix.$key]))
				{
					$file_vars[$var_prefix.$key] = $val;
				}
			}

			$variables[] = $file_vars;
		}


		return self::_get_EE()->TMPL->parse_variables($tagdata, $variables);
	}

	/**
	 * Ensures a writable cache path
	 *
	 * @param $path
	 * @return string $cache_path
	 */
	public static function ensure_cache_path($path)
	{
		$cache_path = self::_get_EE()->config->item('cache_path');
		if (empty($cache_path))
		{
			$cache_path = APPPATH.'cache/';
		}
		$path_parts = explode("/", $path);

		foreach ($path_parts as $folder)
		{
			$cache_path = $cache_path . $folder . '/';
			if ( ! is_dir($cache_path))
			{
				mkdir($cache_path, DIR_WRITE_MODE);
				@chmod($cache_path, DIR_WRITE_MODE);
			}
		}

		return $cache_path;
	}

	/**
	 * Load Module Settings
	 */
	public static function get_global_settings()
	{
		if ( ! isset(self::$_cache['settings']))
		{
			$query = self::_get_EE()->db->select('settings')
				->where('name', 'assets')
				->get('fieldtypes');

			if (!$query->num_rows())
			{
				show_error(lang('no_fieldtype'));
			}

			$settings = unserialize(base64_decode($query->row('settings')));

			$settings = array_merge(array('license_key' => '', 's3_access_key_id' => '', 's3_secret_access_key' => ''), $settings);

			self::$_cache['settings'] = $settings;
		}

		return self::$_cache['settings'];
	}

	/**
	 * Sort file list
	 *
	 * @param $files
	 * @param $orderby
	 * @param $sort
	 **/
	public static function sort_files(&$files, $orderby, $sort)
	{
		// ignore if no files
		if (! $files) return;

		if (! in_array($orderby, array('name', 'folder', 'date', 'size'))) $orderby = 'name';
		if (! in_array($sort, array('asc', 'desc'))) $sort = 'asc';

		foreach ($files as &$file)
		{
			$sort_names[] = strtolower($file->filename());
			$sort_folders[] = $file->folder();
			if ($orderby == 'size') $sort_sizes[] = $file->size();
			else if ($orderby == 'date') $sort_dates[] = $file->date_modified();
		}

		$SORT = ($sort == 'asc') ? SORT_ASC : SORT_DESC;

		switch ($orderby)
		{
			case 'name':
				// sort by name, then folder
				array_multisort($sort_names, $SORT, SORT_STRING, $sort_folders, SORT_ASC, SORT_STRING, $files);
				break;

			case 'folder':
				// sort by folder, then name
				array_multisort($sort_folders, $SORT, SORT_STRING, $sort_names, SORT_ASC, SORT_STRING, $files);
				break;

			case 'date':
				// sort by date, then name, then folder
				array_multisort($sort_dates, $SORT, SORT_NUMERIC, $sort_names, SORT_ASC, SORT_STRING, $sort_folders, SORT_ASC, SORT_STRING, $files);
				break;

			case 'size':
				// sort by size, then name, then folder
				array_multisort($sort_sizes, $SORT, SORT_NUMERIC, $sort_names, SORT_ASC, SORT_STRING, $sort_folders, SORT_ASC, SORT_STRING, $files);
				break;
		}
	}

	/**
	 * Create EE thumbnails.
	 *
	 * @param $image_path
	 * @param $upload_folder_id
	 * @return mixed
	 */
	public static function create_ee_thumbnails($image_path, $upload_folder_id)
	{
		if (!class_exists('Assets_ee_source'))
		{
			require_once PATH_THIRD . 'assets/sources/ee/source.ee.php';
		}

		$preferences = self::_get_EE()->filemanager->fetch_upload_dir_prefs($upload_folder_id);
		$preferences['file_name'] = pathinfo($image_path, PATHINFO_BASENAME);

		$preferences['server_path'] = Assets_ee_source::resolve_server_path(Assets_helper::normalize_path($preferences['server_path']));


		// Trick Filemanager into creating the thumbnail where WE need it
		$preferences['server_path'] .= str_replace($preferences['server_path'], '',
			str_replace(pathinfo($image_path, PATHINFO_BASENAME), '', $image_path));

		// On Windows machines CI's Image_lib gets all sorts of confused, so have to make sure our paths use the DIRECTORY_SEPARATOR separator
		if (DIRECTORY_SEPARATOR === "\\")
		{
			$preferences['server_path'] = str_replace('/', '\\', $preferences['server_path']);
			$image_path = str_replace('/', '\\', $image_path);
		}

		return self::_get_EE()->filemanager->create_thumb($image_path, $preferences);
	}

	/**
	 * Get JSON formatted data for any given data.
	 *
	 * @param $data
	 * @return string
	 */
	public static function get_json($data)
	{
		if (version_compare(APP_VER, '2.6', '<') OR !function_exists('json_encode'))
		{
			return self::_get_EE()->javascript->generate_json((array)$data, TRUE);
		}
		else
		{
			return json_encode($data);
		}
	}

	/**
	 * Apply source overrides.
	 *
	 * @param $source_id
	 * @param StdClass $settings
	 * @return StdClass
	 */
	public static function apply_source_overrides($source_id, $settings)
	{

		static $overrides = NULL;
		if (is_null($overrides))
		{
			$overrides = self::_get_EE()->config->item('assets_source_settings');
			if (!is_array($overrides))
			{
				$overrides = array();
			}
		}

		if (isset($overrides[$source_id]) && is_array($overrides[$source_id]))
		{
			foreach ($overrides[$source_id] as $key => $value)
			{
				$settings->{$key} = $value;
			}
		}

		return $settings;
	}

	/**
	 * Return true, if the item name is allowed.
	 *
	 * @param $name
	 * @return bool
	 */
	public static function is_allowed_file_name($name)
	{
		static $_combined_patterns = NULL;
		if (is_null($_combined_patterns))
		{
			$config_patterns = self::_get_EE()->config->item('assets_ignore_file_patterns');
			if (!is_array($config_patterns))
			{
				$config_patterns = array();
			}

			$_combined_patterns = array_merge(self::$_skip_file_patterns, $config_patterns);
		}

		foreach ($_combined_patterns as $pattern)
		{
			if (preg_match('/'.trim($pattern, '/').'/', $name))
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Return true, if the item name is allowed.
	 *
	 * @param $name
	 * @return bool
	 */
	public static function is_allowed_folder_name($name)
	{
		static $_combined_patterns = NULL;
		if (is_null($_combined_patterns))
		{
			$config_patterns = self::_get_EE()->config->item('assets_ignore_folder_patterns');
			if (!is_array($config_patterns))
			{
				$config_patterns = array();
			}

			$_combined_patterns = array_merge(self::$_skip_folder_patterns, $config_patterns);
		}

		foreach ($_combined_patterns as $pattern)
		{
			if (preg_match('/'.trim($pattern, '/').'/', $name))
			{
				return FALSE;
			}
		}
		return TRUE;
	}

}
