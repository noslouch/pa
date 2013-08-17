<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Helper File
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Image_helper
{
	private $package = 'channel_images';

	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->site_id = $this->EE->config->item('site_id');
		if (isset($this->EE->channel_images) == FALSE) $this->EE->channel_images = new stdClass();
	}

	// ********************************************************************************* //

	public function &get_actions()
	{
		if (class_exists('ImageAction') == FALSE) include(PATH_THIRD.'channel_images/actions/imageaction.php');

		$actions = array();

		// Get the files & sort
		$files = scandir(PATH_THIRD.'channel_images/actions/');
		sort($files);

		if (is_array($files) === FALSE || count($files) == 0) return;

		// Loop over all fields
		foreach ($files as $file)
		{
			// The file must start with: action.
            if (strpos($file, 'action.') === 0) {

                // Get the class name
                $name = str_replace(array('action.', '.php'), '', $file);
                $class = 'ImageAction_'.$name;

                // Load the file
                $path = PATH_THIRD.'channel_images/actions/'.$file;
                require_once $path;

                // Does the class exists now?
                if (class_exists($class) === FALSE) continue;

				$obj = new $class();

    			// Is it enabled? ready to use?
    			if (isset($obj->info['enabled']) == FALSE OR $obj->info['enabled'] == FALSE) continue;

    			// Store it!
				$actions[$name] = $obj;

				// We need to be sure it's formatted correctly
    			if (isset($obj->info['title']) == FALSE) unset($actions[$name]);
    			if (isset($obj->info['name']) == FALSE) unset($actions[$name]);
            }
		}

		return $actions;
	}

	// ********************************************************************************* //

	function define_theme_url()
	{
		if (defined('URL_THIRD_THEMES') === TRUE)
		{
			$theme_url = URL_THIRD_THEMES;
		}
		else
		{
			$theme_url = $this->EE->config->item('theme_folder_url').'third_party/';
		}

		// Are we working on SSL?
		if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE)
		{
			$theme_url = str_replace('http://', 'https://', $theme_url);
		}

		if (! defined('CHANNELIMAGES_THEME_URL')) define('CHANNELIMAGES_THEME_URL', $theme_url . 'channel_images/');

		return CHANNELIMAGES_THEME_URL;
	}

	// ********************************************************************************* //

	public function get_theme_url()
	{
		$theme_url = $this->EE->config->item('theme_folder_url').'third_party/' . $this->package . '/';
		$theme_url = str_replace(array('http://','https://'), '//', $theme_url);
		return $theme_url;
	}

	// ********************************************************************************* //

	function format_bytes($bytes) {
	   if ($bytes < 1024) return $bytes.' B';
	   elseif ($bytes < 1048576) return round($bytes / 1024, 2).' KB';
	   elseif ($bytes < 1073741824) return round($bytes / 1048576, 2).' MB';
	   elseif ($bytes < 1099511627776) return round($bytes / 1073741824, 2).' GB';
	   else return round($bytes / 1099511627776, 2).' TB';
	}

	// ********************************************************************************* //

	/**
	 * Grab File Module Settings
	 * @return array
	 */
	function grab_settings($site_id=FALSE)
	{

		$settings = array();

		if (isset($this->EE->session->cache['Channel_Images_Settings']) == TRUE)
		{
			$settings = $this->EE->session->cache['Channel_Images_Settings'];
		}
		else
		{
			$this->EE->db->select('settings');
			$this->EE->db->where('module_name', 'Channel_images');
			$query = $this->EE->db->get('exp_modules');
			if ($query->num_rows() > 0) $settings = unserialize($query->row('settings'));
		}

		$this->EE->session->cache['Channel_Images_Settings'] = $settings;

		if ($site_id)
		{
			$settings = isset($settings['site_id:'.$site_id]) ? $settings['site_id:'.$site_id] : array();
		}

		return $settings;
	}

	// ********************************************************************************* //

	/**
	 * Get Upload Preferences (Cross-compatible between ExpressionEngine 2.0 and 2.4)
	 * @param  int $group_id Member group ID specified when returning allowed upload directories only for that member group
	 * @param  int $id       Specific ID of upload destination to return
	 * @return array         Result array of DB object, possibly merged with custom file upload settings (if on EE 2.4+)
	 */
	function get_upload_preferences($group_id = NULL, $id = NULL, $ignore_site_id = FALSE)
	{
		if (version_compare(APP_VER, '2.4', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			$row = $this->EE->file_upload_preferences_model->get_file_upload_preferences($group_id, $id, $ignore_site_id);
			$this->EE->session->cache['upload_prefs'][$id] = $row;
			return $row;
		}

		if (version_compare(APP_VER, '2.1.5', '>='))
		{
			// for admins, no specific filtering, just give them everything
			if ($group_id == 1)
			{
				// there a specific upload location we're looking for?
				if ($id != '')
				{
					$this->EE->db->where('id', $id);
				}

				$this->EE->db->from('upload_prefs');
				if ($ignore_site_id != TRUE) $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
				$this->EE->db->order_by('name');

				$result = $this->EE->db->get();
			}
			else
			{
				// non admins need to first be checked for restrictions
				// we'll add these into a where_not_in() check below
				$this->EE->db->select('upload_id');
				$no_access = $this->EE->db->get_where('upload_no_access', array('member_group'=>$group_id));

				if ($no_access->num_rows() > 0)
				{
					$denied = array();
					foreach($no_access->result() as $result)
					{
						$denied[] = $result->upload_id;
					}
					$this->EE->db->where_not_in('id', $denied);
				}

				// there a specific upload location we're looking for?
				if ($id)
				{
					$this->EE->db->where('id', $id);
				}

				$this->EE->db->from('upload_prefs');
				if ($ignore_site_id != TRUE) $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
				$this->EE->db->order_by('name');

				$result = $this->EE->db->get();
			}
		}
		else
		{
			$this->EE->load->model('tools_model');
			$result = $this->EE->tools_model->get_upload_preferences($group_id, $id);
		}

		// If an $id was passed, just return that directory's preferences
		if ( ! empty($id))
		{
			$result = $result->row_array();
			$this->EE->session->cache['upload_prefs'][$id] = $result;
			return $result;
		}

		// Use upload destination ID as key for row for easy traversing
		$return_array = array();
		foreach ($result->result_array() as $row)
		{
			$return_array[$row['id']] = $row;
		}

		$this->EE->session->cache['upload_prefs'][$id] = $return_array;

		return $return_array;
	}

	// ********************************************************************************* //

	/**
	 * Grab File Module Settings
	 * @return array
	 */
	public function grab_field_settings($field_id)
	{
		if (isset($this->EE->session->cache['channel_images']['fiels_settings'][$field_id]) == FALSE)
		{
			$query = $this->EE->db->select('field_settings')->from('exp_channel_fields')->where('field_id', $field_id)->get();
			$settings = unserialize(base64_decode($query->row('field_settings')));
			$this->EE->session->cache['channel_images']['fiels_settings'][$field_id] = $settings;
		}
		else
		{
			$settings = $this->EE->session->cache['channel_images']['fiels_settings'][$field_id];
		}

		return $settings;
	}

	// ********************************************************************************* //

	function get_router_url($type='url', $method='channel_images_router')
	{
		// Do we have a cached version of our ACT_ID?
		if (isset($this->EE->session->cache['Channel_Images']['Router_Url'][$method]['ACT_ID']) == FALSE)
		{
			$this->EE->db->select('action_id');
			$this->EE->db->where('class', 'Channel_images');
			$this->EE->db->where('method', $method);
			$query = $this->EE->db->get('actions');
			$ACT_ID = $query->row('action_id');
		}
		else $ACT_ID = $this->EE->session->cache['Channel_Images']['Router_Url'][$method]['ACT_ID'];

		// RETURN: Full Action URL
		if ($type == 'url')
		{
			// Grab Site URL
			$url = $this->EE->functions->fetch_site_index(0, 0);

			if (defined('MASKED_CP') == FALSE OR MASKED_CP == FALSE)
			{
				// Replace site url domain with current working domain
				$server_host = (isset($_SERVER['HTTP_HOST']) == TRUE && $_SERVER['HTTP_HOST'] != FALSE) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
				$url = preg_replace('#http\://(([\w][\w\-\.]*)\.)?([\w][\w\-]+)(\.([\w][\w\.]*))?\/#', "http://{$server_host}/", $url);
			}

			// Config Overrife for ACT URL's?
			$over = $this->EE->config->item('channel_images_act_url');
			if (is_array($over) == TRUE)
			{
				if (isset($over[$method]) == TRUE) $url = $over[$method];
			}

			// Create new URL
			$ajax_url = $url.QUERY_MARKER.'ACT=' . $ACT_ID;

			// Are we working on SSL?
			if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE)
			{
				$ajax_url = str_replace('http://', 'https://', $ajax_url);
			}

			if (isset($this->EE->session->cache['Channel_Images']['Router_Url'][$method]['URL']) == TRUE) return $this->EE->session->cache['Channel_Images']['Router_Url'][$method]['URL'];
			$this->EE->session->cache['Channel_Images']['Router_Url'][$method]['URL'] = $ajax_url;
			return $this->EE->session->cache['Channel_Images']['Router_Url'][$method]['URL'];
		}

		// RETURN: ACT_ID Only
		if ($type == 'act_id') return $ACT_ID;
	}

	// ********************************************************************************* //

	public function is_ssl()
	{
		$is_SSL = FALSE;

		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    		|| $_SERVER['SERVER_PORT'] == 443) {

    		$is_SSL = TRUE;
		}


		return $is_SSL;
	}

	// ********************************************************************************* //

	/**
	 * Generate new XID
	 *
	 * @return string the_xid
	 */
	function xid_generator()
	{
		// Maybe it's already been made by EE
		if (defined('XID_SECURE_HASH') == TRUE) return XID_SECURE_HASH;

		// First is secure_forum enabled?
		if ($this->EE->config->item('secure_forms') == 'y')
		{
			// Did we already cache it?
			if (isset($this->EE->session->cache['XID']) == TRUE && $this->EE->session->cache['XID'] != FALSE)
			{
				return $this->EE->session->cache['XID'];
			}

			// Is there one already made that i can use?
			$this->EE->db->select('hash');
			$this->EE->db->from('exp_security_hashes');
			$this->EE->db->where('ip_address', $this->EE->input->ip_address());
			$this->EE->db->where('`date` > UNIX_TIMESTAMP()-3600');
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$this->EE->session->cache['XID'] = $row->hash;
				return $this->EE->session->cache['XID'];
			}

			// Lets make one then!
			$XID	= $this->EE->functions->random('encrypt');
			$this->EE->db->insert('exp_security_hashes', array('date' => $this->EE->localize->now, 'ip_address' => $this->EE->input->ip_address(), 'hash' => $XID));

			// Remove Old
			//$DB->query("DELETE FROM exp_security_hashes WHERE date < UNIX_TIMESTAMP()-7200"); // helps garbage collection for old hashes

			$this->EE->session->cache['XID'] = $XID;
			return $XID;
		}
	}

	// ********************************************************************************* //

	public function generate_json($obj)
	{
		if (function_exists('json_encode') === FALSE)
		{
			if (class_exists('Services_JSON_CUSTOM') === FALSE) include dirname(__FILE__).'/JSON.php';
			$JSON = new Services_JSON_CUSTOM();
			return $JSON->encode($obj);
		}
		else
		{
			return json_encode($obj);
		}
	}

	// ********************************************************************************* //

	public function decode_json($obj)
	{
		if (function_exists('json_decode') === FALSE)
		{
			if (class_exists('Services_JSON_CUSTOM') === FALSE) include dirname(__FILE__).'/JSON.php';
			$JSON = new Services_JSON_CUSTOM();
			return $JSON->decode($obj);
		}
		else
		{
			return json_decode($obj);
		}
	}

	// ********************************************************************************* //

	/**
	 * Fetch URL with file_get_contents or with CURL
	 *
	 * @param string $url
	 * @return mixed
	 */
	public function fetch_url_file($url, $user=false, $pass=false)
	{
		$data = '';


		/** --------------------------------------------
		/**  cURL
		/** --------------------------------------------*/
		if (function_exists('curl_init') === TRUE && ($ch = @curl_init()) !== FALSE)
		{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5 (.NET CLR 3.5.30729)');

			// Channel Images connect Time out
			$ci_config = $this->EE->config->item('channel_images');
			if (isset($ci_config['curl_connect_timeout']) === TRUE)
			{
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $ci_config['curl_connect_timeout']);
			}

			if (isset($ci_config['curl_timeout']) === TRUE)
			{
				curl_setopt($ch, CURLOPT_TIMEOUT, $ci_config['curl_timeout']);
			}

			if ($user != FALSE)
			{
				curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
				if (defined('CURLOPT_HTTPAUTH')) curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			}

			$data = curl_exec($ch);
			curl_close($ch);

			if ($data !== FALSE)
			{
				return $data;
			}
		}

		/** --------------------------------------------
		/**  file_get_contents()
		/** --------------------------------------------*/

		if ((bool) @ini_get('allow_url_fopen') !== FALSE && $user == FALSE)
		{
			if ($data = @file_get_contents($url))
			{
				return $data;
			}
		}

		/** --------------------------------------------
        /**  fsockopen() - Last but only slightly least...
        /** --------------------------------------------*/

		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';

		if (isset($parts['query']) && $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 7);

		if (is_resource($fp))
		{
			fputs ($fp, "GET ".$path." HTTP/1.0\r\n" );
			fputs ($fp, "Host: ".$host . "\r\n" );
			fputs ($fp, "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1)\r\n");

			if ($user != FALSE)
			{
				fputs ($fp, "Authorization: Basic ".base64_encode("$user:$pass")."\r\n");
			}

			fputs ($fp, "Connection: close\r\n\r\n");

			$header = '';
			$body   = '';

			/* ------------------------------
			/*  This error suppression has to do with a PHP bug involving
			/*  SSL connections: http://bugs.php.net/bug.php?id=23220
			/* ------------------------------*/

			$old_level = error_reporting(0);

			/*
			while ( ! feof($fp))
			{
				$data .= trim(fgets($fp, 128));
			}
			*/

			// put the header in variable $header
			do // loop until the end of the header
			{
				$header .= fgets ( $fp, 128 );

			} while ( strpos ( $header, "\r\n\r\n" ) === false );

			// now put the body in variable $body
			while ( ! feof ( $fp ) )
			{
				$body .= fgets ( $fp, 128 );
			}

			error_reporting($old_level);

			$data = $body;

			fclose($fp);
		}

		return $data;
	}

	// ********************************************************************************* //

	function parse_keywords($str, $remove=array())
	{
		// Remove all whitespace except single space
		$str = preg_replace("/(\r\n|\r|\n|\t|\s)+/", ' ', $str);

		// Characters that we do not want to allow...ever.
		// In the EE cleaner, we lost too many characters that might be useful in a Custom Field search, especially with Exact Keyword searches
		// The trick, security-wise, is to make sure any keywords output is converted to entities prior to any possible output
		$chars = array(	'{'	,
						'}'	,
						"^"	,
						"~"	,
						"*"	,
						"|"	,
						"["	,
						"]"	,
						'?'.'>'	,
						'<'.'?' ,
					  );

		// Keep as a space, helps prevent string removal security holes
		$str = str_replace(array_merge($chars, $remove), ' ', $str);

		// Only a single single space for spaces
		$str = preg_replace("/\s+/", ' ', $str);

		// Kill naughty stuff
		$str = trim($this->EE->security->xss_clean($str));

		return $str;
	}

	// ********************************************************************************* //

	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to TRUE, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @access	public
	 * @param	string	path to file
	 * @param	bool	whether to delete any directories found in the path
	 * @return	bool
	 */
	function delete_files($path, $del_dir = FALSE, $level = 0)
	{
		// Trim the trailing slash
		$path = preg_replace("|^(.+?)/*$|", "\\1", $path);

		if ( ! $current_dir = @opendir($path))
			return;

		while(FALSE !== ($filename = @readdir($current_dir)))
		{
			if ($filename != "." and $filename != "..")
			{
				if (is_dir($path.'/'.$filename))
				{
					// Ignore empty folders
					if (substr($filename, 0, 1) != '.')
					{
						delete_files($path.'/'.$filename, $del_dir, $level + 1);
					}
				}
				else
				{
					unlink($path.'/'.$filename);
				}
			}
		}
		@closedir($current_dir);

		if ($del_dir == TRUE AND $level > 0)
		{
			@rmdir($path);
		}
	}

	// ********************************************************************************* //

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function is_natural_number($str)
	{
   		return (bool)preg_match( '/^[0-9]+$/', $str);
	}

	// ********************************************************************************* //

	/**
	 * Array Extend
	 * "Extend" recursively array $a with array $b values (no deletion in $a, just added and updated values)
	 * @param array $a
	 * @param array $b
	 */
	function array_extend($a, $b) {
	    foreach($b as $k=>$v) {
	        if( is_array($v) ) {
	            if( !isset($a[$k]) ) {
	                $a[$k] = $v;
	            } else {
	                $a[$k] = $this->array_extend($a[$k], $v);
	            }
	        } else {
	            $a[$k] = $v;
	        }
	    }
	    return $a;
	}

	// ********************************************************************************* //

	/**
     * Function for looking for a value in a multi-dimensional array
     *
     * @param string $value
     * @param array $array
     * @return bool
     */
	function in_multi_array($value, $array)
	{
		foreach ($array as $key => $item)
		{
			// Item is not an array
			if (!is_array($item))
			{
				// Is this item our value?
				if ($item == $value) return TRUE;
			}

			// Item is an array
			else
			{
				// See if the array name matches our value
				//if ($key == $value) return true;

				// See if this array matches our value
				if (in_array($value, $item)) return TRUE;

				// Search this array
				else if ($this->in_multi_array($value, $item)) return TRUE;
			}
		}

		// Couldn't find the value in array
		return FALSE;
	}

	// ********************************************************************************* //

	/**
	 * Get Entry_ID from tag paramaters
	 *
	 * Supports: entry_id="", url_title="", channel=""
	 *
	 * @return mixed - INT or BOOL
	 */
	function get_entry_id_from_param($get_channel_id=FALSE)
	{
		$entry_id = FALSE;
		$channel_id = FALSE;

		$this->EE->load->helper('number');

		if ($this->EE->TMPL->fetch_param('entry_id') != FALSE && $this->is_natural_number($this->EE->TMPL->fetch_param('entry_id')) != FALSE)
		{
			$entry_id = $this->EE->TMPL->fetch_param('entry_id');
		}
		elseif ($this->EE->TMPL->fetch_param('url_title') != FALSE)
		{
			$channel = FALSE;
			$channel_id = FALSE;

			if ($this->EE->TMPL->fetch_param('channel') != FALSE)
			{
				$channel = $this->EE->TMPL->fetch_param('channel');
			}

			if ($this->EE->TMPL->fetch_param('channel_id') != FALSE && $this->is_natural_number($this->EE->TMPL->fetch_param('channel_id')))
			{
				$channel_id = $this->EE->TMPL->fetch_param('channel_id');
			}

			$this->EE->db->select('exp_channel_titles.entry_id');
			$this->EE->db->select('exp_channel_titles.channel_id');
			$this->EE->db->from('exp_channel_titles');
			if ($channel) $this->EE->db->join('exp_channels', 'exp_channel_titles.channel_id = exp_channels.channel_id', 'left');
			$this->EE->db->where('exp_channel_titles.url_title', $this->EE->TMPL->fetch_param('url_title'));
			if ($channel) $this->EE->db->where('exp_channels.channel_name', $channel);
			if ($channel_id) $this->EE->db->where('exp_channel_titles.channel_id', $channel_id);
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows() > 0)
			{
				$channel_id = $query->row('channel_id');
				$entry_id = $query->row('entry_id');
				$query->free_result();
			}
			else
			{
				return FALSE;
			}
		}

		if ($get_channel_id != FALSE)
		{
			if ($this->EE->TMPL->fetch_param('channel') != FALSE)
			{
				$channel_id = $this->EE->TMPL->fetch_param('channel_id');
			}

			if ($channel_id == FALSE)
			{
				$this->EE->db->select('channel_id');
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->limit(1);
				$query = $this->EE->db->get('exp_channel_titles');
				$channel_id = $query->row('channel_id');

				$query->free_result();
			}

			$entry_id = array( 'entry_id'=>$entry_id, 'channel_id'=>$channel_id );
		}



		return $entry_id;
	}

	// ********************************************************************************* //

	/**
	 * Get Channel ID from tag paramaters
	 *
	 * Supports: channel="", channel_id=""
	 *
	 * @return mixed - INT or BOOL
	 */
	function get_channel_id_from_param()
	{
		$channel_id = FALSE;

		//----------------------------------------
		// Store them all
		//----------------------------------------
		$param_channel		= $this->EE->TMPL->fetch_param('channel');
		$param_channel_id	= $this->EE->TMPL->fetch_param('channel_id');

		//----------------------------------------
		// Channel ID?
		//----------------------------------------
		if (strpos($param_channel_id, '|') !== FALSE)
		{
			$channel_id = array();
			$temp = explode('|', $param_channel_id);

			foreach($temp as $item)
			{
				if ($this->is_natural_number($item) == FALSE) continue;
				$channel_id[] = $item;
			}

			return $channel_id;
		}
		else
		{
			if ($this->is_natural_number($param_channel_id) != FALSE) return $param_channel_id;
		}


		//----------------------------------------
		// Grab all Channels!
		//----------------------------------------
		if ($param_channel != FALSE)
		{
			$channels = array();

			// Maybe we did this already?
			if (isset($this->EE->session->cache['DevDemon']['AllChannelsLight']) == FALSE)
			{
				$query = $this->EE->db->query("SELECT channel_id, channel_name FROM exp_channels");
				foreach($query->result() as $row) $channels[$row->channel_name] = $row->channel_id;
				$this->EE->session->cache['DevDemon']['AllChannelsLight'] = $channels;
			}
			else
			{
				$channels = $this->EE->session->cache['DevDemon']['AllChannelsLight'];
			}
		}
		else
		{
			return FALSE;
		}

		//----------------------------------------
		// Channel?
		//----------------------------------------
		if (strpos($param_channel, '|') !== FALSE)
		{
			$channel_id = array();
			$temp = explode('|', $param_channel);

			foreach($temp as $item)
			{
				if (isset($channels[$item]) == FALSE) continue;
				$channel_id[] = $channels[$item];
			}

			return $channel_id;
		}
		else
		{
			if (isset($channels[$param_channel]) == FALSE) continue;
			else return $channels[$param_channel];
		}

		return $channel_id;
	}

	// ********************************************************************************* //

	/**
	 * Fetch data between var pairs
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $source - Source
	 * @return string
	 */
    function fetch_data_between_var_pairs($varname='', $source = '')
    {
    	if ( ! preg_match('/'.LD.($varname).RD.'(.*?)'.LD.'\/'.$varname.RD.'/s', $source, $match))
               return;

        return $match['1'];
    }

	// ********************************************************************************* //

	/**
	 * Fetch data between var pairs (including optional parameters)
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $source - Source
	 * @return string
	 */
    function fetch_data_between_var_pairs_params($open='', $close='', $source = '')
    {
    	if ( ! preg_match('/'.LD.preg_quote($open).'.*?'.RD.'(.*?)'.LD.'\/'.$close.RD.'/s', $source, $match))
               return;

        return $match['1'];
    }

	// ********************************************************************************* //

	/**
	 * Replace var_pair with final value
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $replacement - Replacement
	 * @param string $source - Source
	 * @return string
	 */
	function swap_var_pairs($varname = '', $replacement = '\\1', $source = '')
    {
    	return preg_replace("/".LD.$varname.RD."(.*?)".LD.'\/'.$varname.RD."/s", $replacement, $source);
    }

	// ********************************************************************************* //

	/**
	 * Replace var_pair with final value (including optional parameters)
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $replacement - Replacement
	 * @param string $source - Source
	 * @return string
	 */
	function swap_var_pairs_params($open = '', $close = '', $replacement = '\\1', $source = '')
    {
    	return preg_replace("/".LD.preg_quote($open).RD."(.*?)".LD.'\/'.$close.RD."/s", $replacement, $source);
    }

	// ********************************************************************************* //

	/**
	 * Custom No_Result conditional
	 *
	 * Same as {if no_result} but with your own conditional.
	 *
	 * @param string $cond_name
	 * @param string $source
	 * @param string $return_source
	 * @return unknown
	 */
    function custom_no_results_conditional($cond_name, $source, $return_source=FALSE)
    {
   		if (strpos($source, LD."if {$cond_name}".RD) !== FALSE)
		{
			if (preg_match('/'.LD."if {$cond_name}".RD.'(.*?)'. LD.'\/if'.RD.'/s', $source, $cond))
			{
				return $cond[1];
			}

		}


		if ($return_source !== FALSE)
		{
			return $source;
		}

		return;
    }

	// ********************************************************************************* //

	public function mcp_js_css($type='', $path='', $package='', $name='', $theme_url=false)
    {
        $url  = $theme_url ? $theme_url : $this->get_theme_url();
		$url .= $path;

		// CSS
		if ($type == 'css')
		{
			if (isset($this->EE->session->cache['css'][$package][$name]) === FALSE)
			{
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $url . '" type="text/css" media="print, projection, screen" />');
				$this->EE->session->cache['css'][$package][$name] = TRUE;
			}
		}

		// JS
		if ($type == 'js')
		{
			if (isset($this->EE->session->cache['javascript'][$package][$name]) === FALSE)
			{
				$this->EE->cp->add_to_head('<script src="' . $url . '" type="text/javascript"></script>');
				$this->EE->session->cache['javascript'][$package][$name] = TRUE;
			}
		}

		// Global Inline Javascript
		if ($type == 'gjs')
		{
			if ( isset($this->EE->session->cache['inline_js'][$this->package]) == FALSE )
			{
				$AJAX_url = $this->get_router_url();

				$js = "	var ChannelImages = ChannelImages ? ChannelImages : {};
						ChannelImages.AJAX_URL = '{$AJAX_url}';
						ChannelImages.ThemeURL = '" . $this->get_theme_url() . "';
						ChannelImages.site_id = '" . $this->site_id . "';
					";

				$this->EE->cp->add_to_head('<script type="text/javascript">' . $js . '</script>');
				$this->EE->session->cache['inline_js'][$this->package] = TRUE;
			}
		}
	}

	// ********************************************************************************* //

	function mcp_meta_parser($type, $url, $name, $package='')
	{
		// -----------------------------------------
		// CSS
		// -----------------------------------------
		if ($type == 'css')
		{
			if ( isset($this->EE->session->cache['DevDemon']['CSS'][$name]) == FALSE )
			{
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $url . '" type="text/css" media="print, projection, screen" />');
				$this->EE->session->cache['DevDemon']['CSS'][$name] = TRUE;
			}
		}

		// -----------------------------------------
		// Javascript
		// -----------------------------------------
		if ($type == 'js')
		{
			if ( isset($this->EE->session->cache['DevDemon']['JS'][$name]) == FALSE )
			{
				$this->EE->cp->add_to_head('<script src="' . $url . '" type="text/javascript"></script>');
				$this->EE->session->cache['DevDemon']['JS'][$name] = TRUE;
			}
		}

		// -----------------------------------------
		// Global Inline Javascript
		// -----------------------------------------
		if ($type == 'gjs')
		{
			if ( isset($this->EE->session->cache['DevDemon']['GJS'][$name]) == FALSE )
			{
				$AJAX_url = $this->get_router_url();

				$js = "	var ChannelImages = ChannelImages ? ChannelImages : new Object();
						ChannelImages.AJAX_URL = '{$AJAX_url}';
						ChannelImages.ThemeURL = '" . CHANNELIMAGES_THEME_URL . "';
						ChannelImages.site_id = '" . $this->site_id . "';
					";

				$this->EE->cp->add_to_head('<script type="text/javascript">' . $js . '</script>');
				$this->EE->session->cache['DevDemon']['GJS'][$name] = TRUE;
			}
		}
	}

} // END CLASS

/* End of file image_helper.php  */
/* Location: ./system/expressionengine/third_party/channel_images/libraries/image_helper.php */
