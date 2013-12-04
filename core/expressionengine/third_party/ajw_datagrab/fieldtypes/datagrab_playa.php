<?php

/**
 * DataGrab Playa fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_playa extends Datagrab_fieldtype {

	function register_setting( $field_name ) {
		return array( $field_name . "_playa_field" );
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();
		$config["label"] = form_label($field_label)  . BR .
		anchor("http://brandnewbox.co.uk/support/details/importing_into_playa_fields_with_datagrab", "Playa notes", 'class="help"');
		$config["value"] = "<p>" . form_dropdown( 
			$field_name, $data["data_fields"], 
			isset( $data["default_settings"]["cf"][$field_name] ) ? 
				$data["default_settings"]["cf"][$field_name] : '' 
			)
			. "</p><p>Field to match: " . NBS .
			form_dropdown( 
				$field_name . "_playa_field", 
				$data["all_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_playa_field"]) ? 
					$data["default_settings"]["cf"][$field_name . "_playa_field" ]: '' ) . "</p>"
			);
		return $config;
	}

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		// Fetch fieldtype settings
		$fs = $this->EE->api_channel_fields->settings[ $field_id ]["field_settings"];
		$field_settings = (unserialize(base64_decode($fs)));
		
		// Initialise playa post data
		$data[ "field_id_" . $field_id ] = array();
		// $data[ "field_id_" . $field_id ]["old"] = "";
		$data[ "field_id_" . $field_id ]["selections"] = array();
		//$data[ "field_id_" . $field_id ]["selections"][] = "";

		// Fetch fieldtype settings
		$DG->_get_channel_fields_settings( $field_id );
		$fs = $this->EE->api_channel_fields->settings[ $field_id ]["field_settings"];
		$field_settings = (unserialize(base64_decode($fs)));
		
		// Can the current datatype handle sub-loops (eg, XML)?
		if( $DG->datatype->datatype_info["allow_subloop"] ) {
		
			// Check this field can be a sub-loop
			if( $DG->datatype->initialise_sub_item( 
				$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
				// Loop over sub items
				while( $subitem = $DG->datatype->get_sub_item( 
					$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
				
					// Check whether item matches a valid entry and create a playa relationship
					$this->EE->db->select( 'exp_channel_titles.entry_id' );
					$this->EE->db->join( 'exp_channel_data', 'exp_channel_titles.entry_id = exp_channel_data.entry_id' );
					if( isset( $field_settings["channels"] ) ) {
						$this->EE->db->where_in( 'exp_channel_titles.channel_id', $field_settings["channels"] );
					}
					if( !isset( $DG->settings["cf"][ $field . "_playa_field" ] ) ) {
						$this->EE->db->where( 'title', $subitem );
					} else {
						$this->EE->db->where( $DG->settings["cf"][ $field . "_playa_field" ], $subitem );
					}
					$query = $this->EE->db->get( 'exp_channel_titles' );
					if( $query->num_rows() > 0 ) {
						$row = $query->row_array();
						$data[ "field_id_" . $field_id ]["selections"][] = $row[ "entry_id" ];
					}

				}
				
			}
	
		}

	}
	
	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {

		// Fetch relationships from exp_playa_relationships
		$this->EE->db->select( "child_entry_id" );
		$this->EE->db->where( "parent_entry_id", $existing_data["entry_id"] );
		$this->EE->db->where( "parent_field_id", $field_id );
		$query = $this->EE->db->get( "exp_playa_relationships" );

		$selections = array();
		foreach( $query->result_array() as $row ) {
			$selections[] = $row["child_entry_id"];
		}

		// Rebuild selections array
		$data[ "field_id_".$field_id ] = array(
			"selections" => $selections
		);
		
	}

}

?>