<?php

/**
 * DataGrab Assets fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_assets extends Datagrab_fieldtype {

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		$data[ "field_id_" . $field_id ] = array();
		
		// Can the current datatype handle sub-loops (eg, XML)?
		if( $DG->datatype->datatype_info["allow_subloop"] ) {
		
			// Check this field can be a sub-loop
			if( $DG->datatype->initialise_sub_item( 
				$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
				// Loop over sub items
				while( $subitem = $DG->datatype->get_sub_item( 
					$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
					if( preg_match('/{filedir_([0-9]+)}/', $subitem, $matches) ) {
						$file = array(
							"filedir" => $matches[1],
							"filename" => str_replace($matches[0], '', $subitem )
						);
		
						$this->EE->db->select( "file_id" );
						$this->EE->db->where( "file_name", $file["filename"] );
						$this->EE->db->where( "filedir_id", $file["filedir"] );
						$query = $this->EE->db->get( "exp_assets_files" );
						if( $query->num_rows() > 0 ) {
							$row = $query->row_array();				
							$data[ "field_id_" . $field_id ][] = $row["file_id"];
						}
					}
		
				}
			}
		}
				
		/*
		[field_id_40] => Array
		        (
		            [0] => 4
		            [1] => 2
		            [2] => 3
		            [3] => 
		        )
		*/		
	}

	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {

		$data[ "field_id_" . $field_id ] = array();

		$this->EE->db->select( "file_id" );
		$this->EE->db->from( "exp_assets_selections" );
		$this->EE->db->where( "entry_id", $existing_data["entry_id"] );
		$this->EE->db->where( "field_id", $field_id );
		$this->EE->db->order_by( "sort_order" );
		$query = $this->EE->db->get();
		
		foreach( $query->result_array() as $row ) {
			$data[ "field_id_" . $field_id ][] = $row["file_id"];
		}

	}

}

?>