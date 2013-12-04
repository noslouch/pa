<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hokoku_pack extends Hokoku_mcp {

	function Hokoku_pack()
	{
		parent::Hokoku_mcp();
	}

	function _prepare_ordered_fields($vars)
	{
		$exp_order 			= isset($vars['field_order']) ? $vars['field_order'] : '';
		$exp_field_names 	= isset($vars['field']) ? $vars['field'] : '';

		$ordered_fields = array();
		if( ! empty($exp_order))
		{
			foreach($exp_order as $key => $item)
			{
				if(strncmp($item, "show_", 5) == 0)
				{
					if(isset($vars[$item]) && $vars[$item] == "y")
					{
						$ordered_fields[] = substr($item, 5);
					}
				} else {
					$field_id = str_replace('field_', '', $item);
					if(isset($exp_field_names[$field_id]))
					{
						$ordered_fields[] = $item;
					}
				}
			}
		}
		return $ordered_fields;
	}

	/**
	 * function pack_csv 	Prepares an CSV file for export based on fed Zenbu data
	 * @param  array  $vars         The data to export
	 * @param  integer $perpage     The current set ("page") of data to export
	 * @param  boolean $final_query Checks if this is the last export step before building final file
	 * @return array                Output messages and other info if needed
	 */
	function pack_csv($vars, $perpage = 0, $final_query = FALSE)
	{

		// Get default settings
		$exp_array			= isset($vars['entry']) ? $vars['entry'] : array();
		$exp_order			= isset($vars['field_order']) ? $vars['field_order'] : '';
		$exp_field_names	= isset($vars['field']) ? $vars['field'] : '';
		$profile_data		= $this->EE->session->cache('hokoku', 'profile_data');
		$output 			= array();

		// Get full path to file 
		$output_filename	= parse_filename($profile_data['export_filename']);
		$file_ext			= '.csv';
		$path				= $this->EE->hokoku_get->_get_cache_destination();
		$filename			= $path . $output_filename . $file_ext;
		
		// Get extra settings
		$cell_delimiter		= $profile_data['export_settings']['delimiter'] ? $profile_data['export_settings']['delimiter'] : 'TAB';
		$cell_delimiter		= $cell_delimiter == 'TAB' ? "\t" : $cell_delimiter;
		$cell_enclosure		= $profile_data['export_settings']['enclosure'] ? $profile_data['export_settings']['enclosure'] : '"';

		if( is_file($filename) && $perpage == 0)
		{
			unlink($filename);
		}

		$ordered_fields = $this->_prepare_ordered_fields($vars);	

		foreach($exp_array as $entry_id => $data_array)
		{
			// Create headers if no file exists yet,
			// if not, just read the file contents and continue building
			if( ! is_file($filename))
			{
				$header_data = '';

				foreach($ordered_fields as $k => $col)
				{
					$header_data_to_add = $col;
					$header_data_to_add = strncmp($header_data_to_add, 'field_', 6) == 0 && isset($exp_field_names[substr($header_data_to_add, 6)]) ? $exp_field_names[substr($header_data_to_add, 6)] : $header_data_to_add;
					$cell_delim = empty($header_data) ? '' : $cell_delimiter;
					$header_data .= $cell_delim . $cell_enclosure . clean_data($header_data_to_add) . $cell_enclosure;
				}
			
				$file_contents = $header_data . NL;
			
			} else {

				$file_contents = ''.NL;
			
			}

			

			$data = '';
			foreach($ordered_fields as $k => $col)
			{
				if(strncmp($col, 'field_', 6) == 0)
				{
					$field_id = substr($col, 6);
					$data_to_add = isset($data_array['fields'][$field_id]) ? $data_array['fields'][$field_id] : ' ';
				} else {
					$data_to_add = $data_array[$col];
				}
				
				// Don't start the row with a cell delimiter
				$cell_delim = empty($data) ? '' : $cell_delimiter;

				// Clean up text. Also converts entities to characters. Looking at you, double-quotes.
				$data_to_add = make_plain_text(clean_data($data_to_add));

				// Double the enclosure character - used to ""escape"" cells with the same character as the enclosure
				$data_to_add = str_replace($cell_enclosure, $cell_enclosure.$cell_enclosure, $data_to_add);

				// Assemble the cell: delimiters, enclosures and all
				$data .= $cell_delim . $cell_enclosure . $data_to_add . $cell_enclosure;
				
			}
			
			$data = $file_contents . $data;

			if ( ! write_file($filename, $data, 'a+'))
			{
				return $output['message'] = 'Failure when trying to write to file.';
			} else {
				$output['message'] = 'Success writing to file.';
			}
			
		}



		//	-------------------------------
		// 	Make this file Excel-compatible
		//	-------------------------------
		//	Must be done after the "final" file is complete and
		//	settings have cues to make the file Excel-compatible
		if($final_query !== FALSE && $cell_delimiter == "\t")
		{

			// Excel needs these conditions to open without
			// having a fit or spurt garbled non-alphabet characters:
			// - Add a Byte Order Mark, convert to UTF-16LE, and be tag-separated

			// First, get the file contents
			$data = read_file($filename);

			// Add a Byte Order Mark (BOM) at the beginning of the file.
			$bom = chr(255) . chr(254); // Represents: FF FE
			
			if(function_exists('mb_convert_encoding'))
			{
				// Convert string from UTF-8 to UTF-16 Little Endian, which Excel can open.
				$data = $bom . mb_convert_encoding( $data, 'UTF-16LE', 'UTF-8');
			}

			if ( ! write_file($filename, $data, 'wb'))
			{
				return $output['message'] = 'Failure when trying to write to file. (Excel compatibility mode)';
			} else {
				$output['message'] = 'Success writing to file. (Excel compatibility mode)';
			}

		}

		return $output;
	}

	/**
	 * function pack_html 	Prepares an HTML file for export based on fed Zenbu data
	 * @param  array  $vars         The data to export
	 * @param  integer $perpage     The current set ("page") of data to export
	 * @param  boolean $final_query Checks if this is the last export step before building final file
	 * @return array                Output messages and other info if needed
	 */
	function pack_html($vars, $perpage, $final_query)
	{
		// Get default settings
		$exp_array			= isset($vars['entry']) ? $vars['entry'] : array();
		$exp_order			= isset($vars['field_order']) ? $vars['field_order'] : '';
		$exp_field_names	= isset($vars['field']) ? $vars['field'] : '';
		$profile_data		= $this->EE->session->cache('hokoku', 'profile_data');

		// Get full path to file 
		$output_filename	= parse_filename($profile_data['export_filename']);
		$file_ext			= '.html';
		$path				= $this->EE->hokoku_get->_get_cache_destination();
		$filename			= $path . $output_filename . $file_ext;

		if( is_file($filename) && $perpage == 0)
		{
			unlink($filename);
		}

		$ordered_fields = $this->_prepare_ordered_fields($vars);

		foreach($exp_array as $entry_id => $data_array)
		{
			if( ! is_file($filename))
			{
				$header_data = '';
				foreach($ordered_fields as $k => $col)
				{
					$header_data_to_add = $col;
					$header_data_to_add = strncmp($header_data_to_add, 'field_', 6) == 0 && isset($exp_field_names[substr($header_data_to_add, 6)]) ? $exp_field_names[substr($header_data_to_add, 6)] : $header_data_to_add;
					$header_data .= "\t" . "\t" . '<th>' . clean_data($header_data_to_add) . '</th>' . NL;
				}
				$file_contents = '<table>' . NL.NL . "\t" . '<tr>' . NL . $header_data . "\t" . '</tr>' . NL . NL;
			} else {
				$file_contents = ''.NL;
			}

			

			$data = '';
			
			foreach($ordered_fields as $k => $col)
			{
				if(strncmp($col, 'field_', 6) == 0)
				{
					$field_id = substr($col, 6);
					//$data_to_add = isset($data_array['fields'][$field_id]) ? '<td>' . clean_data($data_array['fields'][$field_id]) . '</td>' : '<td> </td>';

					
					$data_to_add = isset($data_array['fields'][$field_id]) ? "\t" . "\t" . '<td>' . make_plain_text(clean_data($data_array['fields'][$field_id])) . '</td>' . NL : "\t" . "\t" . '<td> </td>' . NL;

				} else {
					//$data_to_add = '<td>' . clean_data($data_array[$col]) . '</td>';

					$data_to_add = "\t" . "\t" . '<td>' . make_plain_text(clean_data($data_array[$col])) . '</td>' . NL;

				}

				$data .= $data_to_add;
			}
			
			$data = $file_contents . "\t" . '<tr>' . NL . $data . "\t" . '</tr>' . NL;
			
			if ( ! write_file($filename, $data, 'a+'))
			{
				$output['message'] = 'fail';
			} else {
				$output['message'] = 'success';
			}

		}

		/**
		*	Add the closing </table>...
		*/
		$output['message'] = '';

		if($final_query === TRUE)
		{
			if ( ! write_file($filename, NL . '</table>', 'a+'))
			{
				$output['message'] = 'fail';
			} else {
				$output['message'] = 'success';
			}
		}

		return $output;
	}

	/**
	 * function pack_json 	Prepares a JSON file for export based on fed Zenbu data
	 * @param  array  $vars         The data to export
	 * @param  integer $perpage     The current set ("page") of data to export
	 * @param  boolean $final_query Checks if this is the last export step before building final file
	 * @return array                Output messages and other info if needed
	 */
	function pack_json($vars, $perpage)
	{

		// Get default settings
		$exp_array			= isset($vars['entry']) ? $vars['entry'] : array();
		$exp_order			= isset($vars['field_order']) ? $vars['field_order'] : '';
		$exp_field_names	= isset($vars['field']) ? $vars['field'] : '';
		$profile_data		= $this->EE->session->cache('hokoku', 'profile_data');

		// Get full path to file 
		$output_filename	= parse_filename($profile_data['export_filename']);
		$file_ext			= '.json';
		$path				= $this->EE->hokoku_get->_get_cache_destination();
		$filename			= $path . $output_filename . $file_ext;

		if( read_file($filename) && $perpage == 0)
		{
			unlink($filename);
		}

		$ordered_fields = $this->_prepare_ordered_fields($vars);

		if( read_file($filename) )
		{
			$data = read_file($filename);
			$data = json_decode($data);
		} else {
			$data = new stdClass();
		}

		foreach($exp_array as $entry_id => $data_array)
		{
			foreach($ordered_fields as $k => $col)
			{
				if(strncmp($col, 'field_', 6) == 0)
				{
					$field_id = substr($col, 6);
					//$data_to_add = isset($data_array['fields'][$field_id]) ? '<td>' . clean_data($data_array['fields'][$field_id]) . '</td>' : '<td> </td>';

					$data->$entry_id->$col = isset($data_array['fields'][$field_id]) ? make_plain_text(clean_data($data_array['fields'][$field_id])) : '';

				} else {
					//$data_to_add = '<td>' . clean_data($data_array[$col]) . '</td>';
					
					$data->$entry_id->$col = make_plain_text(clean_data($data_array[$col]));
							
				}	
			}
		}

		$data = json_encode($data);
			
		/**
		*	Writing...
		*/
		if ( ! write_file($filename, $data, 'wt'))
		{
			$output['message'] = 'fail';
		} else {
			$output['message'] = 'success';
		}

		return $output;
	}

}
?>