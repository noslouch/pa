<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
*	======================
*	function load_ft_class
*	======================
*	Checks if Zenbu has support for a specific fieldtype and loads the class.
*	If the fieldtype is not bundled with Zenbu, tries to load fieldtype class from original file
*	@param	string	$ft_name	The name of the fieldtype class
*	@return	void 
*/
function load_ft_class($ft_name)
{
	$ft_class		= ucfirst($ft_name);	// My_field_ft
	$ft_name		= substr($ft_name, 0, -3); // my_field
	$ft_filename	= $ft_name . '.php'; // my_field.php
	
	// if( ! class_exists($ft_class))
	// {
		$EE =& get_instance();
		$EE->load->library('api');
		$EE->load->helper('file'); 
		$EE->api->instantiate('channel_fields');
		$EE->api_channel_fields->include_handler($ft_name);
		
		if(read_file(PATH_THIRD.'zenbu/fieldtypes/'.$ft_filename) !== FALSE)
		{	
			require_once PATH_THIRD.'zenbu/fieldtypes/'.$ft_filename;
		}
	// }	
}


/**
 * =========================
 * function create_object
 * =========================
 * Check if the object we want to create is from a
 * Zenbu fieltype class (where the cool functions are).
 * If not, create an object from the official fieldtype class
 * (and hope they added cool Zenbu functions)
 * @param  string $ft_class 	The class name
 * @return object $ft_object 	The created object from the fieldtype class
 */
function create_object($ft_class)
{
	if(class_exists('zenbu_'.$ft_class))
	{
		$ft_class = 'zenbu_'.$ft_class;
		$ft_object 	= new $ft_class();
	} else {
		$ft_object 	= new $ft_class();
	}
	return $ft_object;
}


/**
 * ======================
 * function find_rule
 * ======================
 * Checks for the presence of a specific rule in Zenbu
 * @param  string 	$type  The type of rule element. Usually 'field', 'cond' or 'val'
 * @param  string 	$value The value of the rule element
 * @param  array 	$rules The passed Zenbu rules
 * @return bool
 */
function find_rule($type, $value, $rules)
{
	foreach ($rules as $key => $rule_arr)
	{
		if(isset($rule_arr[$type]) && $rule_arr[$type] == $value)
		{
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * ======================
 * function get_action_id
 * ======================
 * Checks for the presence of a specific rule in Zenbu
 * @param  string 	$type  The type of rule element. Usually 'field', 'cond' or 'val'
 * @param  string 	$value The value of the rule element
 * @param  array 	$rules The passed Zenbu rules
 * @return bool
 */
function get_action_id($class, $method)
{
	$EE =& get_instance();

	// Return data if already cached
	if($EE->session->cache('zenbu', 'get_action_id_'.$method))
	{
		return $EE->session->cache('zenbu', 'get_action_id_'.$method);
	}

	$action_id = "";
	$EE->db->from("actions");
	$EE->db->where("actions.class", $class);
	$EE->db->where("actions.method", $method);
	$action_id_query = $EE->db->get();
	
	if($action_id_query->num_rows() > 0)
	{
		foreach($action_id_query->result_array() as $row)
		{
			$action_id = $row['action_id'];
		}
	}

	$EE->session->set_cache('zenbu', 'get_action_id_'.$method, $action_id);

	return $action_id;
}
	
?>