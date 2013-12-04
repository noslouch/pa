<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	CartThrob field
*	@author	CartThrob Team
*	@link	http://cartthrob.com/
*	============================================
*	File cartthrob_discount.php
*	
*/

class Zenbu_cartthrob_discount_ft extends Cartthrob_discount_ft
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
		$this->EE->load->helper(array('html'));
		$this->EE->load->library('table');
		$this->EE->lang->loadfile('cartthrob');
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
	function zenbu_display($entry_id, $channel_id, $data, $table_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons)
	{
		$list_array = array();	
		$data = unserialize(base64_decode($data));
		
		if(empty($data))
		{
			return '&nbsp;';
		}
		
		$this->EE->table->set_template(array (
                    'table_open'          => '<table class="mainTable matrixTable" border="0" cellpadding="0" cellspacing="0">',
                    'thead_open' => '',
                    'thead_close' => '',
                    'heading_cell_start'  => '<th style="padding: 0">',
                    'heading_cell_end'  => '</th>',
                    ));
		        $this->EE->table->set_heading(array('&nbsp;', '&nbsp;'));
		
		foreach($data as $label => $info)
		{
			// Make a string out of array data
			if(is_array($info))
			{
				$info = implode(', ', $info);
			}

			// Helps in getting a few language strings for discount type. Not all unfortunately.
			if($label == 'type')
			{
				$info = str_replace('Cartthrob_discount_', '', $info);
				$info = $this->EE->lang->line($info) == $info ? $this->EE->lang->line($info . '_title') : $this->EE->lang->line($info);
			}

			$this->EE->table->add_row('<strong>'.$this->EE->lang->line($label) . '</strong> ', $info);
		}
		
		$output = $this->EE->table->generate();
		
		// Get rid of stored data for next table
		$this->EE->table->clear();
						
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
	*	@return					A query to be integrated with entry results. Should be in CI Active Record format ($this->EE->db->…)
	*/
	function zenbu_result_query($rules = array(), $field_id = "", $fieldtypes, $already_queried = FALSE)
	{
		// Uncomment the below line if you want to disable CT deep searching
		// return;
		if(empty($rules) || empty($field_id))
		{
			return;
		}
		
		/**
		*	Data is stored as base64-encoded data
		*	Fetch entries that have CT data and create an array
		*	with base64-decoded data. Then search in that serialized string 
		*/
		$query = $this->EE->db->query("/* Zenbu: CartThrob keyword search */ \n SELECT entry_id, field_id_" . $field_id . " FROM exp_channel_data WHERE field_id_" . $field_id . " IS NOT NULL AND field_id_" . $field_id . " != ''");
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{	
				$ct_data[$row['entry_id']] = base64_decode($row['field_id_' . $field_id]);								
			}
		}
		
		$query->free_result();
		
		/**
		*	Search in serialized strings from found entries above
		*/
		foreach($rules as $rule)
		{
			$rule_field_id = (strncmp($rule['field'], 'field_', 6) == 0) ? substr($rule['field'], 6) : 0;
			if($rule_field_id == $field_id)
			{
				$keyword = isset($rule['val']) ? $rule['val'] : '';
				$cond	= isset($rule['cond']) ? $rule['cond'] : 'contains';
				foreach($ct_data as $entry_id => $ct_string)
				{
					if(stripos($ct_string, $keyword) !== FALSE)
					{
						$where_in_entries[] = $entry_id;
					}	
					
				}

				if(isset($where_in_entries))
				{
					if($cond == "contains")
					{
						$this->EE->db->where_in("exp_channel_titles.entry_id", $where_in_entries);
					} elseif($cond == "doesnotcontain") {
						$this->EE->db->where_not_in("exp_channel_titles.entry_id", $where_in_entries);
					}
				}
				
				/**
				*	Handling no matches situations
				*/
				if( ! empty($keyword) && ! isset($where_in_entries) && $cond == 'contains')
				{
					$this->EE->db->where("exp_channel_titles.entry_id", 0);
				}

			}
		}
		
	}
	
	
} // END CLASS

/* End of file matrix.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/cartthrob_discount.php */
?>