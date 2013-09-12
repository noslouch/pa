<?php

/**
 * DataGrab CSV import class
 *
 * Allows CSV imports
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Ajw_csv extends Datagrab_type {

	var $datatype_info = array(
		'name'		=> 'CSV',
		'version'	=> '0.1',
		'allow_subloop' => TRUE,
		'allow_multiple_fields' => TRUE
		);

	var $settings = array(
		"filename" => "",
		"delimiter" => "",
		"encloser" => "",
		"skip" => 0
		);
		
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
			form_label('Delimiter', 'delimiter') .
			'<div class="subtext">The character used to separate fields in the file. Use TAB for a tab-delimited file.</div>', 
			form_input(
				array(
					'name' => 'delimiter',
					'id' => 'delimiter',
					'value' => $this->get_value( $values, "delimiter" ),
					'size' => '4'
					)
				)
			),
		array( 
			form_label('Encloser', 'encloser') .
			'<div class="subtext">If in doubt, or if the data has no encloser, use the default "</div>', 
			form_input(
				array(
					'name' => 'encloser',
					'id' => 'encloser',
					'value' => $this->get_value( $values, "encloser" ),
					'size' => '4'
					)
				)
			),
		array(
			form_label('Use first row as titles', 'skip') .
			'<div class="subtext">Select this if the first row of the file contains titles and should not be imported</div>',
			form_checkbox('skip', '1', ( $this->get_value( $values, "skip" ) == 1 ? TRUE : FALSE ), ' id="skip"')
			)
		);

		return $form;
	}

	function fetch() {
		
		ini_set('auto_detect_line_endings', true);
		
		if ( !isset( $this->settings["filename"] ) ) {
			$this->errors[] = "You must supply a filename/url.";
			return -1;
		}

		// Open CSV file and save handle
		$this->handle = @fopen($this->settings["filename"], "r");

		if ( $this->handle === FALSE ) {
			$this->errors[] = "Cannot open the file/url: " . $this->settings["filename"] . ".  <br/>If you are trying to access this file 
			using http://, try accessing directly as a file.";
			return -1;
		}
		
	}

	function next() {
		
		if ( $this->settings['delimiter'] == '\t' OR $this->settings['delimiter'] == 'TAB' ) {
			$this->settings['delimiter'] = "\t";
		}

		if ( $this->settings['encloser'] == '' ) {
			$this->settings['encloser'] = '"';
		}

		// Get next line of CSV file
		$item = fgetcsv($this->handle, 10000, $this->settings["delimiter"], $this->settings["encloser"]);
		
		// Bug in fgetcsv, if the first character of a field is a special character it goes missing
		// $line = fgets($this->handle, 10000);
		// $item = $this->_csvstring_to_array($line, $this->settings["delimiter"], $this->settings["encloser"]);

		// print_r( $item );

		// Make sure empty rows are not used
		if( count( $item ) == 1 && empty( $item[ 0 ] ) ) {
			return FALSE;
		}

		return $item;
	}

	function fetch_columns() {
		
		// Get first line of file
		$this->fetch();
		$columns = $this->next();
		
		// Loop through fields, adding Column # and truncating any long labels
		$titles = array();
		$count = 0;
		foreach( $columns as $title ) {
			if ( strlen( $title ) > 32 ) {
				$title = substr( $title, 0, 32 ) . "...";
			}
			$titles[] = "Column " . ++$count . " - eg, " . $title;
		}

		return $titles;
		
	}

	function initialise_sub_item( $item, $id, $config, $field ) {
		// Reset sub loop
		$this->sub_item_ptr = 0;
		return TRUE;
	}
	
	function get_sub_item( $item, $id, $config, $field ) {
		
		// Find delimiter (if set)
		$delimiter = ",";
		if( isset( $config["cf"][ $field . "_delimiter" ] ) ) {
			$delimiter = $config["cf"][ $field . "_delimiter" ];
		}
	
		// Find item and split into sub items
		$item = $this->get_item( $item, $id );
		//$sub_items = explode($delimiter, $item);
		// print_r( $item );
		$sub_items = $this->_csvstring_to_array( $item, $delimiter, "'" );
		$no_elements = count($sub_items);
		
		// Return false if there are no items to return
		$this->sub_item_ptr++;
		if( $no_elements == "" || $this->sub_item_ptr > $no_elements ) {
			return FALSE;
		}
		
		// Return sub item
		return trim($sub_items[$this->sub_item_ptr - 1]);
	}

	function _csvstring_to_array($data, $delimiter = ',', $enclosure = '"', $newline = "\n"){

	        $pos = $last_pos = -1;
	        $end = strlen($data);
	        $row = 0;
	        $quote_open = false;
	        $trim_quote = false;
	
	        $return = array();
	
	        // Create a continuous loop
	        for ($i = -1;; ++$i){
	            ++$pos;
	            // Get the positions
	            $comma_pos = strpos($data, $delimiter, $pos);
	            $quote_pos = strpos($data, $enclosure, $pos);
	            $newline_pos = strpos($data, $newline, $pos);
	
	            // Which one comes first?
	            $pos = min(($comma_pos === false) ? $end : $comma_pos, ($quote_pos === false) ? $end : $quote_pos, ($newline_pos === false) ? $end : $newline_pos);
	
	            // Cache it
	            $char = (isset($data[$pos])) ? $data[$pos] : null;
	            $done = ($pos == $end);
	
	            // It it a special character?
	            if ($done || $char == $delimiter || $char == $newline){
	
	                // Ignore it as we're still in a quote
	                if ($quote_open && !$done){
	                    continue;
	                }
	
	                $length = $pos - ++$last_pos;
	
	                // Is the last thing a quote?
	                if ($trim_quote){
	                    // Well then get rid of it
	                    --$length;
	                }
	
	                // Get all the contents of this column
	                $return[$row][] = ($length > 0) ? str_replace($enclosure . $enclosure, $enclosure, substr($data, $last_pos, $length)) : '';
	
	                // And we're done
	                if ($done){
	                    break;
	                }
	
	                // Save the last position
	                $last_pos = $pos;
	
	                // Next row?
	                if ($char == $newline){
	                    ++$row;
	                }
	
	                $trim_quote = false;
	            }
	            // Our quote?
	            else if ($char == $enclosure){
	
	                // Toggle it
	                if ($quote_open == false){
	                    // It's an opening quote
	                    $quote_open = true;
	                    $trim_quote = false;
	
	                    // Trim this opening quote?
	                    if ($last_pos + 1 == $pos){
	                        ++$last_pos;
	                    }
	
	                }
	                else {
	                    // It's a closing quote
	                    $quote_open = false;
	
	                    // Trim the last quote?
	                    $trim_quote = true;
	                }
	
	            }
	
	        }
	
	        return $return[0];
	    }
}

?>