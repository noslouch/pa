<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['name'] = 'Hokoku';
$config['version'] = '1.1';
$config['nsm_addon_updater']['versions_xml'] = 'http://zenbustudio.com/software/version_check/hokoku';
if( ! defined('HOKOKU_VER') )
{
	define('HOKOKU_VER', $config['version']);
	define('HOKOKU_NAME', $config['name']);
}