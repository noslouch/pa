<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Structure Fieldtype
 *
 * @package             Structure Fieldtype for EE2
 * @author              Jack McDade (jack@jackmcdade.com)
 * @copyright           Copyright (c) 2013 Travis Schmeisser
 * @link                http://buildwithstructure.com
 */

require_once PATH_THIRD.'structure/config.php';
require_once PATH_THIRD.'structure/sql.structure.php';

class Structure_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'Structure',
		'version'	=> STRUCTURE_VERSION
	);

	var $structure;

	var $sql;

	public $EE;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Structure_ft()
	{
		EE_Fieldtype::__construct();

		$this->sql = new Sql_structure();
		$this->site_pages = $this->sql->get_site_pages();
		$this->site_id = $this->EE->config->item('site_id');
	}

	public function install()
	{
		return array(
			'structure_list_type' => 'pages'
		);
	}
	
	// --------------------------------------------------------------------


	/**
     * Normal Fieldtype Display
     */
	function display_field($data)
	{
		$channel_id = isset($this->settings['structure_list_type']) && is_numeric($this->settings['structure_list_type']) ? $this->settings['structure_list_type'] : false;
		return $this->build_dropdown($data, $this->field_name, $this->field_id, $channel_id);
	}

	
	/**
     * Matrix Cell Display
     */
	function display_cell($data)
	{
		return $this->build_dropdown($data, $this->cell_name, $this->field_id);
	}
	
	function grid_display_cell($data)
	{
		return $this->display_cell($data);
	}


	function grid_display_settings($data)
	{
		return array(
			$this->grid_settings_row( "Populate with...", $this->_get_dropdown($data) )
		);
	    
	}

	    
	/**
     * Low Variables Fieldtype Display
	 *
	 * @return int entry_id of selected URL
     */
    function display_var_field($data)
    {
		return $this->build_dropdown($data, $this->field_name);
    }


	/**
     * Low Variables Fieldtype Var Tag
	 *
	 * @return string url
     */
	function display_var_tag($var_data, $tagparams, $tagdata)
	{
		return $this->EE->functions->create_page_url($this->site_pages['url'], $this->site_pages['uris'][$var_data], false);
	}


	function display_settings($data)
	{
	    $this->EE->table->add_row('Populate selection with...', $this->_get_dropdown($data));
	}
	
	function _get_dropdown($data)
	{
	    $selected = array_get($data, 'structure_list_type', null);

	    $rows = array();
		$listing_channels = $this->sql->get_structure_channels('listing');

		$dropdown_options = array('pages' => 'Pages Tree');
		if ($listing_channels) {
			foreach ($listing_channels as $id => $channel) {
				$dropdown_options[$id] = 'Listing Channel: '. $channel['channel_title'];
			}
		}

	   return form_dropdown('structure_list_type', $dropdown_options, $selected);
	}
	
	
	public function save_settings($data)
	{
		return array(
			'structure_list_type' => $this->EE->input->post('structure_list_type')
		);
	}


	// --------------------------------------------------------------------

	/**
    * Structure Pages Select Dropdown
    *
    * @return string select HTML
    * @access private
    */
	private function build_dropdown($data, $name, $field_id = false, $channel_id = false)
	{
		$structure_data = $channel_id ? $this->sql->get_listing_channel_data($channel_id) : $this->sql->get_data();

		$exclude_status_list[] = "closed";
		$closed_parents = array();

		foreach ($structure_data as $key => $entry_data)
		{
			if (in_array(strtolower($entry_data['status']), $exclude_status_list) || (isset($entry_data['parent_id']) && in_array($entry_data['parent_id'], $closed_parents)))
			{
				$closed_parents[] = $entry_data['entry_id'];
				unset($structure_data[$key]);
			}
		}

		$structure_data = array_values($structure_data);

		$options = array();
		$options[''] = "-- None --";

		foreach ($structure_data as $page)
		{
			if (isset($page['depth'])) {
				$options[$page['entry_id']] = str_repeat('--', $page['depth']) . $page['title'];
			} else {
				$options[$page['entry_id']] = $page['title'];
			}
		}

		return form_dropdown($name, $options, $data);
	}

	// --------------------------------------------------------------------

	function replace_tag($data, $params = '', $tagdata = '')
	{
		if ($data != "" && is_numeric($data))
		{
			$uri = isset($this->site_pages['uris'][$data]) ? $this->site_pages['uris'][$data] : NULL;
			return Structure_Helper::remove_double_slashes(trim($this->EE->functions->fetch_site_index(0, 0), '/') . $uri);
		}
		return FALSE;
	}
}

// END Structure_ft class

/* End of file ft.structure.php */
/* Location: ./system/expressionengine/third_party/structure/ft.structure.php */