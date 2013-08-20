<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images GREYSCALE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_greyscale extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Greyscale',
		'name'		=>	'greyscale',
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

		//if (function_exists('imagefilter') === false) $this->info['enabled'] = FALSE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		return $this->EE->lang->line('ci:greyscale:exp');
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$res = $this->open_image($file);
		if ($res != TRUE) return FALSE;

		$this->image_progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;

		if (function_exists('imagefilter') === TRUE)
		{
			@imagefilter($this->EE->channel_images->image, IMG_FILTER_GRAYSCALE);
		}
		else
		{
			$img_width  = imageSX($this->EE->channel_images->image);
	    	$img_height = imageSY($this->EE->channel_images->image);

			// convert to grayscale
        	$palette = array();
   			for ($c=0;$c<256;$c++)
			{
				$palette[$c] = imagecolorallocate($this->EE->channel_images->image,$c,$c,$c);
			}

			for ($y=0;$y<$img_height;$y++)
			{
				for ($x=0;$x<$img_width;$x++)
				{
					$rgb = imagecolorat($this->EE->channel_images->image,$x,$y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					$gs = (($r*0.299)+($g*0.587)+($b*0.114));
					imagesetpixel($this->EE->channel_images->image,$x,$y,$palette[$gs]);
				}
			}
		}

	    $this->save_image($file);

		return TRUE;
	}

	// ********************************************************************************* //


}

/* End of file action.greyscale.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.greyscale.php */
