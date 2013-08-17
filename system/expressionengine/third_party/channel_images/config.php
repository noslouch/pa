<?php

/**
 * Config file for Channel Images
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 * @see				http://ee-garage.com/nsm-addon-updater/developers
 */

if ( ! defined('CHANNEL_IMAGES_NAME'))
{
	define('CHANNEL_IMAGES_NAME',         'Channel Images');
	define('CHANNEL_IMAGES_CLASS_NAME',   'channel_images');
	define('CHANNEL_IMAGES_VERSION',      '5.4.2');
}

$config['name'] 	= CHANNEL_IMAGES_NAME;
$config["version"] 	= CHANNEL_IMAGES_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://www.devdemon.com/'.CHANNEL_IMAGES_CLASS_NAME.'/versions_feed/';

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/channel_images/config.php */
