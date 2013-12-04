<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	VZ URL
*	@author	Eli Van Zoeren
*	@link	http://devot-ee.com/add-ons/vz-url-extension
*	============================================
*	File vz_url.php
*	
*/


class Zenbu_vz_url_ft extends Vz_url_ft
{
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
	function zenbu_display($entry_id, $channel_id, $data, $table_data = array(), $field_id, $settings, $rules = array())
	{
		if( ! empty($data))
		{
			$output = anchor($data, highlight($data, $rules, 'field_'.$field_id), 'target="_blank"');
		} else {
			$output = '&nbsp;';
		}
		
		return $output;
	}
	
	
} // END CLASS

/* End of file vz_url.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/vz_url.php */
?>