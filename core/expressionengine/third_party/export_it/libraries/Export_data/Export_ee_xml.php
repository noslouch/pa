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
 * Export It - Export EE XML Library
 *
 * Wrapper to create the ExpressionEngine XML Format
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_data/Export_ee_xml.php
 */
class Export_ee_xml
{
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function generate(array $arr)
	{
		$return = '';
		$return .= '<members>';
	    
	    foreach($arr AS $i => $item)
	    {
	    	$return .= '<member>';
	    	foreach($item AS $key => $value)
	    	{
	    		if($key == 'password')
	    		{
	    			$return .= '<password type="sha1"><![CDATA['.$value.']]></password>';
	    		}
	    		else
	    		{
	    			$return .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
	    		}
	    	}
	    	$return .= '</member>';
	    }

	    $return .= '</members>';	

	    return $return;
	}
}