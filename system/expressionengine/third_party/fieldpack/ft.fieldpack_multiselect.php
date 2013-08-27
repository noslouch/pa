<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


if (! class_exists('Fieldpack_Fieldtype'))
{
	require PATH_THIRD.'fieldpack/fieldpack_fieldtype.php';
}


/**
 * Field Pack - Multi-select Class
 *
 * @package   P&T Field Pack
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Fieldpack_multiselect_ft extends Fieldpack_Multi_Fieldtype {

	var $info = array(
		'name'     => 'Field Pack - Multiselect',
		'version'  => FIELDPACK_VER
	);

	var $class = 'fieldpack_multiselect';
	var $total_option_levels = 2;

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

		new FF2EE2(array('ff_multiselect', 'fieldpack_multiselect'));

		$this->helper->convert_types('pt_multiselect', 'fieldpack_multiselect');
		$this->helper->uninstall_fieldtype('pt_multiselect');
		$this->helper->disable_extension();

		return array();
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

		$r = form_hidden($field_name, 'n')
		   . form_multiselect($field_name.'[]', $this->settings['options'], $data);

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
		$this->_include_ce_icon('multiselect');
		return $this->display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field
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

	/**
	 * Validate
	 */
	function validate($data)
	{
		// is this a required field?
		if ($this->settings['field_required'] == 'y' && $data =='n' )
		{
			return $this->EE->lang->line('required');
		}

		return TRUE;
	}
}
