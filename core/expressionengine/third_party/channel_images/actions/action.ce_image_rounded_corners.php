<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images CE IMAGE REFLECTION action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_ce_image_rounded_corners extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'CE Image: Rounded Corners',
		'name'		=>	'ce_image_rounded_corners',
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

		if (file_exists(PATH_THIRD.'ce_img/pi.ce_img.php') != FALSE) $this->info['enabled'] = TRUE;
		$this->info['enabled'] = FALSE; //Disable it temp
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{


		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['corner_identifier']) == FALSE) $vData['corner_identifier'] = 'all';
		if (isset($vData['radius']) == FALSE) $vData['radius'] = '30';
		if (isset($vData['color']) == FALSE) $vData['color'] = 'ffffff';

		return $this->EE->load->view('actions/ce_image_rounded_corners', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.ce_image_rounded_corners.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.ce_image_rounded_corners.php */
