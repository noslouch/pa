<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

if (! class_exists('Fieldpack_Fieldtype'))
{
	require PATH_THIRD.'fieldpack/fieldpack_fieldtype.php';
}

/**
 * P&T List Fieldtype Class for EE2
 *
 * @package   P&T List
 * @author    Pixel & Tonic Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc
 */
class Fieldpack_list_ft extends Fieldpack_Fieldtype {

	var $info = array(
		'name'    => 'Field Pack - List',
		'version' => FIELDPACK_VER
	);

	// enable tag pairs
	var $has_array_data = TRUE;

	/**
	 * Fieldtype Constructor
	 */
	function __construct()
	{
		parent::__construct();

		/** ----------------------------------------
		/**  Prepare Cache
		/** ----------------------------------------*/

		if (! isset($this->EE->session->cache['fieldpack_list']))
		{
			$this->EE->session->cache['fieldpack_list'] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache['fieldpack_list'];
	}

	/**
	 * Install the list field type.
	 *
	 * @return array|void
	 */
	public function install()
	{
		$this->helper->convert_types('pt_list', 'fieldpack_list');
		$this->helper->uninstall_fieldtype('pt_list');
		$this->helper->convert_Low_variables('pt_list', 'fieldpack_list');
		$this->helper->disable_extension();
	}

	// --------------------------------------------------------------------

	/**
	 * Display element's settings.
	 *
	 * @param $settings
	 * @return array
	 */
	function display_element_settings($settings)
	{
		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data, $cell = FALSE)
	{
		$this->_include_theme_css('styles/list.css');
		$this->_include_theme_js('scripts/list.js');

		$field_name = $cell ? $this->cell_name : $this->field_name;
		$field_id = str_replace(array('[', ']'), array('_', ''), $field_name);

		if (! $cell)
		{
			$this->_insert_js('new ptList(jQuery("#'.$field_id.'"));');
		}

		$r = '<ul id="'.$field_id.'" class="pt-list ee2">';

		if ($data)
		{
			$list = is_array($data) ? $data : explode("\n", html_entity_decode($data));

			foreach($list as $li)
			{
				$r .= '<li><span>'.$li.'</span>'
				    .   '<input type="hidden" name="'.$field_name.'[]" value="'.str_replace('"', '&quot;', $li).'" />'
				    . '</li>';
			}
		}

		$r .=   '<li class="input">'.form_input($field_name.'[]').'</li>'
		    . '</ul>';

		return $r;
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		$this->_include_theme_js('scripts/list_matrix2.js');

		return $this->display_field($data, TRUE);
	}

	/**
	 * Display LV field
	 */
	function display_var_field($data)
	{
		return $this->display_field($data);
	}

	/**
	 * Display Element
	 */
	function display_element($data)
	{
		$this->_include_ce_icon('list');
		$this->_include_theme_js('scripts/list_ce.js');
		return $this->display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Filter Data
	 */
	private function _filter_data($item)
	{
		return ($item !== '');
	}

	/**
	 * Save Field
	 */
	function save($data)
	{
		if (! is_array($data)) $data = array();

		// flatten list into one string
		$data = implode("\n", array_filter($data, array(&$this, '_filter_data')));

		// use real quotes
		$data = str_replace('&quot;', '"', $data);

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
	 * Save Var
	 */
	function save_var_field($data)
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
	 * Pre-process
	 */
	function pre_process($data)
	{
		$data = explode("\n", $data);

		foreach ($data as &$item)
		{
			$item = array('item' => $item);
		}

		return $data;
	}

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		// ignore if empty
		if (! $data) return '';

		if (! $tagdata)
		{
			return $this->replace_ul($data, $params);
		}

		// pre_process() fallback for Matrix
		if (is_string($data)) $data = $this->pre_process($data);

		$r = $this->EE->TMPL->parse_variables($tagdata, $data);

		if (isset($params['backspace']) && $params['backspace'])
		{
			$r = substr($r, 0, -$params['backspace']);
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Replace UL
	 */
	function replace_ul($data, $params = array())
	{
		return '<ul>'.NL
		     .   $this->replace_tag($data, $params, '<li>'.LD.'item'.RD.'</li>'.NL)
		     . '</ul>';
	}

	/**
	 * Replace OL
	 */
	function replace_ol($data, $params = array())
	{
		return '<ol>'.NL
		     .   $this->replace_tag($data, $params, '<li>'.LD.'item'.RD.'</li>'.NL)
		     . '</ol>';
	}

	// --------------------------------------------------------------------

	/**
	 * Display Variable tag
	 */
	function display_var_tag($data, $params, $tagdata)
	{
		return $this->replace_tag($data, $params, $tagdata);
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
