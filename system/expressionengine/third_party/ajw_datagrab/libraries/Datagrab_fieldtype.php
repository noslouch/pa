<?php
/**
 * DataGrab Fieldtype Class
 * 
 * Provides methods to interact with EE fieldtypes 
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 **/

class Datagrab_fieldtype {
		
	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Datagrab_fieldtype() {
		$this->EE =& get_instance();
	}
	
	/**
	 * Fetch a list of configuration settings that this field type can use
	 *
	 * @param string $name the field name
	 * @return array of configuration setting names
	 * @author Andrew Weaver
	 */
	function register_setting( $name ) {
		return array();
	}
	
	/**
	 * Generate the form elements to configure this field
	 *
	 * @param string $field_name the field's name
	 * @param string $field_label the field's label
	 * @param string $field_type the field's type
	 * @param string $data array of data that can be used to select from
	 * @return array containing form's label and elements
	 * @author Andrew Weaver
	 */
	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();
		$config["label"] = form_label($field_label);
		$config["value"] = form_dropdown( $field_name, $data["data_fields"], 
			isset($data["default_settings"]["cf"][$field_name]) ? 
			$data["default_settings"]["cf"][$field_name] : '' );
		return $config;
	}
	
	/**
	 * Prepare data for posting
	 *
	 * @param object $DG The DataGrab model object
	 * @param string $item The current row of data from the data source
	 * @param string $field_id The id of the field
	 * @param string $field The name of the field
	 * @param string $data The data array to insert into the channel
	 * @param string $update Update or insert?
	 */
	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		$data[ $field_id ] = $datatype->get_item( $item, $DG->settings[ $field ] );
	}
	
	/**
	 * As prepare_post_data but set after the check for existing entries
	 *
	 * @param object $DG The DataGrab model object
	 * @param string $item The current row of data from the data source
	 * @param string $field_id The id of the field
	 * @param string $field The name of the field
	 * @param string $data The data array to insert into the channel
	 * @param string $update Update or insert?
	 */
	function final_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
	}
	
	/**
	 * As prepare_post_data but set after entry has been added
	 *
	 * @param object $DG The DataGrab model object
	 * @param string $item The current row of data from the data source
	 * @param string $field_id The id of the field
	 * @param string $field The name of the field
	 * @param string $data The data array to insert into the channel
	 * @param string $update Update or insert?
	 */
	function post_process_entry( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
	}
	
	/**
	 * Rebuild the POST data of from existing entry
	 *
	 * @param string $DG 
	 * @param string $field_id 
	 * @param string $data 
	 * @param string $existing_data 
	 * @return void
	 * @author Andrew Weaver
	 */
	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {
		// print get_class( $this );
		$data[ "field_id_".$field_id ] = $existing_data[ "field_id_".$field_id ];
	}
	
}

?>