<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Factory Class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */

class Ce_cache_factory
{
	public static $valid_drivers = array( 'file', 'db', 'static', 'apc', 'memcache', 'memcached', 'redis', 'dummy' );

	/**
	 * @static
	 * @param array $drivers An array of drivers for the factory to return.
	 * @param bool $auto_add_dummy Should the 'dummy' driver automatically be included if not specified?
	 * @return array
	 */
	public static function factory( $drivers = array(), $auto_add_dummy = false )
	{
		if ( empty( $drivers ) )
		{
			$drivers = array();
		}

		//was a single driver passed in instead of a string?
		if ( is_string( $drivers ) )
		{
			//turn it into an array
			$drivers = array( $drivers );
		}

		if ( is_array( $drivers ) )
		{
			//make sure the drivers are valid
			$temps = $drivers;
			$drivers = array();

			foreach ( $temps as  $temp )
 			{
 				if ( in_array( strtolower( $temp ), self::$valid_drivers ) )
				{
					$drivers[] = strtolower( $temp );
				}
			}
			unset( $temps );
		}
		else
		{
			$drivers = arrays();
		}

		if ( $auto_add_dummy )
		{
			//make sure the dummy key exists
			if ( false !== $key = array_search( 'dummy', $drivers ) ) //dummy driver present
			{
				//just grab the array up to the dummy driver, as there is no need to include any additional drivers
				$drivers = array_splice( $drivers, 0, $key + 1 );
			}
			else
			{
				//add the dummy driver
				$drivers[] = 'dummy';
			}
		}

		$final = array();

		//include the driver base class
		if ( ! class_exists( 'Ce_cache_driver' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/drivers/Ce_cache_driver.php';
		}

		//load the drivers
		foreach ( $drivers as $driver )
		{
			$class = 'Ce_cache_' . $driver;

			//include the drivers if needed
			if ( ! class_exists( $class ) )
			{
				$path = PATH_THIRD . "ce_cache/libraries/drivers/{$class}.php";

				if ( file_exists( $path ) ) //we found the file
				{
					//include the driver
					include $path;
				}
				else //we could not find the driver
				{
					//skip on to the next driver
					continue;
				}
			}

			//instantiate the class
			$temp = new $class;

			//check if the class is supported
			if ( $temp->is_supported() ) //it is supported
			{
				//include the class in the final drivers array
				$final[] = $temp;
			}
		}

		//return the final array of drivers
		return $final;
	}

	public static function is_supported( $driver )
	{
		$driver = strtolower( $driver );

		//make sure the driver is valid
		if ( ! in_array( $driver, self::$valid_drivers ) )
		{
			return false;
		}

		//include the driver base class
		if ( ! class_exists( 'Ce_cache_driver' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/drivers/Ce_cache_driver.php';
		}

		//load the driver
		$class = 'Ce_cache_' . $driver;

		//include the drivers if needed
		if ( ! class_exists( $class ) )
		{
			$path = PATH_THIRD . "ce_cache/libraries/drivers/{$class}.php";

			if ( file_exists( $path ) ) //we found the file
			{
				//include the driver
				include $path;
			}
			else //we could not find the driver
			{
				return false;
			}
		}

		//instantiate the class
		$driver = new $class;

		//see if the driver is supported
		return $driver->is_supported();
	}
}