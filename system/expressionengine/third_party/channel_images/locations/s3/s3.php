<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images S3 location
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class CI_Location_s3 extends Image_Location
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct($settings=array())
	{
		parent::__construct();

		$this->lsettings = $settings;
	}

	// ********************************************************************************* //

	public function delete_dir($dir)
	{
		$this->init();

		// Subdirectory?
		$subdir = (isset($this->lsettings['directory']) == TRUE && $this->lsettings['directory'] != FALSE) ? $this->lsettings['directory'] . '/' .$dir : $dir;

		// Get all objects
		$objects = $this->S3->get_object_list($this->lsettings['bucket'], array('prefix' => $subdir));

		foreach  ($objects as $file)
		{
			//$this->S3->batch()->delete_object($this->lsettings['bucket'], $file);
			$this->S3->delete_object($this->lsettings['bucket'], $file);
		}

		//$responses = $this->S3->batch()->send();

		return TRUE;
	}

	// ********************************************************************************* //

	public function upload_file($source_file, $dest_filename, $dest_folder)
	{
		$this->init();

		// Extension
		$extension = substr( strrchr($source_file, '.'), 1);

		// Subdirectory?
		$subdir = (isset($this->lsettings['directory']) == TRUE && $this->lsettings['directory'] != FALSE) ? $this->lsettings['directory'] . '/' : '';

		// Mime type
		$filemime = 'image/jpeg';
		if ($extension == 'png') $filemime = 'image/png';
		elseif ($extension == 'gif') $filemime = 'image/gif';

		$upload_arr = array();
		$upload_arr['fileUpload'] = $source_file;
		$upload_arr['contentType'] = $filemime;
		$upload_arr['acl'] = $this->lsettings['acl'];
		$upload_arr['storage'] = $this->lsettings['storage'];
		$upload_arr['headers'] = array();

		$headers = $this->EE->config->item('ci_s3_headers');

		if ($headers != FALSE && is_array($headers) === TRUE)
		{
			$upload_arr['headers'] = $headers;
		}

		$response = $this->S3->create_object($this->lsettings['bucket'], $subdir.$dest_folder.'/'.$dest_filename, $upload_arr);

		// Success?
		if($response->isOK())
		{
			return TRUE;
		}
		else
		{
			return (string) $response->body->Message;
		}
	}

	// ********************************************************************************* //

	public function download_file($dir, $filename, $dest_folder)
	{
		$this->init();

		// Subdirectory?
		$subdir = (isset($this->lsettings['directory']) == TRUE && $this->lsettings['directory'] != FALSE) ? $this->lsettings['directory'] . '/' : '';

		$response = $this->S3->get_object($this->lsettings['bucket'], $subdir.$dir.'/'.$filename);

		// Success?
		if($response->isOK())
		{
			$this->EE->load->helper('file');
			write_file($dest_folder.$filename, $response->body);

			return TRUE;
		}
		else
		{
			return (string) $response->body;
		}

		return FALSE;
	}

	// ********************************************************************************* //

	public function delete_file($dir, $filename)
	{
		$this->init();

		// Subdirectory?
		$subdir = (isset($this->lsettings['directory']) == TRUE && $this->lsettings['directory'] != FALSE) ? $this->lsettings['directory'] . '/' : '';

		$this->S3->delete_object($this->lsettings['bucket'], $subdir.$dir.'/'.$filename);

		return FALSE;
	}

	// ********************************************************************************* //

	public function parse_image_url($dir, $filename)
	{
		$this->init();

		$url = '';

		// Subdirectory?
		$subdir = (isset($this->lsettings['directory']) == TRUE && $this->lsettings['directory'] != FALSE) ? $this->lsettings['directory'] . '/' : '';

		if ($this->lsettings['acl'] == 'public-read')
		{
			if (isset($this->lsettings['cloudfront_domain']) == TRUE && $this->lsettings['cloudfront_domain'] != FALSE)
			{
				$url = 'http://'.$this->lsettings['cloudfront_domain']. '/'.$subdir.$dir . '/' . $filename;
			}
			else
			{
				$url = $this->S3->get_object_url($this->lsettings['bucket'], $subdir.$dir . '/' . $filename);
			}
		}
		else
		{

			$url = $this->S3->get_object_url($this->lsettings['bucket'], $subdir.$dir . '/' . $filename, '60 minutes');
		}

		return $url;
	}

	// ********************************************************************************* //

	public function test_location()
	{
		error_reporting(-1);

		if ($this->init() == FALSE)
		{
			exit('AMAZON INIT FAILED. <br /> Check Key, Secret Key and Bucket');
		}

		$o = '<style type="text/css">.good {font-weight:bold; color:green} .bad {font-weight:bold; color:red}</style>';

		$bucket = trim($this->lsettings['bucket']);
		$region = $this->lsettings['region'];
		$acl = $this->lsettings['acl'];
		$storage = $this->lsettings['storage'];
		$file = uniqid(mt_rand()).'.tmp';
		$subdir = (isset($this->lsettings['directory']) == TRUE && $this->lsettings['directory'] != FALSE) ? $this->lsettings['directory'] . '/' : '';

		// Check for Safe Mode?
		$safemode = strtolower(@ini_get('safe_mode'));
		if ($safemode == 'on' || $safemode == 'yes' || $safemode == 'true' ||  $safemode == 1)	$o .= "PHP Safe Mode (OFF): <span class='bad'>Failed</span> <br>";
		else $o .= "PHP Safe Mode (OFF): <span class='good'>Passed</span> <br>";

		// Does the Bucket Exist?
		if ($this->S3->if_bucket_exists($bucket))
		{
			$o .= 'Bucket Exists?: ' . '<span class="good">Yes</span> <br />';
		}
		else
		{
			$o .= 'Bucket Exists: ' . '<span class="bad">No</span> <br />';

			$res = $this->S3->create_bucket($bucket, $region);

			if ($res->isOK())
			{
				$o .= 'Bucket Creation: ' . '<span class="good">Passed</span> <br />';
			}
			else
			{
				$o .= 'Bucket Creation: ' . '<span class="bad">Failed</span> <br />';
				$o .= '<em>' . (string) $res->body->Message . '</em>  <br />';
			}
		}

		// Create The File
		$res = $this->S3->create_object($bucket, $subdir.$file, array('body' => 'TEST', 'contentType' => 'text/plain', 'acl' => $acl, 'storage' => $storage) );

		if ($res->isOK())
		{
			$o .= 'Create Test File: ' . '<span class="good">Passed</span> <br />';
		}
		else
		{
			$o .= 'Create Test File: ' . '<span class="bad">Failed</span> <br />';
			$o .= '<em>' . (string) $res->body->Message . '</em> <br />';
		}

		// Delete The File
		$res = $this->S3->delete_object($bucket, $subdir.$file);
		if ($res->isOK())
		{
			$o .= 'Delete Test File: ' . '<span class="good">Passed</span> <br />';
		}
		else
		{
			$o .= 'Delete Test File: ' . '<span class="bad">Failed</span> <br />';
		}


		$o .= "<br /> Even if all tests PASS, uploading can still fail due Apache/htaccess misconfiguration";

		return $o;
	}

	// ********************************************************************************* //

	private function init()
	{
		if (isset($this->S3) == TRUE)
		{
			return TRUE;
		}
		else
		{
			if ($this->lsettings['key'] == FALSE OR $this->lsettings['secret_key'] == FALSE OR $this->lsettings['bucket'] == FALSE)
			{
				return FALSE;
			}

			if (class_exists('CFRuntime') == FALSE)
			{
				// Include the SDK
				require_once PATH_THIRD.'channel_images/locations/s3/sdk/sdk.class.php';
			}

			// Just to be sure
			if (class_exists('AmazonS3') == FALSE)
			{
				include PATH_THIRD.'channel_images/locations/s3/sdk/services/s3.class.php';
			}

			// Instantiate the AmazonS3 class
			$this->S3 = new AmazonS3(array('key'=>trim($this->lsettings['key']), 'secret'=>trim($this->lsettings['secret_key'])));
			$this->S3->ssl_verification = FALSE;

			// Init Configs
			$temp = $this->EE->config->item('ci_s3_storage');
			$this->lsettings['storage'] = constant('AmazonS3::' . $temp[$this->lsettings['storage']]);

			$temp = $this->EE->config->item('ci_s3_acl');
			$this->lsettings['acl'] = constant('AmazonS3::' . $temp[$this->lsettings['acl']]);

			$temp = $this->EE->config->item('ci_s3_regions');
			$this->lsettings['region'] = constant('AmazonS3::' . $temp[$this->lsettings['region']]);

			return TRUE;
		}
	}

	// ********************************************************************************* //
}

/* End of file local.php */
/* Location: ./system/expressionengine/third_party/channel_images/locations/s3/s3.php */
