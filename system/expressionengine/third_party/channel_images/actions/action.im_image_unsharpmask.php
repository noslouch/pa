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
class ImageAction_im_image_unsharpmask extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Imagick: Unsharp Mask',
		'name'		=>	'im_image_unsharpmask',
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
    	$image->unsharpMaskImage($this->settings['radius'], $this->settings['sigma'], $this->settings['amount'], $this->settings['threshold']);
    	$image->writeImage($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['radius']) == FALSE) $vData['radius'] = '1.5';
		if (isset($vData['sigma']) == FALSE) $vData['sigma'] = '0.75';
		if (isset($vData['amount']) == FALSE) $vData['amount'] = '1.7';
		if (isset($vData['threshold']) == FALSE) $vData['threshold'] = '0.02';

		return $this->EE->load->view('actions/im_image_unsharpmask', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.ce_image_sharpen.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.ce_image_sharpen.php */
