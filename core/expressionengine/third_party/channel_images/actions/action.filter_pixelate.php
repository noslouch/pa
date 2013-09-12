<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images FILTER: PIXELATE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_filter_pixelate extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Filter: Pixelate',
		'name'		=>	'filter_pixelate',
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

		if (defined('IMG_FILTER_PIXELATE') == FALSE) $this->info['enabled'] = FALSE;
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		$res = $this->open_image($file);
		if ($res != TRUE) return FALSE;

		@imagefilter($this->EE->channel_images->image, IMG_FILTER_PIXELATE, $this->settings['block_size'], $this->settings['advanced']);

		$this->save_image($file);

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

/* End of file action.filter_pixelate.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.filter_pixelate.php */
