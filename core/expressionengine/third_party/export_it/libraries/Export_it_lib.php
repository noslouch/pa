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
 * Export It - General Library Class
 *
 * Contains all the generic methods for Export It
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_it_lib.php
 */
class Export_it_lib
{
	/**
	 * Preceeds URLs 
	 * @var mixed
	 */
	private $url_base = FALSE;
	
	/**
	 * The full path to the log file for the progress bar
	 * @var string
	 */
	public $progress_log_file;
	
	/**
	 * The memory limits to attempt setting
	 * @var array
	 */
	public $memory_limits = array(
		'64MB',
		'96MB',
		'128MB',
		'160MB',
		'192MB',
		'256MB'
	);

	/**
	 * A list of valid SQL operators
	 * @var array
	 */
	private $valid_operators = array('>', '=>', '<=', '<', '=', '!=', 'LIKE');	
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->model('export_it_settings_model', 'export_it_settings');
		$this->settings = $this->get_settings();
	}
	
	public function get_settings()
	{
		if (!isset($this->EE->session->cache['export_it']['settings'])) 
		{	
			$this->EE->session->cache['export_it']['settings'] = $this->EE->export_it_settings->get_settings();
		}
		
		return $this->EE->session->cache['export_it']['settings'];
	}
	
	/**
	 * Sets up the right menu options
	 * @return multitype:string
	 */
	public function get_right_menu()
	{
		$menu = array(
				'members'		=> $this->url_base.'members',
				'channel_entries'	=> $this->url_base.'channel_entries',
				'comments'	=> $this->url_base.'comments'
		);
		
		if($this->is_installed_module('Mailinglist'))
		{
			$menu['mailing_list'] = $this->url_base.'mailing_list';
		}
		
		$menu['settings'] = $this->url_base.'settings';
		
		return $menu;
		
	}

	/**
	 * Wrapper that runs all the tests to ensure system stability
	 * @return array;
	 */
	public function error_check()
	{
		$errors = array();
		if($this->settings['license_number'] == '')
		{
			$errors['license_number'] = 'missing_license_number';
		}
		return $errors;
	}
	
	public function export_formats($type = 'channel_entries')
	{
		switch($type)
		{
			case 'members':
				return array('xls' => 'Excel', 'xml' => 'XML', 'json' => 'JSON', 'ee_xml' => 'EE Member XML');
			break; 
			
			case 'mailing_list':
				return array('xls' => 'Excel', 'xml' => 'XML', 'json' => 'JSON');
			break;
			
			case 'comments':
				return array('disqus' => 'Disqus', 'xml' => 'XML', 'json' => 'JSON');
			break;	

			case 'channel_entries':
				return array('xls' => 'Excel', 'xml' => 'XML', 'json' => 'JSON');
			break;
		}
	}
	
	public function get_comment_channels()
	{
		if (!$this->EE->cp->allowed_group('can_moderate_comments') && !$this->EE->cp->allowed_group('can_edit_all_comments'))
		{
			$query = $this->EE->channel_model->get_channels(
									(int) $this->EE->config->item('site_id'), 
									array('channel_title', 'channel_id', 'cat_group'));
		}
		else
		{
			$this->EE->db->select('channel_title, channel_id, cat_group');
			$this->EE->db->where('site_id', (int) $this->EE->config->item('site_id'));
			$this->EE->db->order_by('channel_title');
		
			$query = $this->EE->db->get('channels'); 
		}
		
		if ( ! $query)
		{
			return array();
		}

		$options = array();
		$options['0'] = lang('select_channel');
		foreach ($query->result() as $row)
		{
			$options[$row->channel_id] = $row->channel_title;
		}

		return $options;		
	}
	
	 public function get_date_select()
	 {
	 	$data = array(
	 		'' => lang('all'),
	 		1 => lang('past_day'),
	 		7 => lang('past_week'),
	 		31 => lang('past_month'),
	 		182 => lang('past_six_months'),
	 		365 => lang('past_year')
		);
		
		return $data;
	 }

	public function get_status_select()
	{
		$data = array(
			'' => lang('all'),
			'p' => lang('pending'),
			'o' => lang('open'),
			'c' => lang('closed')
		);
		
		return $data;
	}	
	
	/**
	 * Wrapper to handle CP URL creation
	 * @param string $method
	 */
	public function _create_url($method)
	{
		return $this->url_base.$method;
	}

	/**
	 * Creates the value for $url_base
	 * @param string $url_base
	 */
	public function set_url_base($url_base)
	{
		$this->url_base = $url_base;
	}
	
	public function perpage_select_options()
	{
		return array(
			   '10' => '10 '.lang('results'),
			   '25' => '25 '.lang('results'),
			   '75' => '75 '.lang('results'),
			   '100' => '100 '.lang('results'),
			   '150' => '150 '.lang('results')
		);		
	}
	
	public function date_select_options()
	{
		return array(
			   '' => lang('date_range'),
			   '1' => lang('past_day'),
			   '7' => lang('past_week'),
			   '31' => lang('past_month'),
			   '182' => lang('past_six_months'),
			   '365' => lang('past_year'),
			   'custom_date' => lang('any_date')
		);				
	}	
	
	public function create_pagination($method, $total, $per_page)
	{
		$config = array();
		$config['page_query_string'] = TRUE;
		$config['base_url'] = $this->url_base.$method;
		$config['total_rows'] = $total;
		$config['per_page'] = $per_page;
		$config['page_query_string'] = TRUE;
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		$this->EE->pagination->initialize($config);
		return $this->EE->pagination->create_links();		
	}
	
	public function is_installed_module($module_name)
	{
		$data = $this->EE->db->select('module_name')->from('modules')->like('module_name', $module_name)->get();
		if($data->num_rows == '1')
		{
			return TRUE;
		}
	}	
	
	/**
	 * Returns a valid SQL operator from a formatted string
	 * @param string $str
	 */
	public function sql_operator($string)
	{
		preg_match('/.*\s/', $string, $matches);
	
		if(isset($matches[0]))
		{
			$match = trim($matches[0]);
				
			if(in_array($match, $this->valid_operators))
			{
				return ' '.$matches[0];
			}
		}
	
		return NULL;
	}
	
	/**
	 * Returns a valid string strips of SQL operators
	 * @param string $str
	 */
	public function strip_operators($string)
	{
		preg_match('/.*\s/', $string, $matches);
	
		if(isset($matches[0]))
		{
			$match = trim($matches[0]);
				
			if(in_array($match, $this->valid_operators))
			{
				$string = preg_replace('/^'.preg_quote($match).'/', '', $string);
			}
		}
	
		return trim($string);
	}

	public function setup_memory_limits()
	{
		if(function_exists('ini_set'))
		{
			foreach($this->memory_limits AS $limit)
			{
				//ini_set('memory_limit', $limit);	
			}
		}	
	}
	
}