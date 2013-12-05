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
 * Export It - Json Ordering Class
 *
 * Wrappers for each of the export methods in the CP. 
 * Basically, generates all the JSON used within datatables
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Json_ordering.php
 */
 class Json_ordering
{
	/**
	 * Handy helper var for the database prefix
	 * @var string
	 */
	public $dbprefix = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->settings = $this->EE->export_it_lib->get_settings();	
		$this->EE->load->helper('text');
		$this->dbprefix = $this->EE->db->dbprefix;
	}
	
	public function channel_options($channel_id, $type = 'status')
	{
		switch($type)
		{
			case 'status':
			default:
				$data = $this->EE->channel_data->get_channel_statuses($channel_id);
			break;
			
			case 'category':
				$data = $this->EE->channel_data->get_channel_categories($channel_id);
			break;
		}

		if(count($data) == '0')
		{
			$return = array('' => 'All');
		}
		else
		{
			$return = array('' => 'All');
			foreach($data AS $item)
			{
				switch($type)
				{
					case 'status':
					default:				
						$return[$item['status']] = $item['status'];
					break;
					
					case 'category':
						$return[$item['cat_id']] = $item['cat_name'];
					break;					
					
				}
			}
		}
		
		return $this->EE->javascript->generate_json($return, TRUE);
	}
	
	/**
	 * Creates the JSON for the Channel Entry export CP method
	 * @param int $perpage
	 * @param string $url_base
	 */
	public function channel_entries_ordering($perpage, $url_base)
	{
		$col_map = array('ct.entry_id', 'ct.title', 'channel_title', 'entry_date', 'status');
		$id = ($this->EE->input->get_post('id')) ? $this->EE->input->get_post('id') : '';
		$keywords = ($this->EE->input->get_post('k_search')) ? $this->EE->input->get_post('k_search') : FALSE;
		$channel_id = ($this->EE->input->get_post('channel_id') && $this->EE->input->get_post('channel_id') != '') ? $this->EE->input->get_post('channel_id') : FALSE;
		$status = ($this->EE->input->get_post('status') && $this->EE->input->get_post('status') != '') ? $this->EE->input->get_post('status') : FALSE;
		$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
		$category = ($this->EE->input->get_post('category') && $this->EE->input->get_post('category') != '') ? $this->EE->input->get_post('category') : FALSE;
		
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['comments_list_limit'];
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0; // Display start point
		$sEcho = $this->EE->input->get_post('sEcho');
	
		$order = array();
	
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}
	
		$tdata = array();
		$i = 0;
	
		if (count($order) == 0)
		{
			$order = $this->dbprefix."members.member_id DESC";
		}
		else
		{
			$sort = '';
			foreach($order AS $key => $value)
			{
				$sort = $key.' '.$value;
			}
			$order = $sort;
		}
	
		$where = array();
		if($channel_id)
		{
			$where['ct.channel_id'] = $channel_id;
		}
	
		if($keywords)
		{
			$where['search'] = $keywords;
		}
	
		if($status)
		{
			$where['status'] = $status;
		}
		
		if($category)
		{
			$cat_where = array('cat_id' => $category);
			$entry_ids = $this->EE->channel_data->get_category_posts($cat_where);
			$ids = array();
			foreach($entry_ids AS $entry_id)
			{
				$ids[] = $entry_id['entry_id'];
			}
			
			$where['ct.entry_id'] = $ids;
		}		
	
		if($date_range)
		{
			$where['date_range'] = $date_range;
		}
	
		$this->EE->channel_data->translate_cft = FALSE; //disable checking custom field data
		$total = $this->EE->channel_data->get_total_entries();
		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total;
		$j_response['iTotalDisplayRecords'] = $this->EE->channel_data->get_total_entries($where);
	
		$data = $this->EE->channel_data->get_entries($where, $perpage, $offset, $order);

		foreach ($data as $item)
		{
			$m[] = '<a href="?D=cp&C=content_publish&M=entry_form&channel_id='.$item['channel_id'].'&entry_id='.$item['entry_id'].'">'.$item['entry_id'].'</a>';
			$m[] = '<a href="javascript:;" class="keyword_filter_value" rel="'.addslashes($item['title']).'">'.$item['title'].'</a>';
			$m[] = '<a href="javascript:;" rel="'.$item['channel_id'].'" class="channel_filter_id">'.$item['channel_title'].'</a>';
			$m[] = m62_convert_timestamp($item['entry_date']);
			$m[] = '<a href="javascript:;" rel="'.$item['status'].'" class="status_filter_id">'.$item['status'].'</a>';
			$tdata[$i] = $m;
			$i++;
			unset($m);
		}
	
		$j_response['aaData'] = $tdata;
		return $this->EE->javascript->generate_json($j_response, TRUE);
	}
	
	/**
	 * Creates the JSON for the Comment export CP method
	 * @param int $perpage
	 * @param string $url_base
	 */
	public function comments_ordering($perpage, $url_base)
	{
		$col_map = array('comment', 'entry.title', 'name', 'comment_date', 'comment.status');
		$id = ($this->EE->input->get_post('id')) ? $this->EE->input->get_post('id') : '';
		$keywords = ($this->EE->input->get_post('k_search')) ? $this->EE->input->get_post('k_search') : FALSE;
		$channel_id = ($this->EE->input->get_post('channel_id') && $this->EE->input->get_post('channel_id') != '') ? $this->EE->input->get_post('channel_id') : FALSE;
		$status = ($this->EE->input->get_post('status') && $this->EE->input->get_post('status') != '') ? $this->EE->input->get_post('status') : FALSE;
		$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
		
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['comments_list_limit'];
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0; // Display start point
		$sEcho = $this->EE->input->get_post('sEcho');
	
		$order = array();
	
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}
	
		$tdata = array();
		$i = 0;
	
		if (count($order) == 0)
		{
			$order = $this->dbprefix."members.member_id DESC";
		}
		else
		{
			$sort = '';
			foreach($order AS $key => $value)
			{
				$sort = $key.' '.$value;
			}
			$order = $sort;
		}
	
		$where = array();
		if($channel_id)
		{
			$where['comments.channel_id'] = $channel_id;
		}
	
		if($keywords)
		{
			$where['search'] = $keywords;
		}
		
		if($status)
		{
			$where['comments.status'] = $status;
		}
		
		if($date_range)
		{
			$where['date_range'] = $date_range;
		}

		$total = $this->EE->comment_data->get_total_comments();
		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total;
		$j_response['iTotalDisplayRecords'] = $this->EE->comment_data->get_total_comments($where);
	
		$data = $this->EE->comment_data->get_comments($where, $perpage, $offset, $order);
		
		$status_select = $this->EE->export_it_lib->get_status_select();
		foreach ($data as $item)
		{
			$status = (isset($status_select[$item['status']]) ? $status_select[$item['status']] : $item['status']);
			$m[] = '<a href="?D=cp&C=addons_modules&M=show_module_cp&module=comment&method=edit_comment_form&comment_id='.$item['comment_id'].'">'.word_limiter($item['comment'], 10).'</a>';
			$m[] = '<a href="javascript:;" rel="'.$item['title'].'" class="keyword_filter_value">'.$item['title'].'</a>';
			$m[] = '<a href="javascript:;" rel="'.$item['name'].'" class="keyword_filter_value">'.$item['name'].'</a>';
			$m[] = m62_convert_timestamp($item['comment_date']);
			$m[] = '<a href="javascript:;" rel="'.$item['channel_id'].'" class="channel_filter_id">'.$item['channel_title'].'</a>';
			$m[] = '<a href="javascript:;" rel="'.$item['status'].'" class="status_filter_id">'.$status.'</a>';
			$tdata[$i] = $m;
			$i++;
			unset($m);
		}
	
		$j_response['aaData'] = $tdata;
		return $this->EE->javascript->generate_json($j_response, TRUE);
	}
	
	/**
	 * Creates the JSON for the Mailing List export CP method
	 * @param int $perpage
	 * @param string $url_base
	 */
	public function mailing_list_ordering($perpage, $url_base)
	{
		$col_map = array('email', 'ip', 'list_name');
		$id = ($this->EE->input->get_post('id')) ? $this->EE->input->get_post('id') : '';
		$keywords = ($this->EE->input->get_post('k_search')) ? $this->EE->input->get_post('k_search') : FALSE;
		$list_id = ($this->EE->input->get_post('list_id') && $this->EE->input->get_post('list_id') != '') ? $this->EE->input->get_post('list_id') : FALSE;
			
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['mailing_list_limit'];
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0; // Display start point
		$sEcho = $this->EE->input->get_post('sEcho');
	
		$order = array();
	
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}
	
		$tdata = array();
		$i = 0;
	
		if (count($order) == 0)
		{
			$order = $this->dbprefix."members.member_id DESC";
		}
		else
		{
			$sort = '';
			foreach($order AS $key => $value)
			{
				$sort = $key.' '.$value;
			}
			$order = $sort;
		}
	
		$where = array();
		if($list_id)
		{
			$where['mailing_list.list_id'] = $list_id;
		}
		
		if($keywords)
		{
			$where['search'] = $keywords;
		}
		
		$total = $this->EE->mailinglist_data->get_total_emails();
		$total_records = $this->EE->mailinglist_data->get_total_emails($where);
		if(isset($total_records->count))
		{
			$total_records = $total_records->count;
		}
		
		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total->count;
		$j_response['iTotalDisplayRecords'] = $total_records;
	
		$mailing_lists = $this->EE->mailinglist_data->get_mailing_lists();
		$data = $this->EE->mailinglist_data->get_list_emails($where, $perpage, $offset, $order);
		foreach ($data as $item)
		{
			$m[] = '<a href="mailto:'.$item['email'].'">'.$item['email'].'</a>';
			$m[] = $item['ip_address'];
			$m[] = m62_create_mailinglist_links($item['list_names'], $mailing_lists);
			$tdata[$i] = $m;
			$i++;
			unset($m);
		}
	
		$j_response['aaData'] = $tdata;
		return $this->EE->javascript->generate_json($j_response, TRUE);
	}
	
	/**
	 * Creates the JSON for the Member export CP method
	 * @param int $perpage
	 * @param string $url_base
	 */
	public function member_ordering($perpage, $url_base)
	{
		$col_map = array('member_id', 'username', 'screen_name', 'email', 'join_date', 'group_title');
		$id = ($this->EE->input->get_post('id')) ? $this->EE->input->get_post('id') : '';
		$group_id = ($this->EE->input->get_post('group_id') && $this->EE->input->get_post('group_id') != '') ? $this->EE->input->get_post('group_id') : FALSE;
		$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
		$keywords = ($this->EE->input->get_post('k_search') && $this->EE->input->get_post('k_search') != '') ? $this->EE->input->get_post('k_search') : FALSE;
			
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['members_list_limit'];
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0; // Display start point
		$sEcho = $this->EE->input->get_post('sEcho');

		$order = array();
		
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}

		$tdata = array();
		$i = 0;
		
		if (count($order) == 0)
		{
			$order = $this->dbprefix."members.member_id DESC";
		}
		else
		{
			$sort = '';
			foreach($order AS $key => $value)
			{
				$sort = $key.' '.$value;
			}
			$order = $sort;
		}

		$where = array();
		if($group_id)
		{
			$where['members.group_id'] = $group_id;
		}
		
		if($keywords)
		{
			$where['search'] = $keywords;
		}
		
		if($date_range)
		{
			$where['date_range'] = $date_range;
		}		
				
		$total = $this->EE->member_data->get_total_members();
		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total;
		
		$j_response['iTotalDisplayRecords'] = $this->EE->member_data->get_total_members($where);
		$data = $this->EE->member_data->get_members($where, FALSE, FALSE, $perpage, $offset, $order);
		foreach ($data as $item)
		{			
			$m[] = '<a href="?D=cp&C=myaccount&id='.$item['member_id'].'">'.$item['member_id'].'</a>';
			
			$m[] = $item['username'];
			$m[] = $item['screen_name'];
			$m[] = '<a href="mailto:'.$item['email'].'">'.$item['email'].'</a>';
			$m[] = m62_convert_timestamp($item['join_date']);
			$m[] = '<a href="javascript:;" rel="'.$item['group_id'].'" class="group_filter_id">'.$item['group_title'].'</a>';
			$tdata[$i] = $m;
			$i++;
			unset($m);
		}		

		$j_response['aaData'] = $tdata;	
		return $this->EE->javascript->generate_json($j_response, TRUE);	
	}	
}