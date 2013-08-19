<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images RESIZE PERCENT action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_resize_percent extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Resize (By Percentage)',
		'name'		=>	'resize_percent',
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
	    $thumb = PhpThumbFactory::create($file, array('resizeUp' => FALSE, 'jpegQuality' => 100, 'jpegProgressive' => $progressive));

	    // Resize it!
		$thumb->resizePercent($this->settings['percent']);

		// Save it
		$thumb->save($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['percent']) == FALSE) $vData['percent'] = '20';


		return $this->EE->load->view('actions/resize_percent', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.resize.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.resize_percent.php */
