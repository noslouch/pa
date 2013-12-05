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
 * Export It - Disqus Export library
 *
 * A wrapper to create a disqus XML file
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_data/Export_disqus.php
 */
class Export_disqus
{
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function generate(array $arr)
	{
		$return = '<?xml version="1.0" encoding="UTF-8"?>
					<rss version="2.0"
					  xmlns:content="http://purl.org/rss/1.0/modules/content/"
					  xmlns:dsq="http://www.disqus.com/"
					  xmlns:dc="http://purl.org/dc/elements/1.1/"
					  xmlns:wp="http://wordpress.org/export/1.0/">
					  <channel>
    	';
		
		foreach($arr AS $entry_id => $entry)
		{
			$return .= '<item>';
			$return .= '<title>'.$entry['title'].'</title>';
			$url = $entry['channel_url'].'/'.$entry['url_title'];
			if(substr($entry['channel_url'], 0, 7) != 'http://' && substr($entry['channel_url'], 0, 8) != 'https://')
			{
				$url = $this->EE->config->config['site_url'].$entry['channel_url'].$entry['entry_url_title'];
			}
			
			$return .= '<link>'.$url.'</link>';
			$return .= '<content:encoded><![CDATA['.$entry['entry_title'].']]></content:encoded>';
			$return .= '<dsq:thread_identifier>'.$entry_id.'</dsq:thread_identifier>';
			$return .= '<wp:post_date_gmt>'.m62_convert_timestamp($entry['entry_date'], "%Y-%m-%d %H:%i:%s").'</wp:post_date_gmt>';
			$return .= '<wp:comment_status>open</wp:comment_status>';
			
			
			foreach($entry['comments'] AS $comment_id => $comment)
			{
				$return .= '<wp:comment>';
				$return .= '<wp:comment_id>'.$comment_id.'</wp:comment_id>';
	    		$return .= '<wp:comment_author>'.$comment['name'].'</wp:comment_author>';
	    		$return .= '<wp:comment_author_email>'.$comment['email'].'</wp:comment_author_email>';
				$return .= '<wp:comment_author_url>'.$comment['url'].'</wp:comment_author_url>';
	    		$return .= '<wp:comment_author_IP>'.$comment['ip_address'].'</wp:comment_author_IP>';
				$return .= '<wp:comment_date_gmt>'.m62_convert_timestamp($comment['comment_date'], "%Y-%m-%d %H:%i:%s").'</wp:comment_date_gmt>';
				$return .= '<wp:comment_content><![CDATA['.$comment['comment'].']]></wp:comment_content>';
	    		$return .= '<wp:comment_approved>'.($comment['status'] == 'o' ? '1' : '0').'</wp:comment_approved>';
				$return .= '<wp:comment_parent>0</wp:comment_parent>';
				$return .= '</wp:comment>';
			}
			$return .= '</item>';
		}
		
		$return .= '
				  </channel>
				</rss>
		';

		return $return;
	}
}