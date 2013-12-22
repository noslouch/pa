<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @since		1.1
 * @filesource 	./system/expressionengine/third_party/export_it/
 */
 
 /**
 * Export It - Mailing List Class
 *
 * Contains all the methods for interacting with the EE mailing list module
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Mailinglist_data.php
 */
class Mailinglist_data
{
	/**
	 * Handy helper var for the database prefix
	 * @var string
	 */
	public $dbprefix = FALSE;
		
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->dbprefix = $this->EE->db->dbprefix;
		$this->EE->load->add_package_path(PATH_MOD.'mailinglist/');
		$this->EE->load->model('mailinglist_model');
	}
	
	/**
	 * Returns the amount of emails within the system
	 * @param mixed $where
	 */
	public function get_total_emails($where = FALSE)
	{
		if($where)
		{
			$this->gen_list_emails_where($where);
			$data = $this->EE->db->count_all_results('mailing_list');
		}
		else
		{
			$sql = "SELECT COUNT(DISTINCT(email)) AS count FROM ".$this->dbprefix."mailing_list ";
			$data = $this->EE->db->query($sql)->row();
		}
		
		return $data;		
	}
	
	/**
	 * Returns an associative array of the emails within the system
	 * @param mixed $where
	 * @param int $limit
	 * @param int $page
	 * @param string $order
	 */
	public function get_list_emails($where = FALSE, $limit = FALSE, $page = '0', $order = 'email ASC')
	{
		$this->EE->db->select('mailing_list.*, group_concat(list_title) AS list_names ');
		$this->EE->db->from('mailing_list');
		$this->EE->db->join('mailing_lists', 'mailing_lists.list_id = mailing_list.list_id');
	
		$where = $this->gen_list_emails_where($where);
		if($limit)
		{
			$this->EE->db->limit($limit, $page);
		}
	
		if($order)
		{
			$this->EE->db->order_by($order);
		}
		
		$this->EE->db->group_by("email");
	
		$data = $this->EE->db->get();
		$emails = $data->result_array();
		
		return $emails;
	}

	/**
	 * Generates the where for returning emails
	 * @param mixed $where
	 */
	public function gen_list_emails_where($where)
	{
		if(isset($where['search']))
		{
			$search = $this->EE->db->escape_like_str($where['search']);
			$this->EE->db->where("(email LIKE '%".$search."%')");
			unset($where['search']);	
		}

		if(is_array($where) && count($where) >= '1')
		{
			foreach($where AS $key => $value)
			{
				$this->EE->db->where($key, $value);
			}
		}
		elseif(is_string($where))
		{
			$this->EE->db->where($where);
		}
	}
	
	/**
	 * Returns an asociative array of the mailing lists.
	 */
	public function get_mailing_lists()
	{
		$lists = $this->EE->mailinglist_model->get_mailinglists();
		if($lists->num_rows == '0')
		{
			return array();
		}
		else
		{
			$arr = array(null => 'All');
			$lists = $lists->result_array();
			foreach($lists AS $list)
			{
				$arr[$list['list_id']] = $list['list_title'];
			}
		}
		return $arr;
	}	
}