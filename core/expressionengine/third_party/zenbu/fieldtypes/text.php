<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	Standard input text field
*	@author	EllisLab
*	============================================
*	File text.php
*	
*/

class Zenbu_text_ft extends Text_ft
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
		// Convert to "regular" number if data is numeric and number is in scientific nomenclature
		if( isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['text_option_3']) 
			&& $settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['text_option_3'] == 'y' 
			&& $data != "" && $data != 0)
		{
			if( isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['text_option_4']) )
			{
				$decimals = (int) $settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['text_option_4'];
			} else {
				$decimals = 0;
			}

			$data = number_format( (float)$data, $decimals, '.', '');
		}

		// Display data using the provided display_text helper function, which includes highlighting function
		$output = display_text($entry_id, $channel_id, $data, $table_data = array(), $field_id, $settings, $rules);
		
		// Output
		return $output;
	}
	
	/**
	*	===================================
	*	function zenbu_field_extra_settings
	*	===================================
	*	Set up display for this fieldtype in "display settings"
	*
	*	@param	$table_col			string	A Zenbu table column name to be used for settings and input field labels
	*	@param	$channel_id			int		The channel ID for this field
	*	@param	$extra_options		array	The Zenbu field settings, used to retieve pre-saved data
	*	@return	$output		The HTML used to display setting fields
	*/
	function zenbu_field_extra_settings($table_col, $channel_id, $extra_options, $field_settings = array())
	{

		// Retrieve previous results if present
		$extra_text_option_1 = (isset($extra_options['text_option_1'])) ? $extra_options['text_option_1'] : '';
		$extra_text_option_2 = (isset($extra_options['text_option_2'])) ? $extra_options['text_option_2'] : 'html';
		
		// Option: Text trimming option						
		$output['text_option_1'] = form_label($this->EE->lang->line('show').'&nbsp;'.form_input('settings['.$channel_id.']['.$table_col.'][text_option_1]', $extra_text_option_1, 'size="2" maxlength="4" class="bottom-margin "').'&nbsp;'.$this->EE->lang->line('characters')) . BR;
		
		// Option: Text display (HTML/no HTML) option)
		$text_option_2_dropdown = array(
			"html" => $this->EE->lang->line("show_html"),
			"nohtml" => $this->EE->lang->line("no_html"),
		);
		$output['text_option_2'] = form_dropdown('settings['.$channel_id.']['.$table_col.'][text_option_2]', $text_option_2_dropdown, $extra_text_option_2, 'class="bottom-margin "' );

		if(isset($field_settings['field_content_type']) && $field_settings['field_content_type'] == 'numeric')
		{
			$extra_text_option_3 = (isset($extra_options['text_option_3']) && $extra_options['text_option_3'] == 'y') ? TRUE : FALSE;
			$extra_text_option_4 = (isset($extra_options['text_option_4'])) ? $extra_options['text_option_4'] : '2';
			
			$output['text_option_3'] = BR . form_label( form_checkbox('settings['.$channel_id.']['.$table_col.'][text_option_3]', 'y', $extra_text_option_3).'&nbsp;'.$this->EE->lang->line('convert_to_regular_number') );
			$output['text_option_4'] = BR . form_label($this->EE->lang->line('number_of_decimals').'&nbsp;'.form_input('settings['.$channel_id.']['.$table_col.'][text_option_4]', $extra_text_option_4, 'size="2" class="bottom-margin "'));
		}
		
		// Output
		return $output;
	
	}
	
	/**
	*	===================================
	*	function zenbu_field_validation
	*	===================================
	*	Set up extra validation for user input in extra settings
	*
	*	@param	$setting	array	Submitted display settings data
	*	@return	$output		array	Setting values for this fieldtype, with extra setting short name as key
	*/
	function zenbu_field_validation($setting)
	{
		/**	
		*	-------------------------
		*	Text option 1: word limit
		*	-------------------------
		*	Check that input is numerical
		*/
		if(isset($setting['text_option_1']) && ! is_numeric($setting['text_option_1']) && ! empty($setting['text_option_1']))
		{
			$this->EE->javascript->output('
				$.ee_notice("'.$this->EE->lang->line("error_not_numeric").'", {"type" : "error"});
			');
			$output['text_option_1'] = (int)$setting['text_option_1'];
			return $output;
		} elseif(empty($setting['text_option_1'])) {
			$output['text_option_1'] = '';
		} else {
			$output['text_option_1'] = (int)$setting['text_option_1'];
		}
		
		/**	
		*	----------------------------
		*	Text option 2: display style
		*	----------------------------
		*	Check that the setting simply exists
		*/
		if(isset($setting['text_option_2']))
		{
			$output['text_option_2'] = $setting['text_option_2'];
		} else {
			$output['text_option_2'] = 'html';
		}

		/**	
		*	----------------------------
		*	Text option 3 & 4: Numerical values
		*	----------------------------
		*	Check that the setting simply exists
		*/
		if(isset($setting['text_option_3']))
		{
			$output['text_option_3'] = $setting['text_option_3'];
		} else {
			$output['text_option_3'] = 'n';
		}

		if(isset($setting['text_option_4']))
		{
			$output['text_option_4'] = $setting['text_option_4'];
		} else {
			$output['text_option_4'] = '0';
		}
		
		return $output;
	}
	
} // END CLASS

/* End of file text.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/text.php */
?>