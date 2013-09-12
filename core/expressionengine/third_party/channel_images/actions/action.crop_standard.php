<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images CROP STANDARD action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_crop_standard extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Crop (Standard)',
		'name'		=>	'crop_standard',
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

	public function run($file)
	{
		@set_include_path(PATH_THIRD.'channel_images/libraries/PHPThumb/');
		@set_include_path(PATH_THIRD.'channel_images/libraries/PHPThumb/thumb_plugins/');

		// Include the library
	    if (class_exists('PhpThumbFactory') == FALSE) require_once PATH_THIRD.'channel_images/libraries/PHPThumb/ThumbLib.inc.php';

	    $progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? 'yes' : 'no';

	    // Create Instance
	    $thumb = PhpThumbFactory::create($file, array('jpegQuality' => $this->settings['quality'], 'jpegProgressive' => $progressive));

	    // Resize it!
	    $thumb->crop($this->settings['start_x'], $this->settings['start_y'], $this->settings['width'], $this->settings['height']);

		// Save it
		$thumb->save($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['start_x']) == FALSE) $vData['start_x'] = '0';
		if (isset($vData['start_y']) == FALSE) $vData['start_y'] = '0';
		if (isset($vData['width']) == FALSE) $vData['width'] = '100';
		if (isset($vData['height']) == FALSE) $vData['height'] = '100';
		if (isset($vData['quality']) == FALSE) $vData['quality'] = '100';

		return $this->EE->load->view('actions/crop_standard', $vData, TRUE);
	}

	// ********************************************************************************* //


	public function save_settings($settings)
	{
		$this->EE->cache['channel_images']['group_final_size'] = $settings;
		unset($this->EE->cache['channel_images']['group_final_size']['start_x']);
		unset($this->EE->cache['channel_images']['group_final_size']['start_y']);
		return $settings;
	}

}

/* End of file action.crop_standard.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.crop_standard.php */
