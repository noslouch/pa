<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'wygwam/config.php';
require_once PATH_THIRD.'wygwam/helper.php';


/**
 * Wygwam Fieldtype Class
 *
 * @package   Wygwam
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Wygwam_ft extends EE_Fieldtype {

	var $info = array(
		'name'    => WYGWAM_NAME,
		'version' => WYGWAM_VER
	);

	var $has_array_data = TRUE;

	private static $convert_previous_data_types = array(
		''        => '--',
		'auto'    => 'Auto &lt;br /&gt; or XHTML',
		'textile' => 'Textile'
	);

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		if (! class_exists('FF2EE2')) require_once PATH_THIRD.'wygwam/lib/ff2ee2/ff2ee2.php';

		$converter = new FF2EE2('wygwam');
		return $converter->global_settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Update
	 */
	function update($from)
	{
		// update the module version number
		$this->EE->db->where('module_name', WYGWAM_NAME)
		             ->update('modules', array('module_version' => WYGWAM_VER));

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Global Settings
	 */
	function display_global_settings()
	{
		if ($this->EE->addons_model->module_installed('wygwam'))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=wygwam');
		}
		else
		{
			$this->EE->lang->loadfile('wygwam');
			$this->EE->session->set_flashdata('message_failure', lang('wygwam_no_module'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Field Settings
	 */
	private function _field_settings($settings, $matrix = FALSE)
	{
		// load the language file
		$this->EE->lang->loadfile('wygwam');

		$r = array();

		// -------------------------------------------
		//  Editor Configuration
		// -------------------------------------------

		if ($this->EE->db->table_exists('wygwam_configs'))
		{
			$this->EE->db->select('config_id, config_name');
			$this->EE->db->order_by('config_name');
			$query = $this->EE->db->get('wygwam_configs');

			if ($query->num_rows())
			{
				$configs = array();
				foreach($query->result_array() as $config)
				{
					$configs[$config['config_id']] = $config['config_name'];
				}

				$config = isset($settings['config']) ? $settings['config'] : '';
				$config_setting = form_dropdown('wygwam[config]', $configs, $config, 'id="wygwam_config"');
			}
			else
			{
				$config_setting = lang('wygwam_no_configs');
			}
		}
		else
		{
			$config_setting = lang('wygwam_no_module');
		}

		$r[] = array(
			lang('wygwam_editor_config', 'wygwam_config'),
			$config_setting . NBS.NBS . ' <a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=wygwam'.AMP.'method=index" target="_blank">'.lang('wygwam_edit_configs').'</a>'
		);

		// -------------------------------------------
		//  Defer
		// -------------------------------------------

		$defer = isset($settings['defer']) ? $settings['defer'] : 'n';

		$r[] = array(
			lang('wygwam_defer', 'wygwam_defer') . ($matrix ? '' : '<br/>' . lang('wygwam_defer_desc')),
			form_dropdown('wygwam[defer]', array('n'=>lang('no'), 'y'=>lang('yes')), $defer, 'id="wygwam_defer"')
		);


		return $r;
	}

	/**
	 * Display Field Settings
	 */
	function display_settings($settings)
	{
		$settings = array_merge(Wygwam_helper::default_settings(), $settings);

		$rows = $this->_field_settings($settings);

		// -------------------------------------------
		//  Field Conversion
		// -------------------------------------------

		// was this previously a different fieldtype?
		if ($settings['field_id'] && $settings['field_type'] != 'wygwam')
		{
			array_unshift($rows, array(
				lang('wygwam_convert_entries', 'wygwam_convert_entries').'<br />'.lang('wygwam_convert_entries_desc'),
				form_dropdown('wygwam[convert]',
					self::$convert_previous_data_types,
					(in_array($settings['field_fmt'], array('br', 'xhtml')) ? 'auto' : ''),
					'id="wygwam_convert_entries"'
				)
			));
		}

		// add the rows
		foreach ($rows as $row)
		{
			$this->EE->table->add_row($row[0], $row[1]);
		}
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($settings)
	{
		global $DSP;

		$settings = array_merge(Wygwam_helper::default_settings(), $settings);

		$rows = $this->_field_settings($settings, TRUE);

		// -------------------------------------------
		//  Column Conversion
		// -------------------------------------------

		// is this a new Wygwam cell?
		if (! isset($settings['config']))
		{
			array_unshift($rows, array(
				lang('wygwam_convert_rows', 'wygwam_convert_rows'),
				form_dropdown('wygwam[convert]',
					self::$convert_previous_data_types,
					'',
					'id="wygwam_convert_rows"'
				)
			));
		}

		return $rows;
	}

	/**
	 * Display Variable Settings
	 */
	function display_var_settings($settings)
	{
		Wygwam_helper::insert_js('(function($){
		                            $("#wygwam").wrap($("<div />").attr("id", "ft_wygwam"));
		                          })(jQuery);');

		return $this->_field_settings($settings);
	}

	/**
	 * Display element settings.
	 *
	 * @param $settings
	 * @return array
	 */
	function display_element_settings($settings)
	{
		if (!is_array($settings))
		{
			$settings = array();
		}

		if (!empty($settings['wygwam']))
		{
			$settings = $settings['wygwam'];
		}

		$settings = array_merge(Wygwam_helper::default_settings(), $settings);

		return $this->_field_settings($settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($settings)
	{
		$settings = array_merge($this->EE->input->post('wygwam'));

		// cross the T's
		$settings['field_fmt'] = 'none';
		$settings['field_show_fmt'] = 'n';
		$settings['field_type'] = 'wygwam';

		// -------------------------------------------
		//  Field Conversion
		// -------------------------------------------

		if (!empty($settings['convert']))
		{
			$field_id = $this->EE->input->post('field_id');
			if ($field_id)
			{
				$this->EE->db->select('entry_id, field_id_'.$field_id.' data, field_ft_'.$field_id.' format');
				$query = $this->EE->db->get_where('channel_data', 'field_id_'.$field_id.' != ""');

				if ($query->num_rows())
				{
					// prepare Typography
					$this->EE->load->library('typography');
					$this->EE->typography->initialize();

					// prepare Textile
					if ($settings['convert'] == 'textile')
					{
						if (! class_exists('Textile'))
						{
							require_once PATH_THIRD.'wygwam/lib/textile/textile.php';
						}

						$textile = new Textile();
					}

					foreach ($query->result_array() as $row)
					{
						$data = $row['data'];
						Wygwam_helper::replace_file_tags($data);

						$convert = FALSE;

						// Auto <br /> and XHTML
						switch ($row['format'])
						{
							case 'br':    $convert = TRUE; $data = $this->EE->typography->nl2br_except_pre($data); break;
							case 'xhtml': $convert = TRUE; $data = $this->EE->typography->auto_typography($data); break;
						}

						// Textile
						if ($settings['convert'] == 'textile')
						{
							$convert = TRUE;
							$data = $textile->TextileThis($data);
						}

						// Save the new field data
						if ($convert)
						{
							Wygwam_helper::replace_file_urls($data);

							$this->EE->db->query($this->EE->db->update_string('exp_channel_data',
								array(
									'field_id_'.$field_id => $data,
									'field_ft_'.$field_id => 'none'
								),
								'entry_id = '.$row['entry_id']
							));
						}
					}
				}
			}

			unset($settings['convert']);
		}

		return $settings;
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		$settings = $settings['wygwam'];

		// -------------------------------------------
		//  Field Conversion
		// -------------------------------------------

		if (!empty($settings['convert']))
		{
			if (!empty($this->col_id))
			{
				$this->EE->db->select('row_id, col_id_'.$this->col_id.' data');
				$query = $this->EE->db->get_where('matrix_data', 'col_id_'.$this->col_id.' != ""');

				if ($query->num_rows())
				{
					// prepare Typography
					$this->EE->load->library('typography');
					$this->EE->typography->initialize();

					// prepare Textile
					if ($settings['convert'] == 'textile')
					{
						if (! class_exists('Textile'))
						{
							require_once PATH_THIRD.'wygwam/lib/textile/textile.php';
						}

						$textile = new Textile();
					}

					foreach ($query->result_array() as $row)
					{
						$data = $row['data'];
						Wygwam_helper::replace_file_tags($data);

						// Auto <br /> and XHTML
						switch ($settings['convert'])
						{
							case 'auto': $data = $this->EE->typography->auto_typography($data); break;
							case 'textile': $data = $textile->TextileThis($data);
						}

						// Save the new field data
						Wygwam_helper::replace_file_urls($data);

						$this->EE->db->query($this->EE->db->update_string('exp_matrix_data',
							array(
								'col_id_'.$this->col_id => $data
							),
							'row_id = '.$row['row_id']
						));
					}
				}
			}

			unset($settings['convert']);
		}

		return $settings;
	}

	/**
	 * Save Variable Settings
	 */
	function save_var_settings()
	{
		return $this->EE->input->post('wygwam');
	}

	/**
	 * Save element settings.
	 *
	 * @param $data
	 * @return mixed
	 */
	function save_element_settings($data)
	{
		return $data['wygwam'];
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data)
	{
		Wygwam_helper::include_field_resources();
		Wygwam_helper::insert_config_js($this->settings);

		$id = str_replace(array('[', ']'), array('_', ''), $this->field_name);
		$defer = (isset($this->settings['defer']) && $this->settings['defer'] == 'y') ? 'true' : 'false';

		// Don't initialize this for Content Element templates
		if ($id != '__element_name_____index___data')
		{
			Wygwam_helper::insert_js('new Wygwam("'.$id.'", "'.$this->settings['config'].'", '.$defer.');');
		}

		// pass the data through form_prep() if this is SafeCracker
		if (REQ == 'PAGE')
		{
			$data = form_prep($data, $this->field_name);
		}

		// convert file tags to URLs
		Wygwam_helper::replace_file_tags($data);

		// convert asset tags to URLs
		$asset_info = Wygwam_helper::replace_asset_tags($data);

		// convert site page tags to URLs
		Wygwam_helper::replace_page_tags($data);

		if ($this->EE->extensions->active_hook('wygwam_before_display'))
		{
			$data = $this->EE->extensions->call('wygwam_before_display', $this, $data);
		}

		return '<div class="wygwam"><textarea id="'.$id.'" name="'.$this->field_name.'" rows="10" data-config="'.$this->settings['config'].'" data-defer="'.($this->settings['defer'] == 'y' ? 'y' : 'n').'">'.$data.'</textarea></div>'.$this->_generate_asset_inputs_string($asset_info);
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		Wygwam_helper::include_field_resources();
		Wygwam_helper::insert_config_js($this->settings);

		// get the cache
		if (! isset($this->EE->session->cache['wygwam']))
		{
			$this->EE->session->cache['wygwam'] = array();
		}
		$cache =& $this->EE->session->cache['wygwam'];

		if (! isset($cache['displayed_cols']))
		{
			Wygwam_helper::include_theme_js('scripts/matrix2.js');
			$cache['displayed_cols'] = array();
		}

		if (! isset($cache['displayed_cols'][$this->col_id]))
		{
			$defer = (isset($this->settings['defer']) && $this->settings['defer'] == 'y') ? 'true' : 'false';

			Wygwam_helper::insert_js('Wygwam.matrixColConfigs.col_id_'.$this->col_id.' = ["'.$this->settings['config'].'", '.$defer.'];');

			$cache['displayed_cols'][$this->col_id] = TRUE;
		}

		// convert file tags to URLs
		Wygwam_helper::replace_file_tags($data);

		// convert asset tags to URLs
		$asset_info = Wygwam_helper::replace_asset_tags($data);

		// convert site page tags to URLs
		Wygwam_helper::replace_page_tags($data);

		if ($this->EE->extensions->active_hook('wygwam_before_display'))
		{
			$data = $this->EE->extensions->call('wygwam_before_display', $this, $data);
		}

		return '<textarea name="'.$this->cell_name.'" rows="10">'.$data.'</textarea>'.$this->_generate_asset_inputs_string($asset_info);;
	}

	/**
	 * Display Variable Field
	 */
	function display_var_field($data)
	{
		// Low Variables doesn't mix in the fieldtype's global settings,
		// so we'll do it manually here
		$this->settings = array_merge($this->settings, Wygwam_helper::get_global_settings());

		if (version_compare(APP_VER, '2.5.0', '<'))
		{
			// it's way too complicated to get EE's file browser
			// loaded on non-Publish pages, so we'll fallback to CKFinder
			$global_settings = Wygwam_helper::get_global_settings();
			if (! isset($global_settings['file_browser']) || $global_settings['file_browser'] == 'ee')
			{
				$global_settings['file_browser'] = 'ckfinder';
				Wygwam_helper::set_global_settings($global_settings);
			}
		}

		return $this->display_field($data);
	}

	/**
	 * Display the element.
	 *
	 * @param $data
	 * @return mixed
	 */
	function display_element($data)
	{
		if (!empty($this->settings['wygwam']))
		{
			$this->settings = array_merge($this->settings, $this->settings['wygwam']);
			unset($this->settings['wygwam']);
		}

		Wygwam_helper::include_theme_js('scripts/content_elements.js');
		return $this->display_field($data, FALSE);

	}

	// --------------------------------------------------------------------

	/**
	 * Validate
	 */
	function validate($data)
	{
		// is this a required field?
		if ($this->settings['field_required'] == 'y' && ! $data)
		{
			return lang('required');
		}

		return TRUE;
	}

	/**
	 * Validate Cell
	 */
	function validate_cell($data)
	{
		// is this a required cell?
		if ($this->settings['col_required'] == 'y' && ! $data)
		{
			return lang('col_required');
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field
	 */
	function save($data)
	{
		// Trim out any whitespace/empty tags
		$data = preg_replace('/^(\s|<(\w+)>(&nbsp;|\s)*<\/\2>|<br \/>)*/', '', $data);
		$data = preg_replace('/(\s|<(\w+)>(&nbsp;|\s)*<\/\2>|<br \/>)*$/', '', $data);

		// Entitize curly braces within codeblocks
		$data = preg_replace_callback('/<code>(.*?)<\/code>/s',
			create_function('$matches',
				'return str_replace(array("{","}"), array("&#123;","&#125;"), $matches[0]);'
			),
			$data
		);

		// Remove Firebug 1.5.2+ div
		$data = preg_replace('/<div firebugversion=(.|\t|\n|\s)*<\\/div>/', '', $data);

		// Decode double quote entities (&quot;)
		//  - Eventually CKEditor will stop converting these in the first place
		//    http://dev.ckeditor.com/ticket/6645
		$data = str_replace('&quot;', '"', $data);

		$data = $this->_convert_urls_to_tags($data);

		// Preserve Read More comments
		//  - For whatever reason, SafeCracker is converting HTML comment brackets into entities
		$data = str_replace('&lt;!--read_more--&gt;', '<!--read_more-->', $data);

		if ($this->EE->extensions->active_hook('wygwam_before_save'))
		{
			$data = $this->EE->extensions->call('wygwam_before_save', $this, $data);
		}


		return $data;
	}

	/**
	 * Save Cell
	 */
	function save_cell($data)
	{
		return $this->save($data);
	}

	/**
	 * Save Variable Field
	 */
	function save_var_field($data)
	{
		return $this->save($data);
	}

	/**
	 * Process the URLs to tags.
	 *
	 * @param $data
	 * @return mixed
	 */
	function save_element($data)
	{
		return $this->save($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Pre Process
	 */
	function pre_process($data)
	{
		Wygwam_helper::$entry_site_id = (isset($this->row['entry_site_id']) ? $this->row['entry_site_id'] : null);

		// convert file tags to URLs
		Wygwam_helper::replace_file_tags($data);

		// convert asset tags to URLs
		Wygwam_helper::replace_asset_tags($data);

		// convert site page tags to URLs
		Wygwam_helper::replace_page_tags($data);

		$this->EE->load->library('typography');

		$tmp_encode_email = $this->EE->typography->encode_email;
		$this->EE->typography->encode_email = FALSE;

		$tmp_convert_curly = $this->EE->typography->convert_curly;
		$this->EE->typography->convert_curly = FALSE;

		$data = $this->EE->typography->parse_type($data, array(
			'text_format'   => 'none',
			'html_format'   => 'all',
			'auto_links'    => (isset($this->row['channel_auto_link_urls']) ? $this->row['channel_auto_link_urls'] : 'n'),
			'allow_img_url' => (isset($this->row['channel_allow_img_urls']) ? $this->row['channel_allow_img_urls'] : 'y')
		));

		$this->EE->typography->encode_email = $tmp_encode_email;
		$this->EE->typography->convert_curly = $tmp_convert_curly;

		// use normal quotes
		$data = str_replace('&quot;', '"', $data);

		return $data;
	}

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		// return images only?
		if (isset($params['images_only']) && $params['images_only'] == 'yes')
		{
			$data = $this->_parse_images($data, $params, $tagdata);
		}

		// Text only?
		else if (isset($params['text_only']) && $params['text_only'] == 'yes')
		{
			// Strip out the HTML tags
			$data = preg_replace('/<[^<]+?>/', '', $data);
		}
		else
		{
			// Remove images?
			if (isset($params['remove_images']) && $params['remove_images'] == 'yes')
			{
				$data = preg_replace('/<img(.*)>/Ums', '', $data);
			}

			// strip out the {read_more} tag
			$data = str_replace('<!--read_more-->', '', $data);
		}

		if ($this->EE->extensions->active_hook('wygwam_before_replace'))
		{
			$data = $this->EE->extensions->call('wygwam_before_replace', $this, $data);
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Has Excerpt Tag
	 */
	function replace_has_excerpt($data)
	{
		return (strpos($data, '<!--read_more-->') !== FALSE) ? 'y' : '';
	}

	/**
	 * Replace Excerpt Tag
	 */
	function replace_excerpt($data, $params)
	{
		if (($read_more_tag_pos = strpos($data, '<!--read_more-->')) !== FALSE)
		{
			$data = substr($data, 0, $read_more_tag_pos);
		}

		return $this->replace_tag($data, $params);
	}

	/**
	 * Replace Extended Tag
	 */
	function replace_extended($data, $params)
	{
		if (($read_more_tag_pos = strpos($data, '<!--read_more-->')) !== FALSE)
		{
			$data = substr($data, $read_more_tag_pos + 16);
		}
		else
		{
			$data = '';
		}

		return $this->replace_tag($data, $params);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Variable Tag
	 */
	function display_var_tag($data)
	{
		return $this->replace_tag($this->pre_process($data));
	}

	/**
	 * Render the element.
	 *
	 * @param $data
	 * @param array $params
	 * @param $tagdata
	 * @return bool
	 */
	function replace_element_tag($data, $params = array(), $tagdata)
	{
		return $this->EE->functions->var_swap($tagdata, array(
			'value' => $this->replace_tag($this->pre_process($data)),
			'element_name' => $this->element_name
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Convert URLs to Wygwam Tags.
	 *
	 * @param $html
	 * @return mixed
	 */
	private function _convert_urls_to_tags($html)
	{
		$asset_ids = $this->EE->input->post('wygwam_asset_ids');
		$asset_urls = $this->EE->input->post('wygwam_asset_urls');

		// If they select any files using Assets.
		if (!empty($asset_ids) && !empty($asset_urls) && count($asset_ids) == count($asset_urls))
		{
			// Convert Asset URLs to tags
			Wygwam_helper::replace_asset_urls($html, $asset_ids, $asset_urls);
		}

		// Convert file URLs to tags
		Wygwam_helper::replace_file_urls($html);

		// Convert page URLs to tags
		Wygwam_helper::replace_page_urls($html);

		return $html;
	}

	/**
	 * @param $asset_info
	 * @return string
	 */
	private function _generate_asset_inputs_string($asset_info)
	{
		$inputs = '';
		for ($counter = 0; $counter < count($asset_info['ids']); $counter++)
		{
			$inputs .= '<input type="hidden" name="wygwam_asset_ids[]" value="'.$asset_info['ids'][$counter].'" />';
			$inputs .= '<input type="hidden" name="wygwam_asset_urls[]" value="'.$asset_info['urls'][$counter].'" />';
		}

		return $inputs;
	}

	/**
	 * Parse Images
	 */
	private function _parse_images($data, $params, $tagdata)
	{
		$images = array();

		if ($tagdata)
		{
			$p = !empty($params['var_prefix']) ? rtrim($params['var_prefix'], ':').':' : '';
		}

		// find all the image tags
		preg_match_all('/<img(.*)>/Ums', $data, $img_matches, PREG_SET_ORDER);

		foreach ($img_matches as $i => $img_match)
		{
			if ($tagdata)
			{
				$img = array();

				// find all the attributes
				preg_match_all('/\s([\w-]+)=([\'"])([^\2]*?)\2/', $img_match[1], $attr_matches, PREG_SET_ORDER);

				foreach ($attr_matches as $attr_match)
				{
					$img[$p.$attr_match[1]] = $attr_match[3];
				}

				// ignore image if it doesn't have a source
				if (empty($img[$p.'src'])) continue;

				// find all the styles
				if (! empty($img[$p.'style']))
				{
					$styles = array_filter(explode(';', trim($img[$p.'style'])));

					foreach ($styles as $style)
					{
						$style = explode(':', $style, 2);
						$img[$p.'style:'.trim($style[0])] = trim($style[1]);
					}
				}

				// use the width and height styles if they're set
				if (! empty($img[$p.'style:width']) && preg_match('/(\d+?\.?\d+)(px|%)/', $img[$p.'style:width'], $width_match))
				{
					$img[$p.'width'] = $width_match[1];
					if ($width_match[2] == '%') $img[$p.'width'] .= '%';
				}

				if (! empty($img[$p.'style:height']) && preg_match('/(\d+?\.?\d+)(px|%)/', $img[$p.'style:height'], $height_match))
				{
					$img[$p.'height'] = $height_match[1];
					if ($height_match[2] == '%') $img[$p.'height'] .= '%';
				}

				$images[] = $img;
			}
			else
			{
				$images[] = $img_match[0];
			}
		}

		// ignore if there were no valid images
		if (! $images) return;

		if ($tagdata)
		{
			// get the absolute number of files before we run the filters
			$constants[$p.'absolute_total_images'] = count($images);
		}

		// offset and limit params
		if (isset($params['offset']) || isset($params['limit']))
		{
			$offset = isset($params['offset']) ? (int) $params['offset'] : 0;
			$limit  = isset($params['limit'])  ? (int) $params['limit']  : count($images);

			$images = array_splice($images, $offset, $limit);
		}

		// ignore if there are no post-filter images
		if (! $images) return;

		if ($tagdata)
		{
			// get the filtered number of files
			$constants[$p.'total_images'] = count($images);

			// parse {total_images} and {absolute_total_images} first, since they'll never change
			$tagdata = $this->EE->TMPL->parse_variables_row($tagdata, $constants);

			// now parse all
			$r = $this->EE->TMPL->parse_variables($tagdata, $images);
		}
		else
		{
			$delimiter = isset($params['delimiter']) ? $params['delimiter'] : '<br />';
			$r = implode($delimiter, $images);
		}

		// backspace param
		if (!empty($params['backspace']))
		{
			$chop = strlen($r) - $params['backspace'];
			$r = substr($r, 0, $chop);
		}

		return $r;
	}
}

// END Wygwam_ft class
