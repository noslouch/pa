<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


if (! defined('PT_FIELDPACK_VER'))
{
	// get the version from config.php
	require PATH_THIRD.'fieldpack/config.php';
}


/**
 * P&T Fieldtype Base Class
 *
 * @package   P&T Field Pack
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
abstract class Fieldpack_Fieldtype extends EE_Fieldtype {

	var $unserialize_data = FALSE;

	var $has_array_data = TRUE;

	var $class = 'fieldpack_generic';

	/**
	 * @var Fieldpack_helper
	 */
	public $helper = null;

	function __construct()
	{
		ee()->lang->loadfile('fieldpack');
		parent::__construct();
		require_once PATH_THIRD . 'fieldpack/helper.php';
		$this->helper = new Fieldpack_helper();
	}

	// --------------------------------------------------------------------

	/**
	 * Options Setting
	 */
	function options_setting($options=array(), $indent = '')
	{
		$r = '';

		if (is_array($options))
		{

			foreach ($options as $name => $label)
			{
				if ($r !== '') $r .= "\n";

				// force string
				$name = (string) $name;

				// is this just a blank option?
				if ($name === '' && $label === '') $name = $label = ' ';

				$r .= $indent . htmlentities($name, ENT_COMPAT, 'UTF-8');

				// is this an optgroup?
				if (is_array($label))
				{
					$r .= "\n".$this->options_setting($label, $indent.'    ');
				}
				else if ($name !== (string) $label)
				{
					$r .= ' : '.$label;
				}
			}
		}

		return $r;
	}

	/**
	 * Display element's settings.
	 *
	 * @param $settings
	 * @return array
	 */
	function display_element_settings($settings)
	{
		$input_name = $this->class.'_options';

		if (isset($settings[$input_name]))
		{
			$settings = $settings[$input_name];
		}

		if (!empty($settings['options']))
		{
			$settings = $settings['options'];
		}

		return array(
			array(
				$this->_get_label_html($input_name),
				$this->_get_settings_html($input_name, $this->options_setting($settings))
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Options Setting
	 */
	function save_options_setting($options = '', $total_levels = 1)
	{
		// prepare options
		$options = preg_split('/[\r\n]+/', $options);
		foreach($options as &$option)
		{
			$option_parts = preg_split('/\s:\s/', $option, 2);
			$option = array();
			$option['indent'] = preg_match('/^\s+/', $option_parts[0], $matches) ? strlen(str_replace("\t", '    ', $matches[0])) : 0;
			$option['name']   = trim($option_parts[0]);
			$option['value']  = isset($option_parts[1]) ? trim($option_parts[1]) : $option['name'];
		}

		return $this->_structure_options($options, $total_levels);
	}

	/**
	 * Save element's settings.
	 *
	 * @param $settings
	 * @return array
	 */
	function save_element_settings($settings)
	{
		$input_name = $this->class.'_options';

		if (!empty($settings[$input_name]))
		{
			return array(
				'options' => $this->save_options_setting($settings[$input_name])
			);
		}

		return $settings;
	}

	/**
	 * Structure Options
	 */
	private function _structure_options(&$options, $total_levels, $level = 1, $indent = -1)
	{
		$r = array();

		while ($options)
		{
			if ($indent == -1 || $options[0]['indent'] > $indent)
			{
				$option = array_shift($options);
				$children = (! $total_levels OR $level < $total_levels)
				              ?  $this->_structure_options($options, $total_levels, $level+1, $option['indent']+1)
				              :  FALSE;
				$r[(string)$option['name']] = $children ? $children : (string)$option['value'];
			}
			else if ($options[0]['indent'] <= $indent)
			{
				break;
			}
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate
	 */
	function validate($data)
	{
		// is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			// make sure there are selections
			if (! $data)
			{
				return lang('required');
			}
		}

		return TRUE;
	}

	/**
	 * Validate Cell
	 */
	function validate_cell($data)
	{
		// is this a required cell?
		if ($this->settings['col_required'] == 'y')
		{
			// make sure there are selections
			if (! $data)
			{
				return lang('col_required');
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

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
		return $this->replace_tag($data, $params, $tagdata);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Iterators
	 */
	function prep_iterators(&$tagdata)
	{
		// find {switch} tags
		$this->_switches = array();
		$tagdata = preg_replace_callback('/'.LD.'switch\s*=\s*([\'\"])([^\1]+)\1'.RD.'/sU', array(&$this, '_get_switch_options'), $tagdata);

		$this->_count_tag = 'count';
		$this->_iterator_count = 0;
	}

	/**
	 * Get Switch Options
	 */
	function _get_switch_options($match)
	{
		global $FNS;

		$marker = LD.'SWITCH['.$FNS->random('alpha', 8).']SWITCH'.RD;
		$this->_switches[] = array('marker' => $marker, 'options' => explode('|', $match[2]));
		return $marker;
	}

	/**
	 * Parse Iterators
	 */
	function parse_iterators(&$tagdata)
	{
		// {switch} tags
		foreach($this->_switches as $i => $switch)
		{
			$option = $this->_iterator_count % count($switch['options']);
			$tagdata = str_replace($switch['marker'], $switch['options'][$option], $tagdata);
		}

		// update the count
		$this->_iterator_count++;

		// {count} tags
		$tagdata = $this->EE->TMPL->swap_var_single($this->_count_tag, $this->_iterator_count, $tagdata);
	}

	/**
	 * Theme URL
	 */
	protected function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url.'fieldpack/';
		}

		return $this->cache['theme_url'];
	}

	/**
	 * Include Theme CSS
	 */
	protected function _include_theme_css($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'" />');
		}
	}

	/**
	 * Include Theme JS
	 */
	protected function _include_theme_js($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'"></script>');
		}
	}

	/**
	 * Includes an icon for Content Elements.
	 */
	protected function _include_ce_icon($icon)
	{
		if (!isset($this->cache['icons']) || !in_array($icon, $this->cache['icons']))
		{
			$this->EE->cp->add_to_head('<style type="text/css">.content_elements_icon_fieldpack_'.$icon.' { background: url('.$this->_theme_url().'images/icons/'.$icon.'.png); background-size: 16px; }</style>');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Insert JS
	 */
	protected function _insert_js($js)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	/**
	 * Construct the settings HTML.
	 *
	 * @param $input_name
	 * @param $options
	 * @param $html_class
	 * @return string
	 */
	protected function _get_settings_html($input_name, $options = "", $html_class = "")
	{
		return '<textarea id="'.$input_name.'" name="'.$input_name.'" rows="6" class="' . $html_class . '">' . $options . '</textarea>';
	}

	/**
	 * Construct the label HTML.
	 *
	 * @param $input_name
	 * @return string
	 */
	protected function _get_label_html($input_name)
	{
		return form_label(lang($input_name, $input_name)) . '<br/>'
			. '<i class="instruction_text">' . lang('field_list_instructions') . '</i><br /><br />' . lang('option_setting_examples');
	}

	/**
	 * Outputs a message about how no options are set yet.
	 *
	 * @access protected
	 * @return string
	 */
	protected function no_options_set()
	{
		return '<p>'.lang('no_options_set').'</p>';
	}
}


// ====================================================================


/**
 * P&T Multi Fieldtype Base Class
 *
 * @package   P&T Field Pack
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Fieldpack_Multi_Fieldtype extends Fieldpack_Fieldtype {

	var $default_field_settings = array(
		'options' => array(
			'Option 1' => 'Option 1',
			'Option 2' => 'Option 2',
			'Option 3' => 'Option 3'
		)
	);

	var $default_cell_settings = array(
		'options' => array(
			'Opt 1' => 'Opt 1',
			'Opt 2' => 'Opt 2'
		)
	);

	var $default_tag_params = array(
		'sort'      => '',
		'backspace' => '0'
	);

	var $total_option_levels = 1;

	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		// load the language file
		$this->EE->lang->loadfile('fieldpack');

		$input_name = $this->class.'_options';
		$options = isset($data['options']) ? $data['options'] : array();

		$this->EE->table->add_row(
			lang($this->class.'_options', $input_name) . '<br />'
				. lang('field_list_instructions') . '<br /><br />'
				. lang('option_setting_examples'),

			'<textarea id="'.$input_name.'" name="'.$input_name.'" rows="6">'.$this->options_setting($options).'</textarea>');
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		// load the language file
		$this->EE->lang->loadfile('fieldpack');

		$options = isset($data['options']) ? $data['options'] : array();

		return array(
			array(
				lang($this->class.'_options'),
				'<textarea class="matrix-textarea" name="options" rows="4">'.$this->options_setting($options).'</textarea>'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($data)
	{
		$post = $this->EE->input->post($this->class.'_options');

		// replace quotes
		$post = str_replace('"', '&quot;', $post);

		return array(
			'options' => $this->save_options_setting($post, $this->total_option_levels)
		);
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		// replace quotes
		$settings['options'] = str_replace('"', '&quot;', $settings['options']);

		$settings['options'] = $this->save_options_setting($settings['options'], $this->total_option_levels);
		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Field Data
	 *
	 * Ensures $data is an array.
	 */
	function prep_field_data(&$data)
	{
		if (! is_array($data))
		{
			if ($this->unserialize_data)
			{
				if (! class_exists('FF2EE2'))
				{
					require PATH_THIRD.'fieldpack/ff2ee2/ff2ee2.php';
				}

				$data = FF2EE2::_unserialize($data);

				if (is_array($data)) return;
			}

			$data = array_filter(preg_split("/[\r\n]+/", $data));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data)
	{
		if (is_string($data)) $data = html_entity_decode($data);

		return $this->_display_field($data, $this->field_name);
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		return $this->_display_field($data, $this->cell_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 */
	function save($data)
	{
		// replace quotes
		return str_replace('"', '&quot;', $data);
	}

	/**
	 * Save Cell
	 */
	function save_cell($data)
	{
		// replace quotes
		return $this->save($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Find Options
	 */
	private function _find_option($needle, $haystack)
	{
		foreach ($haystack as $key => $value)
		{
			$r = $value;
			if ($needle == $key OR (is_array($value) AND (($r = $this->_find_option($needle, $value)) !== FALSE)))
			{
				return $r;
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (! isset($this->settings['options']) || ! $this->settings['options'])
		{
			return $data;
		}

		if (! $tagdata)
		{
			return $this->replace_ul($data, $params);
		}

		$this->prep_field_data($data);
		$r = '';

		if ($this->settings['options'] && $data)
		{
			// optional sorting
			if (isset($params['sort']) && $params['sort'])
			{
				$sort = strtolower($params['sort']);

				if ($sort == 'asc')
				{
					sort($data);
				}
				else if ($sort == 'desc')
				{
					rsort($data);
				}
			}

			// offset and limit
			if (isset($params['offset']) || isset($params['limit']))
			{
				$offset = isset($params['offset']) ? $params['offset'] : 0;
				$limit = isset($params['limit']) ? $params['limit'] : count($data);
				$data = array_splice($data, $offset, $limit);
			}

			// parse {total_selections} up front
			$tagdata = $this->EE->TMPL->swap_var_single('total_selections', (string)count($data), $tagdata);

			// prepare for {switch} and {count} tags
			$this->prep_iterators($tagdata);

			foreach($data as $option_name)
			{
				if (($option = $this->_find_option($option_name, $this->settings['options'])) !== FALSE)
				{
					// copy $tagdata
					$option_tagdata = $tagdata;

					// simple var swaps
					$option_tagdata = $this->EE->TMPL->swap_var_single('option', $option, $option_tagdata);
					$option_tagdata = $this->EE->TMPL->swap_var_single('option_name', $option_name, $option_tagdata);

					// parse {switch} and {count} tags
					$this->parse_iterators($option_tagdata);

					$r .= $option_tagdata;
				}
			}

			if (isset($params['backspace']) && $params['backspace'])
			{
				$r = substr($r, 0, -$params['backspace']);
			}
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Unordered List
	 */
	function replace_ul($data, $params = array())
	{
		return "<ul>\n"
		     .   $this->replace_tag($data, $params, "  <li>{option}</li>\n")
		     . '</ul>';
	}

	/**
	 * Ordered List
	 */
	function replace_ol($data, $params = array())
	{
		return "<ol>\n"
		     .   $this->replace_tag($data, $params, "  <li>{option}</li>\n")
		     . '</ol>';
	}

	// --------------------------------------------------------------------

	/**
	 * Is Selected?
	 */
	function replace_selected($data, $params = array())
	{
		$this->prep_field_data($data);

		return (isset($params['option']) AND in_array($params['option'], $data)) ? 1 : 0;
	}

	/**
	 * Total Selections
	 */
	function replace_total_selections($data, $params = array())
	{
		$this->prep_field_data($data);

		return $data ? (string) count($data) : '0';
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

		$variables = $this->pre_process($data);

		if (preg_match_all('/(\{values(\s.*?)?\}(.*)\{\/values\})/', $tagdata, $matches))
		{
			foreach ($matches[1] as $index => $matched_markup)
			{
				$params = array();
				if (!empty($matches[2][$index]))
				{
					$parameters = array_filter(preg_split("/\s/", $matches[2][$index]));
					foreach ($parameters as $parameter)
					{
						if (strpos($parameter, '='))
						{
							list ($key, $value) = explode("=", $parameter);
							$params[$key] = trim($value, "'" . '"');
						}
					}
				}
				$replace = $this->replace_tag($variables, $params, $matches[3][$index]);
				$tagdata = str_replace($matches[1][$index], $replace, $tagdata);
			}
		}

		return $tagdata;
	}
}
