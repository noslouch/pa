<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

if (! class_exists('Fieldpack_Fieldtype'))
{
	require PATH_THIRD.'fieldpack/fieldpack_fieldtype.php';
}

/**
 * P&T Switch Fieldtype Class for EE2
 *
 * @package   P&T Switch
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Fieldpack_switch_ft extends Fieldpack_Fieldtype {

	var $info = array(
		'name'    => "Field Pack - Switch",
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

		if (! isset($this->EE->session->cache['fieldpack_switch']))
		{
			$this->EE->session->cache['fieldpack_switch'] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache['fieldpack_switch'];
	}

	/**
	 * Install the switch field type.
	 *
	 * @return array|void
	 */
	public function instalL()
	{
		$this->helper->convert_types('pt_switch', 'fieldpack_switch');
		$this->helper->uninstall_fieldtype('pt_switch');
		$this->helper->convert_Low_variables('pt_switch', 'fieldpack_switch');
		$this->helper->disable_extension();
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		$rows = $this->_field_settings($data);

		foreach ($rows as $row)
		{
			$this->EE->table->add_row($row[0], $row[1]);
		}
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		return $this->_field_settings($data, 'class="matrix-textarea"');
	}

	/**
	 * Display LV Settings
	 */
	function display_var_settings($data)
	{
		return $this->_field_settings($data);
	}

	/**
	 * Display element's settings.
	 *
	 * @param $settings
	 * @return array
	 */
	function display_element_settings($settings)
	{
		if (!empty($settings['pt_switch']))
		{
			$settings = $settings['pt_switch'];
		}

		$rows = $this->_field_settings($settings);

		foreach ($rows as &$row)
		{
			// Smash it all together in a glorious HTML string
			$out = '';
			foreach ($row as $field)
			{
				$out .= $field;
			}
			$row = array($out);
		}

		$this->_include_theme_css('styles/switch.css');

		return $rows;
	}

	/**
	 * Field Settings
	 */
	private function _field_settings($data, $attr = '')
	{
		// merge in default field settings
		$data = array_merge(
			array(
				'off_label' => 'NO',
				'off_val'   => '',
				'on_label'  => 'YES',
				'on_val'    => 'y',
				'default'   => 'off'
			),
			$data
		);

		return array(
			// OFF Label
			array(
				lang('fieldpack_switch_off_label', 'fieldpack_switch_off_label'),
				form_input('pt_switch[off_label]', $data['off_label'], $attr)
			),

			// OFF Value
			array(
				lang('fieldpack_switch_off_val', 'fieldpack_switch_off_val'),
				form_input('pt_switch[off_val]', $data['off_val'], $attr)
			),

			// ON Label
			array(
				lang('fieldpack_switch_on_label', 'fieldpack_switch_on_label'),
				form_input('pt_switch[on_label]', $data['on_label'], $attr)
			),

			// ON Value
			array(
				lang('fieldpack_switch_on_val', 'fieldpack_switch_on_val'),
				form_input('pt_switch[on_val]', $data['on_val'], $attr)
			),

			// Default
			array(
				lang('fieldpack_switch_default', 'fieldpack_switch_default'),
				form_dropdown('pt_switch[default]', array('off' => 'OFF', 'on' => 'ON'), $data['default'])
			),
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($settings)
	{
		$settings = $this->EE->input->post('pt_switch');

		// cross the T's
		$settings['field_fmt'] = 'none';
		$settings['field_show_fmt'] = 'n';
		$settings['field_type'] = 'fieldpack_switch';

		return $settings;
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		return $settings['pt_switch'];
	}

	/**
	 * Save LV Settings
	 */
	function save_var_settings($settings)
	{
		return $this->EE->input->post('pt_switch');
	}

	/**
	 * Save element's settings.
	 *
	 * @param $settings
	 * @return array
	 */
	function save_element_settings($settings)
	{
		$input_name = 'pt_switch';

		if (!empty($settings[$input_name]))
		{
			$settings = $settings[$input_name];
		}

		return $settings;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data, $cell = FALSE)
	{
		$this->_include_theme_css('styles/switch.css');
		$this->_include_theme_js('scripts/switch.js');

		$field_name = $cell ? $this->cell_name : $this->field_name;
		$field_id = str_replace(array('[', ']'), array('_', ''), $field_name);

		if ($cell)
		{
			$new = (! isset($this->row_id));
		}
		else
		{
			$new = (! $this->EE->input->get('entry_id') && substr($field_name, 0, 3) != 'var');

			// Don't initialize this for Content Element templates
			if ($field_id != '__element_name_____index___data')
			{
				$this->_insert_js('new ptSwitch(jQuery("#'.$field_id.'"));');
			}
		}

		// Pretend it's a new entry if $data isn't set to one of the values
		if ($data != $this->settings['off_val'] && $data != $this->settings['on_val'])
		{
			$new = TRUE;
		}

		$options = array(
			$this->settings['off_val'] => $this->settings['off_label'],
			$this->settings['on_val'] => $this->settings['on_label']
		);

		if ($new && !$data)
		{
			if (! isset($this->settings['default'])) $this->settings['default'] = 'off';
			$data = $this->settings[$this->settings['default'].'_val'];
		}

		return form_dropdown($field_name, $options, $data, 'id="'.$field_id.'"');
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		$this->_include_theme_js('scripts/switch_matrix2.js');

		return $this->display_field($data, TRUE);
	}

	/**
	 * Display Var
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
		$this->_include_ce_icon('switch');
		$this->_include_theme_js('scripts/switch_ce.js');
		return $this->display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Label
	 */
	function replace_label($data, $params = array(), $tagdata = FALSE)
	{
		if ($data == $this->settings['on_val'])
		{
			return $this->settings['on_label'];
		}
		else
		{
			return $this->settings['off_label'];
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
		$data = array(
			'value' => $data
		);
		return $this->EE->functions->var_swap($tagdata, $data);
	}
}
