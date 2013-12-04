<?php

/**
 * DataGrab Grid fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_grid extends Datagrab_fieldtype {

	/**
	 * Register a setting so it can be saved
	 *
	 * @param string $field_name 
	 * @return void
	 */
	function register_setting( $field_name ) {
		return array( 
			$field_name . "_columns", 
			$field_name . "_unique",
			$field_name . "_extra1", 
			$field_name . "_extra2" 
		);
	}

	/**
	 * Create the form elements to map matrix fields
	 *
	 * @param string $field_name 
	 * @param string $field_label 
	 * @param string $field_type 
	 * @param string $data 
	 * @return void
	 * @author Andrew Weaver
	 */
	function display_configuration( $field_name, $field_label, $field_type, $data ) {

		$config = array();
		$config["label"] = form_label($field_label)
			. BR . anchor("http://brandnewbox.co.uk/support/", "Grid notes", 'class="help"');
		$config["value"] = "";
		$config["value"] .= form_hidden( $field_name, "1" );

		// Get current saved setting
		if( isset( $data["default_settings"]["cf"][ $field_name."_columns" ] ) ) {
			$default = $data["default_settings"]["cf"][ $field_name."_columns" ];
		} else {
			$default = array();
		}

		// Find columns for this grid
		$this->EE->db->select( "col_id, col_type, col_label" );
		$this->EE->db->from( "exp_grid_columns g" );
		$this->EE->db->join( "exp_channel_fields c", "g.field_id = c.field_id" );
		$this->EE->db->where( "c.field_name", $field_name );
		$this->EE->db->order_by( "col_order ASC" );
		$query = $this->EE->db->get();
		
		// Build ui
		$grid_columns = $query->result_array();
		foreach( $query->result_array() as $row ) {
			$config["value"] .= "<p>" . 
				$row["col_label"] . NBS . ":" . NBS;
			$config["value"] .= form_dropdown( 
					$field_name . "_columns[" . $row["col_id"] . "]", 
					$data["data_fields"],
					isset( $default[ $row["col_id"] ] ) ? $default[ $row["col_id"] ] : ''
				);
				
			if( $row[ "col_type" ] == "file" ) {
				$config["value"] .= NBS . NBS . "Upload folder: " . NBS;
				
				// Get upload folders
				if( !isset( $folders ) ) {
					$this->EE->db->select( "id, name" );
					$this->EE->db->from( "exp_upload_prefs" );
					$this->EE->db->order_by( "id" );
					$query = $this->EE->db->get();
					$folders = array();
					foreach( $query->result_array() as $folder ) {
						$folders[ $folder["id"] ] = $folder["name"];
					}
				}

				$config["value"] .= form_dropdown( 
					$field_name . "_extra1[" . $row["col_id"] . "]", 
					$folders,
					isset($data["default_settings"]["cf"][ $field_name . "_extra1" ][$row["col_id"]]) ? $data["default_settings"]["cf"][ $field_name . "_extra1" ][$row["col_id"]] : ''
				);
				$config["value"] .= NBS . NBS . "Fetch?: " . NBS;
				$config["value"] .= form_dropdown( 
					$field_name . "_extra2[" . $row["col_id"] . "]", 
					array("No", "Yes"),
					isset($data["default_settings"]["cf"][ $field_name . "_extra2" ][$row["col_id"]]) ? $data["default_settings"]["cf"][ $field_name . "_extra2" ][$row["col_id"]] : ''
				);
			}

				
			$config["value"] .= "</p>";
		}

		$column_options = array();
		$column_options["-1"] = "Delete all existing rows";
		$column_options["0"] = "Keep existing rows and append new";
		$sub_options = array();
		foreach( $grid_columns as $row ) {
			$sub_options[ $row["col_id"] ] = $row[ "col_label" ];
		}
		$column_options["Update the row if this column matches:"] = $sub_options;
		
		$config["value"] .= "<p>" . 
			"Action to take when an entry is updated: " .
			form_dropdown( 
				$field_name . "_unique", 
				$column_options,
				(isset($data["default_settings"]["cf"][$field_name . "_unique"]) ? 
					$data["default_settings"]["cf"][$field_name . "_unique" ]: '' )
			) .
			"</p>";
		

		return $config;
	}
	
	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
	}
		
	function final_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {

		// Find columns for this grid
		$this->EE->db->select( "col_id, col_type, col_label" );
		$this->EE->db->from( "exp_grid_columns g" );
		$this->EE->db->where( "field_id", $field_id );
		$query = $this->EE->db->get();		
		$grid_columns = $query->result_array();

		// $fields contains a list of grid columns mapped to data elements
		// eg, $fields[3] => 5 means map data element 5 to grid column 3
		$fields = $DG->settings["cf"][ $field . "_columns" ];

		$grid = array();
		$col_num = 0;
		
		// Loop over columns 
		foreach( $grid_columns as $column ) {

			$col_id = $column["col_id"];
		
			// Loop over data items
			if( isset( $fields[ $col_id ] ) ) {
			if( $DG->datatype->initialise_sub_item( 
				$item, $fields[ $col_id ], $DG->settings, $field ) ) {

				$subitem = $DG->datatype->get_sub_item( 
					$item, $fields[ $col_id ], $DG->settings, $field );
				$row_num = 1;
				$row_idx = "new_row_".$row_num;

				while( $subitem !== FALSE ) {
		
					if( !isset( $grid[ $row_idx ] ) ) {
						$grid[ $row_idx ] = array();
					}
					
					if( $column["col_type"] == "file" ) {
					
						$subitem = $DG->_get_file( 
							$subitem, 
							$DG->settings["cf"][ $field . "_extra1" ][ $col_id ],
							$DG->settings["cf"][ $field . "_extra2" ][ $col_id ] == 1 ? TRUE : FALSE
						);
					
					} 					
						
					$grid[ $row_idx ][ "col_id_" . $col_id ] = $subitem;	
		
					$subitem = $DG->datatype->get_sub_item( 
						$item, $fields[ $col_id ], $DG->settings, $field );
					$row_num++;				
					$row_idx = "new_row_".$row_num;
				}
				
			}
			}		
		
		}
	
		if( $update ) {
		
			// Find out what to do with existing data (delete or keep?)
			$unique = 0;
			if( isset($DG->settings["cf"][ $field . "_unique" ]) ) {
				$unique = $DG->settings["cf"][ $field . "_unique" ];
			}
			
			if( $unique != -1 ) {
				// -1 means ignore exisiting data

				// Fetch existing data
				$old = $this->_rebuild_grid_data( $update, $DG, $field_id );
				
				if( $unique == 0 ) {
					// Merge old and new data
					$grid = array_merge( $old, $grid );
				} else {
				
					$col_id = "col_id_".$unique;
					foreach( $old as $i => $oldrow ) {
						foreach( $grid as $j => $newrow ) {
							if( $oldrow[ $col_id ] == $newrow[ $col_id ] ) {
								$old[ $i ] = $newrow;
								unset( $grid[ $j ] );
								continue;
							}
						}
					}
					$grid = array_merge( $old, $grid );
				
				}
			}
			
		}
	
		// print_r( $grid );
	
		$data[ "field_id_" . $field_id ] = $grid;
	
	}

	function _rebuild_grid_data( $entry_id, $DG, $field_id ) {
	
		$this->EE->db->select( "*" );
		$this->EE->db->from( "exp_channel_grid_field_".$field_id );
		$this->EE->db->where( "entry_id", $entry_id );
		$this->EE->db->order_by( "row_order ASC" );
		$query = $this->EE->db->get();
		
		$grid = array();
		foreach( $query->result_array() as $row ) {
			$row_id = $row["row_id"];
			unset( $row["row_id"] );
			unset( $row["entry_id"] );
			unset( $row["row_order"] );
			$grid[ "row_id_" . $row_id ] = $row;
		}
		
		return $grid;
	}

}

?>