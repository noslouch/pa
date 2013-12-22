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
 * Export It - Freeform Library Class
 *
 * Contains all the methods for interacting with Freeform
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Freeform_data.php
 */
class Freeform_data
{	
	/**
	 * Container for the fields so they're only processed once
	 * @var array
	 */
	private $channel_fields = array();
	
	/**
	 * Flag to disable translating custom fieldtypes. Will return limited dataset
	 * @var bool
	 */
	public $translate_cft = TRUE;
	
	/**
	 * Flag to include all fields from channel_titles
	 * @var bool
	 */
	public $complete_select = FALSE;	
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.'freeform/');
		$this->EE->load->model('freeform_form_model');
		$this->EE->load->model('freeform_entry_model');
		$this->EE->load->model('freeform_field_model');
		$this->EE->load->library('freeform_forms');
		$this->EE->load->library('freeform_fields');		
	}
	
	/**
	 * Returns a form by a given form_name
	 * @param string $name
	 */
	public function get_form_by_name($name)
	{
		return $this->get_forms(array('form_name' => $name));
	}
	
	/**
	 * Returns a form by a given form_id
	 * @param string $name
	 */
	public function get_form($form_id)
	{
		return $this->get_forms(array('form_id' => $form_id));
	}

	/**
	 * Returns the forms by a given $where
	 * @param string $name
	 */
	public function get_forms($where = FALSE)
	{
		if($where)
		{
			$this->gen_where($where);
		}
		
		$data = $this->EE->db->get('freeform_forms')->row_array();
		$data['field_ids'] = explode('|', $data['field_ids']);

		$this->form_data[$data['form_id']] = $data;
		return $data;
	}
	
	/**
	 * Returns a form field by a given field_name
	 * @param string $name
	 */
	public function get_field_by_name($name)
	{
		$this->EE->db->where('field_name', $name);
		return $this->EE->db->get('freeform_fields');
	}
	
	/**
	 * Returns a form field by a given field_id
	 * @param int $id
	 */
	public function get_field($id)
	{
		$this->EE->db->where('field_id', $id);
		
		return $this->EE->db->get('freeform_fields');
	}
	
	/**
	 * Returns the form fields based on $where
	 * @param mixed $where
	 */	
	public function get_fields($where = FALSE)
	{
		if($where)
		{
			$this->gen_where($where);
		}
		
		$fields = $this->EE->db->get('freeform_fields')->result_array();
		$return = array();
		foreach($fields As $field)
		{
			$return[$field['field_id']] = $field;
		}
		return $return;
	}
	
	public function set_entries_table($form_id)
	{
		return 'freeform_form_entries_'.$form_id;
	}
	
	/**
	 * Returns the amount of entries based on $where
	 * @param mixed $where
	 */
	public function get_total_entries($form_id, $where = FALSE)
	{
		$table = $this->set_entries_table($form_id);
		$this->EE->db->select("COUNT(entry_id) AS count ");
		$this->EE->db->from($table.' entries');	
		if($where)
		{
			$this->gen_entry_where($where);
		}
		
		$data = $this->EE->db->get();
		if($data->num_rows == '1')
		{
			return $data->row('count');
		}		
	}
	
	/**
	 * Returns the entries
	 * @param mixed $where
	 */
	public function get_entries($form_id, $where = FALSE, $limit = FALSE, $page = '0', $order = 'entry_date DESC')
	{
		$table = $this->set_entries_table($form_id);
		if(isset($where['search']) && !$this->channel_field_ids)
		{
			$this->channel_field_ids = $this->get_channel_field_ids();
		}
				
		$this->EE->db->select("*");
		$this->EE->db->from($table.' entries');

		if($where)
		{
			$this->gen_entry_where($where);
		}
		
		if($limit)
		{
			$this->EE->db->limit($limit, $page);
		}
	
		if($order)
		{
			$this->EE->db->order_by($order);
		}

		$data = $this->EE->db->get();
		//echo $this->EE->db->last_query();
		return $this->translate_custom_fields($form_id, $data->result_array());
	}
	
	public function translate_custom_fields($form_id, $entries)
	{
		$field_where = array('field_id' => $this->form_data[$form_id]['field_ids']);
		$field_data = $this->get_fields($field_where);		
		foreach($field_data AS $key => $value)
		{
			$name = $value['field_type'];
			$this->field_api[$value['field_id']] = $this->EE->freeform_fields->instantiate_fieldtype($name);
			$this->field_api[$value['field_id']]->settings = json_decode($value['settings'], TRUE);
		}	
		
		$count = 1;
		foreach($entries AS $key => $entry)
		{
			foreach($this->form_data[$form_id]['field_ids'] AS $field_id)
			{
				if(isset($entries[$key]['form_field_'.$field_id]))
				{
					$entries[$key][$field_data[$field_id]['field_name']] = $this->field_api[$field_id]->replace_tag($entries[$key]['form_field_'.$field_id]);
					unset($entries[$key]['form_field_'.$field_id]);
				}
			}
			$count++;
		}
		
		return $entries;
	}
	
	/**
	 * Abstracts creation of the SQL WHERE
	 * @param array $where
	 */
	public function gen_entry_where($where)
	{
		if(isset($where['date_range']) && $where['date_range'] != 'custom_date')
		{
			if(is_numeric($where['date_range']))
			{
				$this->EE->db->where('entry_date >', (mktime()-($where['date_range']*24*60*60)));
			}
			else
			{
				$parts = explode('to', $where['date_range']);
				if(count($parts) == '2')
				{
					$start = strtotime($parts['0']);
					$end = strtotime($parts['1']);
					$where_date = " entry_date BETWEEN '$start' AND '$end'";
					$this->EE->db->where($where_date, null, FALSE);
				}
			}
	
			unset($where['date_range']);
		}
	
		if(isset($where['search']))
		{
			$search_where = array('ct.title', 'channel_title');
			$cols = array();
			foreach($search_where AS $field)
			{
				$cols[] = $field." LIKE '%".$where['search']."%'";
			}
				
			if(count($this->channel_field_ids) >= '1')
			{
				foreach($this->channel_field_ids AS $field)
				{
					$cols[] = 'field_id_'.$field." LIKE '%".$where['search']."%'";
				}
			}
				
			if(count($cols) >= 1)
			{
				$str_where = " (".implode(' OR ', $cols).") ";
				$this->EE->db->where($str_where, FALSE, FALSE);
			}
	
			unset($where['search']);
		}
	
		$this->gen_where($where);
	}
	
	/**
	 * An reusable abstraction for the creation of the SQL WHERE outside of
	 * EE channel entries.
	 * @param array $where
	 */
	public function gen_where($where)
	{
		if(is_array($where) && count($where) >= '1')
		{
			foreach($where AS $key => $value)
			{
				if(!is_array($value))
				{
					$this->EE->db->where($key, $value);
				}
				else
				{
					$this->EE->db->where_in($key, $value);
				}
			}
		}
		elseif(is_string($where))
		{
			$this->EE->db->where($where);
		}
	}	
}