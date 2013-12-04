<?php

/**
 * DataGrab JSON import class
 *
 * Allows JSON imports
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Ajw_json extends Datagrab_type {

	var $datatype_info = array(
		'name' => 'JSON',
		'version' => '0.1',
		'allow_comments' => FALSE,
		'allow_subloop' => FALSE
	);
	
	var $settings = array(
		"filename" => "",
		"path" => ""
		);
	
	var $items;
	var $sub_item_ptr;
	
	function settings_form( $values = array() ) {
		
		$form = array(
			array(
				form_label('Filename or URL', 'filename') .
				'<div class="subtext">Can be a file on the local file system or from a website site url (http://...)</div>', 
				form_input(
					array(
						'name' => 'filename',
						'id' => 'filename',
						'value' => $this->get_value( $values, "filename" ),
						'size' => '50'
						)
					)
				),
			array(
				form_label('JSON Element', 'path')  .
				'<div class="subtext">Optional. The JSON file should return an array of objects to import. Some JSON APIs return the array within another element. eg, if the objects are returned in a \'data\' element you should specify it in this field.</div>',
				form_input(
					array(
						'name' => 'path',
						'id' => 'path',
						'value' => $this->get_value( $values, "path" ),
						'size' => '50'
						)
					)
				)
			);
	
		return $form;
	}
	
	function fetch() {

		// Read JSON file
		$data = @file_get_contents( $this->settings["filename"] );
		if( $data === FALSE ) {
			$this->errors[] = "Cannot open file/url: " . $this->settings["filename"];
			return -1;
		}

		if( function_exists('json_decode') === FALSE ) {
			$this->EE->load->library('Services_Json');
		}
		$json_obj = json_decode( $data );
		$json_array = (array)$json_obj;
		if( count($json_array) == 0) {
			$this->errors[] = "Cannot parse JSON in file/url: " . $this->settings["filename"];
			return -1;
		}

		// Parse JSON in array $this->items
		if( $this->settings["path"] != "" ) {
			$this->items = $json_array[ $this->settings["path"] ];			
		} else {
			$this->items = $json_array;
		}
	}

	function next() {

		$item = current( $this->items );
		next( $this->items );

		if ( $item == FALSE ) {
			return FALSE;
		}

		$new = array();
		$item = $this->_parse_json_into_array( $item, $new );

		return $new;
	}
	
	function fetch_columns() {

		$this->fetch();
		$columns = $this->next();

		while( $item = $this->next() ) {
			$columns = array_merge( $columns, $item );
		}

		$titles = array();
		$count = 0;
		foreach( $columns as $idx => $title ) {
			if( substr( $idx, -1, 1) != "#" ) {
				if ( strlen( $title ) > 32 ) {
					$title = substr( htmlspecialchars($title), 0, 32 ) . "...";
				}
				$titles[ $idx ] = $idx . " - eg, " . $title;
			}
		}

		return $titles;
	}
	
	function initialise_sub_item( $item, $id, $config, $field ) {
		$this->sub_item_ptr = 0;
		return TRUE;
	}
	
	function get_sub_item( $item, $id, $config, $field ) {
		
		$this->sub_item_ptr++;
		
		$no_elements = $this->get_item( $item, $id . "#" );
		
		if( $no_elements == "" || $this->sub_item_ptr > $no_elements ) {
			return FALSE;
		}
		
		if ( $this->sub_item_ptr > 1 ) {
			$suffix = '#' . $this->sub_item_ptr;
		} else {
			$suffix = '';
		}
		
		return $this->get_item( $item, $id . $suffix );
	}
	
	/* Private functions */
	
	function _parse_json_into_array( $json_obj, &$result, $prefix="" ) {

		$json_array = (array)$json_obj;

		foreach( $json_array as $key => $value ) {
			$newkey = $prefix."/".$key;
			$newkey = trim( $newkey, "/" );
			if( is_object( $value ) ){
				//print_r( $value );
				$result[ $newkey ] = $this->_parse_json_into_array( $value, $result, $newkey );
			} else {
				if ( is_array( $value ) ) {
					$result[ $newkey ] = $this->_parse_json_into_array( $value, $result, $newkey );
				} else {
					$result[ $newkey ] = $value;
				}
			}
		}

		// return $json_array;
	}
		
}

?>