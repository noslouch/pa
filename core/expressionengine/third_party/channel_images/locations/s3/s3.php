<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images S3 location
 *
 * @package         DevDemon_ChannelImages
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com/channel_images/
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
        $subdir = (isset($this->lsettings['directory']) == true && $this->lsettings['directory'] != false) ? $this->lsettings['directory'] . '/' .$dir : $dir;

        // Get all objects
        $objects = $this->S3->getBucket($this->lsettings['bucket'], $subdir);

        foreach  ($objects as $file)
        {
            //$this->S3->batch()->delete_object($this->lsettings['bucket'], $file);
            $this->S3->deleteObject($this->lsettings['bucket'], $file['name']);
        }

        //$responses = $this->S3->batch()->send();

        return true;
    }

    // ********************************************************************************* //

    public function upload_file($source_file, $dest_filename, $dest_folder)
    {
        $this->init();

        // Extension
        $extension = substr( strrchr($source_file, '.'), 1);

        // Subdirectory?
        $subdir = (isset($this->lsettings['directory']) == true && $this->lsettings['directory'] != false) ? $this->lsettings['directory'] . '/' : '';

        // Mime type
        $filemime = 'image/jpeg';
        if ($extension == 'png') $filemime = 'image/png';
        elseif ($extension == 'gif') $filemime = 'image/gif';

        /*
        $upload_arr = array();
        $upload_arr['fileUpload'] = $source_file;
        $upload_arr['contentType'] = $filemime;
        $upload_arr['acl'] = $this->lsettings['acl'];
        $upload_arr['storage'] = $this->lsettings['storage'];
        $upload_arr['headers'] = array();

        $headers = $this->EE->config->item('ci_s3_headers');

        if ($headers != false && is_array($headers) === true)
        {
            $upload_arr['headers'] = $headers;
        }

        $response = $this->S3->create_object($this->lsettings['bucket'], $subdir.$dest_folder.'/'.$dest_filename, $upload_arr);
        */

        $headers = $this->EE->config->item('ci_s3_headers');
        if ($headers == false) $headers = array();
        $headers['Content-Type'] = $filemime;

        $response = $this->S3->putObject(
            $this->S3->inputFile($source_file),
            $this->lsettings['bucket'],
            $subdir.$dest_folder.'/'.$dest_filename,
            $this->lsettings['acl'],
            array(),
            $headers,
            $this->lsettings['storage']
        );

        // Success?
        if (!$response) {
            return $this->S3->response->error['message'];
        }

        return true;
    }

    // ********************************************************************************* //

    public function download_file($dir, $filename, $dest_folder)
    {
        $this->init();

        // Subdirectory?
        $subdir = (isset($this->lsettings['directory']) == true && $this->lsettings['directory'] != false) ? $this->lsettings['directory'] . '/' : '';

        try {
            $this->S3->getObject($this->lsettings['bucket'], $subdir.$dir.'/'.$filename, $dest_folder.$filename);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    // ********************************************************************************* //

    public function delete_file($dir, $filename)
    {
        $this->init();

        // Subdirectory?
        $subdir = (isset($this->lsettings['directory']) == true && $this->lsettings['directory'] != false) ? $this->lsettings['directory'] . '/' : '';

        try {
            $this->S3->deleteObject($this->lsettings['bucket'], $subdir.$dir.'/'.$filename);
        } catch (Exception $e) {

        }

        return false;
    }

    // ********************************************************************************* //

    public function parse_image_url($dir, $filename)
    {
        $this->init();

        if ($this->lsettings['region'] == 'us-east-1') {
            $this->S3->setEndpoint('s3.amazonaws.com');
            $endpoint = 's3.amazonaws.com';
        } else {
            $this->S3->setEndpoint($this->lsettings['endpoint']);
            $endpoint = $this->lsettings['endpoint'];
        }

        $url = '';

        // Subdirectory?
        $subdir = (isset($this->lsettings['directory']) == true && $this->lsettings['directory'] != false) ? $this->lsettings['directory'] . '/' : '';

        if (isset($this->lsettings['cloudfront_domain']) == true && $this->lsettings['cloudfront_domain'] != false) {
            return 'http://'.$this->lsettings['cloudfront_domain']. '/'.$subdir.$dir . '/' . $filename;
        }

        if ($this->lsettings['acl'] == 'public-read')
        {
            //return 'https://'.$endpoint.'/'.$this->lsettings['bucket'].'/'.$subdir.$dir . '/' . $filename;
            return 'https://'.$this->lsettings['bucket'].'.s3.amazonaws.com/'.$subdir.$dir . '/' . $filename;
        }
        else
        {
            return $this->S3->getAuthenticatedURL($this->lsettings['bucket'], $subdir.$dir . '/' . $filename, 3600, false, true);
        }

        /*
        $this->S3->set_region($this->lsettings['region']);

        if ($this->lsettings['acl'] == 'public-read')
        {
            if (isset($this->lsettings['cloudfront_domain']) == true && $this->lsettings['cloudfront_domain'] != false)
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
        */

        return $url;
    }

    // ********************************************************************************* //

    public function test_location()
    {
        error_reporting(-1);

        if ($this->init() == false) {
            exit('AMAZON INIT FAILED. <br /> Check Key, Secret Key and Bucket');
        }

        $this->S3->setExceptions(true);

        $o = '<style type="text/css">.good {font-weight:bold; color:green} .bad {font-weight:bold; color:red}</style>';

        $bucket = trim($this->lsettings['bucket']);
        $region = $this->lsettings['region'];
        $acl = $this->lsettings['acl'];
        $storage = $this->lsettings['storage'];
        $file = uniqid(mt_rand()).'.tmp';
        $subdir = (isset($this->lsettings['directory']) == true && $this->lsettings['directory'] != false) ? $this->lsettings['directory'] . '/' : '';

        // Check for Safe Mode?
        $safemode = strtolower(@ini_get('safe_mode'));
        if ($safemode == 'on' || $safemode == 'yes' || $safemode == 'true' ||  $safemode == 1)  $o .= "PHP Safe Mode (OFF): <span class='bad'>Failed</span> <br>";
        else $o .= "PHP Safe Mode (OFF): <span class='good'>Passed</span> <br>";

        // Does the Bucket Exist?
        try {
            $this->S3->getBucketLocation($bucket);
            $o .= 'Bucket Exists?: ' . '<span class="good">Yes</span> <br />';
        } catch (Exception $e) {
            $o .= 'Bucket Exists: ' . '<span class="bad">No</span> <br />';

            try {
                $res = $this->S3->putBucket($bucket, $acl, $region);
                $o .= 'Bucket Creation: ' . '<span class="good">Passed</span> <br />';
            } catch (Exception $e) {
                $o .= 'Bucket Creation: ' . '<span class="bad">Failed</span> <br />';
                $o .= '<em>' . (string) $e->getMessage() . '</em>  <br />';
            }
        }

        // Create The File
        $res = $this->S3->putObject('TEST', $bucket, $subdir.$file, S3::ACL_PUBLIC_READ, array(), array('Content-Type' => 'text/plain'));

        if ($res)
        {
            $o .= 'Create Test File: ' . '<span class="good">Passed</span> <br />';
        }
        else
        {
            $o .= 'Create Test File: ' . '<span class="bad">Failed</span> <br />';
            $o .= '<em>' . (string) $this->S3->response->error['message'] . '</em> <br />';
        }

        // Delete The File
        $res = $this->S3->deleteObject($bucket, $subdir.$file);
        if ($res)
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
        if (isset($this->S3) == true) {
            return true;
        }

        if ($this->lsettings['key'] == false OR $this->lsettings['secret_key'] == false OR $this->lsettings['bucket'] == false) {
            return false;
        }

        // Just to be sure
        if (class_exists('S3') === false) {
            include PATH_THIRD.'channel_images/locations/s3/s3.class.php';
        }

        // Instantiate the AmazonS3 class
        $this->S3 = new S3(trim($this->lsettings['key']), trim($this->lsettings['secret_key']), false);
        $this->S3->setExceptions(false);

        //$this->S3->set_region($this->lsettings['region']);

        // Init Configs
        if ($this->lsettings['storage'] == 'standard') {
            $this->lsettings['storage'] = S3::STORAGE_CLASS_STANDARD;
        } else {
            $this->lsettings['storage'] = S3::STORAGE_CLASS_RRS;
        }

        //$temp = $this->EE->config->item('ci_s3_storage');
        //$this->lsettings['storage'] = constant('AmazonS3::' . $temp[$this->lsettings['storage']]);

        //$temp = $this->EE->config->item('ci_s3_acl');
        //$this->lsettings['acl'] = constant('AmazonS3::' . $temp[$this->lsettings['acl']]);

        $temp = $this->EE->config->item('ci_s3_regions');
        //$this->lsettings['region'] = constant('AmazonS3::' . $temp[$this->lsettings['region']]);

        $temp = $this->EE->config->item('ci_s3_endpoints');
        $this->lsettings['endpoint'] = $temp[$this->lsettings['region']];



        return true;
    }

    // ********************************************************************************* //
}

/* End of file local.php */
/* Location: ./system/expressionengine/third_party/channel_images/locations/s3/s3.php */
