<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

if (! class_exists('Fieldpack_Fieldtype'))
{
	require PATH_THIRD.'fieldpack/fieldpack_fieldtype.php';
}

/**
 * P&T Pill Fieldtype Class for EE2
 *
 * @package   P&T Pill
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Fieldpack_pill_ft extends Fieldpack_Fieldtype {

	var $info = array(
		'name'    => 'Field Pack - Pill',
		'version' => FIELDPACK_VER
	);

	/**
	 * Fieldtype Constructor
	 */
	function __construct()
	{
		parent::__construct();

		/** ----------------------------------------
		/**  Prepare Cache
		/** ----------------------------------------*/

		if (! isset($this->EE->session->cache['fieldpack_pill']))
		{
			$this->EE->session->cache['fieldpack_pill'] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache['fieldpack_pill'];
	}

	/**
	 * Install the pill field type.
	 *
	 * @return array|void
	 */
	public function install()
	{
		$this->helper->convert_types('pt_pill', 'fieldpack_pill');
		$this->helper->uninstall_fieldtype('pt_pill');
		$this->helper->convert_Low_variables('pt_pill', 'fieldpack_pill');
		$this->helper->disable_extension();
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		$this->EE->table->add_row(
			lang('fieldpack_pill_options', 'fieldpack_pill_options') . '<br />'
				. lang('field_list_instructions') . '<br /><br />'
				. lang('option_setting_examples'),

			'<textarea id="pt_pill_options" name="pt_pill_options" rows="6">'.$this->_options_setting($data).'</textarea>'
		);
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		// load the language file
		$this->EE->lang->loadfile('fieldpack');

		return array(array(
			lang('fieldpack_pill_options'),
			'<textarea class="matrix-textarea" name="options" rows="4">'.$this->_options_setting($data).'</textarea>'
		));
	}

	/**
	 * Display Var Settings
	 */
	function display_var_settings($data)
	{
		// load the language file
		$this->EE->lang->loadfile('fieldpack');

		return array(array(
			lang('fieldpack_pill_options', 'fieldpack_pill_options') . '<br /><br />'
			. lang('option_setting_examples'),

			'<textarea id="pt_pill_options" name="pt_pill_options" rows="6">'.$this->_options_setting($data).'</textarea>'
		));
	}

	/**
	 * Display settings for Content Elements.
	 *
	 * @param $settings
	 * @return array
	 */
	function display_element_settings ($settings)
	{
		if (!empty($settings['pt_pill_options']))
		{
			$settings['options'] = $settings['pt_pill_options'];
		}
		return array(
			array(
				$this->_get_label_html("fieldpack_pill_options"),
				$this->_get_settings_html("pt_pill_options", $this->_options_setting($settings))
			)
		);
	}

	/**
	 * Options Setting Value
	 */
	private function _options_setting($settings)
	{
		$r = '';

		if (isset($settings['options']))
		{
			foreach($settings['options'] as $name => $label)
			{
				if ($r !== '') $r .= "\n";
				$r .= $name;
				if ($name !== $label) $r .= ' : '.$label;
				if (isset($settings['default']) && $settings['default'] == $name) $r .= ' *';
			}
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($data)
	{
		$options = $this->EE->input->post('pt_pill_options');

		return $this->_save_settings($options);
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		return $this->_save_settings($settings['options']);
	}

	/**
	 * Save Var Settings
	 */
	function save_var_settings($settings)
	{
		$options = $this->EE->input->post('pt_pill_options');

		return $this->_save_settings($options);
	}

	/**
	 * Save Element settings.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function save_element_settings($data)
	{
		$input_name = 'pt_pill_options';
		if (!empty($data[$input_name]))
		{
			$data = array(
				'options' => $this->save_options_setting($data[$input_name])
			);
		}
		return $data;
	}

	/**
	 * Save Settings
	 */
	private function _save_settings($options = '')
	{
		$r = array('options' => array());

		$options = preg_split('/[\r\n]+/', $options);
		foreach($options as &$option)
		{
			// default?
			if ($default = (substr($option, -1) == '*')) $option = substr($option, 0, -1);

			$option_parts = preg_split('/\s:\s/', $option, 2);
			$option_name  = (string) trim($option_parts[0]);
			$option_label = isset($option_parts[1]) ? (string) trim($option_parts[1]) : $option_name;

			$r['options'][$option_name] = $option_label;
			if ($default) $r['default'] = $option_name;
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data, $cell = FALSE)
	{
		if (empty($this->settings['options']))
		{
			return $this->no_options_set();
		}

		$this->_include_theme_css('styles/pill.css');
		$this->_include_theme_js('scripts/pill.js');

		$field_name = $cell ? $this->cell_name : $this->field_name;
		$field_id = str_replace(array('[', ']'), array('_', ''), $field_name);

		// Don't initialize this for Content Element templates and Matrix cells
		if (! $cell && $field_id != '__element_name_____index___data')
		{
			$this->_insert_js('new ptPill(jQuery("#'.$field_id.'"));');
		}

		// default?
		if (! $data && isset($this->settings['default'])) $data = $this->settings['default'];

		return form_dropdown($field_name, $this->settings['options'], $data, 'id="'.$field_id.'"');
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		$this->_include_theme_js('scripts/pill_matrix2.js');

		return $this->display_field($data, TRUE);
	}

	/**
	 * Display variable field
	 */
	function display_var_field($data)
	{
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
		$this->_include_ce_icon('pill');
		$this->_include_theme_js('scripts/pill_ce.js');
		return $this->display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Label tag
	 */
	function replace_label($data, $params = array(), $tagdata = FALSE)
	{
		if (isset($this->settings['options'][$data]))
		{
			return $this->settings['options'][$data];
		}
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
		$label = $data;

		// Defensively load label value
		if (isset($this->settings['options'][$data]))
		{
			$label = $this->settings['options'][$data];
		}

		$value = $data;

		$replace = array(
			'value' => $value,
			'label' => $label
		);

		return $this->EE->functions->var_swap($tagdata, $replace);
	}
}
