<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/export_it/
 */
 
 /**
 * Export It - JSON Library
 *
 * A wrapper to create JSON
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_data/Export_json.php
 */
class Export_json
{
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function generate(array $arr)
	{
		if(!isset($this->EE->javascript))
		{
			$this->EE->load->library('javascript');
		}
		return $this->EE->javascript->generate_json($arr);
	}
}