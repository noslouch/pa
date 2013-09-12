<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images WATERMARK IMAGE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_watermark_image extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Watermark (Image)',
		'name'		=>	'watermark_image',
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
		$this->EE->load->library('image_lib');

		$p = $this->settings;

		$config['wm_type'] = 'overlay';
		$config['source_image'] = $file;
		$config['dynamic_output'] = FALSE;
		$config['quality'] = '100%';
		$config['padding'] = $p['padding'];
		$config['wm_hor_offset'] 	= $p['horizontal_offset'];
		$config['wm_vrt_offset'] 	= $p['vertical_offset'];

		/*
		switch ($p['vertical_alignment'])
		{
			case 'center':
				$config['wm_vrt_alignment'] = 'C';
				break;
			case 'left':
				$config['wm_vrt_alignment'] = 'L';
				break;
			case 'right':
				$config['wm_vrt_alignment'] = 'R';
				break;
			default:
				$config['wm_vrt_alignment'] = 'C';
				break;
		}

		switch ($p['horizontal_alignment'])
		{
			case 'top':
				$config['wm_hor_alignment'] = 'T';
				break;
			case 'middle':
				$config['wm_hor_alignment'] = 'M';
				break;
			case 'bottom':
				$config['wm_hor_alignment'] = 'B';
				break;
			default:
				$config['wm_hor_alignment'] = 'B';
				break;
		}
		*/

		$config['wm_vrt_alignment'] = $p['vertical_alignment'];
		$config['wm_hor_alignment'] = $p['horizontal_alignment'];

		// Overlay
		$config['wm_overlay_path'] = $p['overlay_path'];
		$config['wm_opacity']	= $p['opacity'];
		$config['wm_x_transp'] 	= $p['x_transp'];
		$config['wm_y_transp'] 	= $p['y_transp'];

		$this->EE->image_lib->initialize($config);
		$this->EE->image_lib->watermark();
		$this->EE->image_lib->clear();

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['padding']) == FALSE) $vData['padding'] = '0';
		if (isset($vData['horizontal_alignment']) == FALSE) $vData['horizontal_alignment'] = '';
		if (isset($vData['vertical_alignment']) == FALSE) $vData['vertical_alignment'] = '';
		if (isset($vData['horizontal_offset']) == FALSE) $vData['horizontal_offset'] = '0';
		if (isset($vData['vertical_offset']) == FALSE) $vData['vertical_offset'] = '0';

		if (isset($vData['overlay_path']) == FALSE) $vData['overlay_path'] = '';
		if (isset($vData['opacity']) == FALSE) $vData['opacity'] = '50';
		if (isset($vData['x_transp']) == FALSE) $vData['x_transp'] = '4';
		if (isset($vData['y_transp']) == FALSE) $vData['y_transp'] = '4';

		return $this->EE->load->view('actions/watermark_image', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.watermark_image.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.watermark_image.php */
