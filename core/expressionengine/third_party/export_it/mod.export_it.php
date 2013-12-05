<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/export_it/
 */
 
 /**
 * Export It - Module Class
 *
 * The external module class used for Actions (ACT) and template tags
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/mod.export_it.php
 */
class Export_it 
{

	/**
	 * The data to send back to the browser
	 * @var string
	 */
	public $return_data	= '';
	
	/**
	 * The format the exported data
	 * @var mixed
	 */
	public $export_format = FALSE;
	
	/**
	 * What fields to include in the export
	 * @var mixed
	 */	
	public $export_fields = FALSE;
	
	/**
	 * What fields to exclude from the export
	 * @var mixed
	 */	
	public $exclude_fields = FALSE;
	
	/**
	 * The name for the exported file
	 * @var mixed
	 */	
	public $export_filename = FALSE;
	
	/**
	 * Where to save the exported data (name will be prepended)
	 * @var mixed
	 */	
	public $save_path = FALSE;
	
	public function __construct()
	{				
		$this->EE =& get_instance();
		$this->EE->load->model('export_it_settings_model', 'export_it_settings');
		$this->EE->load->library('export_it_lib');
		
		$this->settings = $this->EE->export_it_lib->get_settings();
		$this->EE->load->library('member_data');
		$this->EE->load->library('channel_data');
		$this->EE->load->library('mailinglist_data');
		$this->EE->load->library('comment_data');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('json_ordering');
		$this->EE->load->library('Export_data/export_data');
		$this->EE->lang->loadfile('export_it');	
		
		if(isset($this->EE->TMPL))
		{
			$this->export_format = $this->EE->TMPL->fetch_param('format', 'xls');
			$this->export_fields = $this->EE->TMPL->fetch_param('export_fields', FALSE);
			$this->exclude_fields = $this->EE->TMPL->fetch_param('exclude_fields', FALSE);
			$this->export_filename = $this->EE->TMPL->fetch_param('filename', FALSE);
			$this->save_path = $this->EE->TMPL->fetch_param('save_path', FALSE);
			
			//setup the export_fields array data
			if($this->export_fields)
			{
				$parts = explode(',', $this->export_fields);
				if(count($parts) >= '1')
				{
					$this->export_fields = array_map('trim', $parts);
				}
			}
			
			//setup the exclude fields aray
			if($this->exclude_fields)
			{
				$parts = explode(',', $this->exclude_fields);
				if(count($parts) >= '1')
				{
					$this->exclude_fields = array_map('trim', $parts);
				}
			}

			//ensure writability on save_path and setup everything needed
			//NOTE REQUIRES export_filename be set too!
			if($this->save_path && $this->export_filename)
			{
				$this->save_path = realpath($this->save_path);
				if(is_writable($this->save_path))
				{
					$this->EE->export_data->disable_download = TRUE;
					$this->EE->export_data->save_path = $this->save_path;
				}
				else
				{
					$this->save_path = FALSE; 
				}
			}
		}
	}
	
	public function members()
	{
		$group_id = $this->EE->TMPL->fetch_param('group_id', FALSE);
		$include_custom_fields = $this->EE->TMPL->fetch_param('include_custom_fields', TRUE);
		$complete_select = $this->EE->TMPL->fetch_param('complete_select', FALSE);		
		$date_range = ($this->EE->TMPL->fetch_param('date_range', FALSE) && $this->EE->TMPL->fetch_param('date_range', FALSE) != '') ? $this->EE->TMPL->fetch_param('date_range', FALSE) : FALSE;
		$keywords = ($this->EE->TMPL->fetch_param('keywords') && $this->EE->TMPL->fetch_param('keywords') != '') ? $this->EE->TMPL->fetch_param('keywords') : FALSE;

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
		
		$data = $this->clean_export_data($this->EE->member_data->get_members($where, $include_custom_fields, $complete_select, FALSE, 0, FALSE));
		$this->EE->export_data->export_members($data, $this->export_format);
		exit;		
	}
	
	public function channel_entries()
	{
		$this->EE->export_it_lib->setup_memory_limits();
		
		$keywords = $this->EE->TMPL->fetch_param('keywords', FALSE);
		$complete = $this->EE->TMPL->fetch_param('complete_select', FALSE);
		$channel_id = $this->EE->TMPL->fetch_param('channel_id', FALSE);
		$channel_name = $this->EE->TMPL->fetch_param('channel', FALSE);
		$status = $this->EE->TMPL->fetch_param('status', FALSE);
		$date_range = $this->EE->TMPL->fetch_param('date_range', FALSE);
		$category = $this->EE->TMPL->fetch_param('category', FALSE);
		
		$where = array();
		if($channel_name)
		{
			$channel = $this->EE->channel_data->get_channel_by_name($channel_name);
			if($channel->num_rows() > 0)
			{
				$channel_id = $channel->row('channel_id');
			}
		}
		
		if($channel_id)
		{
			$where['ct.channel_id'] = $channel_id;
		}
		else 
		{
			return 'channel_id is REQUIRED :(';
		}
		
		if($keywords)
		{
			$where['search'] = $keywords;
		}
		
		if($status)
		{
			$where['status'] = $status;
		}
		
		foreach($this->EE->TMPL->tagparams as $param => $value)
		{
			if(preg_match('/where:/', $param))
			{
				$param    = preg_replace('/where:/', '', $param);
				$field    = $this->EE->channel_data->get_field_by_name($param);				
				$operator = $this->EE->export_it_lib->sql_operator($value);
				$value    = $this->EE->export_it_lib->strip_operators($value);
				
				if($field->num_rows() > 0)
				{
					$where['field_id_'.$field->row('field_id').$operator] = $value;	
				}
				else
				{		
					$where[$param.$operator] = $value;
				}				
			}
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
		
		if($complete && $complete != '')
		{
			$this->EE->channel_data->complete_select = TRUE;
		}
		
		$data = $this->clean_export_data($this->EE->channel_data->get_entries($where));
		if($this->export_filename)
		{
			$this->EE->export_data->export_channel_entries($data, $this->export_format, $this->export_filename);
		}
		else
		{
			$this->EE->export_data->export_channel_entries($data, $this->export_format);
		}
		exit;
	}
	
	public function save_export_data()
	{
		$data = ob_get_contents();
	}
	
	public function mailinglist()
	{
		$exclude_duplicates = $this->EE->TMPL->fetch_param('exclude_duplicates');
		$keywords = $this->EE->TMPL->fetch_param('keywords', FALSE);
		$list_id = $this->EE->TMPL->fetch_param('list_id', FALSE);
		$where = array();
		if($list_id)
		{
			$where['mailing_list.list_id'] = $list_id;
		}
		
		if($keywords)
		{
			$where['search'] = $keywords;
		}
		
		$data = $this->clean_export_data($this->EE->mailinglist_data->get_list_emails($where));
		
		if($this->export_filename)
		{		
			$this->EE->export_data->export_mailing_list($data, $this->export_format, $this->export_filename);
		}
		else
		{
			$this->EE->export_data->export_mailing_list($data, $this->export_format);
		}
		exit;		
	}
	
	public function comments()
	{
		$date_range = $this->EE->TMPL->fetch_param('date_range' , FALSE);
		$status = $this->EE->TMPL->fetch_param('status', FALSE);
		$channel_id = $this->EE->TMPL->fetch_param('channel_id', FALSE);
		$keywords = $this->EE->TMPL->fetch_param('keywords', FALSE);
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
		
		$data = $this->EE->comment_data->get_comments($where);
		$comments = array();
		$channel_data = array();
		foreach($data AS $comment)
		{
			if(!isset($channel_data[$comment['channel_id']]))
			{
				$channel_data[$comment['channel_id']] = $this->EE->channel_model->get_channel_info($comment['channel_id'])->row();
			}
				
			$comments[$comment['entry_id']]['title'] = $comment['title'];
			$comments[$comment['entry_id']]['entry_title'] = $comment['title'];
			$comments[$comment['entry_id']]['entry_date'] = $comment['entry_date'];
			$comments[$comment['entry_id']]['comment_url'] = $channel_data[$comment['channel_id']]->comment_url;
			$comments[$comment['entry_id']]['channel_url'] = $channel_data[$comment['channel_id']]->channel_url;
			$comments[$comment['entry_id']]['url_title'] = $comment['url_title'];
			$comments[$comment['entry_id']]['entry_url_title'] = $comment['url_title'];
			$comments[$comment['entry_id']]['channel_id'] = $comment['channel_id'];
			$comments[$comment['entry_id']]['channel_title'] = $comment['channel_title'];
			$comments[$comment['entry_id']]['channel_name'] = $comment['channel_name'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['comment_id'] = $comment['comment_id'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['name'] = $comment['name'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['email'] = $comment['email'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['status'] = $comment['status'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['url'] = $comment['url'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['location'] = $comment['location'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['ip_address'] = $comment['ip_address'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['comment_date'] = $comment['comment_date'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['edit_date'] = $comment['edit_date'];
			$comments[$comment['entry_id']]['comments'][$comment['comment_id']]['comment'] = $comment['comment'];
		}

		if($this->export_filename)
		{		
			$this->EE->export_data->export_comments($comments, $this->export_format, $this->export_filename);
		}
		else
		{
			$this->EE->export_data->export_comments($comments, $this->export_format);
		}
		
		exit;		
	}
	
	public function matrix()
	{
		$entry_id = $this->EE->TMPL->fetch_param('entry_id', FALSE);
		$field_id = $this->EE->TMPL->fetch_param('field_id', FALSE);
		if(!$field_id)
		{
			return 'Missing field_id';
		}
				
		$data = $this->EE->channel_data->clean_matrix_data($entry_id, $field_id);
		$return = array();
		foreach($data AS $key => $value)
		{
			$return[$key] = $data[$key]['matrix'][$key];
		}

		$data = $this->clean_export_data($return);
		
		if($this->export_filename)
		{		
			$this->EE->export_data->export_channel_entries($data, $this->export_format, $this->export_filename);
		}
		else
		{
			$this->EE->export_data->export_channel_entries($data, $this->export_format, 'matrix');
		}
		
		exit;	
	}
	
	public function channel_files_dl_log()
	{
		$entry_id = $this->EE->TMPL->fetch_param('entry_id', FALSE);
		$file_id = $this->EE->TMPL->fetch_param('file_id', FALSE);

		$this->EE->db->select("cf.filename, cfdl.*, m.username, m.email, ct.title AS entry_title");
		$this->EE->db->from('channel_files cf');
		$this->EE->db->join('channel_files_download_log cfdl', 'cfdl.file_id = cf.file_id');
		$this->EE->db->join('members m', 'm.member_id = cfdl.member_id', 'left');
		$this->EE->db->join('channel_titles ct', 'ct.entry_id = cf.entry_id', 'left');

		if($file_id)
		{
			$this->EE->db->where('cf.file_id', $file_id);
		}
		
		if($entry_id)
		{
			$this->EE->db->where('cf.entry_id', $entry_id);
		}		
		
		//$this->EE->db->limit(50);
		$data = $this->EE->db->get();
		$arr = $data->result_array();
		if(count($arr) >= '1')
		{
			$i = 0;
			foreach($arr AS $key => $file)
			{
				$arr[$key]['date'] = m62_convert_timestamp($arr[$key]['date']);
				$arr[$key]['ip_address'] = long2ip($arr[$key]['ip_address']);
			}
		}		

		$data = $this->clean_export_data($arr);
		
		if($this->export_filename)
		{		
			$this->EE->export_data->export_channel_entries($data, $this->export_format, $this->export_filename);
		}
		else
		{
			$this->EE->export_data->export_channel_entries($data, $this->export_format, 'channel_files_dl_log');
		}
		exit;
	}

	public function channel_files()
	{
		$entry_id = $this->EE->TMPL->fetch_param('entry_id', FALSE);
		$file_id = $this->EE->TMPL->fetch_param('file_id', FALSE);
	
		$this->EE->db->select("cf.*, m.username, m.email, ct.title AS entry_title");
		$this->EE->db->from('channel_files cf');
		$this->EE->db->join('members m', 'm.member_id = cf.member_id', 'left');
		$this->EE->db->join('channel_titles ct', 'ct.entry_id = cf.entry_id', 'left');
	
		if($file_id)
		{
			$this->EE->db->where('cf.file_id', $file_id);
		}
	
		if($entry_id)
		{
			$this->EE->db->where('cf.entry_id', $entry_id);
		}
	
		//$this->EE->db->limit(50);
		$data = $this->EE->db->get();
		$arr = $data->result_array();
		if(count($arr) >= '1')
		{
			$i = 0;
			foreach($arr AS $key => $file)
			{
				$arr[$key]['date'] = m62_convert_timestamp($arr[$key]['date']);
			}
		}
	
		$data = $this->clean_export_data($arr);
		
		if($this->export_filename)
		{		
			$this->EE->export_data->export_channel_entries($data, $this->export_format, $this->export_filename);

		}
		else
		{
			$this->EE->export_data->export_channel_entries($data, $this->export_format, 'channel_files');
		}
		exit;
	}

	public function query()
	{
		$sql = $this->EE->TMPL->fetch_param('sql', FALSE);
		if (!$sql || substr(strtolower(trim($sql)), 0, 6) != 'select')
		{
			return FALSE;
		}	

		$query = $this->EE->db->query($sql);
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		
		$export_data = array();
		foreach ($query->result_array() as $count => $row)
		{
			$export_data[$count] = $row;
		}

		switch($this->export_format)
		{
			case 'xml':
			default:
				$this->EE->export_data->download_xml($export_data, $this->export_filename.'.xml', 'query', 'data');
			break;
					
			case 'json':
				$this->EE->export_data->download_json($export_data, $this->export_filename.'.json');
			break;
					
			case 'xls':
				$this->EE->export_data->download_array($export_data, TRUE, $this->export_filename.'.xls');
			break;
		}
		exit;		
	}
	
	public function ff_entries()
	{
		$form_id = $this->EE->TMPL->fetch_param('form_id', FALSE);
		$form_name = $this->EE->TMPL->fetch_param('form_name', FALSE);
		$author_id = $this->EE->TMPL->fetch_param('author_id', FALSE);
		$entry_id = $this->EE->TMPL->fetch_param('entry_id', FALSE);
		$status = $this->EE->TMPL->fetch_param('status', FALSE);
		
		$this->EE->load->library('Freeform_data');
		$where = array();
		if($form_name)
		{
			$form_data = $this->EE->freeform_data->get_form_by_name($form_name);
			if(count($form_data) > 0)
			{
				$form_id = $form_data['form_id'];
			}
		}
		elseif($form_id)
		{
			$form_data = $this->EE->freeform_data->get_form($form_id);
		}	
		else
		{
			return 'form_id is REQUIRED :(';
		}

		if($author_id)
		{
			$where['author_id'] = $author_id;
		}
		
		if($entry_id)
		{
			$where['entry_id'] = $entry_id;
		}
		
		if($status)
		{
			$where['status'] = $status;
		}
		
		foreach($this->EE->TMPL->tagparams as $param => $value)
		{
			if(preg_match('/where:/', $param))
			{
				$param = preg_replace('/where:/', '', $param);
				$field = $this->EE->freeform_data->get_field_by_name($param);
				$operator = $this->EE->export_it_lib->sql_operator($value);
				$value = $this->EE->export_it_lib->strip_operators($value);
		
				if($field->num_rows() > 0)
				{
					$where['form_field_'.$field->row('field_id').$operator] = $value;
				}
				else
				{
					$where[$param.$operator] = $value;
				}
			}
		}

		//$total = $this->EE->freeform_data->get_total_entries($form_id, $where);
		$entries = $this->EE->freeform_data->get_entries($form_id, $where);
		$entries = $this->clean_export_data($entries);
		if($this->export_filename)
		{
			$this->EE->export_data->export_freeform_entries($entries, $this->export_format, $this->export_filename);
		
		}
		else
		{
			$this->EE->export_data->export_freeform_entries($entries, $this->export_format, 'freeform');
		}
		exit;				
	}
	
	private function clean_export_data(array $data)
	{
		if(is_array($this->export_fields) && count($this->export_fields) >= '1')
		{
			$return = array();
			foreach($data AS $key => $value)
			{
				foreach($this->export_fields AS $index => $field)
				{
					if(isset($value[$field]))
					{
						$return[$key][$field] = $value[$field];
					}
					else
					{
						$return[$key][$field] = '';
					}
				}
				unset($data[$key]);
			}
			
			$data = $return;
		}
		
		if(is_array($this->exclude_fields) && count($this->exclude_fields) >= '1')
		{
			foreach($data AS $key => $value)
			{
				foreach($this->exclude_fields AS $index => $field)
				{
					if(isset($value[$field]))
					{
						unset($data[$key][$field]);
					}
				}
			}			
		}
				
		return $data;
	}
	
	public function api()
	{
		$this->EE->load->library('Api_server/api_server');
		$this->EE->api_server->run();
		exit;
	} 	
}