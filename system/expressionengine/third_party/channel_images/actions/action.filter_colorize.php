<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images COLORIZE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_filter_colorize extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Filter: Colorize',
		'name'		=>	'filter_colorize',
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

		@imagefilter($this->EE->channel_images->image, IMG_FILTER_COLORIZE, $this->settings['red'], $this->settings['green'], $this->settings['blue'], $this->settings['alpha']);

		$this->save_image($file);

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

		return $this->EE->load->view('actions/filter_colorize', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.filter_colorize.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.filter_colorize.php */
