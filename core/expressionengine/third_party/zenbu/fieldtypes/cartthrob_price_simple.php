<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	CartThrob field
*	@author	CartThrob Team
*	@link	http://cartthrob.com/
*	============================================
*	File cartthrob_price_simple.php
*	
*/

class Zenbu_cartthrob_price_simple_ft extends Cartthrob_price_simple_ft
{
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
		if( ! empty($data))
		{
			$number_format_defaults_prefix = $table_data['number_format_defaults_prefix'];
			$output = $number_format_defaults_prefix.$data;
		} else {
			$output = '&nbsp;';
		}		
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
	*	@param	$settings				array	The settings array, containing saved field order, display, extra options etc settings
	*	@param	$rel_array				array	A simple array useful when using related entry-type fields (optional)
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id, $output_upload_prefs, $settings)
	{
		$output = array();
		$this->EE->db->from('cartthrob_settings');
		$this->EE->db->where('key', 'number_format_defaults_prefix');
		$this->EE->db->where('site_id', $this->EE->session->userdata['site_id']);
		$query = $this->EE->db->get();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$output['number_format_defaults_prefix'] = $row['value'];
			}
		}
		
		return $output;
	}
	
	
} // END CLASS

/* End of file matrix.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/cartthrob_price_simple.php */
?>