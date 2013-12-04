<?php

/**
 * DataGrab File fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_file extends Datagrab_fieldtype {

	function register_setting( $field_name ) {
		return array( 
			$field_name . "_filedir", 
			$field_name . "_fetch" 
		);
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {

		// Get current saved setting
		if( isset( $data["default_settings"]["cf"] ) ) {
			$default = $data["default_settings"]["cf"];
		} else {
			$default = array();
		}

		// Get upload folders
		$this->EE->db->select( "id, name" );
		$this->EE->db->from( "exp_upload_prefs" );
		$this->EE->db->order_by( "id" );
		$query = $this->EE->db->get();
		$folders = array();
		foreach( $query->result_array() as $row ) {
			$folders[ $row["id"] ] = $row["name"];
		}

		// Build config form
		$config = array();
		$config["label"] = form_label($field_label) . BR .
			'<a href="http://brandnewbox.co.uk/support/details/" class="help">File notes</a>';
		$config["value"] = "<p>" . 
			form_dropdown( 
				$field_name, 
				$data["data_fields"], 
				isset( $default[$field_name] ) ? $default[$field_name] : '' 
			) . 
			"</p><p>Upload folder: " . NBS .
			form_dropdown( 
				$field_name . "_filedir", 
				$folders, 
				isset( $default[ $field_name . "_filedir" ] ) ? $default[ $field_name . "_filedir" ] : ''
				) . 
			"</p><p>Fetch files from urls: " . NBS .
			form_dropdown( 
				$field_name . "_fetch", 
				array("No" , "Yes"), 
				isset( $default[ $field_name . "_fetch" ] ) ? $default[ $field_name . "_fetch" ] : ''
				) . 
			"</p>";
				
		return $config;
	}

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {	

		$data["field_id_".$field_id] = "";

		// Fetch file from data
		if( $DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] ) != "" ) {
		
			$filename = $DG->_get_file( 
				$DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] ),
				$DG->settings["cf"][ $field . '_filedir' ],
				$DG->settings["cf"][ $field . '_fetch' ] == 1 ? TRUE : FALSE
			);
		
			if( $filename !== FALSE ) {
				$data[ "field_id_" . $field_id ] = $filename;
			}

		} 

		// print $data["field_id_".$field_id] . '<br/>';

	}

}

?>