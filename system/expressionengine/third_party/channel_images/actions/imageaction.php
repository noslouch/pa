<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Action File
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction
{
	public $image_jpeg_quality = 100;
	public $image_progressive = FALSE;

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'',
		'name'		=>	'',
		'version'	=>	'1.0',
		'enabled'	=>	TRUE,
	);

	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->field_name = 'channel_images[action_groups][][actions]['.$this->info['name'].']';

		if (isset($this->EE->channel_images->image) == FALSE)
		{
			$this->EE->channel_images->image = false;
			$this->EE->channel_images->image_path = '';
			$this->EE->channel_images->image_ext = '';
			$this->EE->channel_images->image_dim = array();
		}
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		return TRUE;
	}

	// ********************************************************************************* //

	public function settings()
	{
		return '';
	}

	// ********************************************************************************* //

	public function display_settings($settings=array())
	{
		// Final Output
		$out = '';

		$action_path = PATH_THIRD . 'channel_images/actions/' . $this->info['name'] . '/';


		// Only for old EE2 versions!
		if (version_compare(APP_VER, '2.1.5', '<'))
		{
			$this->EE->load->_ci_view_path = $action_path.'views/';

		}

		// Add package path (so view files can render properly)
		$this->EE->load->add_package_path($action_path);

		// Do we need to load LANG file?
		if (@is_dir($action_path . 'language/') == TRUE)
		{
			$this->EE->lang->load($this->info['name'], $this->EE->lang->user_lang, FALSE, TRUE, $action_path);
		}


		// Add some global vars!
		$vars = array();
		$vars['action_field_name'] = $this->field_name;
		$this->EE->load->vars($vars);

		// Execute the settings method
		$out = $this->settings($settings);

		// Cleanup by removing
		$this->EE->load->remove_package_path($action_path);

		return $out;
	}

	// ********************************************************************************* //

	public function save_settings($settings)
	{
		return $settings;
	}

	// ********************************************************************************* //

	public function open_image($file)
	{
		// Is it already open?
		if ($this->EE->channel_images->image_path == $file) return TRUE;

		// Is there another once open? close it!
		if (is_resource($this->EE->channel_images->image) === TRUE)
		{
			@imagedestroy($this->EE->channel_images->image);
		}

		$data = @getimagesize($file);
		if (!$data) return FALSE;

		$this->EE->channel_images->image_dim['width'] = $data[0];
		$this->EE->channel_images->image_dim['height'] = $data[1];

		// Get the image extension
		$this->EE->channel_images->image_ext = '';

		switch ($data[2])
		{
			case IMAGETYPE_GIF:
				$this->EE->channel_images->image_ext = 'gif';
				break;
			case IMAGETYPE_PNG:
				$this->EE->channel_images->image_ext = 'png';
				break;
			case IMAGETYPE_JPEG:
				$this->EE->channel_images->image_ext = 'jpg';
				break;
			default:
				return FALSE;
		}

		//open the file and create main image handle
		switch ($this->EE->channel_images->image_ext)
		{
			case 'gif':
				$this->EE->channel_images->image = @imagecreatefromgif($file);
				break;
			case 'png':
				$this->EE->channel_images->image = @imagecreatefrompng($file);
				break;
			case 'jpg':
			case 'jpeg':
				$this->EE->channel_images->image = @imagecreatefromjpeg($file);
				break;
			default:
				return FALSE;
		}

		if ($this->EE->channel_images->image == false) return FALSE;

		// Store it..
		$this->EE->channel_images->image_path = $file;

		return TRUE;
	}

	// ********************************************************************************* //

	public function save_image($dest_file='', $resource=FALSE, $extension=FALSE)
	{
		if ($resource == FALSE)
		{
			$resource =& $this->EE->channel_images->image;
			$extension = $this->EE->channel_images->image_ext;
		}

		if ($this->image_jpeg_quality == FALSE)
		{
			$this->image_jpeg_quality = 100;
		}

		switch ($extension)
		{
			case 'gif':
				imagegif($resource, $dest_file);
				break;
			case 'png':
				imagepng($resource, $dest_file);
				break;
			case 'jpg':
			case 'jpeg':

				// Do we need to store progressive jpeg?
				if ($this->image_progressive == TRUE) @imageinterlace($resource, 1);

				imagejpeg($resource, $dest_file, $this->image_jpeg_quality);
				break;
			default:
				return FALSE;
		}

		// We don't do image destory because we might work on it..

		return TRUE;
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file imageaction.php  */
/* Location: ./system/expressionengine/third_party/channel_images/actions/imageaction.php */
