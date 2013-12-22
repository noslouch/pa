<?php

/**
 * DataGrab XML import class
 *
 * Allows XML imports
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Ajw_xml extends Datagrab_type {

	var $datatype_info = array(
		'name' => 'XML',
		'version' => '0.1',
		'allow_comments' => TRUE,
		'allow_subloop' => TRUE
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
				form_label('XML path', 'path')  .
				'<div class="subtext">The path within the XML to the element you want to import (eg, /rss/channel/item). Note: this is <b>not</b> the path to the file.</div>',
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

		$xml = @file_get_contents( $this->settings["filename"] );
		if( $xml === FALSE ) {
			$this->errors[] = "Cannot open file/url: " . $this->settings["filename"];
			return -1;
		}

		/*
		if ( function_exists('curl_init'))
		{
			$xml = $this->_curl_fetch( $this->settings["filename"] ); 
		} else {
			$xml = $this->_fsockopen_fetch( $this->settings["filename"] );
		}
		*/

		$this->EE->load->library('xmlparser'); 
		$xml_obj = $this->EE->xmlparser->parse_xml( $xml );

		if ( $xml_obj === FALSE ) {
			$this->errors[] = "Cannot parse the XML from file/url: " . $this->settings["filename"];
			return -1;
		}
				
		$this->items = array();
		$this->_fetch_xml( $xml_obj, $this->settings["path"], $this->items );

		if ( $this->items == "" ) {
			$this->errors[] = "Please check the path is correct: " . $this->settings["path"];
			return -1;
		}

	}

	function next() {

		$item = current( $this->items );
		next( $this->items );

		return $item;
		
	}
	
	function fetch_columns() {
		$this->fetch();
		$columns = $this->next();

		while( $item = $this->next() ) {
			$columns = array_merge( $columns, $item );
		}

		if ( !is_array($columns) ) {
			$this->errors[] = "Cannot find any data. Is the XML path correct?";
			return FALSE;
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
		
		$no_elements = $this->get_item( $item, $id . "#", FALSE );
		if( $no_elements === FALSE ) {
			$no_elements = 99;
		}
		
		if( $no_elements == "" ) {
			$no_elements = 1;
		}
		
		if( $this->sub_item_ptr > $no_elements ) {
			return FALSE;
		}
		
		$new_id = $id;
		if ( $this->sub_item_ptr > 1 ) {
			if( strpos($id, '@') ) {
				$parts = explode("@", $id);
				$new_id = $parts[0] . '#' . $this->sub_item_ptr . '@' . $parts[1];
			} else {
				$new_id = $id . '#' . $this->sub_item_ptr;
			}
		} else {
			$new_id = $id;
		}
		
		// print "<p>$new_id - " . $this->get_item( $item, $new_id, FALSE ) . "</p>";
				
		return $this->get_item( $item, $new_id, FALSE );
	}
	
	/* Private functions */
	
	function _fetch_xml( $x, $search, &$items, $path="", $element=0, $in_element=false, $subpath="" ) {

		$path = $path . "/" . $x->tag ;

		//print "@" . $search . "@ v @" . $path . "@<br/>";

		if ( $path == $search ) {

			// Path matches exactly our search element - we are in a new item
			$element++;
			$items[ $element ] = array();		
			$subpath = "";
			if ( is_array( $x->attributes ) ) {
				foreach ( $x->attributes as $attr_key => $attr_value ) {
					$items[ $element ][ $subpath . "@" . $attr_key ] = $attr_value;
				}
			}
			$in_element = true;

		} elseif ( $str = strstr( $path, $search ) ) {
			
			// We are within an existing item  - get xpath of subcomponent
			$subpath = substr( $str, strlen( $search )+1 );
			if ( ! isset( $items[ $element ][ $subpath . "#" ] ) ) {
				$items[ $element ][ $subpath . "#" ] = 0;
			}
			$count = $items[ $element ][ $subpath . "#" ]++;
			if ( isset( $items[ $element ][ $subpath ] ) ) {
				$subpath .= "#" . ( $count + 1);
			}
		} else {
			$in_element = false;
		}

		if ( count( $x->children ) == 0 ) {

			// Element has children ie, is not a parent element
			if ( $in_element ) {
				// If within an item, add to its array
				$items[ $element ][ $subpath ] = $x->value;
			}
			
		} else {

			// Loop over all child elements...        
			foreach ( $x->children as $key => $value ) {
				// ...and recurse through xml structure
				$element = $this->_fetch_xml( $value, $search, $items, $path, $element, $in_element, $subpath );
			}

		}

		// Add attributes
		if( $in_element ) {
			if ( is_array( $x->attributes ) ) {
				foreach ( $x->attributes as $attr_key => $attr_value ) {
					$items[ $element ][ $subpath . "@" . $attr_key ] = $attr_value;
				}
			}
		}

		return $element;
	}
	
	function _curl_fetch($url)
	{
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}

	function _fsockopen_fetch($url)
	{
		$target = parse_url($url);

		$data = '';

		$fp = fsockopen($target['host'], 80, $error_num, $error_str, 8); 

		if (is_resource($fp))
		{
			fputs($fp, "GET {$url} HTTP/1.0\r\n");
			fputs($fp, "Host: {$target['host']}\r\n");
			fputs($fp, "User-Agent: EE/xmlgrab PHP/" . phpversion() . "\r\n\r\n");

			$headers = TRUE;

			while( ! feof($fp))
			{
				$line = fgets($fp, 4096);

				if ($headers === FALSE)
				{
					$data .= $line;
				}
				elseif (trim($line) == '')
				{
					$headers = FALSE;
				}
			}

			fclose($fp); 
		}

		return $data;
	}
	
}

?>