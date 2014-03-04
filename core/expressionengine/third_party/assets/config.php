<?php

if (! defined('ASSETS_NAME'))
{
	define('ASSETS_NAME', 'Assets');
	define('ASSETS_VER',  '2.2.4');
	define('ASSETS_DESC', 'Heavy duty asset management');
	define('ASSETS_DOCS', 'http://pixelandtonic.com/assets/docs');
}

// NSM Addon Updater
$config['name'] = ASSETS_NAME;
$config['version'] = ASSETS_VER;
$config['nsm_addon_updater']['versions_xml'] = 'http://pixelandtonic.com/assets/releasenotes.rss';
