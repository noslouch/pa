<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images FLIP action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_flip extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Flip Image',
		'name'		=>	'flip',
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

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['axis']) == FALSE) $vData['axis'] = 'horizontal';

		return $this->EE->load->view('actions/flip', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$res = $this->open_image($file);
		if ($res != TRUE) return FALSE;

		$this->image_progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;

		$width = $this->EE->channel_images->image_dim['width'];
		$height = $this->EE->channel_images->image_dim['height'];

		$imgdest = imagecreatetruecolor($width, $height);

		if (imagetypes() & IMG_PNG)
		{
			imagesavealpha($imgdest, true);
			imagealphablending($imgdest, false);
		}

		for ($x=0 ; $x<$width ; $x++)
		{
			for ($y=0 ; $y<$height ; $y++)
			{
				if ($this->settings['axis'] == 'both') imagecopy($imgdest, $this->EE->channel_images->image, $width-$x-1, $height-$y-1, $x, $y, 1, 1);
				else if ($this->settings['axis'] == 'horizontal') imagecopy($imgdest, $this->EE->channel_images->image, $width-$x-1, $y, $x, $y, 1, 1);
				else if ($this->settings['axis'] == 'vertical') imagecopy($imgdest, $this->EE->channel_images->image, $x, $height-$y-1, $x, $y, 1, 1);
			}
		}

		$this->EE->channel_images->image = $imgdest;

		$this->save_image($file);

		return TRUE;
	}

	// ********************************************************************************* //

}

/* End of file action.flip.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.flip.php */
