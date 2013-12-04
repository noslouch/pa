<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
*	======================
*	function clean_data
*	======================
*	Cleans up Zenbu-specialized export data (eg. invisible fields, &nbsp;'s, etc) that
*	makes little sense to keep on export
*	@param	string	$data	The data string, uncleaned
*	@return	string 	$data 	The cleaned data 
*/
function clean_data($data)
{
	// Return empty trying if only a space is present. No need to pollute output with these.
	if($data == '&nbsp;')
	{
		$data = '';
	}

	// Remove any invisible spans that might have been left out.
	$data = preg_replace('/<span(.*?)invisible(.*?)<\/span>/', '', $data);
	
	//	----------------------------------------
	//	Multibyte functions
	//	----------------------------------------
	if(function_exists('mb_convert_encoding'))
	{
		// Change everything to entities.
		// What's already an entity stays put,
		// what's not an entity gets turned into one.
		$data = mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8');

		// Now that we're all in entities, revert everyone to readable characters!
		$data = mb_convert_encoding($data, 'UTF-8', 'HTML-ENTITIES');
	}

	$data = trim($data);

	return $data;
}


/**
 * ======================
 * function make_plain_text
 * ======================
 * Functions to make text... just text
 * @param  string $data The uncleaned string
 * @return string $data The cleaned string
 */
function make_plain_text($data)
{
	$data = strip_tags($data, '<img>');
	
	// Remove those pesky starting tabs and similar junk
	// Some applications actually display garbled characters
	// when non-printed junk is present
	$data = trim($data);
	
	return $data;
}

/**
 * ======================
 * function parse_filename
 * ======================
 * Convert EE date format string into date information
 * @param  string $filename The original filename, as entered by the user
 * @return string $output 	The converted filename
 */
function parse_filename($filename)
{
	$EE =& get_instance();
	
	// Check if string has an EE-readable date character (%)
	// If not, return as-is, or else string gets parsed as a Unix time string. 
	$parse = preg_match("/%/u", $filename);

	if( ! $parse )
	{
		return $EE->security->sanitize_filename($filename);
	}

	
	$current_time = $EE->localize->now;
	if(version_compare(APP_VER, '2.6', '>'))
	{
		$output = $EE->localize->format_date($filename, $current_time);
	} else {
		$output = $EE->localize->decode_date($filename, $current_time);
	}

	return $EE->security->sanitize_filename($output);
}
	
?>