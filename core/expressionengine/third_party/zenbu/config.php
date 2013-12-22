<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['name'] = 'Zenbu';
$config['version'] = '1.8.5.2';
$config['nsm_addon_updater']['versions_xml'] = 'http://zenbustudio.com/software/version_check/zenbu';
if( ! defined('ZENBU_VER') )
{
	define('ZENBU_VER', $config['version']);
	define('ZENBU_NAME', $config['name']);
}