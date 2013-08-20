<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_images/config'.EXT;

/**
 * Channel Images Module RTE
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Channel_images_rte
{

	public $info = array(
		'name'			=> 'Channel Images',
		'version'		=> CHANNEL_IMAGES_VERSION,
		'description'	=> 'Allows you to use images uploaded through Channel Images in the RTE',
		'cp_only'		=> 'n'
	);

	private $EE;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		// Make a local reference of the ExpressionEngine super object
		$this->EE =& get_instance();

		$this->EE->load->add_package_path(PATH_THIRD . 'channel_images/');
		$this->EE->lang->loadfile('channel_images');
		$this->EE->load->library('image_helper');
		$this->EE->image_helper->define_theme_url();
	}

	// ********************************************************************************* //

	/**
	 * Globals we need
	 *
	 * @access	public
	 */
	function globals()
	{
		return array(
			'rte.channel_images'=> array(
				'label'			=> lang('channel_images'),
				'add_image'		=> lang('ci:add_image'),
				'no_images'		=> lang('ci:no_images'),
				'original'		=> lang('ci:original'),
				'caption_text'	=> lang('ci:caption_text'),
			)
		);
	}

	// ********************************************************************************* //

	function libraries()
	{
		return array(
			'ui'	=> array('dialog', 'tabs')
		);
	}

	// ********************************************************************************* //

	/**
	 * Styles we need
	 *
	 * @access	public
	 */
	function styles()
	{
		$styles	= file_get_contents(PATH_THIRD_THEMES.'channel_images/channel_images_rte.css', TRUE );
		return str_replace('{theme_folder_url}', CHANNELIMAGES_THEME_URL, $styles);
	}

	// ********************************************************************************* //

	/**
	 * JS Defintion
	 *
	 * @access	public
	 */
	function definition()
	{
		return file_get_contents(PATH_THIRD_THEMES.'channel_images/channel_images_rte.js', TRUE );
	}

	// ********************************************************************************* //


} // END Channel_images_rte

/* End of file rte.channel_images.php */
/* Location: ./system/expressionengine/third_party/channel_images/rte.channel_images.php */