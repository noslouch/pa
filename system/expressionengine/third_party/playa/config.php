<?php

if (! defined('PLAYA_NAME'))
{
	define('PLAYA_NAME', 'Playa');
	define('PLAYA_VER',  '4.4.5');
	define('PLAYA_DESC', 'The proverbial multiple relationships field');
	define('PLAYA_DOCS', 'http://pixelandtonic.com/playa/docs');
}

// NSM Addon Updater
$config['name'] = PLAYA_NAME;
$config['version'] = PLAYA_VER;
$config['nsm_addon_updater']['versions_xml'] = 'http://pixelandtonic.com/playa/releasenotes.rss';
