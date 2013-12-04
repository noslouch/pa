<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	CartThrob field
*	@author	CartThrob Team
*	@link	http://cartthrob.com/
*	============================================
*	File cartthrob_order_items.php
*	
*/

class Zenbu_cartthrob_order_items_ft extends Cartthrob_order_items_ft
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
		$output = '&nbsp;';
		if(empty($table_data))
		{
			return $output;
		}
		
		$this->EE->table->set_template(array (
                    'table_open'          => '<table class="mainTable matrixTable" border="0" cellpadding="0" cellspacing="0">',
                    'thead_open' => '',
                    'thead_close' => '',
                    ));
        
		$c = 0;
		if(isset($table_data['order_id_'.$entry_id]))
		{
			foreach($table_data['order_id_'.$entry_id] as $row_id => $data_array)
			{
				$data_array_output = array();
				if($c == 0)
				{
					foreach($data_array as $label => $val)
					{
						if($label == 'extra' && is_array($data_array['extra']))
						{
							foreach($data_array['extra'] as $h => $val)
							{
								if($h != "row_id")
								{
									$header_array[] = $h;
								}
							}
						} else {
							$header_array[] = $label;
						}
					}
					$this->EE->table->set_heading($header_array);
				}
				$c++;
				
				foreach($data_array as $key => $val)
				{
				
					if($key == "extra" && is_array($val))
					{
						foreach($val as $k => $v)
						{
							if($k != "row_id")
							{
								$data_array_output[] = ( ! empty($v)) ? $v : '&nbsp;';
							}
						}
						unset($data_array['extra']);
					} else {
						$data_array_output[] = ( ! empty($val)) ? $val : '&nbsp;';
					}
				}
				
				$this->EE->table->add_row($data_array_output);
				
			}
		}
		
		$output = $this->EE->table->generate();
		$this->EE->table->clear();
		$output = ($output == "Undefined table data") ? '&nbsp;' : $output;
						
		return $output;
	}
	
	/**
	*	=============================
	*	function zenbu_get_table_data
	*	=============================
	*	Retrieve data stored in other database tables 
	*	based on results from Zenbu's entry list
	*	@uses	Instead of many small queries, this function can be used to carry out
	*			a single query of data to be later processed by the zenbu_display() method
	*
	*	@param	$entry_ids				array	An array of entry IDs from Zenbu's entry listing results
	*	@param	$field_ids				array	An array of field IDs tied to/associated with result entries
	*	@param	$channel_id				int		The ID of the channel in which Zenbu searched entries (0 = "All channels")
	*	@param	$output_upload_prefs	array	An array of upload preferences
	*	@param	$settings				array	The settings array, containing saved field order, display, extra options etc settings (optional)
	*	@param	$rel_array				array	A simple array useful when using related entry-type fields (optional)
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id)
	{
		$output = array();
		
		$this->EE->db->from('cartthrob_order_items');
		$this->EE->db->where_in('order_id', $entry_ids);
		$this->EE->db->order_by('row_order');
		$query = $this->EE->db->get();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$output['order_id_'.$row['order_id']]['row_id_'.$row['row_id']]['entry_id'] = $row['entry_id'];
				$output['order_id_'.$row['order_id']]['row_id_'.$row['row_id']]['title'] = $row['title'];
				$output['order_id_'.$row['order_id']]['row_id_'.$row['row_id']]['quantity'] = $row['quantity'];
				$output['order_id_'.$row['order_id']]['row_id_'.$row['row_id']]['price'] = $row['price'];
				$output['order_id_'.$row['order_id']]['row_id_'.$row['row_id']]['extra'] = unserialize(base64_decode($row['extra']));
			}
		}
		
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
		foreach($rules as $rule)
		{
			$rule_field_id = (strncmp($rule['field'], 'field_', 6) == 0) ? substr($rule['field'], 6) : 0;
			if($rule_field_id == $field_id)
			{
				$keyword = isset($rule['val']) ? $rule['val'] : '';
				$cond	= isset($rule['cond']) ? $rule['cond'] : 'contains';
			
				$query = $this->EE->db->query("/* Zenbu: CartThrob keyword search - Order items */ \n SELECT order_id FROM exp_cartthrob_order_items WHERE CONCAT(entry_id, title, quantity, price) LIKE '%" . $keyword. "%'");
				
				if(isset($where_in_entries))
				{
					unset($where_in_entries);
				}

				if($query->num_rows() > 0)
				{
					foreach($query->result_array() as $row)
					{	
						$where_in_entries[] = $row['order_id'];								
					}
				}
		
				$query->free_result();

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
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/cartthrob_order_items.php */
?>