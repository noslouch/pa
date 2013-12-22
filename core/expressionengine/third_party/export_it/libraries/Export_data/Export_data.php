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
 * Export It - Export Library
 *
 * Contains all the Export methods. 
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_data/Export_data.php
 */
class Export_data
{
	public $disable_download = FALSE;
	
	public $save_path = FALSE;
	
	public $enable_api = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		//$this->disable_download = TRUE;
		$this->EE->load->library('Export_data/export_disqus');
		$this->EE->load->library('Export_data/export_json');
		$this->EE->load->library('Export_data/export_ee_xml');
		$this->EE->load->library('Export_data/export_xls');
		$this->EE->load->helper('utilities');
	}
	
	public function export_channel_entries(array $data, $export_format, $filename = 'channel_entry_export')
	{
		switch($export_format)
		{	
			case 'xml':
			default:
				$this->download_xml($data, $filename.'.xml', 'channel_entries', 'entry');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;
			
			case 'xls':
				$this->download_array($data, TRUE, $filename.'.xls');
			break;			
		}		
	}
	
	public function export_channel_entry($data, $export_format, $filename = 'channel_entry_export')
	{
		switch($export_format)
		{	
			case 'xml':
			default:
				$this->download_xml($data, $filename.'.xml', 'channel_entries', 'entry');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;
			
			case 'xls':
				$this->download_array($data, TRUE, $filename.'.xls');
			break;
		}		
	}	
	
	public function export_comments($data, $format, $filename = 'comment_export')
	{
		switch($format)
		{			
			case 'disqus':
			default:
				$this->download_disqus($data, TRUE, $filename.'.rss');
			break;
			
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'Comments', 'comment');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;
				
			case 'xls':
				$this->download_array($data, TRUE, $filename.'.xls');
			break;					
		}
	}
	
	public function export_comment($export_format = 'json', $comment_id = '', $filename = 'comment_export')
	{
		$where = array();
		if($comment_id != '')
		{
			$where['comment_id'] = $comment_id;
		}
		
		$data = $this->EE->channel_data->get_comments($where);
		switch($export_format)
		{
			case 'disqus':
			default:
				$this->download_disqus($data, TRUE, $filename.'.rss');
			break;
			
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'Comments', 'comment');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;			
		}
	}	
	
	public function export_members($data, $format = 'xls', $filename = 'member_export')
	{
		if($this->disable_download)
		{
			$data = $this->sanitize_member($data);
		}
		
		foreach($data AS $key => $value)
		{
			if(isset($value['join_date']))
			{
				$data[$key]['join_date'] = ($value['join_date'] != '0' ? m62_convert_timestamp($value['join_date']) : '0');
			}
		
			if(isset($value['last_entry_date']))
			{
				$data[$key]['last_entry_date'] = ($value['last_entry_date'] != '0' ? m62_convert_timestamp($value['last_entry_date']) : '0');
			}
				
			if(isset($value['last_comment_date']))
			{
				$data[$key]['last_comment_date'] = ($value['last_comment_date'] != '0' ? m62_convert_timestamp($value['last_comment_date']) : '0');
			}
				
			if(isset($value['last_activity']))
			{
				$data[$key]['last_activity'] = ($value['last_activity'] != '0' ? m62_convert_timestamp($value['last_activity']) : '0');
			}
		
			if(isset($value['last_visit']))
			{
				$data[$key]['last_visit'] = ($value['last_visit'] != '0' ? m62_convert_timestamp($value['last_visit']) : '0');
			}
			
			if(isset($value['last_forum_post_date']))
			{
				$data[$key]['last_forum_post_date'] = ($value['last_forum_post_date'] != '0' ? m62_convert_timestamp($value['last_forum_post_date']) : '0');
			}
			
			if(isset($value['last_email_date']))
			{
				$data[$key]['last_email_date'] = ($value['last_email_date'] != '0' ? m62_convert_timestamp($value['last_email_date']) : '0');
			}			
		}		
				
		switch($format)
		{
			case 'xls':
			default:
				$this->download_array($data, TRUE,  $filename.'.xls');
			break;
			
			case 'ee_xml':
				$this->download_ee_xml($data, $filename.'.xml');
			break;
			
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'members', 'member');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;			
		}		
	}	
	
	public function export_mailing_list($data, $format = 'xls', $filename = 'mailing_list_export')
	{	
		switch($format)
		{
			case 'xls':
			default:
				$this->download_array($data, TRUE, $filename.'.xls');
			break;
			
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'mailing_list', 'subscriber');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;			
		}
	}
	
	public function export_freeform_entries($data, $format = 'xls', $filename = 'freeform_export')
	{
		switch($format)
		{
			case 'xls':
			default:
				$this->download_array($data, TRUE, $filename.'.xls');
				break;
					
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'freeform', 'entry');
				break;
					
			case 'json':
				$this->download_json($data, $filename.'.json');
				break;
		}
	}	
	
	public function export_category($export_format = 'json', $cat_id = '', $filename = 'category_export')
	{
		$where = array();
		if($cat_id != '')
		{
			$where['cat_id'] = $cat_id;
		}
		
		$data = $this->EE->channel_data->get_category($where);
		switch($export_format)
		{	
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'categories', 'category');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;			
		}
	}

	public function export_category_posts($export_format = 'json', $cat_id = '', $filename = 'category_posts_export')
	{
		$where = array();
		if($cat_id != '')
		{
			$where['cat_id'] = $cat_id;
		}
		
		$data = $this->EE->channel_data->get_category_posts($where);
		switch($export_format)
		{	
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'entries', 'entry');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;			
		}
	}

	public function export_categories($export_format = 'json', $entry_id = '', $filename = 'categories_export')
	{
		$where = array();
		$where['entry_id'] = $entry_id;
		
		$data = $this->EE->channel_data->get_categories($where);
		switch($export_format)
		{	
			case 'xml':
				$this->download_xml($data, $filename.'.xml', 'categories', 'category');
			break;
			
			case 'json':
				$this->download_json($data, $filename.'.json');
			break;			
		}
	}	
	
	public function download_json(array $data, $file_name = '')
	{
		$export_data = $this->EE->export_json->generate($data);
		if(!$this->disable_download)
		{
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$file_name\"");
			echo $export_data;
		}
		elseif($this->enable_api)
		{
			header('Content-type: application/json');
			echo $export_data;
		}		
		else
		{
			$this->save_export($export_data, $file_name);
		}
		
	}
	
	public function download_xml($data, $file_name, $root_name, $branch_name = 'item', $subbranch_name = 'sub')
	{
		$this->EE->load->library('xml_writer');
	    $this->EE->xml_writer->setRootName($root_name);
	    $this->EE->xml_writer->initiate();

	    foreach($data AS $i => $item)
	    {
	    	$this->EE->xml_writer->startBranch($branch_name);
	    	foreach($item AS $key => $value)
	    	{
	    		$this->_add_xml_nodes($key, $value);
	    	}
	    	
	    	$this->EE->xml_writer->endBranch();
	    }

	    $export_data = $this->EE->xml_writer->getXml(false);	
	    if(!$this->disable_download)
	    {
	    	header("Content-type: application/octet-stream");
	    	header("Content-Disposition: attachment; filename=\"$file_name\"");
	    	echo $export_data;
	    }
	    elseif($this->enable_api)
	    {
	    	header('Content-type: text/xml');
	    	echo $export_data;
	    }
		else
		{
			$this->save_export($export_data, $file_name);
		}	    	
	}
	
	private function _add_xml_nodes($key, $value)
	{
		if(!is_array($value) && !is_numeric($key))
		{
			$wrap = TRUE;
			if(is_numeric($value))
			{
				$wrap = FALSE;
			}
			$this->EE->xml_writer->addNode($key, $value, array(), $wrap);
			return;			
			
		}

		if(is_array($value) && !is_numeric($key))
		{
			$this->EE->xml_writer->startBranch($key);
		}
		foreach($value AS $_key => $sub)
		{
			if(!is_array($sub))
			{
				$wrap = TRUE;
				if(is_numeric($value))
				{
					$wrap = FALSE;
				}				
				$this->EE->xml_writer->addNode($_key, $sub, array(), $wrap);
			}
			else 
			{					
				$this->_add_xml_nodes($_key, $sub);
				
			}				
		}
		
		if(is_array($value) && !is_numeric($key))
    	{
    		$this->EE->xml_writer->endBranch();
		}
	    			
	}
	
	public function download_ee_xml($data, $file_name = FALSE)
	{
		$export_data = $this->EE->export_ee_xml->generate($data);
		if(!$this->disable_download)
		{
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$file_name\"");
			echo $export_data;
		}
		else
		{
			$this->save_export($export_data, $file_name);
		}
	}
	
	/**
	 * Forces an array to download as a csv file
	 * @param array $arr
	 * @param bool $keys_as_headers
	 * @param bool $file_name
	 */
	public function download_array(array $arr, $keys_as_headers = TRUE, $file_name = 'download.txt')
	{
		$export_data = $this->EE->export_xls->create($arr, TRUE);
		if(!$this->disable_download)
		{
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$file_name\"");
			echo $export_data;
		}
		else
		{
			$this->save_export($export_data, $file_name);
		}
	}	
	
	/**
	 * Forces a download of the Disqus data
	 * @param array $arr
	 */
	public function download_disqus(array $arr)
	{
		$file_name = 'disqus.rss';
		$export_data = $this->EE->export_disqus->generate($arr);
		if(!$this->disable_download)
		{
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$file_name\"");
			echo $export_data;
		}
		else
		{
			$this->save_export($export_data, $file_name);
		}
	}
	
	public function save_export($export_data, $file_name)
	{
		$path = $this->save_path.'/'.$file_name;
		$this->EE->load->helper('file');
		write_file($path, $export_data, 'w');
		
	}
	
	/**
	 * Handles cleaning up the member data (remove password, etc)
	 * @param array $users
	 * @return multitype:
	 */
	private function sanitize_member(array $users)
	{
		$count = count($users);
		for($i=0; $i<$count;$i++)
		{
			if(isset($users[$i]['password']))
			{
				unset($users[$i]['password']);
			}
		}
		return $users;
	}
}