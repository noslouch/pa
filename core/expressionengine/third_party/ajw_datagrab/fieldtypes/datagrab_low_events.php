<?php

/**
 * DataGrab Low Events fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_low_events extends Datagrab_fieldtype {

	function register_setting( $field_name ) {
		return array( 
			$field_name . "_low_events_start_date", 
			$field_name . "_low_events_start_time", 
			$field_name . "_low_events_end_date", 
			$field_name . "_low_events_end_time", 
			$field_name . "_low_events_all_day"
		);
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();
		$config["label"] = "<p>" .
		form_label($field_label);
		/*  . NBS .
		anchor("http://brandnewbox.co.uk/support/details/importing_into_playa_fields_with_datagrab", "(?)", 'class="help"');
		*/
		$config["value"] = "Start date: " . NBS . 
			form_hidden( $field_name, "y" ) . form_dropdown( 
			$field_name . "_low_events_start_date", $data["data_fields"], 
			isset( $data["default_settings"]["cf"][$field_name . "_low_events_start_date"] ) ? 
				$data["default_settings"]["cf"][$field_name . "_low_events_start_date"] : '' 
			) . 
			"</p><p>" . "Start time: " . NBS .
			form_dropdown( 
				$field_name . "_low_events_start_time", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_low_events_start_time"]) ? 
					$data["default_settings"]["cf"][$field_name . "_low_events_start_time" ]: '' )
			) .
			"</p><p>" . "End date: " . NBS .
			form_dropdown( 
				$field_name . "_low_events_end_date", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_low_events_end_date"]) ? 
					$data["default_settings"]["cf"][$field_name . "_low_events_end_date" ]: '' )
			) .
			"</p><p>" . "End time: " . NBS .
			form_dropdown( 
				$field_name . "_low_events_end_time", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_low_events_end_time"]) ? 
					$data["default_settings"]["cf"][$field_name . "_low_events_end_time" ]: '' )
			) .
			"</p><p>" . "All day?: " . NBS .
			form_dropdown( 
				$field_name . "_low_events_all_day", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_low_events_all_day"]) ? 
					$data["default_settings"]["cf"][$field_name . "_low_events_all_day" ]: '' )
			) .
			"</p>";
						
		return $config;
	}


	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		$event = array(
			"start_date" => "",
			"start_time" => "",
			"end_time" => "",
			"end_date" => "",
			"all_day" => ""
		);
	
		if( $DG->settings["cf"][ $field."_low_events_start_date" ] != "" ) {
			$event[ "start_date" ] = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_low_events_start_date" ] );
			$event[ "start_date" ] = $this->_parse_date( $event[ "start_date" ] );
		}
		if( $DG->settings["cf"][ $field."_low_events_start_time" ] != "" ) {
			$event[ "start_time" ] = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_low_events_start_time" ] );
			$event[ "start_time" ] = $this->_parse_time( $event[ "start_time" ] );
		}
		if( $DG->settings["cf"][ $field."_low_events_end_date" ] != "" ) {
			$event[ "end_date" ] = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_low_events_end_date" ] );
			$event[ "end_date" ] = $this->_parse_date( $event[ "end_date" ] );
		}
		if( $DG->settings["cf"][ $field."_low_events_start_date" ] != "" ) {
			$event[ "end_time" ] = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_low_events_end_time" ] );
			$event[ "end_time" ] = $this->_parse_time( $event[ "end_time" ] );
		}
		if( $DG->settings["cf"][ $field."_low_events_all_day" ] != "" ) {
			$event[ "all_day" ] = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_low_events_all_day" ] );
		}

		$data[ "field_id_" . $field_id ] = $event;

	}

	function final_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {				
	}
	
	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {
	}

	function _parse_date( $date ) {
	
		// Is date already in correct format? If so, just return it
		if( preg_match('/^(19|20)\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/', $date) ) {
			return $date;
		}
		
		// If not, try and convert it timestamp and format it correctly
		$datestr = $date;
		
		// Wild assumption that if site is set to "eu" then the data
		// will also be in eu format not us
		if( $this->EE->config->item('time_format') == "eu" ) {
			$datestr = str_replace("/", "-", $datestr );
		}
		
		$ndate = strtotime( $datestr );
		
		if( $ndate !== FALSE ) {
			return date('Y-m-d', $ndate ); // YYYY-MM-DD
		}
		
		return $date;
	}

	function _parse_time( $date ) {
	
		// Is time already in correct format? If so, just return it
		if( preg_match('/^(?:0?[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $date) ) {
			return $date;
		}
		
		// If not, try and convert it timestamp and format it correctly
		$datestr = $date;
		
		// Wild assumption that if site is set to "eu" then the data
		// will also be in eu format not us
		if( $this->EE->config->item('time_format') == "eu" ) {
			$datestr = str_replace("/", "-", $datestr );
		}
		
		$ndate = strtotime( $datestr );
		
		if( $ndate !== FALSE ) {
			return date('H:i', $ndate ); // HH:MM
		}
		
		return $date;
	}


}

?>