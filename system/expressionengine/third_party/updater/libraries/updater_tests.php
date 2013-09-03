<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updater Tests File
 *
 * @package			DevDemon_Updater
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Updater_tests
{

	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
	}

	// ********************************************************************************* //

	public function test_transfer_method($settings=array())
	{
		$data = array();
		$data['settings'] = $settings;
		$data['connect'] = FALSE;
		$data['actions'] = array('chdir', 'mkdir', 'upload', 'rename', 'delete');
		$data['dirs']['backup'] = array();
		$data['dirs']['system'] = array();
		$data['dirs']['system_third_party'] = array();
		$data['dirs']['themes'] = array();
		$data['dirs']['themes_third_party'] = array();

		foreach ($data['dirs'] as $dir => $actions)
		{
			$path = $data['settings']['path_map'][$dir];

			foreach ($data['actions'] as $action)
			{
				$data['dirs'][$dir][$action] = FALSE;
			}
		}

		switch ($settings['file_transfer_method'])
		{
			case 'local':
				$data = $this->local_transfer_method($data);
				break;
			case 'ftp':
				$data = $this->ftp_transfer_method($data);
				break;
			case 'sftp':
				$data = $this->sftp_transfer_method($data);
				break;
		}

		exit($this->EE->load->view('settings_transfer_method', $data, TRUE));
	}

	// ********************************************************************************* //

	private function local_transfer_method($data)
	{
		$unique = uniqid(mt_rand());

		foreach ($data['dirs'] as $dir => $actions)
		{
			$path = $data['settings']['path_map'][$dir];

			foreach ($data['actions'] as $action)
			{
				// --------------------------------------------
		        //  Change DIR?
		        // --------------------------------------------
		        if ($action == 'chdir')
		        {
		        	if (file_exists($path) == TRUE) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  MKDIR?
		        // --------------------------------------------
		        if ($action == 'mkdir')
		        {
		        	if (@mkdir($path.$unique)) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Upload File
		        // --------------------------------------------
		        if ($action == 'upload')
		        {
		        	if (@touch($path.$unique.'.tmp')) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Rename
		        // --------------------------------------------
		        if ($action == 'rename')
		        {
		        	if (@rename($path.$unique, $path.$unique.'_OLD') && @rename($path.$unique.'.tmp', $path.$unique.'_OLD.tmp') ) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Delete
		        // --------------------------------------------
		        if ($action == 'delete')
		        {
		        	if (@rmdir($path.$unique.'_OLD') && @unlink($path.$unique.'_OLD.tmp')) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }
			}
		}

		return $data;
	}

	// ********************************************************************************* //

	private function ftp_transfer_method($data)
	{
		$ftps = $data['settings']['ftp'];

		if ($ftps['passive'] == 'yes') $ftps['passive'] = TRUE;
		else $ftps['passive'] = FALSE;

		if ($ftps['ssl'] == 'yes') $ftps['ssl'] = TRUE;
		else $ftps['ssl'] = FALSE;

		require_once(PATH_THIRD.'updater/libraries/devdemon_ftp.php');
		$FTP = new Devdemon_ftp($ftps);

		// Just in case
		if ($ftps['ssl'] == TRUE) $FTP->ssl = TRUE;

		if ( ! is_resource($FTP->conn_id))
		{
			// FTP CONNECT
			if (!$FTP->connect())
			{
				$data['connect'] = FALSE;
			}
			else $data['connect'] = TRUE;
		}

		$unique = uniqid(mt_rand());

		foreach ($data['dirs'] as $dir => $actions)
		{
			$path = $data['settings']['path_map'][$dir];

			foreach ($data['actions'] as $action)
			{
				// --------------------------------------------
		        //  Change DIR?
		        // --------------------------------------------
		        if ($action == 'chdir')
		        {
		        	if ($FTP->changedir($path) == TRUE) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  MKDIR?
		        // --------------------------------------------
		        if ($action == 'mkdir')
		        {
		        	if ($FTP->mkdir($path.$unique)) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Upload File
		        // --------------------------------------------
		        if ($action == 'upload')
		        {
		        	if ($FTP->upload(PATH_THIRD.'updater/libraries/JSON.php', $path.$unique.'.tmp', 0775))
		        	{
		        		$data['dirs'][$dir][$action] = TRUE;
		        	}
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Rename
		        // --------------------------------------------
		        if ($action == 'rename')
		        {
		        	if ($FTP->rename($path.$unique.'.tmp', $path.$unique.'_OLD.tmp')
		        		&& $FTP->rename($path.$unique, $path.$unique.'_OLD') )
		        	{
		        		$data['dirs'][$dir][$action] = TRUE;
		        	}
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Delete
		        // --------------------------------------------
		        if ($action == 'delete')
		        {
		        	if ($FTP->delete_file($path.$unique.'_OLD.tmp')
		        		&& $FTP->delete_dir($path.$unique.'_OLD') )
		        	{
		        		$data['dirs'][$dir][$action] = TRUE;
		        	}
		        	else break;
		        	continue;
		        }
			}
		}

		return $data;
	}

	// ********************************************************************************* //

	private function sftp_transfer_method($data)
	{
		$SFTPs = $data['settings']['sftp'];

		set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)).'/libraries/phpseclib/');

		if (class_exists('Net_SFTP') === false)
		{
			require_once(dirname(dirname(__FILE__)).'/libraries/phpseclib/Net/SFTP.php');
		}

		$SFTP = new Net_SFTP($SFTPs['hostname'], $SFTPs['port']);

		if ( !($SFTP->bitmap & NET_SSH2_MASK_LOGIN) )
		{
			$this->EE->firephp->log($SFTPs);
			$password = $SFTPs['password'];

			if (isset($SFTPs['auth_method']) === TRUE && $SFTPs['auth_method'] == 'key')
			{
				$key_pass = (isset($SFTPs['key_password']) === TRUE) ? $SFTPs['key_password'] : '';
				$password = new Crypt_RSA();
				if ($key_pass) $password->setPassword($key_pass);

				$key_contents = '';
				if (isset($SFTPs['key_contents']) === TRUE)
				{
					$key_contents = $SFTPs['key_contents'];
				}

				if (isset($SFTPs['key_path']) === TRUE && $SFTPs['key_path'] != FALSE)
				{
					if (file_exists($SFTPs['key_path']) === TRUE)
					{
						$key_contents = file_get_contents($SFTPs['key_path']);
					}
				}

				$password->loadKey($key_contents);
			}

			if ($SFTP->login($SFTPs['username'], $password) != TRUE)
			{
				$data['connect'] = FALSE;
			} else $data['connect'] = TRUE;
		}

		$unique = uniqid(mt_rand());

		foreach ($data['dirs'] as $dir => $actions)
		{
			$path = $data['settings']['path_map'][$dir];

			foreach ($data['actions'] as $action)
			{
				// --------------------------------------------
		        //  Change DIR?
		        // --------------------------------------------
		        if ($action == 'chdir')
		        {
		        	if ($SFTP->chdir($path) == TRUE) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  MKDIR?
		        // --------------------------------------------
		        if ($action == 'mkdir')
		        {
		        	if ($SFTP->mkdir($path.$unique)) $data['dirs'][$dir][$action] = TRUE;
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Upload File
		        // --------------------------------------------
		        if ($action == 'upload')
		        {
		        	if ($SFTP->put($path.$unique.'.tmp', PATH_THIRD.'updater/libraries/JSON.php', NET_SFTP_LOCAL_FILE))
		        	{
		        		$data['dirs'][$dir][$action] = TRUE;
		        	}
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Rename
		        // --------------------------------------------
		        if ($action == 'rename')
		        {
		        	if ($SFTP->rename($path.$unique.'.tmp', $path.$unique.'_OLD.tmp')
		        		&& $SFTP->rename($path.$unique, $path.$unique.'_OLD') )
		        	{
		        		$data['dirs'][$dir][$action] = TRUE;
		        	}
		        	else break;
		        	continue;
		        }

		        // --------------------------------------------
		        //  Delete
		        // --------------------------------------------
		        if ($action == 'delete')
		        {
		        	if ($SFTP->delete($path.$unique.'_OLD.tmp')
		        		&& $SFTP->rmdir($path.$unique.'_OLD') )
		        	{
		        		$data['dirs'][$dir][$action] = TRUE;
		        	}
		        	else break;
		        	continue;
		        }
			}
		}

		@$SFTP->disconnect();

		return $data;
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file updater_tests.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_helper.php */
