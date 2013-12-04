<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	Pixel&Tonic's Field Pack Multiselect field
*	@author	Pixel&tonic http://pixelandtonic.com
*	@link	http://pixelandtonic.com/ee
*	============================================
*	File fieldpack_multiselect.php
*	
*/

class Zenbu_fieldpack_multiselect_ft extends Fieldpack_multiselect_ft
{
	var $dropdown_type = "contains_doesnotcontain";
	
	/**
	*	Constructor
	*
	*	@access	public
	*/
	function __construct()
	{
		$this->EE =& get_instance();
	}
	
	/**
	*	======================
	*	function zenbu_display
	*	======================
	*	Set up display in entry result cell
	*
	*	@param	$entry_id			int		The entry ID of this single result entry
	*	@param	$channel_id			int		The channel ID associated to this single result entry
	*	@param	$data				array	Raw data as found in database cell in exp_channel_data
	*	@param	$table_data			array	Data array usually retrieved from other table than exp_channel_data
	*	@param	$field_id			int		The ID of this field
	*	@param	$settings			array	The settings array, containing saved field order, display, extra options etc settings
	*	@param	$rules				array	An array of entry filtering rules 
	*	@param	$upload_prefs		array	An array of upload preferences (optional)
	*	@param 	$installed_addons	array	An array of installed addons and their version numbers (optional)
	*	@param	$fieldtypes			array	Fieldtype of available fieldtypes: id, name, etc (optional)
	*	@return	$output		The HTML used to display data
	*/
	function zenbu_display($entry_id, $channel_id, $data, $table_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons, $fieldtypes)
	{
		$output = (empty($data)) ? '&nbsp;' : '';
		$keyword = "";
		
		foreach($rules as $rule)
		{
			if($rule['field'] == 'field_'.$field_id)
			{
				$keyword = $rule['val'];
			}
		}
		
		if(empty($data))
		{
			return $output;
		}
		
		$field_settings = $fieldtypes['settings'][$field_id];
		$field_setting = $field_settings['options'];

		// Process options by checking for optgroups, 
		// which are removed. Options are then assembled together
		$f_options = $this->assemble_options($field_setting);

		$field_data = explode("\n", $data);

		foreach($field_data as $key => $value)
		{
			$output .= (isset($f_options[$value])) ? $f_options[$value].', ' : '';
		}
		
		$output = substr($output, 0, -2);
		$output = highlight($output, $rules, 'field_'.$field_id);

		return $output;
	}
	
	/**
	*	===================================
	*	function zenbu_result_query
	*	===================================
	*	Extra queries to be intergrated into main entry result query
	*
	*	@param	$rules				int		An array of entry filtering rules 
	*	@param	$field_id			array	The ID of this field
	*	@param	$fieldtypes			array	$fieldtype data
	*	@param	$already_queried	bool	Used to avoid using a FROM statement for the same field twice
	*	@param	$installed_addons	array	An array of installed addons and their version numbers (optional)
	*	@return					A query to be integrated with entry results. Should be in CI Active Record format ($this->EE->db->…)
	*/
	function zenbu_result_query($rules = array(), $field_id = "", $fieldtypes)
	{
		if(empty($rules))
		{
			return;
		}
		
		$field_settings = (isset($fieldtypes['settings'][$field_id])) ? $fieldtypes['settings'][$field_id] : '';
		$f_options = $this->assemble_options($field_settings['options']);
		
		if(isset($f_options))
		{
			// Get the keywords stored in db field from keyword based on label
			$keyword_in_db = "";
			foreach($f_options as $key => $val)
			{
				foreach($rules as $rule)
				{
					if(strncmp($rule['field'], 'field_', 6) == 0)
					{
						$keyword = $rule['val'];
						
						if(stripos($f_options[$key], $keyword) !== FALSE)
						{
							$keyword_in_db[] = $key;
						}
					}
				}
			}
			
			foreach($rules as $rule)
			{
				if(strncmp($rule['field'], 'field_', 6) == 0)
				{
					$keyword = $rule['val'];
					if(empty($keyword))
					{
						return;
					}
					
					// Build query to get entries with or without the keyword stored in db field
					switch ($rule['cond'])
					{
						case "contains" :
							if(empty($keyword_in_db))
							{
								// If the search keyword is not among the options,
								// make it so that no results are returned.
								$like_query = "entry_id = 0";
							} else {
								$like_query = implode($keyword_in_db, '%" OR field_id_'.$field_id.' LIKE "%');
								$like_query = 'field_id_'.$field_id.' LIKE "%'.$like_query.'%"';
							}
						break;
						case "doesnotcontain" :
							if( ! empty($keyword_in_db))
							{
								$like_query = implode($keyword_in_db, '%" AND field_id_'.$field_id.' NOT LIKE "%');
								$like_query = 'field_id_'.$field_id.' NOT LIKE "%'.$like_query.'%" OR field_id_'.$field_id.' IS NULL';
							} else {
								return;
							}
						break;
					}

				}
			}
						
		}
		
		$query = $this->EE->db->query("SELECT entry_id FROM exp_channel_data WHERE ".$like_query);
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$entries[] = $row['entry_id'];
			}
		} else {
			$entries[] = 0;
		}
		
		// Filter by entry IDs within the above results
		$this->EE->db->where_in("exp_channel_titles.entry_id", $entries);
	}


	/**
	*	===================================
	*	function assemble_options
	*	===================================
	*	Remove optgroups from field option array
	*
	*	@param	$field_setting		array	Array of options, including optgroups 
	*	@return	$f_options 			array 	Cleaned up array of options, all on the same level
	*/
	private function assemble_options($field_setting)
	{
		$f_options = array();
		foreach($field_setting as $key => $val)
		{
			if(is_array($field_setting[$key]))
			{
				foreach($val as $k => $v)
				{
					$f_options[$k] = $v;
				}
				
			} else {
				$f_options[$key] = $val;
			}
		}
		return $f_options;
	}
	
	
} // END CLASS

/* End of file fieldpack_multiselect.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/fieldpack_multiselect.php */
?>