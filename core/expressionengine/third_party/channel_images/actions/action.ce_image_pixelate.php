<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images CE IMAGE PIXELATE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_ce_image_pixelate extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'CE Image: Pixelate',
		'name'		=>	'ce_image_pixelate',
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
		if (defined('IMG_FILTER_PIXELATE') == FALSE) $this->info['enabled'] = FALSE;
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		if (class_exists('Ce_image') == FALSE) include PATH_THIRD.'ce_img/libraries/Ce_image.php';
		$CE = new Ce_image(array('cache_dir' => '', 'unique' => 'none', 'overwrite_cache' => true, 'allow_overwrite_original' => true));

		if ($this->settings['advanced'] == 'yes') $this->settings['advanced'] = TRUE;
		else $this->settings['advanced'] = FALSE;

		$CE->make($file, array(
                'filters' => array(
                        array( 'pixelate', $this->settings['block_size'], $this->settings['advanced'])
                )
        ));

		$CE->close();

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['block_size']) == FALSE) $vData['block_size'] = '0';
		if (isset($vData['advanced']) == FALSE) $vData['advanced'] = 'no';

		return $this->EE->load->view('actions/ce_image_pixelate', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.ce_image_pixelate.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.ce_image_pixelate.php */
