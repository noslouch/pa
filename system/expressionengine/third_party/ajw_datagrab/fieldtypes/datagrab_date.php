<?php

/**
 * DataGrab Date fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_date extends Datagrab_fieldtype {

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		if( $DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] ) != "" ) {
			$data[ "field_id_" . $field_id ] = $DG->_parse_date(
				$DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] )
			);
			$data[ "field_id_" . $field_id ] -= $DG->settings["config"]["offset"];
		} else {
			$data[ "field_id_" . $field_id ] = "";
		}
		$data[ "field_offset_" . $field_id ] = 'n';
		
	}

}

?>