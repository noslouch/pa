<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Location Class
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class Image_Location
{
	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct($settings=array())
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD . 'channel_images/');
		$this->EE->load->library('image_helper');
	}

	// ********************************************************************************* //

	public function create_dir($dir)
	{
		return FALSE;
	}

	// ********************************************************************************* //

	public function delete_dir($dir)
	{
		return FALSE;
	}

	// ********************************************************************************* //

	public function upload_file($source_file, $dest_filename, $dest_folder)
	{
		return FALSE;
	}

	// ********************************************************************************* //

	public function download_file($dir, $filename, $dest_folder)
	{
		return FALSE;
	}

	// ********************************************************************************* //

	public function delete_file($dir, $filename)
	{
		return FALSE;
	}

	// ********************************************************************************* //

	public function parse_image_url($dir, $filename)
	{
		return '';
	}

	// ********************************************************************************* //

	public function test_location()
	{
		exit('TEST FAILED!');
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file image_location.php  */
/* Location: ./system/expressionengine/third_party/channel_images/locations/image_location.php */