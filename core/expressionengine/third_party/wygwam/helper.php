<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Wygwam Helper Class for EE2
*/
class Wygwam_helper
{
	public static $entry_site_id;

	private static $_global_settings;
	private static $_theme_url;
	private static $_included_field_resources = FALSE;
	private static $_included_configs;
	private static $_file_tags;
	private static $_page_tags;
	private static $_pages_module_installed;
	private static $_site_pages;
	private static $_page_data;

	private static $_tb_groups;
	private static $_tb_combos;
	private static $_tb_label_overrides;

	/**
	 * Gets Wygwam's global settings.
	 *
	 * static
	 * @return array
	 */
	public static function get_global_settings()
	{
		if (! isset(self::$_global_settings))
		{
			$defaults = array(
				'license_key' => '',
				'file_browser' => 'ee'
			);

			$query = get_instance()->db->select('settings')
			                       ->where('name', 'wygwam')
			                       ->get('fieldtypes');

			$settings = unserialize(base64_decode($query->row('settings')));

			self::$_global_settings = array_merge($defaults, $settings);
		}

		return self::$_global_settings;
	}

	/**
	 * Sets Wygwam's global settings.
	 *
	 * @static
	 * @param array $global_settings
	 */
	public static function set_global_settings($global_settings)
	{
		self::$_global_settings = $global_settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns toolbar button groupings, based on CKEditor's default "Full" toolbar.
	 *
	 * @static
	 * @return array
	 */
	public static function tb_groups()
	{
		if (!isset(self::$_tb_groups))
		{
			self::$_tb_groups = array(
				array('Source'),
				array('Templates'),
				array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord'),
				array('Undo', 'Redo'),
				array('Scayt'),
				array('Bold', 'Italic', 'Underline', 'Strike'),
				array('Subscript', 'Superscript'),
				array('RemoveFormat'),
				array('NumberedList', 'BulletedList'),
				array('Outdent', 'Indent'),
				array('JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'),
				array('Blockquote', 'CreateDiv'),
				array('Link', 'Unlink', 'Anchor'),
				array('Image', 'Table', 'HorizontalRule', 'SpecialChar', 'MediaEmbed'),
				array('ReadMore'),
				array('Styles'),
				array('Format'),
				array('TextColor', 'BGColor'),
				array('Maximize', 'ShowBlocks'),
			);

			$EE = get_instance();

			// -------------------------------------------
			//  'wygwam_tb_groups' hook
			//   - Allow extensions to modify the available toolbar groups
			//
				if ($EE->extensions->active_hook('wygwam_tb_groups'))
				{
					self::$_tb_groups = $EE->extensions->call('wygwam_tb_groups', self::$_tb_groups);
				}
			//
			// -------------------------------------------
		}

		return self::$_tb_groups;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns which toolbar items are combos.
	 *
	 * @static
	 * @return array
	 */
	public static function tb_combos()
	{
		if (!isset(self::$_tb_combos))
		{
			self::$_tb_combos = array('Styles', 'Format');

			$EE = get_instance();

			// -------------------------------------------
			//  'wygwam_tb_combos' hook
			//   - Allow extensions to modify which toolbar items should be considered selects.
			//
				if ($EE->extensions->active_hook('wygwam_tb_combos'))
				{
					self::$_tb_combos = $EE->extensions->call('wygwam_tb_combos', self::$_tb_combos);
				}
			//
			// -------------------------------------------
		}

		return self::$_tb_combos;
	}

	/**
	 * Returns the real toolbar button names.
	 *
	 * @static
	 * @return array
	 */
	public static function tb_label_overrides()
	{
		if (!isset(self::$_tb_label_overrides))
		{
			self::$_tb_label_overrides = array(
				'PasteText'      => 'Paste As Plain Text',
				'PasteFromWord'  => 'Paste from Word',
				'Scayt'          => 'Spell Check As You Type',
				'RemoveFormat'   => 'Remove Format',
				'Strike'         => 'Strike Through',
				'NumberedList'   => 'Insert/Remove Numbered List',
				'BulletedList'   => 'Insert/Remove Bulleted List',
				'Outdent'        => 'Decrease Indent',
				'Indent'         => 'Increase Indent',
				'CreateDiv'      => 'Create Div Container',
				'HorizontalRule' => 'Insert Horizontal Line',
				'About'          => 'About CKEditor',
				'MediaEmbed'     => 'Embed Media',
				'ReadMore'       => 'Read More',
				'ShowBlocks'     => 'Show Blocks',
			);

			$EE = get_instance();

			// -------------------------------------------
			//  'wygwam_tb_label_overrides' hook
			//   - Allow extensions to modify which toolbar items should be considered selects.
			//
				if ($EE->extensions->active_hook('wygwam_tb_label_overrides'))
				{
					self::$_tb_label_overrides = $EE->extensions->call('wygwam_tb_label_overrides', self::$_tb_label_overrides);
				}
			//
			// -------------------------------------------
		}

		return self::$_tb_label_overrides;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a map of common EE language folder names to CKEditor language codes.
	 *
	 * @static
	 * @return array
	 */
	public static function lang_map()
	{
		return array(
			'arabic'              => 'ar',
			'arabic-utf8'         => 'ar',
			'arabic-windows-1256' => 'ar',
			'czech'               => 'cs',
			'cesky'               => 'cs',
			'danish'              => 'da',
			'german'              => 'de',
			'deutsch'             => 'de',
			'english'             => 'en',
			'spanish'             => 'es',
			'spanish_ee201pb'     => 'es',
			'finnish'             => 'fi',
			'french'              => 'fr',
			'hungarian'           => 'hu',
			'croatian'            => 'hr',
			'italian'             => 'it',
			'japanese'            => 'ja',
			'korean'              => 'ko',
			'dutch'               => 'nl',
			'norwegian'           => 'no',
			'polish'              => 'pl',
			'brazilian'           => 'pt',
			'portuguese'          => 'pt',
			'brasileiro'          => 'pt',
			'brasileiro_160'      => 'pt',
			'russian'             => 'ru',
			'russian_utf8'        => 'ru',
			'russian_win1251'     => 'ru',
			'slovak'              => 'sk',
			'swedish'             => 'sv',
			'swedish_ee20pb'      => 'sv',
			'turkish'             => 'tr',
			'ukrainian'           => 'uk',
			'chinese'             => 'zh',
			'chinese_traditional' => 'zh',
			'chinese_simplified'  => 'zh'
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the default config settings.
	 *
	 * @static
	 * @return array
	 */
	public static function default_config_settings()
	{
		$toolbars = self::default_toolbars();

		return array(
			'toolbar'        => $toolbars['Basic'],
			'height'         => '200',
			'resize_enabled' => 'y',
			'contentsCss'    => array(),
			'parse_css'      => FALSE,
			'restrict_html'  => 'y',
			'upload_dir'     => ''
		);
	}

	/**
	 * Returns the default toolbars.
	 *
	 * @static
	 * @return array
	 */
	public static function default_toolbars()
	{
		return array(
			'Basic' => array('Bold','Italic','Underline','NumberedList','BulletedList','Link','Unlink','Anchor'),
			'Full'  => array('Source','Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord','Undo', 'Redo','Scayt','Bold', 'Italic', 'Strike','RemoveFormat','NumberedList', 'BulletedList','Outdent', 'Indent','Blockquote','Link', 'Unlink', 'Anchor','Image', 'Table', 'HorizontalRule', 'SpecialChar','ReadMore','Styles','Format','Maximize')
		);
	}

	/**
	 * Returns the default global Wygwam settings.
	 *
	 * @static
	 * @return array
	 */
	public static function default_global_settings()
	{
		return array(
			'license_key' => ''
		);
	}

	/**
	 * Returns the default Wygwam field settings.
	 *
	 * @static
	 * @return array
	 */
	public static function default_settings()
	{
		return array(
			'upload_dir' => ''
		);
	}

	/**
	 * Returns the base CKEditor config.
	 *
	 * @static
	 * @return array
	 */
	public static function base_config()
	{
		return array_merge(array(
			'skin'                          => 'wygwam3',
			'toolbarCanCollapse'            => 'n',
			'dialog_backgroundCoverOpacity' => 0,
			'entities_processNumerical'     => 'y',
			'forcePasteAsPlainText'         => 'y'
		), self::default_config_settings());
	}

	// --------------------------------------------------------------------

	/**
	 * Converts flat array of buttons into multi-dimensional
	 * array of toolgroups and their buttons.
	 *
	 * @static
	 * @param      $buttons
	 * @param bool $include_missing
	 * @return array
	 */
	public static function create_toolbar($buttons, $include_missing = FALSE)
	{
		$toolbar = array();

		// EmbedMedia => MediaEmbed
		$key = array_search('EmbedMedia', $buttons);
		if ($key !== FALSE)
		{
			$buttons[$key] = 'MediaEmbed';
		}

		// group buttons by toolgroup
		$tb_groups = self::tb_groups();
		foreach($tb_groups as $group_index => &$group)
		{
			$group_selection_index = NULL;
			$missing = array();
			foreach($group as $button_index => &$button)
			{
				// selected?
				if (($button_selection_index = array_search($button, $buttons)) !== FALSE)
				{
					if ($group_selection_index === NULL) $group_selection_index = $button_selection_index;
					if ( ! isset($toolbar[$group_selection_index])) $toolbar[$group_selection_index] = array();
					$toolbar[$group_selection_index]['b'.$button_index] = $button;
				}
				else if ($include_missing)
				{
					$missing['b'.$button_index] = '!'.$button;
				}
			}
			if ($group_selection_index !== NULL)
			{
				if ($include_missing) $toolbar[$group_selection_index] = array_merge($missing, $toolbar[$group_selection_index]);
				ksort($toolbar[$group_selection_index]);
				$toolbar[$group_selection_index] = array_values($toolbar[$group_selection_index]);
			}
		}

		// sort by keys and remove them
		ksort($toolbar);
		$r = array();
		foreach($toolbar as $toolgroup) array_push($r, $toolgroup);
		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the boolean config settings.
	 *
	 * @static
	 * @return array
	 */
	public static function config_booleans()
	{
		return array(
			'autoGrow_onStartup',
			'autoParagraph',
			'colorButton_enableMore',
			'disableNativeSpellChecker',
			'disableObjectResizing',
			'disableReadonlyStyling',
			'editingBlock',
			'entities',
			'entities_greek',
			'entities_latin',
			'entities_processNumerical',
			'fillEmptyBlocks',
			'forceEnterMode',
			'forcePasteAsPlainText',
			'forceSimpleAmpersand',
			'fullPage',
			'htmlEncodeOutput',
			'ignoreEmptyParagraph',
			'image_removeLinkByEmptyURL',
			'pasteFromWordNumberedHeadingToList',
			'pasteFromWordPromptCleanup',
			'pasteFromWordRemoveFontStyles',
			'pasteFromWordRemoveStyles',
			'readOnly',
			'resize_enabled',
			'startupFocus',
			'startupOutlineBlocks',
			'templates_replaceContent',
			'toolbarCanCollapse',
			'toolbarGroupCycling',
			'toolbarStartupExpanded'
		);
	}

	/**
	 * Returns the config settings that are lists.
	 *
	 * @static
	 * @return array
	 */
	public static function config_lists()
	{
		return array(
			'contentsCss',
			'templates_files'
		);
	}

	/**
	 * Returns the config settings that are literals.
	 *
	 * @static
	 * @return array
	 */
	public static function config_literals()
	{
		return array(
			'enterMode',
			'on',
			'stylesheetParser_skipSelectors',
			'stylesheetParser_validSelectors',
			'filebrowserBrowseFunc',
			'filebrowserLinkBrowseFunc',
			'filebrowserImageBrowseFunc',
			'filebrowserFlashBrowseFunc',
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns Wygwam's themes folder URL.
	 *
	 * @static
	 * @return string
	 */
	public static function theme_url()
	{
		if (! isset(self::$_theme_url))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : get_instance()->config->slash_item('theme_folder_url').'third_party/';
			self::$_theme_url = $theme_folder_url.'wygwam/';
		}

		return self::$_theme_url;
	}

	/**
	 * Includes a CSS file in the page head.
	 *
	 * static
	 * @param string $file
	 */
	public static function include_theme_css($file)
	{
		get_instance()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.self::theme_url().$file.'" />');
	}

	/**
	 * Includes a JS file in the page foot.
	 *
	 * @static
	 * @param string $file
	 */
	public static function include_theme_js($file)
	{
		get_instance()->cp->add_to_foot('<script type="text/javascript" src="'.self::theme_url().$file.'"></script>');
	}

	/**
	 * Insert CSS in the page head.
	 *
	 * @static
	 * @param string $css
	 */
	public static function insert_css($css)
	{
		get_instance()->cp->add_to_head('<style type="text/css">'.$css.'</style>');
	}

	/**
	 * Insert JS in the page foot.
	 *
	 * @static
	 * @param string $js
	 */
	public static function insert_js($js)
	{
		get_instance()->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	/**
	 * Includes the necessary CSS and JS files to get Wygwam fields working.
	 *
	 * @static
	 */
	public static function include_field_resources()
	{
		if (! self::$_included_field_resources)
		{
			self::include_theme_js('lib/ckeditor/ckeditor.js');
			self::include_theme_js('scripts/wygwam.js');
			self::include_theme_css('styles/wygwam.css');
			self::insert_css('.content_elements_icon_wygwam { background: url('.self::theme_url().'images/ce_icon.png); background-size: 16px; }');

			$js = 'Wygwam.themeUrl = "'.self::theme_url().'";'
			    . 'Wygwam.ee2plus = '.(version_compare(APP_VER, '2.2', '>=') ? 'true' : 'false').';';

			$filedirs = self::get_upload_preferences(1);

			if ($filedirs)
			{
				$filedir_urls = array();
				foreach ($filedirs as $filedir)
				{
					$filedir_urls[$filedir['id']] = $filedir['url'];
				}

				$js .= 'Wygwam.filedirUrls = '.self::get_json($filedir_urls).';';
			}

			self::insert_js($js);

			self::$_included_field_resources = TRUE;
		}
	}

	/**
	 * Inserts the Wygwam config JS in the page foot.
	 *
	 * @static
	 * @param array $settings The field settings
	 */
	public static function insert_config_js($settings)
	{
		$EE = get_instance();
		$global_settings = self::get_global_settings();

		// starting point
		$config = self::base_config();

		// -------------------------------------------
		//  Editor Config
		// -------------------------------------------

		if ($EE->db->table_exists('wygwam_configs')
			&& is_numeric($settings['config'])
			&& ($query = $EE->db->select('settings')->get_where('wygwam_configs', array('config_id' => $settings['config'])))
			&& $query->num_rows()
		)
		{
			// merge custom settings into config
			$custom_settings = unserialize(base64_decode($query->row('settings')));
			$config = array_merge($config, $custom_settings);
		}
		else
		{
			$settings['config'] = 'default';
		}

		// skip if already included
		if (isset(self::$_included_configs) && in_array($settings['config'], self::$_included_configs))
		{
			return;
		}

		// language
		if (! isset($config['language']) || ! $config['language'])
		{
			$lang_map = self::lang_map();
			$language = $EE->session->userdata('language');
			$config['language'] = isset($lang_map[$language]) ? $lang_map[$language] : 'en';
		}

		// toolbar
		if (is_array($config['toolbar']))
		{
			$config['toolbar'] = self::create_toolbar($config['toolbar']);
		}

		// css
		if (! $config['contentsCss'])
		{
			unset($config['contentsCss']);
		}

		// set the autoGrow_minHeight to the height
		$config['autoGrow_minHeight'] = $config['height'];

		// allowedContent
		if ($config['restrict_html'] == 'n')
		{
			$config['allowedContent'] = true;
		}

		unset($config['restrict_html']);

		// extraPlugins
		if (!empty($config['extraPlugins']))
		{
			$extraPlugins = array_map('trim', explode(',', $config['extraPlugins']));
		}
		else
		{
			$extraPlugins = array();
		}

		$extraPlugins[] = 'wygwam';
		$extraPlugins[] = 'readmore';

		if ($config['parse_css'])
		{
			if (!in_array('stylesheetparser', $extraPlugins))
			{
				$extraPlugins[] = 'stylesheetparser';
			}

			unset($config['parse_css']);
		}

		$config['extraPlugins'] = implode(',', $extraPlugins);

		// -------------------------------------------
		//  File Browser Config
		// -------------------------------------------

		$user_group = $EE->session->userdata('group_id');
		$upload_dir = isset($config['upload_dir']) ? $config['upload_dir'] : NULL;
		$upload_prefs = self::get_upload_preferences($user_group, $upload_dir);

		$file_browser = isset($global_settings['file_browser']) ? $global_settings['file_browser'] : 'ee';

		// no EE file browser for SafeCracker
		if (REQ == 'PAGE' && $file_browser == 'ee') $file_browser = 'ckfinder';

		switch ($file_browser)
		{
			case 'ckfinder':

				if (! $upload_prefs) break;

					// CKFinder can only pull files from a single upload directory, so make sure it's set
				if (! $upload_dir) break;

				if (! isset($_SESSION)) @session_start();
				if (! isset($_SESSION['wygwam_'.$config['upload_dir']])) $_SESSION['wygwam_'.$config['upload_dir']] = array();
				$sess =& $_SESSION['wygwam_'.$config['upload_dir']];

				// add the FCPATH if this is a relative path
				if (! preg_match('/^(\/|\\\|[a-zA-Z]+:)/', $upload_prefs['server_path']))
				{
					$upload_prefs['server_path'] = FCPATH . $upload_prefs['server_path'];
				}

				$sess['p'] = $upload_prefs['server_path'];
				$sess['u'] = $upload_prefs['url'];
				$sess['t'] = $upload_prefs['allowed_types'];
				$sess['s'] = $upload_prefs['max_size'];
				$sess['w'] = $upload_prefs['max_width'];
				$sess['h'] = $upload_prefs['max_height'];

				$config['filebrowserImageBrowseUrl'] = self::theme_url().'lib/ckfinder/ckfinder.html?Type=Images&id='.$config['upload_dir'];
				$config['filebrowserImageUploadUrl'] = self::theme_url().'lib/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&id='.$config['upload_dir'];

				if ($upload_prefs['allowed_types'] == 'all')
				{
					$config['filebrowserBrowseUrl'] = self::theme_url().'lib/ckfinder/ckfinder.html?id='.$config['upload_dir'].'&type=Files';
					$config['filebrowserUploadUrl'] = self::theme_url().'lib/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&id='.$config['upload_dir'];
					$config['filebrowserFlashBrowseUrl'] = self::theme_url().'lib/ckfinder/ckfinder.html?Type=Flash&id='.$config['upload_dir'];
					$config['filebrowserFlashUploadUrl'] = self::theme_url().'lib/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash&id='.$config['upload_dir'];
				}

				break;

			case 'assets':

				// make sure Assets is actually installed
				// (otherwise, just use the EE File Manager)
				if (self::is_assets_installed())
				{
					// include sheet resources
					if (! class_exists('Assets_helper'))
					{
						require PATH_THIRD.'assets/helper.php';
					}

					$assets_helper = new Assets_helper;
					$assets_helper->include_sheet_resources();

					// if no upload directory was set, just default to "all"
					if (! $upload_dir) $upload_dir = '"all"';

					// If this has a source type passed in as well, wrap it in quotes.
					if (strpos($upload_dir, ":"))
					{
						$upload_dir = '"'.$upload_dir.'"';
					}
					$config['filebrowserBrowseFunc']      = 'function(params) { Wygwam.loadAssetsSheet(params, '.$upload_dir.', "any"); }';
					$config['filebrowserImageBrowseFunc'] = 'function(params) { Wygwam.loadAssetsSheet(params, '.$upload_dir.', "image"); }';
					$config['filebrowserFlashBrowseFunc'] = 'function(params) { Wygwam.loadAssetsSheet(params, '.$upload_dir.', "flash"); }';

					break;
				}

			default:

				if (! $upload_prefs) break;

				// load the file browser
				$EE->load->library('file_field');
				$EE->file_field->browser();

				// if no upload directory was set, just default to "all"
				if (! $upload_dir) $upload_dir = '"all"';

				$config['filebrowserBrowseFunc']      = 'function(params) { Wygwam.loadEEFileBrowser(params, '.$upload_dir.', "any"); }';
				$config['filebrowserImageBrowseFunc'] = 'function(params) { Wygwam.loadEEFileBrowser(params, '.$upload_dir.', "image"); }';

		}

		// add any site page data to wygwam config
		if ($pages = self::get_all_page_data())
		{
			$EE->lang->loadfile('wygwam');
			$site_page_string = lang('wygwam_site_page');

			foreach ($pages as $page)
			{
				$config['link_types'][$site_page_string][] = array(
			            'label' => $page[2],
			            'url'   => $page[4]
				);
			}
		}

		// -------------------------------------------
		//  'wygwam_config' hook
		//   - Override any of the config settings
		//
			if ($EE->extensions->active_hook('wygwam_config'))
			{
				$config = $EE->extensions->call('wygwam_config', $config, $settings);
			}
		//
		// -------------------------------------------

		unset($config['upload_dir']);

		// -------------------------------------------
		//  JSONify Config and Return
		// -------------------------------------------

		$config_literals = self::config_literals();
		$config_booleans = self::config_booleans();

		$js = '';

		foreach ($config as $setting => $value)
		{
			if (! in_array($setting, $config_literals))
			{
				if (in_array($setting, $config_booleans))
				{
					$value = ($value == 'y' ? TRUE : FALSE);
				}

				$value = self::get_json($value);

				// Firefox gets an "Unterminated string literal" error if this line gets too long,
				// so let's put each new value on its own line
				if ($setting == 'link_types')
				{
					$value = str_replace('","', "\",\n\t\t\t\"", $value);
				}
			}

			$js .= ($js ? ','.NL : '')
			     . "\t\t".'"'.$setting.'": '.$value;
		}

		// Strip out any non-space whitespace chars
		$js = str_replace(array(chr(10), chr(11), chr(12), chr(13)), ' ', $js);

		self::insert_js(NL."\t".'Wygwam.configs["'.$settings['config'].'"] = {'.NL.$js.NL."\t".'};'.NL);
		self::$_included_configs[] = $settings['config'];
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the system upload preferences in a unified interface, regardless of which EE2 version it is.
	 *
	 * @static
	 * @param  int $group_id Member group ID specified when returning allowed upload directories only for that member group
	 * @param  int $id       Specific ID of upload destination to return
	 * @return array         Result array of DB object, possibly merged with custom file upload settings (if on EE 2.4+)
	 */
	public static function get_upload_preferences($group_id = NULL, $id = NULL)
	{
		$EE = get_instance();

		if (version_compare(APP_VER, '2.4', '>='))
		{
			$EE->load->model('file_upload_preferences_model');
			return $EE->file_upload_preferences_model->get_file_upload_preferences($group_id, $id);
		}

		if (version_compare(APP_VER, '2.1.5', '>='))
		{
			$EE->load->model('file_upload_preferences_model');
			$result = $EE->file_upload_preferences_model->get_upload_preferences($group_id, $id);
		}
		else
		{
			$EE->load->model('tools_model');
			$result = $EE->tools_model->get_upload_preferences($group_id, $id);
		}

		// If an $id was passed, just return that directory's preferences
		if ( ! empty($id))
		{
			return $result->row_array();
		}

		// Use upload destination ID as key for row for easy traversing
		$return_array = array();
		foreach ($result->result_array() as $row)
		{
			$return_array[$row['id']] = $row;
		}

		return $return_array;
	}

	// --------------------------------------------------------------------

	/**
	 * Gets all the possible {filedir_X} tags and their replacement URLs.
	 *
	 * @static
	 * @access private
	 * @param bool $sort
	 * @return array
	 */
	private static function _get_file_tags($sort = FALSE)
	{
		if (! isset(self::$_file_tags))
		{
			$tags = array();
			$urls = array();

			if ($file_paths = get_instance()->functions->fetch_file_paths())
			{
				if ($sort)
				{
					uasort($file_paths, array('Wygwam_helper', '_cmp_file_urls'));
				}

				foreach ($file_paths as $id => $url)
				{
					// ignore "/" URLs
					if ($url == '/') continue;

					$tags[] = LD.'filedir_'.$id.RD;
					$urls[] = $url;
				}
			}

			self::$_file_tags = array($tags, $urls);
		}

		return self::$_file_tags;
	}

	/**
	 * Compares two file URLs.
	 *
	 * @static
	 * @access private
	 */
	public static function _cmp_file_urls($a, $b)
	{
		return -(strcmp(strlen($a), strlen($b)));
	}

	/**
	 * Replaces {filedir_X} tags with their URLs.
	 *
	 * @static
	 * @param string &$data
	 */
	public static function replace_file_tags(&$data)
	{
		$tags = self::_get_file_tags();
		$data = str_replace($tags[0], $tags[1], $data);
	}

	/**
	 * Replaces File URLs with {filedir_X} tags
	 *
	 * @static
	 * @param string &$data
	 */
	public static function replace_file_urls(&$data)
	{
		$tags = self::_get_file_tags();
		$data = str_replace($tags[1], $tags[0], $data);
	}

	/**
	 * Replaces Asset URLs with {assets_X} tags.
	 *
	 * @static
	 * @param $data
	 * @param $asset_ids
	 * @param $asset_urls
	 */
	public static function replace_asset_urls(&$data, $asset_ids, $asset_urls)
	{
		foreach ($asset_urls as $key => $asset_url)
		{
			$replace = '{assets_'.$asset_ids[$key].':'.$asset_url.'}';
			$search = str_replace('/', '\/', preg_quote(rtrim($asset_url, '/')));
			$search = '/(?!\")('.$search.')\/?(?=\")/uU';

			$data = preg_replace($search, $replace, $data);
		}
	}

	/**
	 * Replaces Asset URLs with {assets_X} tags.
	 *
	 * @static
	 * @param $data
	 * @return array
	 */
	public static function replace_asset_tags(&$data)
	{
		preg_match_all("/\\{assets_(\\d*):((.*)(\\}))/uU", $data, $matches);

		if ($matches && !empty($matches[0]))
		{
			$asset_ids = $matches[1];
			$asset_urls = $matches[3];

			if (self::is_assets_installed())
			{
				$EE = get_instance();
				$EE->load->add_package_path(PATH_THIRD.'assets/');
				$EE->load->library('assets_lib');
				$files = $EE->assets_lib->get_file_by_id($asset_ids);
			}

			for ($counter = 0; $counter < count($matches[1]); $counter++)
			{
				$file_id = $matches[1][$counter];

				// The file has been deleted or Assets is not installed.
				if ((isset($files[$file_id]) && $files[$file_id] === false) || !isset($files[$file_id]))
				{
					$replace = $matches[3][$counter];
				}
				else
				{
					$replace = $files[$file_id]->url();
				}

				$data = str_replace('{assets_'.$file_id.':'.$matches[3][$counter].'}', $replace, $data);
			}

			return array('ids' => $asset_ids, 'urls' => $asset_urls);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Gets all the possible {page_X} tags and their replacement URLs
	 *
	 * @static
	 * @access private
	 * @param bool $sort
	 * @return array
	 */
	private static function _get_page_tags($sort = FALSE)
	{
		if (! isset(self::$_page_tags))
		{
			$tags = array();
			$urls = array();

			$page_data = self::get_all_page_data(FALSE);

			if ($sort)
			{
				usort($page_data, array('Wygwam_helper', '_cmp_page_urls'));
			}

			foreach ($page_data as $page)
			{
				$tags[] = LD.'page_'.$page[0].RD;
				$urls[] = $page[4];
			}

			self::$_page_tags = array($tags, $urls);
		}

		return self::$_page_tags;
	}

	/**
	 * Compare Page URLs
	 *
	 * @static
	 * @access private
	 */
	private function _cmp_page_urls($a, $b)
	{
		return -(strcmp(strlen($a[4]), strlen($b[4])));
	}

	/**
	 * Replaces {page_X} tags with the page URLs.
	 *
	 * @static
	 * @param string &$data
	 */
	public static function replace_page_tags(&$data)
	{
		if (strpos($data, LD.'page_') !== FALSE)
		{
			$tags = self::_get_page_tags();
			foreach ($tags[0] as $key => $page_tag)
			{
				$pattern = '/(?!&quot;|\")('.preg_quote($page_tag).')(&quot;|\"|\/)?/u';
				preg_match_all($pattern, $data, $matches);

				if ($matches && count($matches[0]) > 0)
				{
					// $matches[2] should either be &quot;, ", / or empty
					foreach ($matches[2] as $innerKey => $match)
					{
						$search = '/('.preg_quote($matches[1][$innerKey]).')/uU';
						$replace = $tags[1][$key];

						// If there is not a trailing quote or slash, we're going to add one.
						if (empty($match))
						{
							$replace .= '/';
						}

						$data = preg_replace($search, $replace, $data);
					}
				}
			}
		}
	}

	/**
	 * Replace page URLs with {page_X} tags.
	 *
	 * @static
	 * @param string &$data
	 */
	public static function replace_page_urls(&$data)
	{
		$tags = self::_get_page_tags(TRUE);

		foreach ($tags[1] as $key => $page_url)
		{
			$page_url = str_replace('/', '\/', preg_quote(rtrim($page_url, '/')));
			$search = '/(?!\")('.$page_url.')\/?(?=\")/uU';
			$data = preg_replace($search, $tags[0][$key], $data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Returns whether the Pages module is installed.
	 *
	 * @static
	 * @access private
	 * @return bool
	 */
	private static function _is_pages_mod_installed()
	{
		if (! isset(self::$_pages_module_installed))
		{
			$query = get_instance()->db->get_where('modules', 'module_name = "Pages"');
			self::$_pages_module_installed = $query->num_rows() ? TRUE : FALSE;
		}

		return self::$_pages_module_installed;
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the current site's pages.
	 *
	 * @static
	 * @access private
	 * @return array
	 */
	private static function _get_site_pages()
	{
		$EE = get_instance();
		$site_id = $EE->config->item('site_id');

		// Is this entry from a different site?
		$different_site = (self::$entry_site_id && $site_id != self::$entry_site_id);
		$entry_site_id = $different_site ? self::$entry_site_id : $site_id;

		if (! isset(self::$_site_pages[$entry_site_id]))
		{
			// Temporarily swap the site config over to the entry's site
			if ($different_site)
			{
				$EE->config->site_prefs('', $entry_site_id);
			}

			$pages = $EE->config->item('site_pages');

			if (is_array($pages) && !empty($pages[$entry_site_id]['uris']))
			{
				// grab a copy of this site's pages
				$site_pages = array_merge($pages[$entry_site_id]);

				// sort by uris
				natcasesort($site_pages['uris']);

				self::$_site_pages[$entry_site_id] = $site_pages;
			}
			else
			{
				self::$_site_pages[$entry_site_id] = array();
			}

			// Return the config to the actual site
			if ($different_site)
			{
				$EE->config->site_prefs('', $site_id);
			}
		}

		return self::$_site_pages[$entry_site_id];
	}

	/**
	 * Gets the Pages module data.
	 *
	 * @static
	 * @access private
	 * @return array
	 */
	private static function _get_pages_mod_data()
	{
		if (! isset(self::$_page_data))
		{
			self::$_page_data = array();

			if (($pages = self::_get_site_pages()) && ($page_ids = array_filter(array_keys($pages['uris']))))
			{
				$EE = get_instance();

				$query = $EE->db->query('SELECT entry_id, channel_id, title, url_title, status
				                         FROM exp_channel_titles
				                         WHERE entry_id IN ('.implode(',', $page_ids).')
				                         ORDER BY entry_id DESC');

				// index entries by entry_id
				$entry_data = array();
				foreach ($query->result_array() as $entry)
				{
					$entry_data[$entry['entry_id']] = $entry;
				}

				foreach ($pages['uris'] as $entry_id => $uri)
				{
					if (! isset($entry_data[$entry_id])) continue;
					$entry = $entry_data[$entry_id];

					$url = $EE->functions->create_page_url($pages['url'], $uri);
					if (!$url || $url == '/') continue;

					self::$_page_data[] = array(
						$entry_id,
						$entry['channel_id'],
						$entry['title'],
						'0',
						$url
					);
				}
			}

			// sort by entry title
			if(count(self::$_page_data) > 0)
			{
				self::$_page_data = self::_subval_sort(self::$_page_data, 2);
			}
		}

		return self::$_page_data;
	}

	/**
	 * Sorts a multidimensional array on an internal array's key.
	 *
	 * @static
	 * @access private
	 * @param array $initial_array
	 * @param string $sub_key
	 * @return array
	 */
	private static function _subval_sort($initial_array, $sub_key)
	{
		$sorted_array = array();

		foreach ($initial_array as $key => $value)
		{
			$temp_array[$key] = strtolower($value[$sub_key]);
		}

		asort($temp_array);

		foreach ($temp_array as $key => $value)
		{
			$sorted_array[] = $initial_array[$key];
		}

		return $sorted_array;
	}

	// --------------------------------------------------------------------

	/**
	 * Gets all site page data from the pages module.
	 *
	 * @static
	 * @param bool $install_check
	 * @return array
	 */
	public static function get_all_page_data($install_check = TRUE)
	{
		$page_data = array();

		if ($install_check)
		{
			if (self::_is_pages_mod_installed())
			{
				$page_data = self::_get_pages_mod_data();
			}
		}
		else
		{
			$page_data = self::_get_pages_mod_data();
		}

		return $page_data;
	}

	/**
	 * Returns whether Assets is installed or not.
	 *
	 * @return bool
	 */
	public static function is_assets_installed()
	{
		return array_key_exists('assets', get_instance()->addons->get_installed());
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
			return get_instance()->javascript->generate_json($data, TRUE);
		}
		else
		{
			return json_encode($data);
		}
	}
}
