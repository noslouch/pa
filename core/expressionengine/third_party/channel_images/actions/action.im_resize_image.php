<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images CE IMAGE SHARPEN action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_im_resize_image extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Imagick: Resize Image',
		'name'		=>	'im_resize_image',
		'version'	=>	'1.0',
		'enabled'	=>	FALSE,
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

		if (class_exists('Imagick')) $this->info['enabled'] = TRUE;
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		$info = $this->getImageDetails($file);

		$image = new Imagick();
		$image->readImage($file);



		if ($info['ext'] == 'jpg') {
			$image->setCompression(Imagick::COMPRESSION_JPEG);
			$image->setCompressionQuality($this->settings['quality']);
			$image->setImageFormat('jpeg');
		}

		if ($info['ext'] == 'gif') {
			//remove the canvas (for .gif)
			$image->setImagePage(0, 0, 0, 0);
		}

    	//crop and resize the image
		$image->resizeImage($this->settings['width'], $this->settings['height'], Imagick::FILTER_LANCZOS, 1, true);

		$image->writeImage($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['width']) == FALSE) $vData['width'] = '100';
		if (isset($vData['height']) == FALSE) $vData['height'] = '100';
		if (isset($vData['quality']) == FALSE) $vData['quality'] = '100';

		return $this->EE->load->view('actions/im_resize_image', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.ce_image_sharpen.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.ce_image_sharpen.php */
