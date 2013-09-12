<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images FILTER: GAUSSIAN_BLUR action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_filter_gaussian_blur extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Filter: Gaussian Blur',
		'name'		=>	'filter_gaussian_blur',
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

	public function run($file, $temp_dir)
	{
		$res = $this->open_image($file);
		if ($res != TRUE) return FALSE;

		if (isset($this->settings['repeat']) === true && $this->settings['repeat'] > 1) {
			for ($i=0; $i < $this->settings['repeat']; $i++) {
				@imagefilter($this->EE->channel_images->image, IMG_FILTER_GAUSSIAN_BLUR);
			}
		} else {
			@imagefilter($this->EE->channel_images->image, IMG_FILTER_GAUSSIAN_BLUR);
		}

		$this->save_image($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['repeat']) == FALSE) $vData['repeat'] = '1';

		return $this->EE->load->view('actions/ce_image_gaussian_blur', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.ce_image_gaussian_blur.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.ce_image_gaussian_blur.php */
