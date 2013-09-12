<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


if (! class_exists('Fieldpack_Fieldtype'))
{
	require PATH_THIRD.'fieldpack/fieldpack_fieldtype.php';
}


/**
 * Field Pack - Checkboxes Class
 *
 * @package   P&T Field Pack
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Fieldpack_checkboxes_ft extends Fieldpack_Multi_Fieldtype {

	var $info = array(
		'name'     => 'Field Pack - Checkboxes',
		'version'  => FIELDPACK_VER
	);

	var $class = 'fieldpack_checkboxes';

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		if (! class_exists('FF2EE2'))
		{
			require PATH_THIRD.'fieldpack/ff2ee2/ff2ee2.php';
		}

		new FF2EE2(array('ff_checkbox_group', 'fieldpack_checkboxes'));
		new FF2EE2(array('ff_checkbox', 'fieldpack_checkboxes'), array(&$this, '_convert_checkbox_settings'));

		$this->helper->convert_types('pt_checkboxes', 'fieldpack_checkboxes');
		$this->helper->uninstall_fieldtype('pt_checkboxes');
		$this->helper->disable_extension();

		return array();
	}

	/**
	 * Convert Checkbox Settings
	 */
	function _convert_checkbox_settings($settings, $field)
	{
		return array('options' => array('y' => $settings['label']));
	}

	// --------------------------------------------------------------------

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		if (! $settings['options'])
		{
			return array('options' => array('y' => ''));
		}

		return parent::save_cell_settings($settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function _display_field($data, $field_name)
	{
		if (empty($this->settings['options']))
		{
			return $this->no_options_set();
		}

		$this->prep_field_data($data);
		$r = form_hidden($field_name, 'n');

		foreach($this->settings['options'] as $option_name => $option_label)
		{
			$selected = in_array($option_name, $data) ? 1 : 0;
			$r .= '<label>'
			    .   form_checkbox($field_name.'[]', $option_name, $selected)
			    .   NBS . $option_label
			    . '</label> ';
		}
		$r .= '<div style="clear:left"></div>';

		return $r;
	}

	/**
	 * Display the element.
	 *
	 * @param $data
	 * @return mixed
	 */
	function display_element($data)
	{
		$this->_include_ce_icon('checkboxes');
		return $this->display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 */
	function save($data)
	{
		$data = is_array($data) ? implode("\n", $data) : '';
		return parent::save($data);
	}

	/**
	 * Save Cell
	 */
	function save_cell($data)
	{
		return $this->save($data);
	}

	/**
	 * Save Element.
	 *
	 * @param $data
	 * @return mixed|string
	 */
	function save_element($data)
	{
		return $this->save($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (! isset($this->settings['options']) || ! $this->settings['options'] || count($this->settings['options']) < 2)
		{
			return $data;
		}

		return parent::replace_tag($data, $params, $tagdata);
	}

}
