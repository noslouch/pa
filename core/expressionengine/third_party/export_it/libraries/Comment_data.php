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
 * Export It - Comment Class
 *
 * Contains all the methods for interacting with the EE comments module
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Comment_data.php
 */
class Comment_data
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
	}
	
	/**
	 * Returns the amount of emails within the system
	 * @param mixed $where
	 */
	public function get_total_comments($where = FALSE)
	{
		if($where)
		{
			$this->gen_list_comment_where($where);
		}
		
		$this->EE->db->join('channel_titles', 'channel_titles.entry_id = comments.entry_id');
		$data = $this->EE->db->count_all_results('comments', FALSE);
		return $data;		
	}
	
	/**
	 * Returns an associative array of the emails within the system
	 * @param mixed $where
	 * @param int $limit
	 * @param int $page
	 * @param string $order
	 */
	public function get_comments($where = FALSE, $limit = FALSE, $page = '0', $order = 'comment_date DESC')
	{
		$this->EE->db->select('comments.*, channel_titles.title, channel_titles.entry_date, channel_titles.url_title, channels.channel_title, channels.channel_name ');
		$this->EE->db->from('comments');
		$this->EE->db->join('channel_titles', 'channel_titles.entry_id = comments.entry_id');
		$this->EE->db->join('channels', 'comments.channel_id = channels.channel_id', FALSE);	
	
		$where = $this->gen_list_comment_where($where);
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
		//exit;
		$emails = $data->result_array();
		
		return $emails;
	}

	/**
	 * Generates the where for returning emails
	 * @param mixed $where
	 */
	public function gen_list_comment_where($where)
	{
		
		if(isset($where['date_range']) && $where['date_range'] != 'custom_date')
		{
			if(is_numeric($where['date_range']))
			{
				$this->EE->db->where('comment_date >', (mktime()-($where['date_range']*24*60*60)));
			}
			else
			{
				$parts = explode('to', $where['date_range']);
				if(count($parts) == '2')
				{
					$start = strtotime($parts['0']);
					$end = strtotime($parts['1']);
					$where_date = " comment_date BETWEEN '$start' AND '$end'";
					$this->EE->db->where($where_date, null, FALSE);
				}
			}
			
			unset($where['date_range']);
		}
				
		if(isset($where['search']))
		{
			$search_where = array('comment', 'name', 'title');
			foreach($search_where AS $field)
			{
				$this->EE->db->or_like($field, $where['search']); 
			}
			
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
	 * Returns the date for the very first comment in the system.
	 * @param mixed $where
	 */
	public function get_first_date($where = FALSE)
	{
		if($where)
		{
			$this->gen_list_comment_where($where);
		}
	
		$this->EE->db->select_min('comment_date');
		$data = $this->EE->db->get('comments');
		return $data->row('comment_date');
	}	
}