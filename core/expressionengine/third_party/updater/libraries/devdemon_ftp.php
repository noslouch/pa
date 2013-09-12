<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Files FTP location library
 *
 * @package			DevDemon_ChannelFiles
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_files/
 */

if (file_exists(BASEPATH.'libraries/Ftp.php') == TRUE)
{
	require_once(BASEPATH.'libraries/Ftp.php');
}
elseif (file_exists(realpath(dirname(__FILE__).'../../../../../../../codeigniter/system/libraries/Ftp.php')))
{
	require_once(realpath(dirname(__FILE__).'../../../../../../../codeigniter/system/libraries/Ftp.php'));
}
else
{
	load_class('Ftp');
}


class Devdemon_ftp extends CI_FTP
{
	/**
	 * Show Error
	 * @var bool
	 */
	public $show_error	= FALSE;

	/**
	 * Error String
	 * @var string
	 */
	public $error		= '';

	/**
	 * FTP Timeout
	 * @var int
	 */
	public $timeout	= 20;

	/**
	 * SSL MODE?
	 * @var bool
	 */
	public $ssl		= FALSE;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	// ********************************************************************************* //

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	 the connection values
	 * @return	bool
	 */
	function connect($config = array())
	{
		if ($this->_is_conn() == TRUE) return TRUE;

		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		if ($this->ssl == TRUE)
		{
			if (function_exists('ftp_ssl_connect'))
			{
				$this->conn_id = @ftp_ssl_connect($this->hostname, $this->port, $this->timeout);
			}
			else
			{
				$this->_error('ftp_ssl_not_supported');
				return FALSE;
			}
		}
		else
		{
			$this->conn_id = @ftp_connect($this->hostname, $this->port, $this->timeout);
		}


		if ($this->conn_id === FALSE)
		{
			$this->_error('ftp_unable_to_connect');
			return FALSE;
		}

		if ( ! $this->_login())
		{
			$this->_error('ftp_unable_to_login');
			return FALSE;
		}

		// Set passive mode if needed
		if ($this->passive == TRUE)
		{
			ftp_pasv($this->conn_id, TRUE);
		}

		return TRUE;
	}

	// ********************************************************************************* //


	public function list_files_raw($path = '.')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		@ftp_chdir($this->conn_id, $path);

		//return ftp_nlist($this->conn_id, '-a '.$path);

		$raw = ftp_rawlist($this->conn_id, '-a '.$path, false);
		$systype = trim(strtolower(ftp_systype($this->conn_id)));

		$items = array();

		if (is_array($raw) === true) {

			if (strpos($systype, 'win') !== false) {
				foreach ($raw as $count => $child) {
					$file = array();
					preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)/', $child, $matches);

					if ($matches) {
						$file['perm'] = '';
						$file['number'] = $count;
						$file['user'] = '';
						$file['group'] = '';
						$file['size'] = trim(strtolower($matches[7]));
						$file['date'] = "{$matches[1]}/{$matches[2]}/{$matches[3]} {$matches[4]}:{$matches[5]} {$matches[6]}";

						if (strpos($file['size'], 'dir') !== false) {
							$file['type'] = 'dir';
							$file['size'] = 0;
						} else {
							$file['type'] = 'file';
						}

						// DOS? Remove comma's
						$file['size'] = str_replace(array(',', '.'), '', $file['size']);

						$filename = trim($matches[8]);

						if ($filename == '.' || $filename == '..') {
		                	continue;
		                }

		                $items[$filename] = $file;
					}
				}
			} else {
				foreach ($raw as $child) {
					$file = array();
	                $chunks = preg_split("/\s+/", $child);

	                $file['perm'] = $chunks[0];
	                $file['number'] = $chunks[1];
	                $file['user'] = $chunks[2];
	                $file['group'] = $chunks[3];
	                $file['size'] = $chunks[4];
	                $date = $chunks[6].'-'.$chunks[5];
	                $time = $chunks[7];

	                if (strpos($time, ':') !== false) {
	                	$date .= '-'.date('Y');
	                } else {
	                	$date .= '-'.$time;
	                	$time = '00:00';
	                }

	                $file['date'] = $date . ' ' . $time;
					$file['type'] = $chunks[0]{0} === 'd' ? 'dir' : 'file';
					array_splice($chunks, 0, 8);
	                $filename = implode(' ', $chunks);
	                $filename = trim($filename);

	                if ($filename == '.' || $filename == '..') {
	                	continue;
	                }

	                $items[$filename] = $file;
	            }
			}
//exit(print_r($items));

		}

		return $items;
	}

	// ********************************************************************************* //

	/**
	 * Download a file from a remote server to the local server
	 *
	 * Modified from the CI FTP to allow some better error reporting.  The CI one had virtually none
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function download($rem_path, $loc_path, $mode = 'auto')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

  		// get remote folder/filename
		$rem_folder   = dirname($rem_path);
		$rem_filename = basename($rem_path);

		// --------------------------------------------
        //  Check if it is a Local Directory or the File
        // --------------------------------------------

		if (@is_dir($loc_path))
		{
			$loc_folder   = rtrim($loc_path, '/');
			$loc_filename = $rem_filename;
		}
		else
		{
			$loc_folder   = dirname($loc_path);
			$loc_filename = basename($loc_path);
		}

		// --------------------------------------------
        //  Validate Local Path
        // --------------------------------------------

		if ( $loc_folder != '.'  && ! @is_dir($loc_folder))
		{
			$this->_error('ftp_bad_local_path');
			return FALSE;
		}
		// check that loc path and file are writable
		elseif ( ! is_really_writable($loc_folder) OR
				(file_exists($loc_folder.'/'.$loc_filename) && ! @is_really_writable($loc_folder.'/'.$loc_filename)))
		{
			$this->_error('ftp_local_path_not_writable');
			return FALSE;
		}

		// --------------------------------------------
        //  Switch Directories
        // --------------------------------------------

		if ( ! @chdir($loc_folder))
		{
			$this->_error('ftp_bad_local_path');
			return FALSE;
		}

		if ( $rem_folder != '.' && ! $this->changedir($rem_folder))
		{
			$this->_error('ftp_bad_remote_path');
			return FALSE;
		}

		// --------------------------------------------
        //  Validate Remote File
        // --------------------------------------------

		$found_file = FALSE;
		$files = $this->list_files();

		if ( ! is_array($files) OR empty($files))
		{
			$this->_error('ftp_bad_remote_file');
			return FALSE;
		}

		foreach ($files as $f)
		{
			if ($f == $rem_filename)
			{
				$found_file = TRUE;
				break;
			}
		}

		if ($found_file === FALSE)
		{
			$this->_error('ftp_bad_remote_file');
			return FALSE;
		}

		// --------------------------------------------
        //  Move Remote File to Local
        // --------------------------------------------

		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($rem_path);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		// download the file
		$result = @ftp_get($this->conn_id, $loc_folder.'/'.$loc_filename, $rem_filename, $mode);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_download');
			}

			return FALSE;
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * containted within it.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_dir($filepath)
	{
		//$this->EE =& get_instance();

		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Add a trailing slash to the file path if needed
		$filepath = preg_replace("/(.+?)\/*$/", "\\1/",  $filepath);

		$list = $this->list_files_raw($filepath);

		if ( ($list != FALSE OR count($list) > 0) )
		{
			foreach (array_keys($list) as $item)
			{
				//if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_DEL: {$filepath}{$item}");

				if ($item == '.' OR $item == '..') continue;

				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()
				if ( ! @ftp_delete($this->conn_id, $filepath.$item))
				{
					$this->delete_dir($filepath.$item.'/');
				}
			}
		}

		$result = @ftp_rmdir($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_delete');
			}
			return FALSE;
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function file_size($rem_path)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_size($this->conn_id, $rem_path);

		if ($result == -1)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_file_not_exist');
			}
			return FALSE;
		}

		return $result;
	}

	// ********************************************************************************* //

	/**
	 * Display error message
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _error($line)
	{
		$this->error = $line;

		if ( $this->show_error == TRUE)
		{
			$CI =& get_instance();
			$CI->lang->load('ftp');
			show_error($CI->lang->line($line));
		}
	}

	// ********************************************************************************* //
}
// END FTP Class

/* End of file devdemon_ftp.php */
/* Location: ./system/expressionengine/third_party/channel_files/locations/ftp/libraries/devdemon_ftp.php */
