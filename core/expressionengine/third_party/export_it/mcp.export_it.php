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
 * Export It - CP Class
 *
 * Control Panel class
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/mcp.export_it.php
 */
class Export_it_mcp 
{
	public $url_base = '';
	
	public $perpage = '20';
	
	/**
	 * The name of the module; used for links and whatnots
	 * @var string
	 */
	private $mod_name = 'export_it';
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		//load EE stuff
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		$this->EE->load->model('export_it_settings_model', 'export_it_settings');
		$this->EE->load->library('export_it_lib');
		$this->EE->load->library('export_it_js');
		$this->EE->load->library('member_data');
		$this->EE->load->library('channel_data');
		$this->EE->load->library('mailinglist_data');
		$this->EE->load->library('comment_data');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('json_ordering');
		$this->EE->load->library('Export_data/export_data');	

		$this->settings = $this->EE->export_it_lib->get_settings();		

		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
		$this->url_base = BASE.AMP.$this->query_base;
		$this->EE->export_it_lib->set_url_base($this->url_base);
		
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name, $this->EE->lang->line('export_it_module_name'));
		$this->EE->cp->set_right_nav($this->EE->export_it_lib->get_right_menu());	
		
		
		$this->errors = $this->EE->export_it_lib->error_check();
		$this->EE->load->vars(array(
				'url_base' => $this->url_base, 
				'query_base' => $this->query_base, 
				'settings' => $this->settings, 
				'errors' => $this->errors, 
				'cp_page_title' => $this->EE->lang->line('export_it_module_name')
		));
		
	}
	
	public function index()
	{
		$this->EE->functions->redirect($this->url_base.'members');
		exit;
	}
	
	public function members()
	{	
		$total = $this->EE->member_data->get_total_members();
		$vars['members'] = $this->EE->member_data->get_members(FALSE, FALSE, TRUE, $this->settings['members_list_limit'], '0', 'members.member_id ASC');		

		$this->EE->cp->add_js_script(array('plugin' => 'dataTables','ui' => 'datepicker'));
		$dt = $this->EE->export_it_js->get_members_datatables('export_members_ajax_filter', 6, 1, $this->settings['members_list_limit']);
		$this->EE->javascript->output($dt);
		$this->EE->javascript->compile();
		$this->EE->load->library('pagination');


		$vars['pagination'] = $this->EE->export_it_lib->create_pagination('export_members_ajax_filter', $total, $this->settings['members_list_limit']);
				
		$vars['total_members'] = $total;
		$vars['date_selected'] = '';
		$vars['member_keywords'] = '';
		
		$first_date = $this->EE->member_data->get_first_date();
		if($first_date)
		{
			$vars['default_start_date'] = m62_convert_timestamp($first_date, '%Y-%m-%d');
		}
		else
		{
			$vars['default_start_date'] = m62_convert_timestamp(mktime(), '%Y-%m-%d');
		}		

		$vars['perpage_select_options'] = $this->EE->export_it_lib->perpage_select_options();
		$vars['date_select_options'] = $this->EE->export_it_lib->date_select_options();		
				
		$vars['member_groups_dropdown'] = $this->EE->member_data->get_member_groups();
		$vars['export_format'] = $this->EE->export_it_lib->export_formats('members');
		$this->EE->load->vars(array(
				'cp_page_title' => $this->EE->lang->line('members')
		));
		return $this->EE->load->view('members', $vars, TRUE);		
	}
	
	public function export_members_ajax_filter()
	{
		die($this->EE->json_ordering->member_ordering($this->perpage, $this->url_base));
	}		
	
	public function channel_entries()
	{	
		$channel_id = $this->EE->input->get_post('channel_id');		
		$this->EE->channel_data->translate_cft = FALSE; //disable checking custom field data
		$total = $this->EE->channel_data->get_total_entries();
		
		$vars = array();
		$where = FALSE;
		$vars['status_options'] = $vars['category_options'] = array('' => 'All');
		if($channel_id)
		{
			$where = array('ct.channel_id' => $channel_id);
			$options = $this->EE->channel_data->get_channel_statuses($channel_id);
			foreach($options AS $item)
			{
				$vars['status_options'][$item['status']] = $item['status'];
			}
			
			$options = $this->EE->channel_data->get_channel_categories($channel_id);
			foreach($options AS $item)
			{
				$vars['category_options'][$item['cat_id']] = $item['cat_name'];
			}			
		}
		
		$vars['entries'] = $this->EE->channel_data->get_entries($where, $this->settings['channel_entries_list_limit']);
		$vars['channel_id'] = $channel_id;
		$this->EE->cp->add_js_script(array('plugin' => 'dataTables','ui' => 'datepicker'));
		$dt = $this->EE->export_it_js->get_channel_entries_datatables('export_channel_entries_ajax_filter', 3, 1, $this->settings['channel_entries_list_limit'],'"aaSorting": [[ 3, "desc" ]],');
		$this->EE->javascript->output($dt);		
		$this->EE->javascript->compile();
		$this->EE->load->library('pagination');
		
		$vars['pagination'] = $this->EE->export_it_lib->create_pagination('export_channel_entries_ajax_filter', $total, $this->settings['channel_entries_list_limit']);
		$vars['total_entries'] = $total;
		
		$vars['date_selected'] = '';
		$vars['keywords'] = '';
		$vars['perpage_select_options'] = $this->EE->export_it_lib->perpage_select_options();
		
		$first_date = $this->EE->channel_data->get_first_date();
		if($first_date)
		{
			$vars['default_start_date'] = m62_convert_timestamp($first_date, '%Y-%m-%d');
		}
		else
		{
			$vars['default_start_date'] = m62_convert_timestamp(mktime(), '%Y-%m-%d');
		}
		
		$vars['date_select_options'] = $this->EE->export_it_lib->date_select_options();
		$vars['date_select'] = $this->EE->export_it_lib->get_date_select();
		$vars['status_select'] = $this->EE->export_it_lib->get_status_select();		
		
		$vars['export_format'] = $this->EE->export_it_lib->export_formats('channel_entries');
		$vars['channel_options'] = $this->EE->export_it_lib->get_comment_channels();
		$this->EE->load->vars(array(
				'cp_page_title' => $this->EE->lang->line('channel_entries')
		));		
		return $this->EE->load->view('channel_entries', $vars, TRUE);			
	}
	
	public function export_channel_entries_ajax_filter()
	{
		die($this->EE->json_ordering->channel_entries_ordering($this->perpage, $this->url_base));
	}	
	
	public function channel_options_ajax_filter()
	{
		$channel_id = ($this->EE->input->get_post('channel_id')) ? $this->EE->input->get_post('channel_id') : FALSE;
		$type = ($this->EE->input->get_post('option_type')) ? $this->EE->input->get_post('option_type') : FALSE;
		if(!$channel_id)
		{
			return $this->EE->javascript->generate_json(array('' => 'All'), TRUE);
		}
		
		die($this->EE->json_ordering->channel_options($channel_id, $type));
	}
	
	public function comments()
	{
		
		$total = $this->EE->comment_data->get_total_comments();
		$vars['comments'] = $this->EE->comment_data->get_comments(FALSE, $this->settings['comments_list_limit']);
		
		$this->EE->cp->add_js_script(array('plugin' => 'dataTables','ui' => 'datepicker'));
		$dt = $this->EE->export_it_js->get_comments_datatables('export_comments_ajax_filter', 3, 1, $this->settings['comments_list_limit']);
		$this->EE->javascript->output($dt);
		$this->EE->javascript->compile();
		$this->EE->load->library('pagination');
		
		$vars['pagination'] = $this->EE->export_it_lib->create_pagination('export_comments_ajax_filter', $total, $this->settings['comments_list_limit']);
		$vars['total_comments'] = $total;
		
		$vars['date_selected'] = '';
		$vars['keywords'] = '';
		$vars['perpage_select_options'] = $this->EE->export_it_lib->perpage_select_options();
		$vars['export_format'] = $this->EE->export_it_lib->export_formats('mailing_list');				
		
		$first_date = $this->EE->comment_data->get_first_date();
		if($first_date)
		{
			$vars['default_start_date'] = m62_convert_timestamp($first_date, '%Y-%m-%d');
		}
		else
		{
			$vars['default_start_date'] = m62_convert_timestamp(mktime(), '%Y-%m-%d');
		}

		$vars['date_select_options'] = $this->EE->export_it_lib->date_select_options();
		$vars['export_format'] = $this->EE->export_it_lib->export_formats('comments');
		$vars['comment_channels'] = $this->EE->export_it_lib->get_comment_channels();
		$vars['date_select'] = $this->EE->export_it_lib->get_date_select();
		$vars['status_select'] = $this->EE->export_it_lib->get_status_select();
		
		$this->EE->load->vars(array(
				'cp_page_title' => $this->EE->lang->line('comments')
		));
				
		return $this->EE->load->view('comments', $vars, TRUE);		
	}
	
	public function export_comments_ajax_filter()
	{
		die($this->EE->json_ordering->comments_ordering($this->perpage, $this->url_base));
	}	
	
	public function mailing_list()
	{		
		
		$total = $this->EE->mailinglist_data->get_total_emails();
		if(isset($total->count))
		{
			$total = $total->count;
		}

		$vars['emails'] = $this->EE->mailinglist_data->get_list_emails(FALSE, $this->settings['mailing_list_limit']);
		
		$this->EE->cp->add_js_script(array('plugin' => 'dataTables'));
		$dt = $this->EE->export_it_js->get_mailing_list_datatables('export_mailing_list_ajax_filter', 3, 1, $this->settings['mailing_list_limit']);
		$this->EE->javascript->output($dt);
		$this->EE->javascript->compile();
		$this->EE->load->library('pagination');
		
		$vars['pagination'] = $this->EE->export_it_lib->create_pagination('export_mailing_list_ajax_filter', $total, $this->settings['mailing_list_limit']);
		$vars['total_emails'] = $total;
		$vars['date_selected'] = '';
		$vars['keywords'] = '';
		$vars['perpage_select_options'] = $this->EE->export_it_lib->perpage_select_options();
		$vars['export_format'] = $this->EE->export_it_lib->export_formats('mailing_list');
		$vars['mailing_lists'] = $this->EE->mailinglist_data->get_mailing_lists();
		
		$this->EE->load->vars(array(
				'cp_page_title' => $this->EE->lang->line('mailing_list')
		));
				
		return $this->EE->load->view('mailing_list', $vars, TRUE);		
	}
	
	public function export_mailing_list_ajax_filter()
	{
		die($this->EE->json_ordering->mailing_list_ordering($this->perpage, $this->url_base));
	}	

	public function settings()
	{
		if(isset($_POST['go_settings']))
		{		
			if($this->EE->export_it_settings->update_settings($_POST))
			{	
				$this->EE->logger->log_action($this->EE->lang->line('log_settings_updated'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('settings_updated'));
				$this->EE->functions->redirect($this->url_base.'settings');		
				exit;			
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('settings_update_fail'));
				$this->EE->functions->redirect($this->url_base.'settings');	
				exit;					
			}
		}
		
		$this->EE->load->vars(array(
				'cp_page_title' => $this->EE->lang->line('settings')
		));		
		
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->output($this->EE->export_it_js->get_accordian_css()); 		
		$this->EE->javascript->compile();
		
		$vars = array();
		$vars['settings_disable'] = FALSE;
		if(isset($this->EE->config->config['export_it']))
		{
			$vars['settings_disable'] = 'disabled="disabled"';
		}		

		$this->settings['api_key'] = $this->EE->encrypt->decode($this->settings['api_key']);
		$vars['api_url'] = $this->EE->config->config['site_url'].'?ACT='.$this->EE->cp->fetch_action_id('Export_it', 'api').'&key='.$this->settings['api_key'];
		$vars['settings'] = $this->settings;
		return $this->EE->load->view('settings', $vars, TRUE);
	}
	
	public function export()
	{
		$this->EE->export_it_lib->setup_memory_limits();
		
		$type = $this->EE->input->get_post('type');
		$export_format = $this->EE->input->get_post('format');
		switch($type)
		{
			case 'mailinglist':
				$format = $this->EE->input->get_post('export_format');
				$exclude_duplicates = $this->EE->input->get_post('exclude_duplicates');
				$keywords = ($this->EE->input->get_post('keywords')) ? $this->EE->input->get_post('keywords') : FALSE;
				$list_id = ($this->EE->input->get_post('list_id') && $this->EE->input->get_post('list_id') != '') ? $this->EE->input->get_post('list_id') : FALSE;
				$where = array();
				if($list_id)
				{
					$where['mailing_list.list_id'] = $list_id;
				}
	
				if($keywords)
				{
					$where['search'] = $keywords;
				}
	
				$data = $this->EE->mailinglist_data->get_list_emails($where);
				$this->EE->export_data->export_mailing_list($data, $format);
				break;
					
			case 'comments':
				$format = $this->EE->input->get_post('format');
				$date_range = $this->EE->input->get_post('date_range');
				$status = $this->EE->input->get_post('status');
				$channel_id = $this->EE->input->get_post('channel_id');
				$keywords = $this->EE->input->get_post('keywords');
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
				
				//now we need to group everything up all nice and tight
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
			
				$this->EE->export_data->export_comments($comments, $format);
				break;
					
			case 'channel_entries':
	
				$keywords = ($this->EE->input->get_post('keywords')) ? $this->EE->input->get_post('keywords') : FALSE;
				$complete = ($this->EE->input->get_post('complete_select')) ? $this->EE->input->get_post('complete_select') : FALSE;
				$channel_id = ($this->EE->input->get_post('channel_id') && $this->EE->input->get_post('channel_id') != '') ? $this->EE->input->get_post('channel_id') : FALSE;
				$status = ($this->EE->input->get_post('status') && $this->EE->input->get_post('status') != '') ? $this->EE->input->get_post('status') : FALSE;
				$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
				$category = ($this->EE->input->get_post('category') && $this->EE->input->get_post('category') != '') ? $this->EE->input->get_post('category') : FALSE;
				
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
				
				if($complete && $complete != '')
				{
					$this->EE->channel_data->complete_select = TRUE;
				}
				
				$data = $this->EE->channel_data->get_entries($where);
				$this->EE->export_data->export_channel_entries($data, $export_format);
	
			break;
					
			case 'members':
			default:
				$export_format = $this->EE->input->get_post('format');
				$group_id = $this->EE->input->get_post('group_id');
				$include_custom_fields = $this->EE->input->get_post('include_custom_fields');
				$complete_select = $this->EE->input->get_post('complete_select');
	
				$group_id = ($this->EE->input->get_post('group_id') && $this->EE->input->get_post('group_id') != '') ? $this->EE->input->get_post('group_id') : FALSE;
				$date_range = ($this->EE->input->get_post('date_range') && $this->EE->input->get_post('date_range') != '') ? $this->EE->input->get_post('date_range') : FALSE;
				$keywords = ($this->EE->input->get_post('member_keywords') && $this->EE->input->get_post('member_keywords') != '') ? $this->EE->input->get_post('member_keywords') : FALSE;
	
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
				
				$data = $this->EE->member_data->get_members($where, $include_custom_fields, $complete_select, FALSE, 0, FALSE);
	
				$this->EE->export_data->export_members($data, $export_format);
				break;
		}
	
		exit;
	}	
}