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
class ImageAction_im_image_resolution extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Imagick: Change Resolution (DPI/PPI)',
		'name'		=>	'im_image_resolution',
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
		$image = new Imagick();
		$image->readImage($file);
		$image->setImageUnits(Imagick::RESOLUTION_PIXELSPERINCH);
		$image->setImageResolution($this->settings['resolution'], $this->settings['resolution']);
    	$image->writeImage($file);
		$image->clear();
		$image->destroy();

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['resolution']) == FALSE) $vData['resolution'] = '72';

		return $this->EE->load->view('actions/im_image_resolution', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.im_dpi.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.im_dpi.php */
