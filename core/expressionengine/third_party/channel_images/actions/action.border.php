<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images BORDER action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class ImageAction_border extends ImageAction
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Border',
		'name'		=>	'border',
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

		$border_width = $this->settings['thickness'];
		$border_color = $this->settings['color'];
		$width_old = $this->EE->channel_images->image_dim['width'];
		$height_old = $this->EE->channel_images->image_dim['height'];

		$old = imagecreatetruecolor($width_old, $height_old);
		imagecopy($old, $this->EE->channel_images->image, 0, 0, 0, 0, $width_old, $height_old);
		imagedestroy($this->EE->channel_images->image);

		$border_width = round( $border_width );
		//border color
		$width = round( $width_old + $border_width * 2 );
		$height = round( $height_old + $border_width * 2);

		$this->EE->channel_images->image = imagecreatetruecolor($width, $height);
		imagealphablending($this->EE->channel_images->image, true);
		imagesavealpha($this->EE->channel_images->image, false);

		$color = $this->hex_to_rgb( $border_color, 'fff' );
		$border_color = imagecolorallocate($this->EE->channel_images->image, $color[0], $color[1], $color[2]);

		//top border
		imagefilledrectangle( $this->EE->channel_images->image, 0, 0, $width, $border_width, $border_color );
		//right border
		imagefilledrectangle( $this->EE->channel_images->image, $width_old + $border_width, $border_width, $width, $height_old + $border_width, $border_color );
		//bottom border
		imagefilledrectangle( $this->EE->channel_images->image, 0, $height_old + $border_width, $width, $height, $border_color );
		//left border
		imagefilledrectangle( $this->EE->channel_images->image, 0, $border_width, $border_width, $height_old + $border_width, $border_color );
		//copy image over top
		imagecopyresized( $this->EE->channel_images->image, $old, $border_width, $border_width, 0, 0, $width_old, $height_old, $width_old, $height_old );

		imagedestroy($old);

		$this->save_image($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['thickness']) == FALSE) $vData['thickness'] = '1';
		if (isset($vData['color']) == FALSE) $vData['color'] = '000000';

		return $this->EE->load->view('actions/ce_image_border', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Cleans up a hex value and converts it to RGB
	 *
	 * @static
	 * @param string $hex Hexadecimal color value.
	 * @param string $default_hex Fall-back hexadecimal color value.
	 * @return array|bool Returns an array on success with the values for red, green, and blue. Returns false on failure.
	 */
	private function hex_to_rgb( $hex, $default_hex = '' )
	{
		$hex = $this->hex_cleanup( $hex );
		if ( $hex == false )
		{
			if ( $default_hex != '' )
			{
				return $this->hex_to_rgb( $default_hex );
			}
			else
			{
				return false;
			}
		}
		return sscanf($hex, '%2x%2x%2x');
	}

	/**
	 * Takes a 3 or 6 digit hex color value, strips off the # (if applicable), and returns a 6 digit hex or ''
	 *
	 * @static
	 * @param string $hex A 3 or 6 digit color value.
	 * @return string A 6 digit hex color value or ''.
	 */
	private function hex_cleanup( $hex )
	{
		$hex = ( preg_match('/#?[0-9a-fA-F]{3,6}/', $hex) ) ? $hex : false;
		if ( ! $hex )
		{
			return false;
		}
		if ($hex[0] == '#')
		{
			$hex = substr($hex, 1);	//trim off #
		}
		if (strlen($hex) == 3)
		{
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if (strlen($hex) != 6)
		{
			$hex = false;
		}
		return $hex;
	}

	/**
	 * Converts RGB color values to a hexadecimal color format.
	 *
	 * @static
	 * @param int $red The red color value. Ranges from 0 to 255.
	 * @param int $green The green color value. Ranges from 0 to 255.
	 * @param int $blue The blue color value. Ranges from 0 to 255.
	 * @param bool $prepend_hash Whether or not to prepend '#' to the hex color value. Defaults to true.
	 * @return string The hexadecimal color.
	 */
	private function rgb_to_hex($red, $green, $blue, $prepend_hash = true )
	{
		return (( $prepend_hash ) ? '#' : '') . str_pad(dechex($red), 2, '0', STR_PAD_LEFT) . str_pad(dechex($green), 2, '0', STR_PAD_LEFT) . str_pad(dechex($blue), 2, '0', STR_PAD_LEFT);
	}
}

/* End of file action.crop_center.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/action.crop_center.php */
