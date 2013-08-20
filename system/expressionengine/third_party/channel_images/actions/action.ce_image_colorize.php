<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images CE IMAGE COLORIZE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_ce_image_colorize extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'CE Image: Colorize',
		'name'		=>	'ce_image_colorize',
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
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		if (class_exists('Ce_image') == FALSE) include PATH_THIRD.'ce_img/libraries/Ce_image.php';
		$CE = new Ce_image(array('cache_dir' => '', 'unique' => 'none', 'overwrite_cache' => true, 'allow_overwrite_original' => true));

		if (isset($this->settings['alpha']) == FALSE)
		{
			$this->settings['alpha'] = 0;
		}

		$CE->make($file,
			array(
                'filters' => array(
                        array( 'colorize', $this->settings['red'], $this->settings['green'], $this->settings['blue'], $this->settings['alpha'])
                )
        ), true);

		$CE->close();

		//print_r($CE->debug_messages);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['red']) == FALSE) $vData['red'] = '0';
		if (isset($vData['green']) == FALSE) $vData['green'] = '0';
		if (isset($vData['blue']) == FALSE) $vData['blue'] = '0';
		if (isset($vData['alpha']) == FALSE) $vData['alpha'] = '0';

		return $this->EE->load->view('actions/ce_image_colorize', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.ce_image_colorize.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.ce_image_colorize.php */
