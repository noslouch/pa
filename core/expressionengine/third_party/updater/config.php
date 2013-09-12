<?php

/**
 * Config file for UPGRADER
 *
 * @package			DevDemon_Upgrader
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/updater/
 * @see				http://ee-garage.com/nsm-addon-updater/developers
 */

if ( ! defined('UPDATER_NAME'))
{
	define('UPDATER_NAME',         'Updater');
	define('UPDATER_CLASS_NAME',   'updater');
	define('UPDATER_VERSION',      '3.2.6');
}

$config['name'] 	= UPDATER_NAME;
$config["version"] 	= UPDATER_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://www.devdemon.com/'.UPDATER_CLASS_NAME.'/versions_feed/';

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/updater/config.php */
