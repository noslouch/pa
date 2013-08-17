<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images WATERMARK TEXT action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_watermark_text extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Watermark (Text)',
		'name'		=>	'watermark_text',
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

		// Check Font Path
		if ($p['font_path'] == FALSE && is_file($p['font_path']) == FALSE)
		{
			if (is_file(APPPATH . 'fonts/texb.ttf') == TRUE) $p['font_path'] = APPPATH . 'fonts/texb.ttf';
		}

		$config['wm_type'] = 'text';
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

		// Text
		$config['wm_text'] = $p['text'];
		$config['wm_font_path'] 	= $p['font_path'];
		$config['wm_font_size'] 	= $p['font_size'];
		$config['wm_font_color'] 	= $p['font_color'];
		$config['wm_shadow_color'] 	= $p['shadow_color'];
		$config['wm_shadow_distance'] = $p['shadow_distance'];

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
		if (isset($vData['horizontal_alignment']) == FALSE) $vData['horizontal_alignment'] = 'right';
		if (isset($vData['vertical_alignment']) == FALSE) $vData['vertical_alignment'] = 'bottom';
		if (isset($vData['horizontal_offset']) == FALSE) $vData['horizontal_offset'] = '0';
		if (isset($vData['vertical_offset']) == FALSE) $vData['vertical_offset'] = '0';

		if (isset($vData['text']) == FALSE) $vData['text'] = 'DevDemon.Com (Channel Images)';
		if (isset($vData['font_path']) == FALSE) $vData['font_path'] = '';
		if (isset($vData['font_size']) == FALSE) $vData['font_size'] = '16';
		if (isset($vData['font_color']) == FALSE) $vData['font_color'] = 'ffffff';
		if (isset($vData['shadow_color']) == FALSE) $vData['shadow_color'] = '000000';
		if (isset($vData['shadow_distance']) == FALSE) $vData['shadow_distance'] = '3';

		return $this->EE->load->view('actions/watermark_text', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file action.watermark_text.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.watermark_text.php */
