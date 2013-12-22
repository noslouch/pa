<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @version		1.1.2
 * @filesource 	./system/expressionengine/third_party/export_it/
 */
 
 /**
 * Export It - Excel Creation Class
 *
 * Takes an array and converts it to tab seperated XLS file
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_data/Export_xls.php
 */
 class Export_xls
{
	/**
	 * The keys used for the columsn.
	 * Since some data won't have matching keys we have to keep track of things
	 * @var array
	 */
	public $keys = array();
	
	/**
	 * Contains the expected structure to use for the export data
	 * @var array
	 */
	public $arr_structure = array();
	
	public function __construct()
	{
	
	}
	
	/**
	 * Wrapper to handle creation
	 * @param array $arr
	 * @param bool $keys_as_headers
	 * @param string $file_name
	 * @return string
	 */
	public function create(array $arr, $keys_as_headers = TRUE, $file_name = 'download.txt')
	{		
		$arr = $this->make_non_nested($arr);
		if(is_array($arr) && count($arr) >= 1)
		{
			$rows = array();
			$cols = array_keys($arr['0']);
			foreach($arr AS $key => $value)
			{
				foreach($value AS $k => $v)
				{
					foreach($this->keys AS $master)
					{
						if($k == $master)
						{
							$value[$k] = $this->escape_csv_value($v, "\t");
							break;
						}
						else
						{
							$value[$k] = '';
						}
					}
				}
				
				$rows[] = implode("\t", $value);
			}

			$data = implode("\t", $this->keys)."\n";
			$data .= implode("\n", $rows);
			
			return $data;
		}
	}	

	/**
	 * Takes an array and flattens it recursively
	 * @param array $out
	 * @param string $key
	 * @param array $in
	 */
	public function make_non_nested_recursive(array &$out, $key, array $in)
	{		
		foreach($in as $k=>$v)
		{
			if(is_array($v))
			{
				$this->make_non_nested_recursive($out, $key . $k . '_', $v);
			}
			else
			{
				$new_key = $key . $k;
				$out[$new_key] = $v;
				if(!in_array($new_key, $this->keys))
				{
					$this->keys[] = $new_key;
				}				
			}
		}
	}
	
	/**
	 * Handles the non nested portions of an array conversion
	 * @param array $in
	 * @return multitype:multitype:
	 */
	public function make_non_nested(array $in)
	{
		$out = array();
		$count = count($in);
		$return = array();
		
		//first we have to ensure the structure is identical for all array elements
		foreach($in AS $item)
		{
			$this->arr_structure = $this->add_missing_keys($this->empty_array_val($item), $this->arr_structure);
		}

		//now we flatten everything up
		for($i=0;$i<$count;$i++)
		{
			$in[$i] = $this->add_missing_keys($this->arr_structure, $in[$i]);
			$this->make_non_nested_recursive($out, '', $in[$i]);
			$return[$i] = $out;
			unset($in[$i]);
		}
		
		return $return;
	}
	
	public function setup_arr_structure($arr)
	{
		$return = array();
		$missing = array_diff_assoc($arr, $this->arr_structure);
		foreach($missing AS $key => $value)
		{				
			$return[$key] = $missing+$this->arr_structure;
		}
		return $return;
	}
	
	/**
	 * Takes an array and empties all values recursively
	 * @param multitype:array $arr
	 * @return multitype:string NULL
	 */
	public function empty_array_val($arr)
	{
		$return = array();
		foreach($arr AS $key => $value)
		{
			if(is_array($value))
			{
				$return[$key] = $this->empty_array_val($value);
			}
			else
			{
				$return[$key] = '';
			}
		}
		
		return $return;
	}
	
	/**
	 * Takes two arrays ensures all keys match from $master to $slave recursively
	 * @param multitype:array $arr
	 * @return multitype:string NULL
	 */
	public function add_missing_keys($master, $slave)
	{
		$return = array();
		foreach($master AS $key => $value)
		{
			if(!isset($slave[$key]))
			{
				$return[$key] = $master[$key];
				continue;
			}
			
			if(is_array($value))
			{
				$return[$key] = $this->add_missing_keys($value, $slave[$key]);
			}
			else
			{
				$return[$key] = $slave[$key];
			}
		}
	
		return $return;
	}	

	/**
	 * Escapes all the values for use in an XLS file
	 * @param string $value
	 * @param string $delim
	 * @return string|mixed
	 */
	public function escape_csv_value($value, $delim = ',')
	{
		$value = str_replace('"', '""', $value);
		if(preg_match('/'.$delim.'/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value))
		{
			return '"'.$value.'"';
		}
		else
		{
			return $value;
		}
	}	
}