<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images CE IMAGE GAUSSIAN_BLUR action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_ce_image_gaussian_blur extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'CE Image: Gaussian Blur',
		'name'		=>	'ce_image_gaussian_blur',
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

		if (file_exists(PATH_THIRD.'ce_img/pi.ce_img.php') != FALSE) $this->info['enabled'] = TRUE;
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		if (class_exists('Ce_image') == FALSE) include PATH_THIRD.'ce_img/libraries/Ce_image.php';
		$CE = new Ce_image(array('cache_dir' => '', 'unique' => 'none', 'overwrite_cache' => true, 'allow_overwrite_original' => true));

		if (isset($this->settings['repeat']) === true && $this->settings['repeat'] > 1) {
			for ($i=0; $i < $this->settings['repeat']; $i++) {
				$CE->make($file, array(
		                'filters' => 'gaussian_blur'
		        ));
			}
		} else {
			$CE->make($file, array(
	                'filters' => 'gaussian_blur'
	        ));
		}

		$CE->close();

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
