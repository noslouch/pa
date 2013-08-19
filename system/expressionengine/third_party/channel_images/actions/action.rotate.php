<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images ROTATE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_rotate extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Rotate Image',
		'name'		=>	'rotate',
		'version'	=>	'1.0',
		'enabled'	=>	TRUE,
	);

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;

		$this->size = getimagesize($file);

		$width = $this->size[0];
		$height = $this->size[1];

		if (isset($this->settings['only_if']) === TRUE)
		{
			// Do we need to rotate?
			if ($this->settings['only_if'] == 'width_bigger' && $width < $height) return TRUE;
			elseif ($this->settings['only_if'] == 'height_bigger' && $height < $width) return TRUE;
		}

		switch($this->size[2])
		{
			case 1:
				if (imagetypes() & IMG_GIF)
				{
					$this->im = imagecreatefromgif($file);
				}
				else return 'No GIF Support!';
				break;
			case 2:
				if (imagetypes() & IMG_JPG)
				{
					$this->im = imagecreatefromjpeg($file);
				}
				else return 'No JPG Support!';
				break;
			case 3:
				if (imagetypes() & IMG_PNG)
				{
					$this->im=imagecreatefrompng($file);
				}
				else return 'No PNG Support!';
				break;
			default:
				return 'File Type??';
		}

		$this->settings['background_color'];
		$this->settings['degrees'];

		$this->im = imagerotate($this->im, 360-$this->settings['degrees'], hexdec($this->settings['background_color']));

		switch($this->size[2]) {
			case 1:
				imagegif($this->im, $file);
				break;
			case 2:
				if ($progressive === TRUE) @imageinterlace($this->im, 1);
				imagejpeg($this->im, $file, 100);
				break;
			case 3:
				imagepng($this->im, $file);
				break;
		}

		imagedestroy($this->im);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['background_color']) == FALSE) $vData['background_color'] = 'ffffff';
		if (isset($vData['degrees']) == FALSE) $vData['degrees'] = '90';
		if (isset($vData['only_if']) == FALSE) $vData['only_if'] = 'always';

		return $this->EE->load->view('actions/rotate', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.rotate.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.rotate.php */
