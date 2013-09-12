<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Reorder helper functions
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */

/**
 * Encode array to string
 *
 * @param      array     Array to encode
 * @return     string
 */
if ( ! function_exists('low_array_encode'))
{
	function low_array_encode($array = array())
	{
		return str_replace('/', '_', rtrim(base64_encode(serialize($array)), '='));
	}
}

// --------------------------------------------------------------------

/**
 * Decode string back to array
 *
 * @param      string    String to decode
 * @return     array
 */
if ( ! function_exists('low_array_decode'))
{
	function low_array_decode($str = '')
	{
		return (is_string($str) && strlen($str)) ? @unserialize(base64_decode(str_replace('_', '/', $str))) : FALSE;
	}
}

// --------------------------------------------------------------------

/**
* Encode settings for DB
*
* @param      array     Array to encode
* @return     string
*/
if ( ! function_exists('encode_reorder_settings'))
{
	function encode_reorder_settings($array = array())
	{
		return base64_encode(serialize($array));
	}
}

// --------------------------------------------------------------------

/**
* Decode string back to array, < v2.0.0
*
* @param      string    String to decode
* @return     array
*/
if ( ! function_exists('decode_reorder_settings'))
{
	function decode_reorder_settings($str = '')
	{
		$default_settings = array(
			'category_options' => 'all',
			'categories'       => FALSE,
			'statuses'         => FALSE,
			'show_expired'     => 'y',
			'show_future'      => 'y',
			'sort_order'       => 'asc',
			'permissions'      => array(),
			'clear_cache'      => 'n'
		);

		// Try to decode, fallback to empty array
		if ( ! ($settings = @unserialize(base64_decode($str))))
		{
			$settings = array();
		}

		// Merge default and given settings
		$settings = array_merge($default_settings, $settings);

		// Check category settings
		if ($settings['categories'] !== FALSE)
		{
			if ( in_array($settings['categories'], array('all', 'one')) )
			{
				$settings['category_options'] = $settings['categories'];
				$settings['categories'] = FALSE;
			}
			else
			{
				$settings['category_options'] = 'some';
				$settings['categories'] = (strlen($settings['categories'])) ? explode('|', $settings['categories']) : FALSE;
			}
		}

		// Check status settings
		if ($settings['statuses'] !== FALSE)
		{
			$settings['statuses'] = (strlen($settings['statuses'])) ? explode('|', $settings['statuses']) : FALSE;
		}

		return $settings;
	}
}

// --------------------------------------------------------------------

/**
 * Linearize array
 *
 * @param      array
 * @param      string
 * @return     string
 */
if ( ! function_exists('low_linearize'))
{
	function low_linearize($array = array(), $d = '|')
	{
		return (string) ($array ? $d.implode($d, $array).$d : '');
	}
}

/**
 * Delinearize string
 *
 * @param      string
 * @return     array
 */
if ( ! function_exists('low_delinearize'))
{
	function low_delinearize($string = '', $d = '|')
	{
		return (array) array_filter(explode($d, trim($string, $d)));
	}
}


if ( ! function_exists('low_multiselect_size'))
{
	function low_multiselect_size($size, $max = 10)
	{
		return sprintf(' size="%s"', ($size > $max ? $max : $size));
	}
}


// --------------------------------------------------------------------

/**
 * Flatten results
 *
 * Given a DB result set, this will return an (associative) array
 * based on the keys given
 *
 * @param      array
 * @param      string    key of array to use as value
 * @param      string    key of array to use as key (optional)
 * @return     array
 */
if ( ! function_exists('low_flatten_results'))
{
	function low_flatten_results($resultset, $val, $key = FALSE)
	{
		$array = array();

		foreach ($resultset AS $row)
		{
			if ($key !== FALSE)
			{
				$array[$row[$key]] = $row[$val];
			}
			else
			{
				$array[] = $row[$val];
			}
		}

		return $array;
	}
}

// --------------------------------------------------------------------

/**
 * Associate results
 *
 * Given a DB result set, this will return an (associative) array
 * based on the keys given
 *
 * @param      array
 * @param      string    key of array to use as key
 * @param      bool      sort by key or not
 * @return     array
 */
if ( ! function_exists('low_associate_results'))
{
	function low_associate_results($resultset, $key, $sort = FALSE)
	{
		$array = array();

		foreach ($resultset AS $row)
		{
			if (array_key_exists($key, $row) && ! array_key_exists($row[$key], $array))
			{
				$array[$row[$key]] = $row;
			}
		}

		if ($sort === TRUE)
		{
			ksort($array);
		}

		return $array;
	}
}

// --------------------------------------------------------------------

/**
 * Converts EE parameter to workable php vars
 *
 * @access     public
 * @param      string    String like 'not 1|2|3' or '40|15|34|234'
 * @return     array     [0] = array of ids, [1] = boolean whether to include or exclude: TRUE means include, FALSE means exclude
 */
if ( ! function_exists('low_explode_param'))
{
	function low_explode_param($str)
	{
		// --------------------------------------
		// Initiate $in var to TRUE
		// --------------------------------------

		$in = TRUE;

		// --------------------------------------
		// Check if parameter is "not bla|bla"
		// --------------------------------------

		if (strtolower(substr($str, 0, 4)) == 'not ')
		{
			// Change $in var accordingly
			$in = FALSE;

			// Strip 'not ' from string
			$str = substr($str, 4);
		}

		// --------------------------------------
		// Return two values in an array
		// --------------------------------------

		return array(preg_split('/(&+|\|)/', $str), $in);
	}
}

// --------------------------------------------------------------

/**
 * Get cache value, either using the cache method (EE2.2+) or directly from cache array
 *
 * @param       string
 * @param       string
 * @return      mixed
 */
if ( ! function_exists('low_get_cache'))
{
	function low_get_cache($a, $b)
	{
		$EE =& get_instance();

		if (method_exists($EE->session, 'cache'))
		{
			return $EE->session->cache($a, $b);
		}
		else
		{
			return (isset($EE->session->cache[$a][$b]) ? $EE->session->cache[$a][$b] : FALSE);
		}
	}
}

// --------------------------------------------------------------

/**
 * Set cache value, either using the set_cache method (EE2.2+) or directly to cache array
 *
 * @param       string
 * @param       string
 * @param       mixed
 * @return      void
 */
if ( ! function_exists('low_set_cache'))
{
	function low_set_cache($a, $b, $c)
	{
		$EE =& get_instance();

		if (method_exists($EE->session, 'set_cache'))
		{
			$EE->session->set_cache($a, $b, $c);
		}
		else
		{
			$EE->session->cache[$a][$b] = $c;
		}
	}
}

// --------------------------------------------------------------

/**
 * Zebra table helper
 *
 * @param       bool
 * @return      string
 */
if ( ! function_exists('low_zebra'))
{
	function low_zebra($reset = FALSE)
	{
		static $i = 0;

		if ($reset) $i = 0;

		return (++$i % 2 ? 'odd' : 'even');
	}
}
// --------------------------------------------------------------

/**
 * Debug
 *
 * @param       mixed
 * @param       bool
 * @return      void
 */
if ( ! function_exists('low_dump'))
{
	function low_dump($var, $exit = TRUE)
	{
		echo '<pre>'.print_r($var, TRUE).'</pre>';
		if ($exit) exit;
	}
}

// --------------------------------------------------------------

/* End of file low_reorder_helper.php */
