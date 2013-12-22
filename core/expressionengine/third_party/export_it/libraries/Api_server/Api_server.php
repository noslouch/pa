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
 * Export It - Api Server Library
 *
 * Contains all the method/wrappers for the Api 
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Api_server/Api_server.php
 */
class Api_server
{
	public $channel_id = FALSE;
	public $entry_id = FALSE;
	public $member_id = FALSE;
	public $list_id = FALSE;
	public $comment_id = FALSE;
	public $format = 'json';
	public $settings;
	public $method = FALSE;
	
	private $extension_ran = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('export_it');
		$this->EE->load->library('javascript');
		$this->EE->load->library('member_data');
		$this->EE->load->library('channel_data');
		$this->EE->load->library('Export_data/export_data');
		$this->EE->load->library('mailinglist_data');
		$this->EE->load->library('comment_data');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('Api_server/Api_auth');		
		$this->settings = $this->EE->export_it_lib->get_settings();
		$this->_setup_api();
		$this->EE->export_data->disable_download = TRUE;
		$this->EE->export_data->enable_api = TRUE;
	}
	
	/**
	 * Wrapper to exectute the API Server
	 */
	public function run()
	{
		$method = $this->method;
		
		if(!$this->extension_ran)
		{
			$this->$method();
		}
		
		if ($this->EE->extensions->active_hook('export_it_api_end') === TRUE)
		{
			$this->EE->extensions->call('export_it_api_end', $this);
			if ($this->EE->extensions->end_script === TRUE) return;
		}		
		exit;
	}
	
	public function get_members()
	{
		$export_format = $this->EE->input->get_post('format');
		$group_id = $this->EE->input->get_post('group_id');
		$include_custom_fields = $this->EE->input->get_post('include_custom_fields');
		$complete_select = $this->EE->input->get_post('complete_select');

		$group_id = ($this->EE->input->get_post('group_id') && $this->EE->input->get_post('group_id') != '') ? $this->EE->input->get_post('group_id') : FALSE;
		$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
		$keywords = ($this->EE->input->get_post('keywords') && $this->EE->input->get_post('keywords') != '') ? $this->EE->input->get_post('keywords') : FALSE;
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['comments_list_limit'];
		$offset = ($this->EE->input->get_post('offset')) ? $this->EE->input->get_post('offset') : 0; // Display start point
		$order = ($this->EE->input->get_post('order')) ? $this->EE->input->get_post('order') : 'member_id DESC'; // Display start point
		
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
		
		$data = $this->EE->member_data->get_members($where, $include_custom_fields, $complete_select, $offset, $perpage, FALSE);
		$this->EE->export_data->export_members($data, $export_format);
	}
	
	public function get_member()
	{
		$export_format = $this->EE->input->get_post('format');
		$member_id = $this->EE->input->get_post('member_id');
		$include_custom_fields = $this->EE->input->get_post('include_custom_fields');
		$complete_select = $this->EE->input->get_post('complete_select');
		$email = $this->EE->input->get_post('email');
		$username = $this->EE->input->get_post('username');
		$screen_name = $this->EE->input->get_post('screen_name');
		
		if(!$member_id && !$email && !$username && !$screen_name)
		{
			$this->error(lang('missing_required_member_fields'), 500);
			exit;			
		}
		
		$where = array();
		if($member_id)
		{
			$where['members.member_id'] = $member_id;
		}
		elseif($email)
		{
			$where['members.email'] = $email;
		}
		elseif($username)
		{
			$where['members.username'] = $username;
		}
		elseif($screen_name)
		{
			$where['members.screen_name'] = $screen_name;
		}			
		
		$data = $this->EE->member_data->get_members($where, $include_custom_fields, $complete_select, FALSE, 1, FALSE);
		$this->EE->export_data->export_members($data, $export_format);
	}	
	
	public function get_channel_entries()
	{
		$keywords = ($this->EE->input->get_post('keywords')) ? $this->EE->input->get_post('keywords') : FALSE;
		$channel_id = ($this->EE->input->get_post('channel_id') && $this->EE->input->get_post('channel_id') != '') ? $this->EE->input->get_post('channel_id') : FALSE;
		$status = ($this->EE->input->get_post('status') && $this->EE->input->get_post('status') != '') ? $this->EE->input->get_post('status') : FALSE;
		$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
		$author_id = ($this->EE->input->get_post('author_id') && $this->EE->input->get_post('author_id') != '') ? $this->EE->input->get_post('author_id') : FALSE;
		
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['channel_entries_list_limit'];
		$offset = ($this->EE->input->get_post('offset')) ? $this->EE->input->get_post('offset') : 0; // Display start point
		$order = ($this->EE->input->get_post('order')) ? $this->EE->input->get_post('order') : 'entry_date DESC'; // Display start point
		
		$where = array();
		if($channel_id)
		{
			$where['ct.channel_id'] = $channel_id;
		}
		
		if($author_id)
		{
			$where['ct.author_id'] = $author_id;
		}
		
		if($keywords)
		{
			$where['search'] = $keywords;
		}
		
		if($status)
		{
			$where['status'] = $status;
		}
		
		if($date_range)
		{
			$where['date_range'] = $date_range;
		}		

		$data = $this->EE->channel_data->get_entries($where, $perpage, $offset, FALSE);
		$this->EE->export_data->export_channel_entries($data, $this->format);		
	}
	
	public function get_channel_entry()
	{
		$entry_id = ($this->EE->input->get_post('entry_id')) ? $this->EE->input->get_post('entry_id') : FALSE;
		$url_title = ($this->EE->input->get_post('url_title')) ? $this->EE->input->get_post('url_title') : FALSE;
		if((!$entry_id || $entry_id == '') && (!$url_title || $url_title == ''))
		{
			$this->error(lang('entry_id_url_title_missing'), 500);
			exit;
		}
		
		$where = array();
		if($entry_id && $entry_id != '')
		{
			$where['cd.entry_id'] = $entry_id;
		}
		else
		{
			$where['url_title'] = $url_title;
		}
		
		$data = $this->EE->channel_data->get_entries($where, 1);
		$this->EE->export_data->export_channel_entries($data, $this->format);		
	}	
	
	public function get_comments()
	{
		$keywords = ($this->EE->input->get_post('keywords')) ? $this->EE->input->get_post('keywords') : FALSE;
		$channel_id = ($this->EE->input->get_post('channel_id') && $this->EE->input->get_post('channel_id') != '') ? $this->EE->input->get_post('channel_id') : FALSE;
		$entry_id = ($this->EE->input->get_post('entry_id') && $this->EE->input->get_post('entry_id') != '') ? $this->EE->input->get_post('entry_id') : FALSE;
		$status = ($this->EE->input->get_post('status') && $this->EE->input->get_post('status') != '') ? $this->EE->input->get_post('status') : FALSE;
		$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
		
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['comments_list_limit'];
		$offset = ($this->EE->input->get_post('limit')) ? $this->EE->input->get_post('limit') : 0; // Display start point
		$order = ($this->EE->input->get_post('order')) ? $this->EE->input->get_post('order') : 'comment_date DESC';
		
		if((!$channel_id || $channel_id == '') && (!$entry_id || $entry_id == ''))
		{
			$this->error(lang('channel_entry_id_missing'), 500);
			exit;
		}
		
		$where = array();
		if($channel_id)
		{
			$where['comments.channel_id'] = $channel_id;
		}
		else
		{
			if($entry_id)
			{
				$where['comments.entry_id'] = $entry_id;
			}			
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

		$data = $this->EE->comment_data->get_comments($where, $perpage, $offset, $order);
		$this->EE->export_data->export_comments($data, $this->format);		
	}
	
	public function get_comment()
	{
		$comment_id = ($this->EE->input->get_post('comment_id')) ? $this->EE->input->get_post('comment_id') : FALSE;
		if((!$comment_id || $comment_id == ''))
		{
			$this->error(lang('comment_id_missing'), 500);
			exit;
		}

		$where['comment_id'] = $comment_id;
		$data = $this->EE->comment_data->get_comments($where, 1, FALSE, FALSE);
		$this->EE->export_data->export_comments($data, $this->format);	
	}
	
	public function get_mailing_list()
	{
		$keywords = ($this->EE->input->get_post('keywords')) ? $this->EE->input->get_post('keywords') : FALSE;
		$list_id = ($this->EE->input->get_post('list_id') && $this->EE->input->get_post('list_id') != '') ? $this->EE->input->get_post('list_id') : FALSE;
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['mailing_list_limit'];
		$offset = ($this->EE->input->get_post('offset')) ? $this->EE->input->get_post('offset') : 0; // Display start point
		$order = ($this->EE->input->get_post('order')) ? $this->EE->input->get_post('order') : 'user_id DESC'; // Display start point
		
		$where = array();
		if($list_id)
		{
			$where['mailing_list.list_id'] = $list_id;
		}
		
		if($keywords)
		{
			$where['search'] = $keywords;
		}		

		$data = $this->EE->mailinglist_data->get_list_emails($where, $perpage, $offset, $order);
		$this->EE->export_data->export_mailing_list($data, $this->format);			
	}
	
	public function get_category()
	{
		$cat_id = $this->EE->input->get_post('cat_id');
		if((!$cat_id || $cat_id == ''))
		{
			$this->error(lang('cat_id_missing'), 500);
			exit;
		}
		$this->EE->export_data->export_category($this->format, $cat_id);		
	}
	
	public function get_category_posts()
	{
		$cat_id = $this->EE->input->get_post('cat_id');
		if((!$cat_id || $cat_id == ''))
		{
			$this->error(lang('cat_id_missing'), 500);
			exit;
		}
		$this->EE->export_data->export_category_posts($this->format, $cat_id);		
	}
	
	public function get_categories()
	{
		$entry_id = $this->EE->input->get_post('entry_id');
		if((!$entry_id || $entry_id == ''))
		{
			$this->error(lang('entry_id_missing'), 500);
			exit;
		}
		$this->EE->export_data->export_categories($this->format, $entry_id);		
	}
	
	public function check_credentials()
	{
		$username = $this->EE->input->get_post('username');
		$password = $this->EE->input->get_post('password');
		if((!$username || $username == '') || (!$password || $password == ''))
		{
			$this->error(lang('username_password_missing'), 500);
			exit;
		}
		
		$this->EE->load->library('auth');
		$this->EE->lang->loadfile('login');
		$authorized = $this->EE->auth->authenticate_username($username, $password);
		if ( ! $authorized)
		{
			$this->error(lang('username_password_invalid'), 500);
			exit;			
		}
		
		$where = array();
		$where['members.member_id'] = $authorized->member('member_id');
		
		$data = $this->EE->member_data->get_members($where, FALSE, FALSE, FALSE, 1, FALSE);
		$this->EE->export_data->export_members($data, $this->format);		
	}
	
	public function get_ff_entries()
	{
		$form_id = $this->EE->input->get_post('form_id', FALSE);
		$form_name = $this->EE->input->get_post('form_name', FALSE);
		$author_id = $this->EE->input->get_post('author_id', FALSE);
		$entry_id = $this->EE->input->get_post('entry_id', FALSE);
		$status = $this->EE->input->get_post('status', FALSE);
	
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
	
		foreach($_GET as $param => $value)
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
		$this->EE->export_data->export_freeform_entries($entries, $this->format, 'freeform');
		exit;
	}	
	
	public function get_file()
	{
		$this->error(lang('bad_method'), 500);
	}
	
	public function get_files()
	{
		$this->error(lang('bad_method'), 500);
	}
	
	private function _setup_api()
	{
		if($this->settings['enable_api'] != '1')
		{
			$this->error(lang('api_disabled'), 500);
			exit;
		}
				
		$this->format = $this->_val_format($this->EE->input->get_post('format'));
		if(!$this->format || $this->format == '')
		{
			$this->error(lang('missing_format'), 500);
			exit;			
		}

		$this->key = $this->EE->input->get_post('key');
		if(!$this->key || $this->key == '' || $this->key != $this->EE->encrypt->decode($this->settings['api_key']))
		{
			$this->error(lang('bad_key'), 500);
			exit;			
		}
		
		$this->method = $this->EE->input->get_post('method');
		if ($this->EE->extensions->active_hook('export_it_api_start') === TRUE)
		{
			if($this->EE->extensions->call('export_it_api_start', $this))
			{
				if ($this->EE->extensions->end_script === TRUE) return;
			}
		}

		if(!$this->method || $this->method == '' || !method_exists($this, $this->method))
		{
			$this->error(lang('bad_method'), 500);
			exit;				
		}
	}
	
	private function _val_format($format)
	{
		$return = 'json';
		switch($format)
		{
			case 'json':
			case 'xml':
				return $format;
			break;
		}		
	}
	
	/**
	 * Error handler
	 * @param unknown_type $output
	 * @param unknown_type $http_code
	 * @param unknown_type $format
	 */
	public function error($output, $http_code, $format = 'json')
	{
		$return = json_encode(array('status' => $http_code, 'message' => $output));
		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
		header('Content-Length: ' . strlen($return));
		echo $return;
	}
}