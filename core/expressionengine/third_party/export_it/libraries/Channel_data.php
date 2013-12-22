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
 * Export It - Channel Data Library Class
 *
 * Contains all the methods for interacting with the Channel and related data
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Channel_data.php
 */
class Channel_data
{
	/**
	 * Channel Title Keys
	 * All the required keys, and base defaults, a channel entry submission array has to contain
	 * @var array
	 */
	private $ct_keys = array(
		'entry_date' => '',
		'author_id' => '1',
		'url_title' => '',
		'versioning_enabled' => '',
		'expiration_date' => '',
		'comment_expiration_date' => '',
		'status' => 'closed',
		'allow_comments' => 'n',
		'channel_id' => ''
	);
	
	/**
	 * Container for the fields so they're only processed once
	 * @var array
	 */
	private $channel_fields = array();
	
	/**
	 * Container for ALL the field ids so they're only processed once
	 * @var array
	 */
	private $channel_field_ids = array();
	
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
		$this->EE->load->model('channel_model');
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');
		$this->EE->api->instantiate('channel_fields');
		$this->EE->load->helper('custom_field');
		$this->EE->load->library('member_data');
		$this->EE->load->model('category_model');
	}
	
	/**
	 * Returns a channel by a given channel_name
	 * @param string $name
	 */
	public function get_channel_by_name($name)
	{
		$this->EE->db->where('channel_name', $name);
		
		return $this->EE->db->get('channels');
	}
	
	/**
	 * Returns a channel by a given channel_id
	 * @param string $name
	 */
	public function get_channel($channel_id)
	{
		$this->EE->db->where('channel_id', $channel_id);
		
		return $this->EE->db->get('channels');
	}
	/**
	 * Returns a channel by a given $where
	 * @param string $name
	 */
	public function get_channels($where = FALSE)
	{
		if($where)
		{
			$this->gen_where($where);
		}
		
		return $this->EE->db->get('channels');
	}
	
	/**
	 * Returns a channel field by a given field_name
	 * @param string $name
	 */
	public function get_field_by_name($name)
	{
		$this->EE->db->where('field_name', $name);
		
		return $this->EE->db->get('channel_fields');
	}
	
	/**
	 * Returns a channel field by a given field_id
	 * @param int $id
	 */
	public function get_field($id)
	{
		$this->EE->db->where('field_id', $id);
		
		return $this->EE->db->get('channel_fields');
	}
	
	/**
	 * Returns the channel fields based on $where
	 * @param mixed $where
	 */	
	public function get_fields($where = FALSE)
	{
		if($where)
		{
			$this->gen_where($where);
		}
		
		return $this->EE->db->get('channel_fields');
	}
	
	/**
	 * Returns the amount of entries based on $where
	 * @param mixed $where
	 */
	public function get_total_entries($where = FALSE)
	{
		$this->EE->db->select("COUNT(title) AS count ");
		$this->EE->db->from('channel_titles ct');
		$this->EE->db->join('channel_data cd', 'ct.entry_id = cd.entry_id', FALSE);
		$this->EE->db->join('channels c', 'ct.channel_id = c.channel_id', FALSE);		
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
	public function get_entries($where = FALSE, $limit = FALSE, $page = '0', $order = 'entry_date DESC')
	{
		if(isset($where['search']) && !$this->channel_field_ids)
		{
			$this->channel_field_ids = $this->get_channel_field_ids();
		}
		
		if($this->complete_select)
		{
			$this->EE->db->select("ct.*, cd.*, c.*");
		}
		else
		{
			$this->EE->db->select("ct.title, ct.url_title, ct.status, ct.entry_date, cd.*, c.channel_id, c.channel_title, ct.author_id");
		}
		
		$this->EE->db->select("m.username, m.member_id, m.screen_name, m.email, m.join_date, m.last_visit, m.group_id, m.member_id, m.in_authorlist");
		$this->EE->db->from('channel_titles ct');
		$this->EE->db->join('channel_data cd', 'ct.entry_id = cd.entry_id');
		$this->EE->db->join('channels c', 'ct.channel_id = c.channel_id');
		$this->EE->db->join('members m', 'ct.author_id = m.member_id');

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
		return $this->_translate_custom_fields($data->result_array());
	}
	
	/**
	 * Returns a single entry
	 * @param array $where
	 */
	public function get_entry(array $where)
	{
		$this->EE->db->select("ct.*, cd.*");
		$this->EE->db->select("m.username, m.member_id, m.screen_name, m.email, m.join_date, m.last_visit, m.group_id, m.member_id, m.in_authorlist");
		
		
		$this->EE->db->from('channel_titles ct');
		$this->EE->db->join('channel_data cd', 'ct.entry_id = cd.entry_id');
		$this->EE->db->join('members m', 'ct.author_id = m.member_id');
				
		if(isset($where['entry_id']))
		{
			$this->EE->db->where('ct.entry_id', $where['entry_id']);
		}
		
		elseif(isset($where['url_title']))
		{
			$this->EE->db->where('ct.url_title', $where['url_title']);
		}

		$data = $this->EE->db->get();
		$return = $data->result_array();
		if(isset($return['0']['channel_id']) && $return['0']['channel_id'] != '')
		{
			return $this->_translate_custom_fields($return, $return['0']['channel_id']);
		}
	}

	/**
	 * Helper function to return only the entry_id from an entry
	 * @param string $url_title
	 * @param int $channel_id
	 */
	public function get_entry_id($url_title, $channel_id)
	{
		$entry = $this->EE->db->get_where('channel_titles', array('url_title' => $url_title, 'channel_id' => $channel_id));
		if($entry->num_rows == '1')
		{
			$entry = $entry->row();
			return $entry->entry_id;
		}		
	}
	
	/**
	 * Returns the date for the very first entry in the system.
	 * @param mixed $where
	 */
	public function get_first_date($where = FALSE)
	{
		if($where)
		{
			$this->gen_entry_where($where);
		}
	
		$this->EE->db->select_min('entry_date');
		$data = $this->EE->db->get('channel_titles');
		return $data->row('entry_date');
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
	
	/**
	 * Returns an array of the statuses for the channel
	 * @return array
	 */
	public function get_channel_statuses($channel_id = FALSE)
	{
		$channel_info = $this->EE->channel_model->get_channel_info($channel_id);
		$status_group = FALSE;
		foreach($channel_info->result_array() AS $row)
		{
			$status_group = $row['status_group'];
			break;
		}
		
		if($status_group)
		{
			return $this->EE->channel_model->get_channel_statuses($status_group)->result_array();
		}
	}

	/**
	 * Returns an array of the categories for the channel
	 * @return array
	 */
	public function get_channel_categories($channel_id = FALSE)
	{
		$channel_info = $this->EE->channel_model->get_channel_info($channel_id);
		$status_group = FALSE;
		foreach($channel_info->result_array() AS $row)
		{
			$cat_groups = explode('|',$row['cat_group']);
			break;
		}

		if($cat_groups)
		{
			$return = array();
			foreach($cat_groups AS $cat_group)
			{
				$data = $this->EE->category_model->get_channel_categories($cat_group)->result_array();
				foreach($data AS $cat)
				{
					$return[] = $cat;
				}
			}

			return $return; 
		}
	}

	/**
	 * Grabs the channel field ids
	 * @return multitype:unknown
	 */
	public function get_channel_field_ids()
	{
		$this->EE->db->select("field_id");
		$this->EE->db->from('channel_fields cf');
		$data = $this->EE->db->get();
		$result = $data->result_array();
		$return = array();
		foreach($result AS $item)
		{
			$return[] = $item['field_id'];
		}
		
		return $return;
	}
	
	/**
	 * Takes the raw entry data from the DB and returns an associative array with human friendly keys
	 * @param array $data
	 */
	public function _translate_custom_fields(array $data)
	{		
		if($this->translate_cft === FALSE)
		{
			return $data;
		}
		
		foreach($data AS $key => $entry)
		{
			if(!isset($this->channel_fields[$entry['channel_id']]))
			{
				$channel_data = $this->EE->channel_model->get_channel_info($entry['channel_id'])->row();
				$this->channel_fields[$entry['channel_id']]  = $this->EE->channel_model->get_channel_fields($channel_data->field_group)->result_array();								
			}

			if(isset($entry['entry_date']))
			{
				$data[$key]['entry_date'] = m62_convert_timestamp($data[$key]['entry_date']);
			}
			
			$channel_fields = $this->channel_fields[$entry['channel_id']];
			$data[$key]['channel_name'] = $channel_data->channel_name;
			if(isset($entry['author_id']) && isset($entry['username']))
			{
				$data[$key]['author_data'] = array(
						'username' => $entry['username'],
						'member_id' => $entry['member_id'],
						'screen_name' => $entry['screen_name'],
						'email' => $entry['email'],
						'join_date' => $entry['join_date'],
						'last_visit' => $entry['last_visit'],
						'group_id' => $entry['group_id'],
						'in_authorlist' => $entry['in_authorlist']
				);
				unset($data[$key]['in_authorlist']);
				unset($data[$key]['username']);
				unset($data[$key]['screen_name']);
				unset($data[$key]['email']);
				unset($data[$key]['join_date']);
				unset($data[$key]['group_id']);
			}			
			
			foreach($channel_fields AS $field)
			{
				if(array_key_exists('field_id_'.$field['field_id'], $entry))
				{
					$data[$key][$field['field_name']] = $entry['field_id_'.$field['field_id']];
					$data[$key][$field['field_name']] = $this->clean_custom_field($data[$key], $entry, $field);
				}
			}
		}
		
		//now remove extra custom field crudge
		$arr = array();
		foreach($data AS $k => $entry)
		{
			foreach($entry AS $key => $value)
			{
				if(stristr($key, 'field_id_') !== FALSE || stristr($key, 'field_ft_') !== FALSE)
				{
					unset($data[$k][$key]);
				}
			}
		}

		return $data;
	}
	
	/**
	 * Takes the raw custom field data and converts the stored data into the expected Custom Fieldtype structure
	 * @param array $data
	 * @param int $entry
	 * @param string $field
	 */
	public function clean_custom_field($data, $entry, $field)
	{
		switch($field['field_type'])
		{
			case 'image':
			case 'file':
				$data[$field['field_name']] = $this->clean_image_data($entry['field_id_'.$field['field_id']]);	
			break;
			
			case 'date':
				$data[$field['field_name']] = m62_convert_timestamp($entry['field_id_'.$field['field_id']]);
			break;			
			
			case 'assets':
				$data[$field['field_name']] = $this->clean_assets_data($entry['field_id_'.$field['field_id']]);
			break;
			
			case 'playa':
				$data[$field['field_name']] = $this->clean_playa_data($entry['field_id_'.$field['field_id']]);	
			break;
			
			case 'channel_files':
				$data[$field['field_name']] = $this->clean_channel_files_data($entry['entry_id'], $field['field_id']);	
			break;
			
			case 'vmg_chosen_member':
				$data[$field['field_name']] = $this->clean_vmg_chosen_member_data($entry['field_id_'.$field['field_id']]);	
			break;
			
			case 'tagger':
				$data[$field['field_name']] = $this->clean_tagger_data($entry['field_id_'.$field['field_id']]);	
			break;
			
			case 'channel_videos':
				$data[$field['field_name']] = $this->clean_channel_videos_data($entry['entry_id'], $field['field_id']);	
			break;	
	
			case 'securitee':
				$data[$field['field_name']] = $this->clean_securitee_data($entry['field_id_'.$field['field_id']]);	
			break;
			
			case 'matrix':
				$data[$field['field_name']] = $this->clean_matrix_data($entry['entry_id'], $field['field_id']);	
			break;
			
			case 'cartthrob_price_simple':
				$data[$field['field_name']] = $this->clean_cartthrob_price_simple_data($entry['field_id_'.$field['field_id']]);
			break;
			
			case 'cartthrob_price_modifiers':
				$data[$field['field_name']] = $this->clean_cartthrob_price_modifiers_data($entry['field_id_'.$field['field_id']]);
			break;
			
			case 'cartthrob_discount':
				$data[$field['field_name']] = $this->clean_cartthrob_discount_data($entry['field_id_'.$field['field_id']]);
			break;

			case 'cartthrob_order_items':
				$data[$field['field_name']] = $this->clean_cartthrob_order_items($entry['entry_id'], $entry['field_id_'.$field['field_id']]);
			break;
		}

		return $data[$field['field_name']];
	}
	
	public function clean_cartthrob_price_simple_data($str)
	{
		if(!file_exists(PATH_THIRD.'cartthrob/'))
		{
			return $str;
		}
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('number');
		return $this->EE->number->format($str);
	}
	
	public function clean_cartthrob_price_modifiers_data($str)
	{
		$data = @unserialize(base64_decode($str));
		if(is_array($data))
		{
			$return = array();
			foreach($data AS $k => $item)
			{
				//$return[$k] = array();
				foreach($item AS $key => $value)
				{
					$return[$data[$k]['option_value']][$key] = $value;
				}
			}
			return $return;
		}
	}
	
	public function clean_cartthrob_discount_data($data)
	{
		if($data)
		{
			$data = base64_decode($data);
			if(strlen($data) >= 3)
			{
				$data = @unserialize($data);
				if(is_array($data))
				{
					return $data;
				}
			}
		}
	}

	public function clean_cartthrob_order_items($entry_id, $data)
	{
		$this->EE->db->select("*");
		$this->EE->db->from('cartthrob_order_items coi');
		$this->EE->db->where('coi.order_id', $entry_id);
		$data = $this->EE->db->get();
		$arr = $data->result_array();
		$total = count($arr);
		if($total == '0')
		{
			return;
		}

		$ignore = array('row_id', 'row_order', 'order_id', 'entry_id');
		$return = array();
		foreach($arr AS $key => $value)
		{
			foreach($value AS $k => $v)
			{
				if(!in_array($k, $ignore))
				{
					$return[$key]['product_'.$k] = $v;
				}
			}
		}

		return $return;
	}
	
	public function clean_tagger_data($data)
	{
		$arr = explode(",",$data);
		if(count($arr) >= '1')
		{
			$return = array();
			$i = 0;
			foreach($arr AS $tag)
			{
				if($tag != '')
				{
					$return[$i]['tag'] = $tag;
				}
				$i++;
			}
			return $return;
		}
	}
	
	public function clean_channel_videos_data($entry_id, $field_id)
	{
		$this->EE->db->select("video_id");
		$this->EE->db->from('channel_videos cv');
		$this->EE->db->where('cv.entry_id', $entry_id);
		$this->EE->db->where('cv.field_id', $field_id);	
		$data = $this->EE->db->get();
		$arr = $data->result_array();
		if(count($arr) >= '1')
		{
			$return = array();
			foreach($arr AS $file)
			{
				if($file['video_id'] != '')
				{
					$return[]['channel_videos'] = $file['video_id'];
				}
			}
			return $return;
		}		
	}
	
	public function clean_channel_files_data($entry_id, $field_id)
	{
		$this->EE->db->select("*");
		$this->EE->db->from('channel_files cf');
		$this->EE->db->where('cf.entry_id', $entry_id);	
		$this->EE->db->where('cf.field_id', $field_id);	
	
		$data = $this->EE->db->get();
		$arr = $data->result_array();
		if(count($arr) >= '1')
		{
			$return = array();
			$i = 0;
			foreach($arr AS $file)
			{
				foreach($file AS $key => $value)
				{
					$return[$i]['channel_file'][$key] = $value;
					if($key == 'member_id')
					{
						$return[$i]['channel_file']['member_info'] = $this->EE->member_data->get_member($value);
					}
					
					if($key == 'date')
					{
						$return[$i]['channel_file']['date'] = m62_convert_timestamp($value);
					}
				}
				$i++;
			}
			return $return;
		}
	}
	
	public function clean_vmg_chosen_member_data($members)
	{
		$arr = explode("|", $members);
		if(count($arr) >= '1')
		{
			$return = array();
			$i = 0;
			foreach($arr AS $member)
			{
				if($member != '')
				{
					$return[$i]['member'][$i] = $this->EE->member_data->get_member($member);
				}
				$i++;
			}
			return $return;
		}		
	}
	
	public function clean_image_data($image)
	{
		$paths = $this->EE->functions->fetch_file_paths();
		foreach($paths AS $key => $value)
		{
			$image = str_replace('{filedir_'.$key.'}', $value, $image);
		}

		return $image;
	}
	
	public function clean_assets_data($assets)
	{
		$images = explode("\n", $assets);
		$return = array();
		foreach($images AS $image)
		{
			$return[]['asset'] = $this->clean_image_data($image);
		}

		return $return;
	}	
	
	public function clean_playa_data($data)
	{
		preg_match_all("/\[(.*?)\]/", $data, $matches);
		if(isset($matches['1']) && is_array($matches['1']))
		{
			$return = array();
			foreach($matches['1'] AS $match)
			{
				if($match != '')
				{
					$title_part = preg_replace("/\[(.*?)\]/", '', $data);
					$parts = explode('-', $title_part, 2);
					$title = (isset($parts['0']) && $parts['0'] != '' ? $parts['0'] : 'N/A');
					$url_title = (isset($parts['1']) && $parts['1'] != '' ? $parts['1'] : 'N/A');
					$return[]['relationship'] = array('entry_id' => $match, 'title' => $title, 'url_title' => $url_title);
				}
			}
			
			return $return;
		}
		
	}
	
	public function clean_securitee_data($data)
	{
		return $this->EE->encrypt->decode(htmlspecialchars_decode($data));
	}

	public function clean_matrix_data($entry_id = FALSE, $field_id = '')
	{
		$this->EE->db->select("md.*");
		$this->EE->db->from('matrix_data md');
		if($entry_id)
		{
			$this->EE->db->where('md.entry_id', $entry_id);
		}
		
		$this->EE->db->where('md.field_id', $field_id);
		$data = $this->EE->db->get();
		if($data->num_rows == '0')
		{
			return FALSE;
		}
		
		//now get cols
		$this->EE->db->select("mc.*");
		$this->EE->db->from('matrix_cols mc');
		$this->EE->db->where('mc.field_id', $field_id);
		$cols = $this->EE->db->get();
		if($cols->num_rows == '0')
		{
			return FALSE;
		}

		$data = $data->result_array();
		$cols = $cols->result_array();

		$return = array();
		$count = 0;
		foreach($data AS $row)
		{

			foreach($cols AS $key => $col)
			{	
				$obj = 'col_id_'.$col['col_id'];
				$item = $row[$obj];
				if(isset($item) && $item != '')
				{
					$return[$count]['matrix'][$count][$col['col_name']] = $item;
					switch($col['col_type'])
					{
						case 'image':
						case 'file':
							$return[$count]['matrix'][$count][$col['col_name']] = $this->clean_image_data($item);	
						case 'assets':
							$return[$count]['matrix'][$count][$col['col_name']] = $this->clean_assets_data($item);
						break;
						case 'playa':
							$return[$count]['matrix'][$count][$col['col_name']] = $this->clean_playa_data($item);	
						break;
						case 'vmg_chosen_member':
							$return[$count]['matrix'][$count][$col['col_name']] = $this->clean_vmg_chosen_member_data($item);	
						break;
						case 'tagger':
							$return[$count]['matrix'][$count][$col['col_name']] = $this->clean_tagger_data($item);	
						break;	
						case 'securitee':
							$return[$count]['matrix'][$count][$col['col_name']] = $this->clean_securitee_data($item);	
						break;
					}				
				}			
			}
			$count++;
		}
		return $return;		
	}

	public function get_comments(array $where)
	{
		$this->EE->db->select("c.*");
		$this->EE->db->select("ct.title AS entry_title, ct.url_title AS entry_url_title, ct.entry_date");
		$this->EE->db->select("channel_url");
		
		
		$this->EE->db->from('comments c');
		$this->EE->db->join('channel_titles ct', 'ct.entry_id = c.entry_id');
		$this->EE->db->join('channels ch', 'ch.channel_id = ct.channel_id');
		
		if(isset($where['channel_id']))
		{
			$this->EE->db->where('c.channel_id', $where['channel_id']);
		}
		elseif(isset($where['entry_id']))
		{
			$this->EE->db->where('c.entry_id', $where['entry_id']);
		}
		elseif(isset($where['comment_id']))
		{
			$this->EE->db->where('c.comment_id', $where['comment_id']);
		}
		
		if(isset($where['status']))
		{
			$this->EE->db->where('c.status', $where['status']);
		}
		
		if(isset($where['comment_date']))
		{
			$this->EE->db->where('c.comment_date >=', $where['comment_date']);
		}

		$data = $this->EE->db->get();
		return $data->result_array();
	}
	
	public function get_category(array $where)
	{
		$this->EE->db->select("c.*");
		
		$this->EE->db->from('categories c');
		
		if(isset($where['cat_id']))
		{
			$this->EE->db->where('c.cat_id', $where['cat_id']);
		}
		
		if(isset($where['cat_name']))
		{
			$this->EE->db->where('c.cat_name', $where['cat_name']);
		}

		if(isset($where['cat_url_title']))
		{
			$this->EE->db->where('c.cat_url_title', $where['cat_url_title']);
		}

		$data = $this->EE->db->get();
		return $data->result_array();		
	}
	
	/**
	 * Returns an array of entry_ids for a related category
	 * @param array $where
	 */
	public function get_category_posts(array $where)
	{
		$this->EE->db->select("cp.entry_id");
		
		$this->EE->db->from('category_posts cp');
		$this->EE->db->where('cp.cat_id', $where['cat_id']);

		$data = $this->EE->db->get();
		return $data->result_array();		
	}

	public function get_categories(array $where)
	{
		$this->EE->db->select("c.*");
		$this->EE->db->from('categories c');
		$this->EE->db->join('category_posts cp', 'cp.cat_id = c.cat_id');
		$this->EE->db->where('cp.entry_id', $where['entry_id']);

		$data = $this->EE->db->get();
		return $data->result_array();		
	}
	
	public function add_update_category(array $cat_data)
	{
		$cat = $this->EE->db->get_where('categories', array('cat_url_title' => $cat_data['cat_url_title'], 'group_id' => $cat_data['group_id']));
		if($cat->num_rows == '0')
		{
			return $this->submit_new_category($cat_data);
		}
		else
		{	
			$entry = $cat->row();
			return $entry->cat_id;
		}
	}
	
	public function submit_new_category(array $cat_data)
	{
		$this->EE->db->insert('categories', $cat_data);
		return $this->EE->db->insert_id();
	}
	
	public function relate_categories($entry_id, array $categories)
	{
		$this->EE->db->delete('category_posts', array('entry_id' => $entry_id));
		foreach($categories AS $cat)
		{
			$this->EE->db->insert('category_posts', array('entry_id' => $entry_id, 'cat_id' => $cat));
		}
	}
	
	public function make_url_title($title, $channel_id = FALSE)
	{
		$word_separator = $this->EE->config->item('word_separator');
		
		$this->EE->load->helper('url');
		$url_title = url_title($title, $word_separator, TRUE);	

		if(!$channel_id)
		{
			return $url_title; //probably a category and i don't give a fuck about that right now
		}
		
		$this->EE->db->where(array('url_title' => $url_title, 'channel_id' => $channel_id));
		$count = $this->EE->db->count_all_results('channel_titles');
		if($count > 0)
		{
			$url_title = substr($url_title, 0, 70);
			$this->EE->db->where(array('url_title' => $url_title, 'channel_id' => $channel_id));
			$count = $this->EE->db->count_all_results('channel_titles');
			if($count > 0)
			{
				$this->EE->db->select("url_title, MID(url_title, ".(strlen($url_title) + 1).") + 1 AS next_suffix", FALSE);
				$this->EE->db->where("url_title REGEXP('".preg_quote($this->EE->db->escape_str($url_title))."[0-9]*$')");
				$this->EE->db->where(array('channel_id' => $channel_id));
				$this->EE->db->order_by('next_suffix', 'DESC');
				$this->EE->db->limit(1);
				$query = $this->EE->db->get('channel_titles');				
				if ($query->num_rows() == 0 OR ($query->row('next_suffix') > 99999))
				{
					return FALSE;
				}
			
				$url_title = $url_title.$query->row('next_suffix');				
			}
		}
		
		return $url_title;
	}
	
	public function add_update_entry($channel_id, $data, $translate = FALSE)
	{
		$this->EE->api_channel_entries->channel_id = $channel_id;
		if(!isset($data['url_title']) || $data['url_title'] == '')
		{
			$data['url_title'] = $this->make_url_title($data['title']);
		}

		$entry = $this->EE->db->get_where('channel_titles', array('url_title' => $data['url_title'], 'channel_id' => $channel_id));
		if($entry->num_rows == '0')
		{
			$this->submit_new_entry($channel_id, $data, $translate);
		}
		else
		{	
			$entry = $entry->row();
			//$this->edit_entry($entry->entry_id, $data, $translate);		
		}
	}

	public function submit_new_entry($channel_id, $data, $translate = FALSE)
	{
		//translate 
		$data['channel_id'] = $channel_id;
		$data = $this->_setup_ct_defaults($data);
		if($translate)
		{
			$data = $this->_translate_to_custom_field($data, $channel_id);	
		}
		
		$this->EE->api->instantiate('channel_categories');
		$this->EE->api->categories = array();
		$this->EE->api->cat_parents = array();
		$this->EE->api->cat_array = array();
		$this->EE->api->errors = array();
		
		// Category parents - we toss the rest
		
		if (isset($data['category']) AND is_array($data['category']))
		{
			foreach ($data['category'] as $cat_id)
			{
				$this->EE->api_channel_categories->cat_parents[] = $cat_id;
			}

			if ($this->EE->api_channel_categories->assign_cat_parent == TRUE)
			{
				$this->EE->api_channel_categories->fetch_category_parents($data['category']);
			}
		}

		//unset($data['category']);
				
		$temp = $data;
		$mod_data = array();
		$meta = array(
			'channel_id'				=> $channel_id,
			'author_id'					=> $data['author_id'],
			'site_id'					=> $this->EE->config->item('site_id'),
			'ip_address'				=> $this->EE->input->ip_address(),
			'title'						=> ($this->EE->config->item('auto_convert_high_ascii') == 'y') ? ascii_to_entities($data['title']) : $data['title'],
			'url_title'					=> $data['url_title'],
			'entry_date'				=> $data['entry_date'],
			'edit_date'					=> date("YmdHis"),
			'versioning_enabled'		=> $data['versioning_enabled'],
			'year'						=> date('Y', $data['entry_date']),
			'month'						=> date('m', $data['entry_date']),
			'day'						=> date('d', $data['entry_date']),
			'expiration_date'			=> $data['expiration_date'],
			'comment_expiration_date'	=> $data['comment_expiration_date'],
			'recent_comment_date'		=> (isset($data['recent_comment_date']) && $data['recent_comment_date']) ? $data['recent_comment_date'] : 0,
			'sticky'					=> (isset($data['sticky']) && $data['sticky'] == 'y') ? 'y' : 'n',
			'status'					=> $data['status'],
			'allow_comments'			=> $data['allow_comments'],
		);
		
		$this->EE->api_channel_entries->_cache['dst_enabled'] = 'n';
		$this->EE->api_channel_entries->_cache['rel_updates'] = '2'; //hack to force relationships to update

		@$this->EE->api_channel_entries->_insert_entry($meta, $data, $mod_data);		

		$entry_id = $this->EE->api_channel_entries->entry_id;
		$this->setup_relationships($entry_id, $data);
		
		//setup categories
		if (isset($data['category']) && is_array($data['category']) && $entry_id)
		{		
			$this->relate_categories($entry_id, $data['category']);
		}
		return $entry_id;
	}
	
	/**
	 * Wrapper to upate an entry
	 * @param int $entry_id
	 * @param array $data
	 * @param bool $translate
	 * @return mixed
	 */
	public function edit_entry($entry_id, $data, $translate = FALSE)
	{
		//translate 
		$data['channel_id'] = $this->EE->api_channel_entries->channel_id;
		$data = $this->_setup_ct_defaults($data);
		if($translate)
		{
			$data = $this->_translate_to_custom_field($data, $this->EE->api_channel_entries->channel_id);	
		}
		
		$mod_data = array();
		$meta = array(
			'channel_id'				=> $this->EE->api_channel_entries->channel_id,
			'author_id'					=> $data['author_id'],
			'site_id'					=> $this->EE->config->item('site_id'),
			'ip_address'				=> $this->EE->input->ip_address(),
			'title'						=> ($this->EE->config->item('auto_convert_high_ascii') == 'y') ? ascii_to_entities($data['title']) : $data['title'],
			'url_title'					=> $data['url_title'],
			'entry_date'				=> $data['entry_date'],
			'edit_date'					=> date("YmdHis"),
			'versioning_enabled'		=> $data['versioning_enabled'],
			'year'						=> date('Y', $data['entry_date']),
			'month'						=> date('m', $data['entry_date']),
			'day'						=> date('d', $data['entry_date']),
			'expiration_date'			=> $data['expiration_date'],
			'comment_expiration_date'	=> $data['comment_expiration_date'],
			'recent_comment_date'		=> (isset($data['recent_comment_date']) && $data['recent_comment_date']) ? $data['recent_comment_date'] : 0,
			'sticky'					=> (isset($data['sticky']) && $data['sticky'] == 'y') ? 'y' : 'n',
			'status'					=> $data['status'],
			'allow_comments'			=> $data['allow_comments'],
		);
		
		$meta_keys = array_keys($meta);
		$meta_keys = array_diff($meta_keys, array('channel_id', 'entry_id', 'site_id'));

		foreach($meta_keys as $k)
		{
			unset($data[$k]);
		}		
		
		$this->EE->api_channel_entries->_cache['dst_enabled'] = 'n';
		$this->EE->api_channel_entries->_cache['rel_updates'] = '2'; //hack to force relationships to update
		$this->EE->api_channel_entries->_update_entry($meta, $data, $mod_data);;
		
		return $entry_id;
	}

	/**
	 * Setup Relationships
	 * Goes through the data looking for relationship fieldtypes and adds or updates any found
	 * @param stirng $parent_id
	 * @param array $data
	 */
	public function setup_relationships($parent_id, array $data)
	{
		$channel_data = $this->EE->channel_model->get_channel_info($data['channel_id'])->row();
		$channel_fields = $this->EE->channel_model->get_channel_fields($channel_data->field_group)->result_array();		
		foreach($channel_fields AS $field)
		{
			if($field['field_type'] == 'rel')
			{
				//now see if the field is in the $data array
				if(isset($data['field_id_'.$field['field_id']]) && $data['field_id_'.$field['field_id']] != '')
				{
					//now check to see if we have an existing entry for the value
					$check = $this->check_relationship($parent_id, $data['field_id_'.$field['field_id']]);
					if(!$check)
					{
						$rel_id = $this->add_relationship($parent_id, $data['field_id_'.$field['field_id']]);					
					}
					else
					{
						$rel_id = $check['0']['rel_id'];
					}

					$this->EE->db->where('entry_id', $parent_id);
					$this->EE->db->update('channel_data', array('field_id_'.$field['field_id'] => $rel_id));	
				}	
			}
		}		
	}
	
	/**
	 * Verifies if a relationship exists
	 * @param int $parent_id
	 * @param int $child_id
	 * @param string $type
	 * @return boolean
	 */
	public function check_relationship($parent_id, $child_id, $type = 'channel')
	{
		$rel = $this->EE->db->get_where('relationships', array('rel_parent_id' => $parent_id, 'rel_child_id' => $child_id));
		if($rel->num_rows == '0')
		{	
			return FALSE;
		}
		return $rel->result_array();
	}
	
	/**
	 * Adds relationship data to an entry
	 * @param int $parent_id
	 * @param int $child_id
	 */
	public function add_relationship($parent_id, $child_id)
	{
		$data = array(
		   'rel_parent_id' => $parent_id,
		   'rel_child_id' => $child_id,
		   'rel_type' => 'channel'
		);
		
		$this->EE->db->insert('relationships', $data); 
		return $this->EE->db->insert_id();		
	}
	
	/**
	 * Wrapper to tranlate custom field data
	 * @param array $data
	 * @param int $channel_id
	 * @return Ambigous <multitype:, unknown>
	 */
	private function _translate_to_custom_field(array $data, $channel_id)
	{
		$channel_data = $this->EE->channel_model->get_channel_info($channel_id)->row();
		$channel_fields = $this->EE->channel_model->get_channel_fields($channel_data->field_group)->result_array();		
		foreach($channel_fields AS $field)
		{
			if(array_key_exists($field['field_name'], $data))
			{
				$data['field_id_'.$field['field_id']] = $data[$field['field_name']];
				$data['field_ft_'.$field['field_id']] = $field['field_fmt'];
			}
		}

		return $data;		
	}
	
	/**
	 * Setup Channel Title Defaults
	 * Ensures that the bare minimum amount of data needed for a successful Channel Entry is submitted
	 * @param array $data
	 */
	private function _setup_ct_defaults(array $data)
	{
		foreach($this->ct_keys AS $key => $value)
		{
			if(!array_key_exists($key, $data) || $data[$key] == '')
			{
				$data[$key] = $value;
			}
		}
		return $data;
	}
}