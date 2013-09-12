<?php

/**
 * Low Reorder config file
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */

if ( ! defined('LOW_REORDER_NAME'))
{
	define('LOW_REORDER_NAME',    'Low Reorder');
	define('LOW_REORDER_PACKAGE', 'low_reorder');
	define('LOW_REORDER_VERSION', '2.1.1');
	define('LOW_REORDER_DOCS',    'http://gotolow.com/addons/low-reorder');
	define('LOW_REORDER_DEBUG',    TRUE);
}

$config['name']    = LOW_REORDER_NAME;
$config['version'] = LOW_REORDER_VERSION;

$config['nsm_addon_updater']['versions_xml'] = LOW_REORDER_DOCS.'/feed';
