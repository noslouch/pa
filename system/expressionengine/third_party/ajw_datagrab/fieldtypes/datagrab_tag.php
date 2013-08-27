<?php

/**
 * DataGrab Tag fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_tag extends Datagrab_fieldtype {

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		// Can the current datatype handle sub-loops (eg, XML)?
		if( $DG->datatype->datatype_info["allow_subloop"] ) {
		
			// Check this field can be a sub-loop
			if( $DG->datatype->initialise_sub_item( 
				$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
				// Loop over sub items
				$tags = array();
				while( $subitem = $DG->datatype->get_sub_item( 
					$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
				
						$tags[] = $subitem;

					}
					$data[ "field_id_" . $field_id ] = implode("\n", $tags);

				}
				
		}

	}

}

?>