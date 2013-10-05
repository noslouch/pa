<?php if (! defined('BASEPATH')) exit('Invalid file request');


require_once PATH_THIRD.'wygwam/helper.php';

/**
 * Wygwam Module CP Class for EE2
 *
 * @package   Wygwam
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Wygwam_mcp {

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		if (REQ == 'CP')
		{
			$this->base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=wygwam';

			// Set the right nav
			$this->EE->cp->set_right_nav(array(
				'wygwam_configs'  => BASE.AMP.$this->base.AMP.'method=index',
				'wygwam_settings' => BASE.AMP.$this->base.AMP.'method=settings'
			));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set Page Title
	 */
	private function _set_page_title($line = 'wygwam_module_name')
	{
		if ($line != 'wygwam_module_name')
		{
			$this->EE->cp->set_breadcrumb(BASE.AMP.$this->base, $this->EE->lang->line('wygwam_module_name'));
		}

		$this->EE->view->cp_page_title = $this->EE->lang->line($line);
	}

	// --------------------------------------------------------------------

	/**
	 * Configs
	 */
	function index()
	{
		$this->EE->load->library('table');

		$this->_set_page_title(lang('wygwam_configs'));

		$vars['base'] = $this->base;

		// configs
		$this->EE->db->select('config_id, config_name');
		$this->EE->db->order_by('config_name');
		$query = $this->EE->db->get('wygwam_configs');
		$vars['configs'] = $query->result_array();

		return $this->EE->load->view('configs', $vars, TRUE);
	}

	/**
	 * Edit Config
	 */
	function config_edit()
	{
		$default_config_settings = Wygwam_helper::default_config_settings();

		if (($config_id = $this->EE->input->get('config_id'))
			&& ($query = $this->EE->db->get_where('wygwam_configs', array('config_id' => $config_id)))
			&& $query->num_rows()
		)
		{
			$config = $query->row_array();
			$config['settings'] = unserialize(base64_decode($config['settings']));
			$config['settings'] = array_merge($default_config_settings, $config['settings']);

			// duplicate?
			if ($this->EE->input->get('clone') == 'y')
			{
				$config['config_id'] = '';
				$config['config_name'] .= ' '.lang('wygwam_clone');
				$this->_set_page_title(lang('wygwam_create_config'));
			}
			else
			{
				$this->_set_page_title(lang('wygwam_edit_config').' - '.$config['config_name']);
			}
		}
		else
		{
			$config = array(
				'config_id' => '',
				'config_name' => '',
				'settings' => $default_config_settings
			);

			$this->_set_page_title(lang('wygwam_create_config'));
		}

		$vars['config'] = $config;
		$vars['base'] = $this->base;

		$this->EE->load->library('table');

		// -------------------------------------------
		//  Upload Directory
		// -------------------------------------------

		$wygwam_settings = Wygwam_helper::get_global_settings();
		$msm = ($this->EE->config->item('multiple_sites_enabled') == 'y');

		// If we're using Assets and it is installed, let's show Assets sources instead of just EE filedirs.
		if (isset($wygwam_settings['file_browser']) && $wygwam_settings['file_browser'] == 'assets' && Wygwam_helper::is_assets_installed())
		{
			// Initialize the Assets lib
			require_once PATH_THIRD.'assets/helper.php';
			$assets_helper = new Assets_helper();

			if ($msm)
			{
				$query = $this->EE->db->query("SELECT site_id, site_label FROM exp_sites")->result();
				foreach ($query as $row)
				{
					$site_map[$row->site_id] = $row->site_label;
				}
			}

			$upload_dirs = array('' => '--');
			$all_sources = ee()->assets_lib->get_all_sources();
			foreach ($all_sources as $source)
			{
				$upload_dirs[$source->type.':'.$source->id] = ($msm && $source->type == 'ee' ? $site_map[$source->site_id] . ' - ' : '') . $source->name;
			}
		}
		else
		{
			$dir_query = $this->EE->db->query('SELECT u.id, u.name, s.site_label
											   FROM exp_upload_prefs u, exp_sites s
											   WHERE u.site_id = s.site_id
											   ORDER BY site_label, name');

			if ($dir_query->num_rows())
			{

				$upload_dirs = array('' => '--');
				foreach($dir_query->result_array() as $row)
				{
					$upload_dirs[$row['id']] = ($msm ? $row['site_label'].' - ' : '') . $row['name'];
				}
			}
		}

		if (!empty($upload_dirs))
		{
			$vars['upload_dir'] = form_dropdown('settings[upload_dir]', $upload_dirs, $config['settings']['upload_dir'], 'id="upload_dir"');
		}
		else
		{
			$this->EE->lang->loadfile('admin_content');
			$vars['upload_dir'] = lang('no_upload_prefs');
		}

		// -------------------------------------------
		//  Advanced Settings
		// -------------------------------------------

		// which settings have we already shown?
		$skip = array_keys($default_config_settings);

		// get settings that should be treated as lists
		$config_lists = Wygwam_helper::config_lists();

		// sort settings by key
		ksort($config['settings']);

		$js = '';

		foreach($config['settings'] as $setting => $value)
		{
			// skip?
			if (in_array($setting, $skip)) continue;

			// format_tags?
			if ($setting == 'format_tags')
			{
				$value = explode(';', $value);
			}

			// list?
			if (in_array($setting, $config_lists))
			{
				$value = implode("\n", $value);
			}

			$json = Wygwam_helper::get_json($value);
			$js .= 'new wygwam_addSettingRow("'.$setting.'", '.$json.');' . NL;
		}

		// Resources
		Wygwam_helper::include_theme_css('lib/ckeditor/skins/wygwam3/editor.css');
		Wygwam_helper::include_theme_css('styles/config_edit.css');
		$this->EE->cp->add_js_script(array('ui' => 'draggable'));
		Wygwam_helper::include_theme_js('lib/ckeditor/ckeditor.js');
		Wygwam_helper::include_theme_js('scripts/config_edit_toolbar.js');
		Wygwam_helper::include_theme_js('scripts/config_edit_advanced.js');
		Wygwam_helper::insert_js('jQuery(document).ready(function(){' . NL . $js . '});');
		Wygwam_helper::insert_js('jQuery("#restrict_html").ptSwitch();');

		return $this->EE->load->view('config_edit', $vars, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Config
	 */
	function config_save()
	{
		// -------------------------------------------
		//  Advanced Settings
		// -------------------------------------------

		$settings = $this->EE->input->post('settings');

		// empty toolbar
		if ($settings['toolbar'] === 'n')
		{
			$settings['toolbar'] = array();
		}

		// format_tags
		if (isset($settings['format_tags']))
		{
			$settings['format_tags'] = implode(';', $settings['format_tags']);
		}

		// lists
		foreach(Wygwam_helper::config_lists() as $list)
		{
			if (isset($settings[$list]))
			{
				$settings[$list] = array_filter(preg_split('/[\r\n]+/', $settings[$list]));
			}
		}

		// -------------------------------------------
		//  Save and redirect to Index
		// -------------------------------------------

		$config_id = $this->EE->input->post('config_id');

		$config_name = $this->EE->input->post('config_name');
		if (! $config_name) $config_name = 'Untitled';

		$data = array(
			'config_name' => $config_name,
			'settings' => base64_encode(serialize($settings))
		);

		if ($config_id)
		{
			$this->EE->db->where('config_id', $config_id);
			$this->EE->db->update('wygwam_configs', $data);
		}
		else
		{
			$this->EE->db->insert('wygwam_configs', $data);
		}

		// redirect to Index
		$this->EE->session->set_flashdata('message_success', lang('wygwam_config_saved'));
		$this->EE->functions->redirect(BASE.AMP.$this->base.AMP.'method=index');
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Config Confirmation
	 */
	function config_delete_confirm()
	{
		$config_id = $this->EE->input->get('config_id');

		$this->EE->db->select('config_name');
		$query = $this->EE->db->get_where('wygwam_configs', array('config_id' => $config_id));

		$this->_set_page_title(lang('wygwam_delete_config').' - '.$query->row('config_name'));

		$vars['base'] = $this->base;
		$vars['config_id'] = $config_id;
		$vars['config_name'] = $query->row('config_name');

		return $this->EE->load->view('config_delete_confirm', $vars, TRUE);
	}

	/**
	 * Delete Config
	 */
	function config_delete()
	{
		$config_id = $this->EE->input->post('config_id');

		$this->EE->db->delete('wygwam_configs', array('config_id' => $config_id));

		// redirect to Index
		$this->EE->session->set_flashdata('message_success', lang('wygwam_config_deleted'));
		$this->EE->functions->redirect(BASE.AMP.$this->base.AMP.'method=index');
	}

	// --------------------------------------------------------------------

	/**
	 * Settings
	 */
	function settings()
	{
		$this->EE->load->library('table');

		$this->_set_page_title();

		$vars['base'] = $this->base;

		// add the global settings to the vars
		$vars = array_merge($vars, Wygwam_helper::get_global_settings());

		return $this->EE->load->view('index', $vars, TRUE);
	}

	/**
	 * Save Settings
	 */
	function save_settings()
	{
		$settings = array(
			'license_key' => $this->EE->input->post('license_key'),
			'file_browser' => $this->EE->input->post('file_browser')
		);

		$data['settings'] = base64_encode(serialize($settings));

		$this->EE->db->where('name', 'wygwam');
		$this->EE->db->update('fieldtypes', $data);

		// redirect to Index
		$this->EE->session->set_flashdata('message_success', lang('global_settings_saved'));
		$this->EE->functions->redirect(BASE.AMP.$this->base.AMP.'method=settings');
	}
}
