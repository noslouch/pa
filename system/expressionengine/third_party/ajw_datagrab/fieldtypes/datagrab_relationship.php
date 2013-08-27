<?php

/**
 * DataGrab Relationship fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_relationship extends Datagrab_fieldtype {

	function register_setting( $field_name ) {
		return array( $field_name . "_relationship_field" );
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();
		$config["label"] = form_label($field_label) /*. NBS .
		anchor("http://brandnewbox.co.uk/support/details/importing_into_relationship_fields_with_datagrab", "(?)", 'class="help"') */;
		$config["value"] = "<p>" . form_dropdown( 
			$field_name, $data["data_fields"], 
			isset( $data["default_settings"]["cf"][$field_name] ) ? 
				$data["default_settings"]["cf"][$field_name] : '' 
			)
			. "</p><p>Field to match: " . NBS .
			form_dropdown( 
				$field_name . "_relationship_field", 
				$data["all_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_relationship_field"]) ? 
					$data["default_settings"]["cf"][$field_name . "_relationship_field" ]: '' ) . "</p>"
			);
		return $config;
	}

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		$order = 1;
		// Can the current datatype handle sub-loops (eg, XML)?
		if( $DG->datatype->datatype_info["allow_subloop"] ) {
		
			// Check this field can be a sub-loop
			if( $DG->datatype->initialise_sub_item( 
				$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
				$data[ "field_id_" . $field_id ] = array();
				$data[ "field_id_" . $field_id ]["sort"] = array();
				$data[ "field_id_" . $field_id ]["data"] = array();

				// Loop over sub items
				while( $subitem = $DG->datatype->get_sub_item( 
					$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
				
					// Check whether item matches a valid entry and create a playa relationship
					$this->EE->db->select( 'exp_channel_titles.entry_id' );
					$this->EE->db->join( 'exp_channel_data', 'exp_channel_titles.entry_id = exp_channel_data.entry_id' );
					if( isset( $field_settings["channels"] ) ) {
						$this->EE->db->where_in( 'exp_channel_titles.channel_id', $field_settings["channels"] );
					}
					if( !isset( $DG->settings["cf"][ $field . "_relationship_field" ] ) ) {
						$this->EE->db->where( 'title', $subitem );
					} else {
						$this->EE->db->where( $DG->settings["cf"][ $field . "_relationship_field" ], $subitem );
					}
					$query = $this->EE->db->get( 'exp_channel_titles' );
					if( $query->num_rows() > 0 ) {
						$row = $query->row_array();
						$data[ "field_id_" . $field_id ]["data"][] = $row[ "entry_id" ];
						$data[ "field_id_" . $field_id ]["sort"][] = $order++;
					}

				}
				
			}
	
		}


	}
	
	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {		

		// Fetch relationships from exp_playa_relationships
		$this->EE->db->select( "child_id, order" );
		$this->EE->db->where( "parent_id", $existing_data["entry_id"] );
		$this->EE->db->where( "field_id", $field_id );
		$this->EE->db->order_by( "order" );
		$query = $this->EE->db->get( "exp_relationships" );

		$d = array();
		$sort = array();
		foreach( $query->result_array() as $row ) {
			$d[] = $row["child_id"];
			$sort[] = $row["order"];
		}

		// Rebuild selections array
		$data[ "field_id_".$field_id ] = array(
			"data" => $d,
			"sort" => $sort
		);

	}

}

?>