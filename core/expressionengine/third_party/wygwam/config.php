<?php

if (! defined('WYGWAM_NAME'))
{
	define('WYGWAM_NAME', 'Wygwam');
	define('WYGWAM_VER',  '3.2.2');
	define('WYGWAM_DESC', 'Wysiwyg editor powered by CKEditor and CKFinder');
	define('WYGWAM_DOCS', 'http://pixelandtonic.com/wygwam/docs');
}

// NSM Addon Updater
$config['name'] = WYGWAM_NAME;
$config['version'] = WYGWAM_VER;
$config['nsm_addon_updater']['versions_xml'] = 'http://pixelandtonic.com/wygwam/releasenotes.rss';
