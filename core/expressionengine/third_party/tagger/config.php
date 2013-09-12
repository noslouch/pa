<?php

/**
 * Config file for Tagger
 *
 * @package			DevDemon_Tagger
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/tagger/
 * @see				http://ee-garage.com/nsm-addon-updater/developers
 */

if ( ! defined('DDTAGGER_NAME'))
{
	define('DDTAGGER_NAME',         'Tagger');
	define('DDTAGGER_CLASS_NAME',   'tagger');
	define('DDTAGGER_VERSION',      '3.2.1');
}

$config['name'] 	= DDTAGGER_NAME;
$config["version"] 	= DDTAGGER_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://www.devdemon.com/'.DDTAGGER_CLASS_NAME.'/versions_feed/';

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/tagger/config.php */
