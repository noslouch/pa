<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Utilities class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */
class Ce_cache_utils
{
	/**
	 * Return the current site's label.
	 *
	 * @static
	 * @return string
	 */
	public static function get_site_label()
	{
		$EE = get_instance();

		$site = trim( $EE->config->item('site_label') );

		$EE->load->helper('security');
		$site = sanitize_filename( $site );

		if ( empty( $site ) )
		{
			$site = 'default_site';
		}

		$site = substr( md5( $site ), 0, 6 );

		return $site;
	}

	/**
	 * Removes double slashes, except when they are preceded by ':', so that 'http://', etc are preserved.
	 *
	 * @param string $str The string from which to remove the double slashes.
	 * @return string The string with double slashes removed.
	 */
	public static function remove_duplicate_slashes( $str )
	{
		return preg_replace( '#(?<!:)//+#', '/', $str );
	}
}